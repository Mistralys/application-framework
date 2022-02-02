<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;

class Application_RequestLog_LogItems_Month extends Application_RequestLog_AbstractFolderContainer
{
    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Bootstrap_Screen_RequestLog::REQUEST_PARAM_MONTH] = (string)$this->getMonthNumber();

        return $this->getYearLogs()->getAdminURL($params);
    }

    protected function isValidFolder(string $folder) : bool
    {
        return is_numeric($folder) && strlen($folder) === 2;
    }

    /**
     * @return int
     */
    public function getMonthNumber() : int
    {
        return (int)$this->id;
    }

    public function getLabel() : string
    {
        return sprintf(
            '%s (%02d)',
            ConvertHelper::month2string($this->getMonthNumber()),
            $this->getMonthNumber()
        );
    }

    /**
     * @return Application_RequestLog_LogItems_Year
     */
    public function getYearLogs() : Application_RequestLog_LogItemInterface
    {
        return $this->parent;
    }

    /**
     * @return Application_RequestLog_LogItems_Day[]
     */
    public function getDays() : array
    {
        return $this->getContainers();
    }

    public function hasDayNumber(int $day) : bool
    {
        return $this->containerIDExists(sprintf('%02d', $day));
    }

    /**
     * @param int $number
     * @return Application_RequestLog_LogItems_Day
     * @throws Application_RequestLog_Exception
     */
    public function getDayByNumber(int $number) : Application_RequestLog_LogItemInterface
    {
        return $this->getContainerByID(sprintf('%02d', $number));
    }

    /**
     * @param string $id
     * @param string $storageFolder
     * @return Application_RequestLog_LogItems_Day
     */
    protected function createContainer(string $id, string $storageFolder) : Application_RequestLog_LogItemInterface
    {
        return new Application_RequestLog_LogItems_Day(
            $this->getRequestLog(),
            $id,
            $storageFolder,
            $this
        );
    }

    public function getLogIdentifier() : string
    {
        return sprintf(
            '%s | Month [%s]',
            $this->parent->getLogIdentifier(),
            $this->getMonthNumber()
        );
    }
}
