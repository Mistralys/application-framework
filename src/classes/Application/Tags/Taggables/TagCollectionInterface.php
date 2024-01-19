<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

/**
 * @see TagCollectionTrait
 */
interface TagCollectionInterface
{
    /**
     * Optional, custom class that extends {@see TagContainer}.
     * @return class-string|NULL
     */
    public function getTagContainerClass() : ?string;

    public function getTagContainer() : TagContainer;

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
}
