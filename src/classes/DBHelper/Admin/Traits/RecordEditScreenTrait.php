<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use DBHelper_BaseCollection;
use DBHelper_BaseRecord;
use UI\AdminURLs\AdminURLInterface;

/**
 * @see RecordEditScreenInterface
 */
trait RecordEditScreenTrait
{
    public function isEditMode() : bool
    {
        return true;
    }

    public function getRecord(): DBHelper_BaseRecord
    {
        return $this->record;
    }

    public function getCollection(): DBHelper_BaseCollection
    {
        return $this->collection;
    }

    public function getRecordMissingURL(): string|AdminURLInterface
    {
        return $this->getArea()->getURL();
    }
}
