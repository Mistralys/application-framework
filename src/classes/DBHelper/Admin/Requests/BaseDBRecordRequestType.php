<?php

declare(strict_types=1);

namespace DBHelper\Admin\Requests;

use Application\Admin\RequestTypes\BaseRequestType;
use Application\Admin\RequestTypes\RequestTypeInterface;
use AppUtils\ClassHelper;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;

/**
 * @implements RequestTypeInterface<DBHelper_BaseRecord>
 */
abstract class BaseDBRecordRequestType extends BaseRequestType
{
    /**
     * @return DBHelper_BaseCollection
     */
    abstract public function getCollection();

    public function getRecord()
    {
        return $this->getCollection()->getByRequest();
    }

    public function getRecordOrRedirect()
    {
        return ClassHelper::requireObjectInstanceOf(
            DBHelper_BaseRecord::class,
            parent::getRecordOrRedirect()
        );
    }

    public function requireRecord()
    {
        return ClassHelper::requireObjectInstanceOf(
            DBHelper_BaseRecord::class,
            parent::requireRecord()
        );
    }
}
