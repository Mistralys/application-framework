<?php

declare(strict_types=1);

namespace DBHelper\BaseCollection\Event;

use Application_EventHandler_EventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface;

class BeforeCreateRecordEvent extends Application_EventHandler_EventableEvent
{
    public function getCollection() : DBHelperCollectionInterface
    {
        return $this->getArgumentObject(0, DBHelperCollectionInterface::class);
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
