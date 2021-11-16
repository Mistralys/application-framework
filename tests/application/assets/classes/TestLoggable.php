<?php

declare(strict_types=1);

class TestLoggable implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public function getLogIdentifier() : string
    {
        return 'Test Loggable';
    }

    public function addLogMessage(...$args) : void
    {
        $this->log(...$args);
    }

    public function addEventLog(...$args) : void
    {
        $this->logEvent(...$args);
    }

    public function addDataLog(array $data) : void
    {
        $this->logData($data);
    }

    public function addErrorLog(...$args) : void
    {
        $this->logError(...$args);
    }

    public function addHeaderLog(...$args) : void
    {
        $this->logHeader(...$args);
    }
}
