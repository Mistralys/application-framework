<?php

declare(strict_types=1);

namespace DBHelper\BaseCollection\Event;

use Application_EventHandler_EventableEvent;
use DBHelper_BaseCollection;
use DBHelper_BaseCollection_OperationContext_Create;
use DBHelper_BaseRecord;

class AfterCreateRecordEvent extends Application_EventHandler_EventableEvent
{
    public function getCollection() : DBHelper_BaseCollection
    {
        return $this->getArgumentObject(0, DBHelper_BaseCollection::class);
    }

    public function getRecord() : DBHelper_BaseRecord
    {
        return $this->getArgumentObject(1, DBHelper_BaseRecord::class);
    }

    public function getContext() : DBHelper_BaseCollection_OperationContext_Create
    {
        return $this->getArgumentObject(2, DBHelper_BaseCollection_OperationContext_Create::class);
    }
}
