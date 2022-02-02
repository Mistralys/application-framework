<?php
/**
 * File containing the {@see Application_Logger} class.
 * 
 * @package Application
 * @subpackage Logging
 * @see Application
 */

declare(strict_types=1);

use AppUtils\FileHelper_Exception;

/**
 * Used to log messages while the application is running.
 *
 * @package Application
 * @subpackage Logging
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class Application_Logger
{
    public const LOG_MODE_FILE = 1;
    public const LOG_MODE_ECHO = 2;
    public const LOG_MODE_NONE = 3;

    public const LINE_LENGTH = 65;

   /**
    * Stores all log messages.
    * @var array
    * @see log()
    */
    private $log = array();
    
   /**
    * @var int
    */
    private $logMode = self::LOG_MODE_NONE;
    
   /**
    * @var boolean
    */
    private $html = false;
    
   /**
    * @var string
    */
    private $separator;
    
   /**
    * @var string
    */
    private $logFile;
    
    public function __construct()
    {
        $this->separator = str_repeat('-', self::LINE_LENGTH);
        $this->logFile = $this->getLogFolder().'/trace.log';
    }
    
   /**
    * Whether logging is enabled.
    * 
    * @return bool
    */
    public function isLoggingEnabled() : bool
    {
        return boot_constant('APP_LOGGING_ENABLED') === true;
    }
    
   /**
    * Enable/disable HTML styling of log messages, when the 
    * logger is in ECHO mode.
    * 
    * @param bool $enable
    * @return Application_Logger
    */
    public function enableHTML(bool $enable=true) : Application_Logger
    {
        $this->html = $enable;
        
        return $this;
    }
    
    public function getLogMode() : int
    {
        return $this->logMode;
    }

    public function getLastMessage() : ?string
    {
        if(!empty($this->log))
        {
            return array_value_get_last($this->log);
        }

        return null;
    }

    /**
     * Clears all log messages stored up to this point.
     * @return $this
     */
    public function clearLog() : Application_Logger
    {
        $this->log = array();
        return $this;
    }

    /**
     * Logs a message, but only if the application
     * is in developer mode.
     *
     * @param string|number|array|null $message Arrays are automatically dumped.
     * @param bool $header
     * @return Application_Logger
     */
    public function log($message = null, bool $header=false) : Application_Logger
    {
        if($header === true)
        {
            return $this->logHeader((string)$message);
        }

        if (empty($message))
        {
            return $this->addLogMessage('', false);
        }

        if(is_array($message))
        {
            return $this->logData($message);
        }
        
        return $this->addLogMessage((string)$message, true);
    }

    public function logSF(string $message, ...$args) : Application_Logger
    {
        return $this->addLogMessage($message, true, ...$args);
    }

    /**
     * @param string $eventName
     * @param string $message
     * @param mixed ...$args
     * @return $this
     */
    public function logEvent(string $eventName, string $message='', ...$args) : Application_Logger
    {
        $sep = ' | ';

        if(empty($message))
        {
            $sep = '';
        }

        return $this->addLogMessage('Event ['.$eventName.']'.$sep.$message, true, ...$args);
    }
    
   /**
    * Retrieves the current time or datetime string.
    * 
    * @return string
    */
    private function getTime() : string
    {
        if($this->logMode === self::LOG_MODE_ECHO) 
        {
            return date('H:i:s');
        } 
        
        return date('d.m.Y H:i:s');
    }
    
   /**
    * Logs a message styled as a header.
    * 
    * @param string $message
    * @param mixed ...$args
    * @return Application_Logger
    */
    public function logHeader(string $message, ...$args) : Application_Logger
    {
        if($this->isLoggingEnabled() === false)
        {
            return $this;
        }

        if(!empty($args))
        {
            $message = sprintf($message, ...$args);
        }

        $this->logEmptyLine();
        $this->logSeparator();
        $this->addLogMessage(mb_strtoupper($message), false);
        $this->logSeparator();
        
        return $this;
    }

    public function logSeparator() : Application_Logger
    {
        return $this->addLogMessage($this->separator, false);
    }

    public function logCloseSection(string $sectionLabel, ...$args) : Application_Logger
    {
        if($this->isLoggingEnabled() === false)
        {
            return $this;
        }

        if(!empty($args))
        {
            $sectionLabel = sprintf($sectionLabel, ...$args);
        }

        $sectionLabel = ' '.mb_strtoupper($sectionLabel).' --/';

        $length = self::LINE_LENGTH - mb_strlen($sectionLabel);

        $message = substr($this->separator, 0, $length).$sectionLabel;

        return $this
            ->addLogMessage($message, false)
            ->logEmptyLine();
    }

    public function logEmptyLine() : Application_Logger
    {
        return $this->addLogMessage('', false);
    }

    /**
     * Logs a data array.
     *
     * @param array $data
     * @return Application_Logger
     * @throws JsonException
     */
    public function logData(array $data) : Application_Logger
    {
        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $json = str_replace('\/', '/', $json);

        $this->addLogMessage('Data dump:', true);
        $this->addLogMessage($json, false);
        
        return $this;
    }

    /**
     * @param string $message
     * @param mixed ...$args
     * @return $this
     */
    public function logError(string $message, ...$args) : Application_Logger
    {
        return $this->addLogMessage('ERROR | '.$message, true, ...$args);
    }

    /**
     * @param string $message
     * @param bool $withTime
     * @param mixed ...$args
     * @return $this
     */
    private function addLogMessage(string $message, bool $withTime, ...$args) : Application_Logger
    {
        if(!$this->isLoggingEnabled())
        {
            return $this;
        }

        if(!empty($args))
        {
            $message = sprintf($message, ...$args);
        }

        if($withTime === true)
        {
            $message = $this->getTime().' | '.$message;
        }

        $this->log[] = $message;
   
        if($this->logMode === self::LOG_MODE_ECHO)
        {
            echo $this->format($message);
        }
        else if($this->logMode === self::LOG_MODE_FILE)
        {
            error_log($message, 3, $this->logFile);
        }
        
        return $this;
    }
    
    private function format(string $message) : string
    {
        if($this->html === false)
        {
            return $message.PHP_EOL;
        }
        
        if(strpos($message, "\n") !== false)
        {
            $message = '<pre>'.$message.'</pre>';
        }
        
        return '<div class="log">'.$message.'</div>';
    }
    
   /**
    * Retrieves the full log up to this point, as an indexed array
    * with one log message per entry. The messages are unformatted.
    *
    * @return string[]
    */
    public function getLog() : array
    {
        return $this->log;
    }
    
   /**
    * Sets the log mode.
    * 
    * @param int $mode
    * @return Application_Logger
    * 
    * @see Application_Logger::LOG_MODE_ECHO
    * @see Application_Logger::LOG_MODE_FILE
    * @see Application_Logger::LOG_MODE_NONE
    */
    public function setLogMode(int $mode) : Application_Logger
    {
        $this->logMode = $mode;
        
        return $this;
    }
    
   /**
    * Retrieves the path in which the logfile is stored.
    * @return string
    */
    public function getLogFolder() : string
    {
        return Application::getStorageSubfolderPath('logs');
    }

   /**
    * Will send log messages to standard output.
    * 
    * @return Application_Logger
    */
    public function logModeEcho() : Application_Logger
    {
        return $this->setLogMode(self::LOG_MODE_ECHO);
    }

   /**
    * Will send log messages to the logfile.
    * 
    * @return Application_Logger
    */
    public function logModeFile() : Application_Logger
    {
        return $this->setLogMode(self::LOG_MODE_FILE);
    }
    
   /**
    * Will ignore all log messages.
    * 
    * @return Application_Logger
    */
    public function logModeNone() : Application_Logger
    {
        return $this->setLogMode(self::LOG_MODE_NONE);
    }

    public function printLog(bool $html=false) : void
    {
        $title =  APP_CLASS_NAME.' application log';

        if(!$html)
        {
            echo
                PHP_EOL.
                $title.PHP_EOL.
                implode(PHP_EOL, $this->getLog()).PHP_EOL;

            return;
        }

        ?>
        <style>
            .applog{
                font-family: monospace;
                font-size: 12pt;
                color:#444;
                background: #fff;
                padding: 14px 20px;
            }
        </style>
        <?php

        $entries = $this->getLog();

        ?>
        <div class="applog">
            <p><strong><?php echo $title ?></strong></p>
            <div><?php echo implode('</div><div>', $entries) ?></div>
        </div>
        <?php
    }

    /**
     * Writes the log to disk, under `storage/logs/request/Y/m/d/H`.
     *
     * @return Application_RequestLog_LogWriter
     * @throws Application_Exception
     * @throws FileHelper_Exception
     */
    public function write() : Application_RequestLog_LogWriter
    {
        return Application::createRequestLog()->writeLog($this);
    }
}
