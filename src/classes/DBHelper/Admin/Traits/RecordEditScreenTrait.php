<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
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

    public function getRecord(): DBHelperRecordInterface
    {
        return $this->record;
    }

    public function getCollection(): DBHelperCollectionInterface
    {
        return $this->collection;
    }

    public function getRecordMissingURL(): string|AdminURLInterface
    {
        return $this->getArea()->getURL();
    }
}
