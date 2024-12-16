<?php
/**
 * @package Tagging
 * @subpackage Collection
 */

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use Application\Area\Tags\ViewTag\BaseTagSettingsScreen;
use Application\Area\Tags\ViewTag\BaseTagTreeScreen;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Area\Tags\BaseViewTagScreen;
use DBHelper_BaseRecord;
use UI;
use UI\Tree\TreeNode;
use UI\Tree\TreeRenderer;

/**
 * @package Tagging
 * @subpackage Collection
 *
 * @property TagCollection $collection
 * @method TagCollection getCollection()
 */
class TagRecord extends DBHelper_BaseRecord
{
    public const ERROR_CANNOT_INHERIT_SORTING_WITHOUT_PARENT = 152101;

    public function setSortType(TagSortType $sortType) : self
    {
        $this->setRecordKey(TagCollection::COL_SORT_TYPE, $sortType->getID());
        return $this;
    }

    protected function init() : void
    {
    }

    // region Utility methods

    public function getSortTypeID() : string
    {
        return $this->getRecordStringKey(TagCollection::COL_SORT_TYPE);
    }

    public function getSortType() : TagSortType
    {
        $type = TagSortTypes::getInstance()->getByID($this->getSortTypeID());

        if(!$type->isInherited()) {
            return $type;
        }

        $parent = $this->getParentTag();
        if($parent !== null) {
            return $parent->getSortType();
        }

        throw new TaggingException(
            'A tag without parent cannot inherit sorting settings.',
            sprintf(
                'Tag [%s] is set to inherit sorting from its parent.',
                $this->getIdentification()
            ),
            self::ERROR_CANNOT_INHERIT_SORTING_WITHOUT_PARENT
        );
    }

    public function setSortWeight(int $weight) : self
    {
        $this->setRecordKey(TagCollection::COL_SORT_WEIGHT, $weight);
        return $this;
    }

    public function getSortWeight() : int
    {
        return $this->getRecordIntKey(TagCollection::COL_SORT_WEIGHT);
    }

    public function setWeight(int $weight) : self
    {
        $this->setRecordKey(TagCollection::COL_WEIGHT, $weight);
        return $this;
    }

    public function getWeight() : int
    {
        return $this->getRecordIntKey(TagCollection::COL_WEIGHT);
    }

    /**
     * @return TagRecord[]
     */
    public function getSubTags() : array
    {
        return $this->getSubTagCriteria()->getItemsObjects();
    }

    public function getSubTagCriteria() : TagCriteria
    {
        $sortType = $this->getSortType();

        return AppFactory::createTags()
            ->getFilterCriteria()
            ->selectParentTag($this)
            ->setOrderBy($sortType->getSortColumn(), $sortType->getSortDirection());
    }

    public function hasSubTags() : bool
    {
        return !empty($this->getSubTags());
    }

    /**
     * Fetches all sub tags recursively, in a flat list
     * sorted alphabetically.
     *
     * WARNING: This does NOT use the tag's sorting settings.
     *
     * @return TagRecord[]
     */
    public function getSubTagsRecursive() : array
    {
        $tags = array();

        foreach($this->getSubTags() as $tag)
        {
            $tags[] = $tag;
            array_push($tags, ...$tag->getSubTagsRecursive());
        }

        usort($tags, function(TagRecord $a, TagRecord $b) {
            return strnatcasecmp($a->getLabel(), $b->getLabel());
        });

        return $tags;
    }

    public function isSubTagOf(TagRecord $tag) : bool
    {
        $parentTag = $this->getParentTag();

        if($parentTag === null) {
            return false;
        }

        if($parentTag->getID() === $tag->getID()) {
            return true;
        }

        return $parentTag->isSubTagOf($tag);
    }

    /**
     * Fetches all parent tags recursively, in a flat list
     * from closest to furthest.
     *
     * @return TagRecord[]
     */
    public function getParentTags() : array
    {
        $tags = array();
        $active = $this;

        while($active->getParentTag() !== null)
        {
            $active = $active->getParentTag();
            $tags[] = $active;
        }

        return $tags;
    }

    public function addSubTag(string $label) : TagRecord
    {
        return AppFactory::createTags()->createNewTag($label, $this);
    }

    public function getLabel(): string
    {
        return $this->getRecordStringKey(TagCollection::COL_LABEL);
    }

    public function getLabelLinked() : string
    {
        return (string)sb()
            ->link($this->getLabel(), $this->getAdminURL());
    }

    public function getParentTagID() : ?int
    {
        $id = $this->getRecordIntKey(TagCollection::COL_PARENT_TAG_ID);
        if($id > 0) {
            return $id;
        }

        return null;
    }

    public function getParentTag() : ?TagRecord
    {
        $parentTagID = $this->getParentTagID();

        if($parentTagID !== null) {
            return AppFactory::createTags()->getByID($parentTagID);
        }

        return null;
    }

    public function getRootTag() : TagRecord
    {
        $parentTag = $this->getParentTag();
        if($parentTag === null) {
            return $this;
        }

        return $parentTag->getRootTag();
    }

    public function isRootTag() : bool
    {
        return $this->getParentTag() === null;
    }

    // endregion

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    // region Admin URLs

    public function getAdminURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_MODE] = BaseViewTagScreen::URL_NAME;
        $params[TagCollection::PRIMARY_NAME] = $this->getID();

        return $this->collection->getAdminURL($params);
    }

    public function getAdminSettingsURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_SUBMODE] = BaseTagSettingsScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminTagTreeURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_SUBMODE] = BaseTagTreeScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminCreateSubTagURL(array $params=array()) : string
    {
        return AppFactory::createTags()
            ->getAdminCreateSubTagURL($this, $params);
    }

    public function getAdminDeleteURL(?int $tagID=null, array $params=array()) : string
    {
        if($tagID === null) {
            $tagID = $this->getID();
        }

        if(!$this->isRootTag()) {
            return $this->getRootTag()->getAdminDeleteURL($tagID, $params);
        }

        $params[BaseTagTreeScreen::REQUEST_PARAM_DELETE_TAG] = $tagID;

        return $this->getAdminTagTreeURL($params);
    }

    // endregion

    public function createTreeRenderer() : TreeRenderer
    {
        return UI::getInstance()->createTreeRenderer($this->createNodeTree());
    }

    public function createNodeTree(?TreeNode $parent=null) : TreeNode
    {
        if($parent === null) {
            $parent = TreeNode::create(UI::getInstance(), $this->getLabel());
            $node = $parent;
        } else {
            $node = $parent->createChildNode($this->getLabel());
        }

        $this->configureTreeNode($node);

        $subTags = $this->getSubTags();
        foreach($subTags as $subTag) {
            $subTag->createNodeTree($node);
        }

        return $node;
    }

    public function getFormValues(): array
    {
        $values = parent::getFormValues();

        // Force the settings manager to use the default sort type
        // value: Since values can be empty strings, the manager
        // will use the default value only if the form value is not set.
        if(empty($values[TagCollection::COL_SORT_TYPE])) {
            $values[TagCollection::COL_SORT_TYPE] = null;
        }

        return $values;
    }

    private function configureTreeNode(TreeNode $node) : void
    {
        $node->setValue($this->getID());

        $node->link($this->getAdminTagTreeURL());

        $node->addButton(
            UI::button()
                ->setIcon(UI::icon()->add())
                ->setTooltip(t('Add a sub-tag to %1$s', $node->getLabel()))
                ->link($this->getAdminCreateSubTagURL())
        );

        $node->addButton(
            UI::button()
                ->setIcon(UI::icon()->delete())
                ->makeDangerous()
                ->makeConfirm(t('Are you sure you want to delete %1$s and all its sub-tags?', sb()->bold($this->getLabel())))
                ->setTooltip(t('Delete this tag and all its sub-tags.'))
                ->link($this->getAdminDeleteURL())
                ->requireFalse($this->isRootTag())
        );
    }
}
