<?php

declare(strict_types=1);

use AppUtils\FileHelper;

/**
 * @property Application_RequestLog_LogItems_Hour $parent
 */
class Application_RequestLog_LogFile extends Application_RequestLog_AbstractLogItem
{
    public const string KEY_FILE_ID = 'fileID';
    public const string KEY_FILE_LABEL = 'fileLabel';
    public const string KEY_FILE_NAME = 'fileName';
    public const string KEY_FILE_PATH = 'filePath';
    public const string KEY_REQUEST_ID = 'requestID';

    private ?Application_RequestLog_LogInfo $fileInfo = null;

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

    public function toArray() : array
    {
        return array(
            self::KEY_FILE_ID => $this->getID(),
            self::KEY_FILE_LABEL => $this->getLabel(),
            self::KEY_FILE_NAME => $this->getFileName(),
            self::KEY_FILE_PATH => $this->getFilePath(),
            self::KEY_REQUEST_ID => $this->getRequestID()
        );
    }
}
