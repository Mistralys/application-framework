<?php

declare(strict_types=1);

/**
 * @method DBHelper_DataTable getSubject()
 */
class DBHelper_DataTable_Events_KeysSaved extends Application_EventHandler_EventableEvent
{
    public function getKeyNames() : array
    {
        return $this->getArgumentArray(1);
    }

    public function getDataTable() : DBHelper_DataTable
    {
        return $this->getSubject();
    }
}
