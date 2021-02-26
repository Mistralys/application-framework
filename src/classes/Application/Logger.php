<?php
/**
 * File containing the {@see Application_Logger} class.
 * 
 * @package Application
 * @subpackage Logging
 * @see Application
 */

declare(strict_types=1);

/**
 * Used to log messages while the application is running.
 *
 * @package Application
 * @subpackage Logging
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class Application_Logger
{
    const LOG_MODE_FILE = 1;
    const LOG_MODE_ECHO = 2;
    const LOG_MODE_NONE = 3;
    
   /**
    * Stores all log messages.
    * @var array
    * @see log()
    */
    private $log = array();
    
   /**
    * @var string
    */
    private $logPrefix = '';

   /**
    * @var string
    */
    private $logSuffix = PHP_EOL;

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
    private $separator = '';
    
   /**
    * @var string
    */
    private $logFile = '';
    
    public function __construct()
    {
        $this->separator = str_repeat('-', 65);
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
    
    /**
     * Logs a message, but only if the application 
     * is in developer mode.
     *
     * @param string|array $message Arrays are automatically dumped.
     */
    public function log($message = null, bool $header=false) : Application_Logger
    {
        if($header === true)
        {
            return $this->logHeader((string)$message);
        }
        else if (empty($message))
        {
            return $this->addLogMessage($this->getTime().' |');
        }
        else if(is_array($message))
        {
            return $this->logData($message);
        }
        
        return $this->addLogMessage($this->getTime().' | '.(string)$message);
    }

    public function logEvent(string $eventName, string $message='') : Application_Logger
    {
        $sep = ' | ';

        if(empty($message))
        {
            $sep = '';
        }

        return $this->log('Event ['.$eventName.']'.$sep.$message);
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
    * @return Application_Logger
    */
    public function logHeader(string $message) : Application_Logger
    {
        $this->addLogMessage($this->separator);
        $this->addLogMessage(mb_strtoupper($message));
        $this->addLogMessage($this->separator);
        
        return $this;
    }
    
   /**
    * Logs a data array.
    * 
    * @param array $data
    * @return Application_Logger
    */
    public function logData(array $data) : Application_Logger
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $json = str_replace('\/', '/', $json);

        $this->addLogMessage($this->getTime().' | Data dump:');
        $this->addLogMessage($json);
        
        return $this;
    }

    /**
     * @param string $message
     * @return $this
     */
    public function logError(string $message) : Application_Logger
    {
        $this->addLogMessage('ERROR | '.$message);
        return $this;
    }
    
    private function addLogMessage(string $message) : Application_Logger
    {
        $this->log[] = $message;
   
        if(!$this->isLoggingEnabled())
        {
            return $this;
        }
        
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
        
        if(strstr($message, "\n"))
        {
            $message = '<pre>'.$message.'</pre>';
        }
        
        $message = '<div class="log">'.$message.'</div>';
        
        return $message;
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
        return self::setLogMode(self::LOG_MODE_ECHO);
    }

   /**
    * Will send log messages to the logfile.
    * 
    * @return Application_Logger
    */
    public function logModeFile() : Application_Logger
    {
        return self::setLogMode(self::LOG_MODE_FILE);
    }
    
   /**
    * Will ignore all log messages.
    * 
    * @return Application_Logger
    */
    public function logModeNone() : Application_Logger
    {
        return self::setLogMode(self::LOG_MODE_NONE);
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
}
