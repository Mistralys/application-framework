<?php

declare(strict_types=1);

namespace DBHelper\BaseCollection\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Delete;

class AfterDeleteRecordEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'AfterDeleteRecord';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getCollection() : DBHelperCollectionInterface
    {
        return $this->getArgumentObject(0, DBHelperCollectionInterface::class);
    }

    public function getRecord() : DBHelperRecordInterface
    {
        return $this->getArgumentObject(1, DBHelperRecordInterface::class);
    }

    public function getContext() : DBHelper_BaseCollection_OperationContext_Delete
    {
        return $this->getArgumentObject(2, DBHelper_BaseCollection_OperationContext_Delete::class);
    }
}
