<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;

/**
 * @property Application_RequestLog_LogItems_Month $parent
 * @method Application_RequestLog_LogItems_Hour[] getContainers()
 * @method Application_RequestLog_LogItems_Hour getContainerByID(string $id)
 */
class Application_RequestLog_LogItems_Day extends Application_RequestLog_AbstractFolderContainer
{
    protected function isValidFolder(string $folder) : bool
    {
        return is_numeric($folder) && strlen($folder) === 2;
    }

    /**
     * @return Application_RequestLog_LogItems_Month
     */
    public function getMonthLogs() : Application_RequestLog_LogItemInterface
    {
        return $this->parent;
    }

    /**
     * @return int
     */
    public function getDayNumber() : int
    {
        return (int)$this->id;
    }

    /**
     * @return Application_RequestLog_LogItems_Hour[]
     */
    public function getHours() : array
    {
        return $this->getContainers();
    }

    public function hasHourNumber(int $hour) : bool
    {
        return $this->containerIDExists(sprintf('%02d', $hour));
    }

    public function getDate() : DateTime
    {
        return new DateTime(sprintf(
            '%s-%s-%s 00:00:00',
            $this->getMonthLogs()->getYearLogs()->getYearNumber(),
            $this->getMonthLogs()->getMonthNumber(),
            $this->getDayNumber()
        ));
    }

    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Bootstrap_Screen_RequestLog::REQUEST_PARAM_DAY] = $this->getDayNumber();

        return $this->getMonthLogs()->getAdminURL($params);
    }

    public function getLabel() : string
    {
        return (string)sb()
            ->add(ConvertHelper::date2dayName($this->getDate()))
            ->parentheses(sprintf('%02d', $this->getDayNumber()));
    }

    /**
     * @param int $hour
     * @return Application_RequestLog_LogItems_Hour
     * @throws Application_RequestLog_Exception
     */
    public function getHourByNumber(int $hour) : Application_RequestLog_LogItemInterface
    {
        return $this->getContainerByID(sprintf('%02d', $hour));
    }

    /**
     * @param string $id
     * @param string $storageFolder
     * @return Application_RequestLog_LogItems_Hour
     */
    protected function createContainer(string $id, string $storageFolder) : Application_RequestLog_LogItemInterface
    {
        return new Application_RequestLog_LogItems_Hour(
            $this->getRequestLog(),
            $id,
            $storageFolder,
            $this
        );
    }

    public function getLogIdentifier() : string
    {
        return sprintf(
            '%s | Day [%s]',
            $this->parent->getLogIdentifier(),
            $this->getDayNumber()
        );
    }
}
