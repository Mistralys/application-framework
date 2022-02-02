<?php

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\Microtime;

class Application_RequestLog_LogWriter implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const KEY_SESSION_ID = 'sessionID';
    public const KEY_MICRO_TIME = 'microTime';
    public const KEY_REQUEST_ID = 'requestID';
    public const KEY_DURATION = 'duration';
    public const KEY_USER_ID = 'userID';
    public const KEY_USER_NAME = 'userName';
    public const KEY_LOG_FILE_RELATIVE = 'logPath';
    public const KEY_DEVELOPER_MODE = 'developerMode';
    public const KEY_SIMULATION_MODE = 'simulationMode';
    public const KEY_UI_ENABLED = 'uiEnabled';
    public const KEY_AUTH_ENABLED = 'authEnabled';
    public const KEY_DATABASE_ENABLED = 'databaseEnabled';
    public const KEY_DEMO_MODE = 'demoMode';
    public const KEY_SERVER_VARS = 'serverVars';
    public const KEY_REQUEST_VARS = 'requestVars';
    public const KEY_QUERY_COUNT = 'queryCount';
    public const KEY_SELECT_QUERY_COUNT = 'selectQueryCount';
    public const KEY_WRITE_QUERY_COUNT = 'writeQueryCount';

    /**
     * @var Application_Logger
     */
    private $logger;

    /**
     * @var Microtime
     */
    private $time;

    /**
     * @var string
     */
    private $requestID;

    /**
     * @var string
     */
    private $sessionID;

    /**
     * @var string
     */
    private $logPath;
    /**
     * @var string
     */
    private $baseFolder;
    /**
     * @var string
     */
    private $baseName;

    /**
     * @var float
     */
    private $duration;

    public function __construct(Application_Logger $logger)
    {
        $this->logger = $logger;
        $this->requestID = Application_Request::getRequestID();
        $this->sessionID = $this->resolveSessionID();
        $this->duration = Application::getTimePassed();

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

    public function write() : Application_RequestLog_LogWriter
    {
        $this->log('Writing the application log to disk.');

        $this->writeLogFile();
        $this->writeSidecarFile();

        return $this;
    }

    private function writeLogFile() : void
    {
        $this->log('Saving log to file: [%s].', $this->logPath);

        FileHelper::saveFile(
            $this->getLogPath(),
            implode(PHP_EOL, $this->logger->getLog())
        );
    }

    public function getData() : array
    {
        $user = Application::getUser();

        return array(
            self::KEY_REQUEST_ID => $this->requestID,
            self::KEY_MICRO_TIME => $this->time->getISODate(),
            self::KEY_SESSION_ID => $this->sessionID,
            self::KEY_DURATION => $this->duration,
            self::KEY_USER_ID => $user->getID(),
            self::KEY_USER_NAME => $user->getName(),
            self::KEY_DEVELOPER_MODE => isDevelMode(),
            self::KEY_SIMULATION_MODE => Application::isSimulation(),
            self::KEY_UI_ENABLED => Application::isUIEnabled(),
            self::KEY_AUTH_ENABLED => Application::isAuthenticationEnabled(),
            self::KEY_DATABASE_ENABLED => Application::isDatabaseEnabled(),
            self::KEY_DEMO_MODE => Application::isDemoMode(),
            self::KEY_SERVER_VARS => $_SERVER ?? null,
            self::KEY_REQUEST_VARS => $_REQUEST ?? null,
            self::KEY_QUERY_COUNT => DBHelper::getQueryCount(),
            self::KEY_SELECT_QUERY_COUNT => DBHelper::countSelectQueries(),
            self::KEY_WRITE_QUERY_COUNT => DBHelper::countWriteQueries(),
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
