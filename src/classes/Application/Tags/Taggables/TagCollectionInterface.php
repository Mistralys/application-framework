<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use Application\Tags\TagRecord;
use UI;
use UI\Tree\TreeNode;
use UI\Tree\TreeRenderer;

/**
 * @see TagCollectionTrait
 */
interface TagCollectionInterface
{
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
     * @return TagRecord
     */
    public function getRootTag() : TagRecord;

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

    /**
     * Gets the label of the root tag, invariant of the current
     * language.
     *
     * @return string
     */
    public function getRootTagLabelInvariant() : string;

    public function getAdminEditTagsURL(array $params=array()) : string;
}
