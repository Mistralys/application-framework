<?php

declare(strict_types=1);

namespace DBHelper\BaseCollection;

use DBHelper\Interfaces\DBHelperRecordInterface;

interface ChildCollectionInterface extends DBHelperCollectionInterface
{
    /**
     * @return class-string<DBHelperCollectionInterface>
     */
    public function getParentCollectionClass(): string;

    /**
     * @return DBHelperRecordInterface Mandatory parent record for child collections.
     */
    public function getParentRecord() : DBHelperRecordInterface;

    public function getParentCollection() : DBHelperCollectionInterface;
}
