<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\AppFactory;
use Application\Tags\TaggingException;
use Application\Tags\TagRecord;
use Application\Tags\TagRegistry;
use AppUtils\ClassHelper;
use UI;
use UI\Tree\TreeNode;
use UI\Tree\TreeRenderer;

/**
 * @see TagCollectionInterface
 */
trait TagCollectionTrait
{
    private ?TagConnector $tagConnector = null;

    public function getTagConnector() : TagConnector
    {
        if(isset($this->tagConnector)) {
            return $this->tagConnector;
        }

        $class = $this->getTagConnectorClass();
        if($class === null) {
            $class = TagConnector::class;
        }

        $connector = ClassHelper::requireObjectInstanceOf(
            TagConnector::class,
            new $class($this)
        );

        $this->tagConnector = $connector;

        return $connector;
    }

    public function isTaggingEnabled() : bool
    {
        return $this->getRootTag() !== null;
    }

    public function getRootTag(): ?TagRecord
    {
        return TagRegistry::getTagByKey($this->getTagRegistryKey());
    }

    public function getRootTagID() : ?int
    {
        $tag = $this->getRootTag();
        if($tag !== null) {
            return $tag->getID();
        }

        return null;
    }

    public function requireRootTag() : TagRecord
    {
        $tag = $this->getRootTag();

        if($tag !== null) {
            return $tag;
        }

        throw new TaggingException(
            'Root tag not set for tag collection',
            '',
            TaggingException::ERROR_ROOT_TAG_NOT_SET
        );
    }

    public function setRootTag(?TagRecord $tag) : void
    {
        TagRegistry::setTagByKey($this->getTagRegistryKey(), $tag);
    }

    public function createTreeRenderer(?UI $ui=null) : TreeRenderer
    {
        if($ui === null) {
            $ui = UI::getInstance();
        }

        return $ui->createTreeRenderer($this->createTagTree($ui));
    }

    public function createTagTree(?UI $ui=null) : TreeNode
    {
        $rootTag = $this->requireRootTag();

        $rootNode = new TreeNode($ui, $rootTag->getLabel());

        $this->addTreeNodesRecursive($rootNode, $rootTag);

        return $rootNode;
    }

    private function addTreeNodesRecursive(TreeNode $node, TagRecord $tag) : void
    {
        foreach($tag->getSubTags() as $subTag)
        {
            $subNode = $node->createChildNode($subTag->getLabel())
                ->setValue($subTag->getID());

            $this->addTreeNodesRecursive($subNode, $subTag);
        }
    }

    /**
     * Gets an array of all tags available for tagging documents,
     * in a flat list sorted alphabetically.
     *
     * @return TagRecord[]
     */
    public function getAvailableTags() : array
    {
        $root = $this->getRootTag();
        if($root !== null) {
            return $root->getSubTagsRecursive();
        }

        return array();
    }

    public function hasAvailableTags() : bool
    {
        $root = $this->getRootTag();
        if($root !== null) {
            return $root->hasSubTags();
        }

        return false;
    }

    public function getAdminEditTagsURL(array $params=array()) : string
    {
        return $this->requireRootTag()->getAdminTagTreeURL($params);
    }
}
