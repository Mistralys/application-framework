<?php

declare(strict_types=1);

namespace DBHelper\BaseRecord\Event;

use Application_EventHandler_EventableEvent;
use DBHelper_BaseRecord;

class KeyModifiedEvent extends Application_EventHandler_EventableEvent
{
    public const EVENT_NAME = 'KeyModified';

    public function getRecord() : DBHelper_BaseRecord
    {
        return $this->getArgumentObject(0, DBHelper_BaseRecord::class);
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
