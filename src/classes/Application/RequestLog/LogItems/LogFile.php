<?php

declare(strict_types=1);

use AppUtils\FileHelper;

class Application_RequestLog_LogFile extends Application_RequestLog_AbstractLogItem
{
    /**
     * @var Application_RequestLog_LogName|NULL
     */
    private $fileInfo;

    public function getFileName() : string
    {
        return $this->id;
    }

    public function getFilePath() : string
    {
        return FileHelper::normalizePath($this->storageFolder.'/'.$this->getFileName());
    }

    public function getFileInfo() : Application_RequestLog_LogName
    {
        if(!isset($this->fileInfo))
        {
            $this->fileInfo = new Application_RequestLog_LogName($this->getFileName());
        }

        return $this->fileInfo;
    }
}
