<?php

declare(strict_types=1);

use Application\AppFactory;
use Application\Application;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\Microtime;

class Application_RequestLog_LogWriter implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const string KEY_SESSION_ID = 'sessionID';
    public const string KEY_MICRO_TIME = 'microTime';
    public const string KEY_REQUEST_ID = 'requestID';
    public const string KEY_DURATION = 'duration';
    public const string KEY_USER_ID = 'userID';
    public const string KEY_USER_NAME = 'userName';
    public const string KEY_LOG_FILE_RELATIVE = 'logPath';
    public const string KEY_DEVELOPER_MODE = 'developerMode';
    public const string KEY_SIMULATION_MODE = 'simulationMode';
    public const string KEY_UI_ENABLED = 'uiEnabled';
    public const string KEY_AUTH_ENABLED = 'authEnabled';
    public const string KEY_DATABASE_ENABLED = 'databaseEnabled';
    public const string KEY_DEMO_MODE = 'demoMode';
    public const string KEY_SERVER_VARS = 'serverVars';
    public const string KEY_REQUEST_VARS = 'requestVars';
    public const string KEY_QUERY_COUNT = 'queryCount';
    public const string KEY_SELECT_QUERY_COUNT = 'selectQueryCount';
    public const string KEY_WRITE_QUERY_COUNT = 'writeQueryCount';
    public const string KEY_PHP_VERSION = 'phpVersion';
    public const string KEY_OPERATING_SYSTEM = 'operatingSystem';
    public const string KEY_OPERATING_SYSTEM_FAMILY = 'operatingSystemFamily';
    public const string KEY_COMMAND_LINE_MODE = 'commandLineMode';
    public const string KEY_SESSION_VARS = 'sessionVars';

    private Application_Logger $logger;
    private Microtime $time;
    private string $requestID;
    private string $sessionID;
    private float $duration;

    public function __construct(Application_Logger $logger)
    {
        $this->logger = $logger;
        $this->requestID = Application_Request::getRequestID();
        $this->sessionID = $this->resolveSessionID();

        // We are rounding the duration to 12 decimal places to
        // avoid rounding errors when casting the float to string,
        // and back to float again: the precision is maintained up
        // to 14 decimals, the rest is rounded up. 12 should be more
        // than enough for all purposes.
        $this->duration = round(Application::getTimePassed(), 12);

        $this->setTime(new Microtime());
    }

    /**
     * @return float
     */
    public function getDuration() : float
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getRequestID() : string
    {
        return $this->requestID;
    }

    /**
     * @return string
     */
    public function getSessionID() : string
    {
        return $this->sessionID;
    }

    public function setTime(Microtime $time) : Application_RequestLog_LogWriter
    {
        $this->time = $time;
        return $this;
    }

    public function getBaseFolder() : string
    {
        return Application::getStorageSubfolderPath(sprintf(
            'logs/request/%s/%s/%s/%s',
            $this->time->format('Y'),
            $this->time->format('m'),
            $this->time->format('d'),
            $this->time->format('H')
        ));
    }

    public function getBaseName() : string
    {
        return Application_RequestLog_LogInfo::generateBaseName(
            $this->time,
            $this->sessionID,
            $this->requestID
        );
    }

    public function getSidecarPath() : string
    {
        return FileHelper::normalizePath(sprintf(
            '%s/%s.json',
            $this->getBaseFolder(),
            $this->getBaseName()
        ));
    }

    public function getLogPath() : string
    {
        $path = FileHelper::normalizePath(sprintf(
            '%s/logs/%s.log',
            $this->getBaseFolder(),
            $this->getBaseName()
        ));

        FileHelper::createFolder(dirname($path));

        return $path;
    }

    /**
     * @return $this
     * @throws FileHelper_Exception
     */
    public function write() : Application_RequestLog_LogWriter
    {
        // Ignore writing the log if we are in the request log
        // viewer interface, as this will only pollute the log.
        if(Application_Bootstrap::getBootClass() === Application_Bootstrap_Screen_RequestLog::class)
        {
            return $this;
        }

        $this->log('Writing the application log to disk.');

        $this->writeLogFile();
        $this->writeSidecarFile();

        return $this;
    }

    /**
     * @return void
     * @throws FileHelper_Exception
     */
    private function writeLogFile() : void
    {
        $path = $this->getLogPath();

        $this->log('Saving log to file: [%s].', $path);

        FileHelper::saveFile(
            $path,
            implode(PHP_EOL, $this->logger->getLog())
        );
    }

    public function getData() : array
    {
        $userID = '';
        $userName = '';

        if(Application::isSessionReady())
        {
            $session = AppFactory::createSession();
            $user = $session->getUser();

            if($user !== null) {
                $userID = $user->getID();
                $userName = $user->getName();
            }
        }

        return array(
            self::KEY_REQUEST_ID => $this->requestID,
            self::KEY_MICRO_TIME => $this->time->getISODate(),
            self::KEY_SESSION_ID => $this->sessionID,
            self::KEY_DURATION => $this->duration,
            self::KEY_USER_ID => $userID,
            self::KEY_USER_NAME => $userName,
            self::KEY_DEVELOPER_MODE => isDevelMode(),
            self::KEY_SIMULATION_MODE => Application::isSimulation(),
            self::KEY_UI_ENABLED => Application::isUIEnabled(),
            self::KEY_AUTH_ENABLED => Application::isAuthenticationEnabled(),
            self::KEY_DATABASE_ENABLED => Application::isDatabaseEnabled(),
            self::KEY_DEMO_MODE => Application::isDemoMode(),
            self::KEY_SERVER_VARS => $_SERVER,
            self::KEY_REQUEST_VARS => $_REQUEST,
            self::KEY_SESSION_VARS => $_SESSION ?? null,
            self::KEY_QUERY_COUNT => DBHelper::getQueryCount(),
            self::KEY_SELECT_QUERY_COUNT => DBHelper::countSelectQueries(),
            self::KEY_WRITE_QUERY_COUNT => DBHelper::countWriteQueries(),
            self::KEY_PHP_VERSION => PHP_VERSION,
            self::KEY_OPERATING_SYSTEM => PHP_OS,
            self::KEY_OPERATING_SYSTEM_FAMILY => PHP_OS_FAMILY,
            self::KEY_COMMAND_LINE_MODE => isCLI(),
            self::KEY_LOG_FILE_RELATIVE => FileHelper::relativizePath(
                $this->getLogPath(),
                $this->getBaseFolder()
            )
        );
    }

    private function writeSidecarFile() : void
    {
        FileHelper::saveAsJSON($this->getData(), $this->getSidecarPath());
    }

    private function resolveSessionID() : string
    {
        if(Application::isSessionSimulated())
        {
            return Application_RequestLog::SESSION_ID_SIMULATED;
        }

        if(Application::isSessionReady())
        {
            return Application::getSession()->getID();
        }

        return Application_RequestLog::SESSION_ID_NONE;
    }

    public function getLogIdentifier() : string
    {
        return 'RequestLog | LogWriter';
    }
}
