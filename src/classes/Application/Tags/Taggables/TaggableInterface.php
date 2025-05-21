<?php

declare(strict_types=1);

namespace Application\Tags\Taggables;

use UI\AdminURLs\AdminURL;

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
    public function getTagRecordPrimaryValue() : int;
    public function adminURLTagging() : AdminURL;
    public function getTagCollection() : TagCollectionInterface;
    public function isTaggingEnabled() : bool;
    public function getTaggableLabel() : string;
}
