<?php

declare(strict_types=1);

use Application\Interfaces\Admin\AdminScreenInterface;
use AppUtils\FileHelper;
use AppUtils\Microtime;

class Application_RequestLog_LogInfo
{
    private string $sidecarPath;
    private Microtime $time;
    private bool $loaded = false;
    private ?string $screenPath = null;

    /**
     * @var array<string|int,mixed>
     */
    private array $data;
    private string $id;


    public function __construct(string $sidecarPath)
    {
        $this->sidecarPath = $sidecarPath;
        $this->id = md5($sidecarPath);
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

    public function getID(): string
    {
        return $this->id;
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
            $this->getDataString(Application_RequestLog_LogWriter::KEY_LOG_FILE_RELATIVE)
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
        return (float)$this->getDataString(Application_RequestLog_LogWriter::KEY_DURATION);
    }

    /**
     * @return string
     */
    public function getRequestID() : string
    {
        return $this->getDataString(Application_RequestLog_LogWriter::KEY_REQUEST_ID);
    }

    public function getUserName() : string
    {
        return $this->getDataString(Application_RequestLog_LogWriter::KEY_USER_NAME);
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
        return $this->getDataString(Application_RequestLog_LogWriter::KEY_SESSION_ID);
    }

    public function getScreenPath() : string
    {
        if(isset($this->screenPath))
        {
            return $this->screenPath;
        }

        // Screen path only makes sense when the dispatcher
        // was the main UI interface one.
        if($this->getDispatcher() !== 'index.php')
        {
            $this->screenPath = '';
            return $this->screenPath;
        }

        $requestVars = $this->getRequestVars();
        $path = array();

        if(isset($requestVars[AdminScreenInterface::REQUEST_PARAM_PAGE]))
        {
            $path[] = $requestVars[AdminScreenInterface::REQUEST_PARAM_PAGE];

            if(isset($requestVars[AdminScreenInterface::REQUEST_PARAM_MODE]))
            {
                $path[] = $requestVars[AdminScreenInterface::REQUEST_PARAM_MODE];

                if(isset($requestVars[AdminScreenInterface::REQUEST_PARAM_SUBMODE]))
                {
                    $path[] = $requestVars[AdminScreenInterface::REQUEST_PARAM_SUBMODE];

                    if(isset($requestVars[AdminScreenInterface::REQUEST_PARAM_ACTION]))
                    {
                        $path[] = $requestVars[AdminScreenInterface::REQUEST_PARAM_ACTION];
                    }
                }
            }
        }

        $this->screenPath = implode('.', $path);

        return $this->screenPath;
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

    /**
     * @return array<string,mixed>
     */
    public function getRequestVars() : array
    {
        $vars = $this->getDataArray(Application_RequestLog_LogWriter::KEY_REQUEST_VARS);

        ksort($vars);

        return $vars;
    }

    /**
     * @return array<string,string|number|NULL>
     */
    public function getServerVars() : array
    {
        $vars = $this->getDataArray(Application_RequestLog_LogWriter::KEY_SERVER_VARS);

        ksort($vars);

        return $vars;
    }

    /**
     * @return array<string,mixed>
     */
    public function getSessionVars() : array
    {
        $vars = $this->getDataArray(Application_RequestLog_LogWriter::KEY_SESSION_VARS);

        ksort($vars);

        return $vars;
    }

    public function getLog() : string
    {
        return FileHelper::readContents($this->getLogFilePath());
    }

    public function getLogSize() : int
    {
        return (int)filesize($this->getLogFilePath());
    }

    public function getPHPVersion() : string
    {
        return $this->getDataString(Application_RequestLog_LogWriter::KEY_PHP_VERSION);
    }

    public function getOS() : string
    {
        return $this->getDataString(Application_RequestLog_LogWriter::KEY_OPERATING_SYSTEM);
    }

    public function getOSFamily() : string
    {
        return $this->getDataString(Application_RequestLog_LogWriter::KEY_OPERATING_SYSTEM_FAMILY);
    }

    public function getQueryCount() : int
    {
        return $this->getDataInteger(Application_RequestLog_LogWriter::KEY_QUERY_COUNT);
    }

    public function getQueryReadCount() : int
    {
        return $this->getDataInteger(Application_RequestLog_LogWriter::KEY_SELECT_QUERY_COUNT);
    }

    public function getQueryWriteCount() : int
    {
        return $this->getDataInteger(Application_RequestLog_LogWriter::KEY_WRITE_QUERY_COUNT);
    }

    public function isDeveloperMode() : bool
    {
        return $this->getDataBool(Application_RequestLog_LogWriter::KEY_DEVELOPER_MODE);
    }

    public function isUIEnabled() : bool
    {
        return $this->getDataBool(Application_RequestLog_LogWriter::KEY_UI_ENABLED);
    }

    public function isDemoMode() : bool
    {
        return $this->getDataBool(Application_RequestLog_LogWriter::KEY_DEMO_MODE);
    }

    public function isCLI() : bool
    {
        return $this->getDataBool(Application_RequestLog_LogWriter::KEY_COMMAND_LINE_MODE);
    }

    public function isDatabaseEnabled() : bool
    {
        return $this->getDataBool(Application_RequestLog_LogWriter::KEY_DATABASE_ENABLED);
    }

    public function isAuthEnabled() : bool
    {
        return $this->getDataBool(Application_RequestLog_LogWriter::KEY_AUTH_ENABLED);
    }

    private function getDataInteger(string $name) : int
    {
        $this->load();

        if(isset($this->data[$name]))
        {
            return (int)$this->data[$name];
        }

        return 0;
    }

    private function getDataArray(string $name) : array
    {
        $this->load();

        if(isset($this->data[$name]) && is_array($this->data[$name]))
        {
            return $this->data[$name];
        }

        return array();
    }

    private function getDataString(string $name) : string
    {
        $this->load();

        if(isset($this->data[$name]))
        {
            return (string)$this->data[$name];
        }

        return '';
    }

    private function getDataBool(string $name) : bool
    {
        $this->load();

        if(isset($this->data[$name]))
        {
            return $this->data[$name] === true;
        }

        return false;
    }
}
