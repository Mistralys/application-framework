<?php
/**
 * File containing the class {@see Application_RequestLog}.
 *
 * @package Application
 * @subpackage RequestLog
 * @see Application_RequestLog
 */

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\Microtime;

/**
 * Specialized helper class that can be used to access the
 * request logs that have been written to disk, if any.
 *
 * For more information on how to enable request logs, please
 * look in the framework documentation (see link).
 *
 * @package Application
 * @subpackage RequestLog
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Bootstrap_Screen_RequestLog
 * @link https://github.com/Mistralys/application-framework/blob/main/docs/Documentation.md#writing-request-logs
 */
class Application_RequestLog extends Application_RequestLog_AbstractFolderContainer
{
    public const SESSION_ID_NONE = 'none';
    public const SESSION_ID_SIMULATED = 'simulated';

    public function __construct()
    {
        parent::__construct(
            $this,
            'main',
            Application::getStorageSubfolderPath('logs/request'),
            $this
        );
    }

    public function clearAllLogs() : Application_RequestLog
    {
        FileHelper::deleteTree($this->getStorageFolder());
        $this->clearContainers();
        return $this;
    }

    public function getAdminURL(array $params=array()) : string
    {
        return Application_Driver::getInstance()
            ->getRequest()
            ->buildURL(
                $params,
                Application_Bootstrap_Screen_RequestLog::DISPATCHER
            );
    }

    public function getAdminLogOutURL(array $params=array()) : string
    {
        $params[Application_Bootstrap_Screen_RequestLog::REQUEST_PARAM_LOG_OUT] = 'yes';

        return $this->getAdminURL($params);
    }

    protected function isValidFolder(string $folder) : bool
    {
        return is_numeric($folder) && strlen($folder) === 4;
    }

    /**
     * @param string $id
     * @param string $storageFolder
     * @return Application_RequestLog_LogItems_Year
     */
    protected function createContainer(string $id, string $storageFolder) : Application_RequestLog_LogItemInterface
    {
        return new Application_RequestLog_LogItems_Year(
            $this->getRequestLog(),
            $id,
            $storageFolder,
            $this
        );
    }

    /**
     * @return Application_RequestLog_LogItems_Year[]
     */
    public function getYears() : array
    {
        return $this->getContainers();
    }

    public function hasYearNumber(int $year) : bool
    {
        return $this->containerIDExists((string)$year);
    }

    /**
     * @param int $year
     * @return Application_RequestLog_LogItems_Year
     * @throws Application_RequestLog_Exception
     */
    public function getYearByNumber(int $year) : Application_RequestLog_LogItemInterface
    {
        return $this->getContainerByID((string)$year);
    }

    /**
     * Writes the current application log to disk.
     *
     * @param Application_Logger $logger
     * @return Application_RequestLog_LogWriter
     * @throws Application_Exception
     * @throws FileHelper_Exception
     */
    public function writeLog(Application_Logger $logger) : Application_RequestLog_LogWriter
    {
        return (new Application_RequestLog_LogWriter($logger))->write();
    }

    public function getLogIdentifier() : string
    {
        return 'RequestLog';
    }
}
