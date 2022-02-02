<?php

declare(strict_types=1);

abstract class Application_RequestLog_AbstractLogItem implements Application_RequestLog_LogItemInterface
{
    use Application_Traits_Loggable;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $storageFolder;

    /**
     * @var Application_RequestLog_LogItemInterface
     */
    protected $parent;

    /**
     * @var Application_RequestLog
     */
    private $requestLog;

    public function __construct(Application_RequestLog $requestLog, string $id, string $storageFolder, Application_RequestLog_LogItemInterface $parent)
    {
        $this->requestLog = $requestLog;
        $this->id = $id;
        $this->storageFolder = $storageFolder;
        $this->parent = $parent;
    }

    public function getRequestLog() : Application_RequestLog
    {
        return $this->requestLog;
    }

    public function getStorageFolder() : string
    {
        return $this->storageFolder;
    }

    public function getID() : string
    {
        return $this->id;
    }
}
