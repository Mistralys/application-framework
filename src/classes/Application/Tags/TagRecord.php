<?php
/**
 * @package Application
 * @subpackage Tags
 */

declare(strict_types=1);

namespace Application\Tags;

use Application\AppFactory;
use Application\Area\Tags\ViewTag\BaseTagSettingsScreen;
use Application\Area\Tags\ViewTag\BaseTagTreeScreen;
use Application_Admin_ScreenInterface;
use Application\Area\Tags\BaseViewTagScreen;
use DBHelper_BaseRecord;

/**
 * @package Application
 * @subpackage Tags
 *
 * @property TagCollection $collection
 * @method TagCollection getCollection()
 */
class TagRecord extends DBHelper_BaseRecord
{
    protected function init() : void
    {
    }

    public function getSubTags() : array
    {
        return $this->getSubTagCriteria()->getItemsObjects();
    }

    public function getSubTagCriteria() : TagCriteria
    {
        return AppFactory::createTags()
            ->getFilterCriteria()
            ->selectParentTag($this);
    }

    /**
     * Fetches all sub tags recursively, in a flat list
     * sorted alphabetically.
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

    protected function recordRegisteredKeyModified($name, $label, $isStructural, $oldValue, $newValue)
    {
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = BaseViewTagScreen::URL_NAME;
        $params[TagCollection::PRIMARY_NAME] = $this->getID();

        return $this->collection->getAdminURL($params);
    }

    public function getAdminSettingsURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseTagSettingsScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminTagTreeURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = BaseTagTreeScreen::URL_NAME;

        return $this->getAdminURL($params);
    }

    public function getAdminCreateSubTagURL(array $params=array()) : string
    {
        return AppFactory::createTags()
            ->getAdminCreateSubTagURL($this, $params);
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

    public function createTreeRenderer() : TagTreeRenderer
    {
        return new TagTreeRenderer($this);
    }
}
