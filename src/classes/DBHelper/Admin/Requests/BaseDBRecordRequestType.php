<?php

declare(strict_types=1);

namespace DBHelper\Admin\Requests;

use Application\Admin\RequestTypes\BaseRequestType;
use Application\Admin\RequestTypes\RequestTypeInterface;
use AppUtils\ClassHelper;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * @implements RequestTypeInterface<DBHelperRecordInterface>
 */
abstract class BaseDBRecordRequestType extends BaseRequestType
{
    abstract public function getCollection() : DBHelperCollectionInterface;

    public function getRecord() : DBHelperRecordInterface
    {
        return $this->getCollection()->getByRequest();
    }

    public function getRecordOrRedirect() : DBHelperRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            DBHelperRecordInterface::class,
            parent::getRecordOrRedirect()
        );
    }

    public function requireRecord() : DBHelperRecordInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            DBHelperRecordInterface::class,
            parent::requireRecord()
        );
    }
}
