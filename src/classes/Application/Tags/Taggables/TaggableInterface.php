<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

interface TaggableInterface
{
    /**
     * Retrieves an instance of the helper class that can be used to manage tags for this record.
     * @return Taggable
     */
    public function getTagManager() : Taggable;
    public function getTagCollection() : TagContainer;
    public function getTaggedRecordPrimary() : int;
}
