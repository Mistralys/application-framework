<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

/**
 * @see TaggableTrait
 */
interface TaggableInterface
{
    /**
     * Retrieves an instance of the helper class that can be used to manage tags for this record.
     * @return Taggable
     */
    public function getTagManager() : Taggable;
    public function getTagConnector() : TagConnector;
    public function getTaggedRecordPrimary() : int;
    public function getAdminTaggingURL(array $params=array()) : string;
    public function getTagCollection() : TagCollectionInterface;
}
