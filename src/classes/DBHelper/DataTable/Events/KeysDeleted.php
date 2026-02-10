<?php

declare(strict_types=1);

use Application\EventHandler\Eventables\BaseEventableEvent;
use AppUtils\ClassHelper;

class DBHelper_DataTable_Events_KeysDeleted extends BaseEventableEvent
{
    public const string EVENT_NAME = 'KeysDeleted';

    public function getName() : string
    {
        return self::EVENT_NAME;
    }

    public function getKeyNames() : array
    {
        return $this->getArgumentArray(1);
    }

    public function getDataTable() : DBHelper_DataTable
    {
        return $this->getSubject();
    }

    public function getSubject(): object
    {
        return ClassHelper::requireObjectInstanceOf(
            DBHelper_DataTable::class,
            parent::getSubject()
        );
    }
}