<?php

declare(strict_types=1);

/**
 * @method Application_RequestLog_LogItems_Month[] getContainers()
 * @method Application_RequestLog_LogItems_Month getContainerByID(string $id)
 */
class Application_RequestLog_LogItems_Year extends Application_RequestLog_AbstractFolderContainer
{
    protected function isValidFolder(string $folder) : bool
    {
        return is_numeric($folder) && strlen($folder) === 2;
    }

    /**
     * @return int
     */
    public function getYearNumber() : int
    {
        return (int)$this->id;
    }

    /**
     * @return Application_RequestLog_LogItems_Month[]
     */
    public function getMonths() : array
    {
        return $this->getContainers();
    }

    public function hasMonthNumber(int $month) : bool
    {
        return $this->containerIDExists(sprintf('%02d', $month));
    }

    /**
     * @param int $month
     * @return Application_RequestLog_LogItems_Month
     * @throws Application_RequestLog_Exception
     */
    public function getMonthByNumber(int $month) : Application_RequestLog_LogItemInterface
    {
        return $this->getContainerByID(sprintf('%02d', $month));
    }

    /**
     * @param string $id
     * @param string $storageFolder
     * @return Application_RequestLog_LogItems_Month
     */
    protected function createContainer(string $id, string $storageFolder) : Application_RequestLog_LogItemInterface
    {
        return new Application_RequestLog_LogItems_Month(
            $this->getRequestLog(),
            $id,
            $storageFolder,
            $this
        );
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Bootstrap_Screen_RequestLog::REQUEST_PARAM_YEAR] = (string)$this->getYearNumber();

        return $this->getRequestLog()->getAdminURL($params);
    }

    public function getLabel() : string
    {
        return (string)$this->getYearNumber();
    }

    public function getLogIdentifier() : string
    {
        return sprintf(
            '%s | Year [%s]',
            $this->parent->getLogIdentifier(),
            $this->getYearNumber()
        );
    }
}
