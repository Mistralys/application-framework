<?php

declare(strict_types=1);

namespace DBHelper\BaseCollection\Event;

use Application_EventHandler_EventableEvent;
use DBHelper_BaseCollection;

class BeforeCreateRecordEvent extends Application_EventHandler_EventableEvent
{
    public function getCollection() : DBHelper_BaseCollection
    {
        return $this->getArgumentObject(0, DBHelper_BaseCollection::class);
    }

    /**
     * @return array<string,mixed>
     */
    public function getRecordData() : array
    {
        return $this->getArgumentArray(1);
    }

    public function getName() : string
    {
        $data = $this->getRecordData();

        return $data['name'] ?? '';
    }
}
