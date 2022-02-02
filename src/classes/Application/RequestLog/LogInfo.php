<?php

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\Microtime;

class Application_RequestLog_LogInfo
{
    /**
     * @var string
     */
    private $sidecarPath;

    /**
     * @var array
     */
    private $data;

    /**
     * @var Microtime
     */
    private $time;

    /**
     * @var bool
     */
    private $loaded = false;

    public function __construct(string $sidecarPath)
    {
        $this->sidecarPath = $sidecarPath;
    }

    public static function generateBaseName(Microtime $time, string $sessionID, string $requestID) : string
    {
        return sprintf(
            '%s%s-%s-%s',
            $time->format('is'),
            $time->getMicroseconds(),
            $sessionID,
            $requestID
        );
    }

    private function load() : void
    {
        if($this->loaded === true)
        {
            return;
        }

        $this->loaded = true;
        $this->data = FileHelper::parseJSONFile($this->sidecarPath);
        $this->time = new Microtime($this->data[Application_RequestLog_LogWriter::KEY_MICRO_TIME]);
    }

    public function getFileLabel() : string
    {
        $time = $this->getTime();

        return sprintf(
            '%02d:%02d:%02d',
            $time->getHour24(),
            $time->getMinutes(),
            $time->getSeconds()
        );
    }

    public function getSidecarFilePath() : string
    {
        return $this->sidecarPath;
    }

    public function getLogFilePath() : string
    {
        return sprintf(
            '%s/%s',
            dirname($this->getSidecarFilePath()),
            $this->data[Application_RequestLog_LogWriter::KEY_LOG_FILE_RELATIVE]
        );
    }

    /**
     * @return int
     */
    public function getMicroseconds() : int
    {
        return $this->getTime()->getMicroseconds();
    }

    public function getTime() : Microtime
    {
        $this->load();

        return $this->time;
    }

    /**
     * @return int
     */
    public function getMinutes() : int
    {
        return $this->getTime()->getMinutes();
    }

    /**
     * The duration of the request, in seconds.
     * @return float
     */
    public function getDuration() : float
    {
        $this->load();

        return (float)$this->data[Application_RequestLog_LogWriter::KEY_DURATION];
    }

    /**
     * @return string
     */
    public function getRequestID() : string
    {
        $this->load();

        return (string)$this->data[Application_RequestLog_LogWriter::KEY_REQUEST_ID];
    }

    public function getUserName() : string
    {
        $this->load();

        return (string)$this->data[Application_RequestLog_LogWriter::KEY_USER_NAME];
    }

    /**
     * @return int
     */
    public function getSeconds() : int
    {
        return $this->getTime()->getSeconds();
    }

    /**
     * @return string
     */
    public function getSessionID() : string
    {
        $this->load();

        return (string)$this->data[Application_RequestLog_LogWriter::KEY_SESSION_ID];
    }

    public function getDispatcher() : string
    {
        $vars = $this->getServerVars();

        if(isset($vars['PHP_SELF']))
        {
            return basename($vars['PHP_SELF']);
        }

        return 'unknown';
    }

    public function getSessionLabel() : string
    {
        if($this->hasSession())
        {
            return $this->getSessionID();
        }

        if($this->isSimulatedSession())
        {
            return t('Simulated');
        }

        return t('No session');
    }

    public function isSimulatedSession() : bool
    {
        return $this->getSessionID() === Application_RequestLog::SESSION_ID_SIMULATED;
    }

    public function isNoSession() : bool
    {
        return $this->getSessionID() === Application_RequestLog::SESSION_ID_NONE;
    }

    public function hasSession() : bool
    {
        return !$this->isSimulatedSession() && !$this->isNoSession();
    }

    public function getRequestVars() : array
    {
        return $this->data[Application_RequestLog_LogWriter::KEY_REQUEST_VARS] ?? array();
    }

    public function getServerVars() : array
    {
        return $this->data[Application_RequestLog_LogWriter::KEY_SERVER_VARS] ?? array();
    }

    public function getLog() : string
    {
        return FileHelper::readContents($this->getLogFilePath());
    }

    public function getLogSize() : int
    {
        return (int)filesize($this->getLogFilePath());
    }
}
