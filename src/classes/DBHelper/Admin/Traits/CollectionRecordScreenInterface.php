<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Interfaces\Admin\MissingRecordInterface;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;

interface CollectionRecordScreenInterface extends AdminScreenInterface, MissingRecordInterface
{
    /**
     * @return DBHelper_BaseRecord
     */
    public function getRecord() : DBHelper_BaseRecord;

    /**
     * @return DBHelper_BaseCollection
     */
    public function getCollection() : DBHelper_BaseCollection;
}
