<?php

declare(strict_types=1);

namespace DBHelper\BaseCollection\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;
use DBHelper\BaseCollection\DBHelperCollectionInterface;

class BeforeCreateRecordEvent extends BaseEventableEvent
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
