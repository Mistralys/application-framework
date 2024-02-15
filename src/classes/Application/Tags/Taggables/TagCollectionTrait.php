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

        $this->setUpTagging();

        $this->handleTaggingInitialized($connector);

        return $connector;
    }

    abstract protected function handleTaggingInitialized(TagConnector $connector) : void;

    public function getRootTag(): TagRecord
    {
        $this->setUpTagging();

        return TagRegistry::getTagByKey($this->getTagRegistryKey());
    }

    public function createTreeRenderer(?UI $ui=null) : TreeRenderer
    {
        return new TreeRenderer($ui, $this->createTagTree($ui));
    }

    public function createTagTree(?UI $ui=null) : TreeNode
    {
        $rootTag = AppFactory::createMedia()->getRootTag();

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
        return $this->getRootTag()->getSubTagsRecursive();
    }

    public function hasAvailableTags() : bool
    {
        return $this->getRootTag()->hasSubTags();
    }

    /**
     * Sets up tagging support for the media management in the current
     * application. This creates the media root tag used to tag documents.
     *
     * NOTE: This is called automatically when the media tagging feature
     * is accessed, so it is not necessary to call this manually. This
     * method exists to trigger the setup when visiting the tagging screens,
     * so the media tag is there when needed.
     *
     * @return void
     * @throws TaggingException
     */
    private function setUpTagging() : void
    {
        $key = $this->getTagRegistryKey();

        if(TagRegistry::isKeyRegistered($key)) {
            return;
        }

        TagRegistry::registerKey($key, $this->getRootTagLabelInvariant());
    }

    public function getAdminEditTagsURL(array $params=array()) : string
    {
        return $this->getRootTag()->getAdminTagTreeURL($params);
    }
}
