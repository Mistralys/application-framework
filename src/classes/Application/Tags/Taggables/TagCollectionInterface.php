<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\Tags\Events\BaseRegisterTagCollectionsListener;
use Application\Tags\TagCollectionRegistry;
use Application\Tags\TaggingException;
use Application\Tags\TagRecord;
use AppUtils\Interfaces\StringPrimaryRecordInterface;
use UI;
use UI\Tree\TreeNode;
use UI\Tree\TreeRenderer;

/**
 * @see TagCollectionTrait
 */
interface TagCollectionInterface extends StringPrimaryRecordInterface
{
    public function getCollectionID() : string;

    /**
     * Offline event listener class used to register this
     * collection in the global tag collections list ({@see TagCollectionRegistry}).
     *
     * These classes typically extend the class {@see BaseRegisterTagCollectionsListener}.
     *
     * @return string
     */
    public function getCollectionRegistrationClass() : string;

    public function getTaggableTypeLabel() : string;

    public function getTaggableByID(int $id) : TaggableInterface;

    /**
     * Optional, custom class that extends {@see TagConnector}.
     * @return class-string|NULL
     */
    public function getTagConnectorClass() : ?string;

    public function getTagConnector() : TagConnector;

    /**
     * Primary key of the record that is being tagged.
     * @return string
     */
    public function getTagPrimary() : string;

    /**
     * Name of the table storing the record-tag connections.
     * @return string
     */
    public function getTagTable() : string;

    /**
     * Name of the source table where the record entries are stored.
     * @return string
     */
    public function getTagSourceTable() : string;

    /**
     * Gets the root tag used for this collection: When tagging
     * records, subtags of this tag will be used.
     *
     * In principle, every type of record should have its own
     * matching root tag, but the same can be used for multiple
     * record types.
     *
     * @return TagRecord|NULL
     */
    public function getRootTag() : ?TagRecord;
    public function getRootTagID() : ?int;

    public function setRootTag(?TagRecord $tag) : void;
    public function isTaggingEnabled() : bool;

    /**
     * Like {@see getRootTag()}, but throws an exception if the
     * root tag is not set.
     *
     * @return TagRecord
     * @throws TaggingException {@see TaggingException::ERROR_ROOT_TAG_NOT_SET}
     */
    public function requireRootTag() : TagRecord;

    /**
     * Creates a tree of tags for use in the media tagging screens,
     * using the media root tag's subtags.
     *
     * @param UI|NULL $ui
     * @return TreeNode
     */
    public function createTagTree(?UI $ui=null) : TreeNode;

    public function createTreeRenderer(?UI $ui=null) : TreeRenderer;

    /**
     * Gets an array of all tags available for tagging documents,
     * in a flat list sorted alphabetically.
     *
     * @return TagRecord[]
     */
    public function getAvailableTags() : array;

    public function hasAvailableTags() : bool;

    /**
     * Gets the name of the key to use in the {@see TagRegistry}
     * for this collection. It determines which root tag will be
     * used when tagging records.
     *
     * @return string
     */
    public function getTagRegistryKey() : string;

    public function getAdminEditTagsURL(array $params=array()) : string;
}
