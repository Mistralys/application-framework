<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Interfaces\Admin\MissingRecordInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * @see RecordCreateScreenTrait
 */
interface RecordScreenInterface extends AdminScreenInterface, MissingRecordInterface
{
    public function getRecord() : DBHelperRecordInterface;
    public function getCollection() : DBHelperCollectionInterface;
}
