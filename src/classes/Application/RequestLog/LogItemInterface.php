<?php

declare(strict_types=1);

interface Application_RequestLog_LogItemInterface extends Application_Interfaces_Loggable
{
    public function getID() : string;

    public function getRequestLog() : Application_RequestLog;

    public function getStorageFolder() : string;
}
