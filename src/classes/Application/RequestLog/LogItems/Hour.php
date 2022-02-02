<?php

declare(strict_types=1);

use AppUtils\FileHelper;

class Application_RequestLog_LogItems_Hour extends Application_RequestLog_AbstractFileContainer
{
    protected function isValidFile(string $file) : bool
    {
        return FileHelper::getExtension($file) === 'json';
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

    public function getLabel() : string
    {
        return (string)sb()
            ->sf('%02d:00', $this->getHourNumber())
            ->add('-')
            ->muted(t('%s files', $this->countFiles()));
    }

    public function countFiles() : int
    {
        $files = $this->getFiles();
        return count($files);
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Bootstrap_Screen_RequestLog::REQUEST_PARAM_HOUR] = $this->getHourNumber();

        return $this->getDayLogs()->getAdminURL($params);
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
     * @param string $requestID
     * @return Application_RequestLog_LogFile
     * @throws Application_RequestLog_Exception
     */
    public function getFileByRequestID(string $requestID) : Application_RequestLog_LogItemInterface
    {
        $files = $this->getFiles();

        foreach($files as $file)
        {
            if($file->getRequestID() === $requestID)
            {
                return $file;
            }
        }

        throw new Application_RequestLog_Exception(
            'Request ID not found',
            sprintf(
                'Could find no file with request ID [%s].',
                $requestID
            ),
            self::ERROR_ID_DOES_NOT_EXIST
        );
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

    public function getLogIdentifier() : string
    {
        return sprintf(
            '%s | Hour [%s]',
            $this->parent->getLogIdentifier(),
            $this->getHourNumber()
        );
    }
}
