<?php

declare(strict_types=1);

use AppUtils\FileHelper;

class Application_RequestLog_LogFile extends Application_RequestLog_AbstractLogItem
{
    /**
     * @var Application_RequestLog_LogInfo|NULL
     */
    private $fileInfo;

    /**
     * @return Application_RequestLog_LogItems_Hour
     */
    public function getHourLogs() : Application_RequestLog_AbstractLogItem
    {
        return $this->parent;
    }

    public function getFileName() : string
    {
        return $this->id;
    }

    public function getFilePath() : string
    {
        return FileHelper::normalizePath($this->storageFolder.'/'.$this->getFileName());
    }

    public function getFileInfo() : Application_RequestLog_LogInfo
    {
        if(!isset($this->fileInfo))
        {
            $this->fileInfo = new Application_RequestLog_LogInfo($this->getFilePath());
        }

        return $this->fileInfo;
    }

    public function getLabel() : string
    {
        return $this->getFileInfo()->getFileLabel();
    }

    public function getRequestID() : string
    {
        return $this->getFileInfo()->getRequestID();
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Bootstrap_Screen_RequestLog::REQUEST_PARAM_ID] = $this->getRequestID();

        return $this->getHourLogs()->getAdminURL($params);
    }

    public function getLogIdentifier() : string
    {
        return sprintf(
            '%s | File [%s]',
            $this->parent->getLogIdentifier(),
            $this->getFileName()
        );
    }
}
