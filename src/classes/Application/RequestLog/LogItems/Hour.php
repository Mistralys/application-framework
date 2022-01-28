<?php

declare(strict_types=1);

use AppUtils\FileHelper;

class Application_RequestLog_LogItems_Hour extends Application_RequestLog_AbstractFileContainer
{
    protected function isValidFile(string $file) : bool
    {
        return FileHelper::getExtension($file) === 'log';
    }

    /**
     * @return Application_RequestLog_LogItems_Day
     */
    public function getDayLogs() : Application_RequestLog_LogItemInterface
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function getHourNumber() : int
    {
        return (int)$this->id;
    }

    /**
     * @return Application_RequestLog_LogFile[]
     */
    public function getFiles() : array
    {
        return $this->getContainers();
    }

    public function hasFiles() : bool
    {
        $files = $this->getFiles();
        return !empty($files);
    }

    /**
     * @param string $name
     * @return Application_RequestLog_LogFile
     * @throws Application_RequestLog_Exception
     */
    public function getFileByName(string $name) : Application_RequestLog_LogItemInterface
    {
        return $this->getContainerByID($name);
    }

    /**
     * @param string $id
     * @param string $storageFolder
     * @return Application_RequestLog_LogFile
     */
    protected function createContainer(string $id, string $storageFolder) : Application_RequestLog_LogItemInterface
    {
        return new Application_RequestLog_LogFile(
            $this->getRequestLog(),
            $id,
            $storageFolder,
            $this
        );
    }
}
