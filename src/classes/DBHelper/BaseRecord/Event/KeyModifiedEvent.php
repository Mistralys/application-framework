<?php

declare(strict_types=1);

namespace DBHelper\BaseRecord\Event;

use Application\EventHandler\Eventables\BaseEventableEvent;
use DBHelper\Interfaces\DBHelperRecordInterface;

class KeyModifiedEvent extends BaseEventableEvent
{
    public const string EVENT_NAME = 'KeyModified';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getRecord() : DBHelperRecordInterface
    {
        return $this->getArgumentObject(0, DBHelperRecordInterface::class);
    }

    public function getKeyName() : string
    {
        return $this->getArgumentString(1);
    }

    public function getOldValue()
    {
        return $this->getArgument(2);
    }

    public function getNewValue()
    {
        return $this->getArgument(3);
    }

    public function getKeyLabel() : ?string
    {
        $label = $this->getArgumentString(4);
        if(!empty($label)) {
            return $label;
        }

        return null;
    }

    public function isStructural() : bool
    {
        return $this->getArgumentBool(5);
    }

    public function isCustomField() : bool
    {
        return $this->getArgumentBool(6);
    }
}
