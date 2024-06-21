<?php
/**
 * File containing the {@see Application_Logger} class.
 * 
 * @package Application
 * @subpackage Logging
 * @see Application
 */

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\FileHelper;
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

    public const LOG_MODE_DEFAULT = self::LOG_MODE_NONE;

    public const LINE_LENGTH = 65;

    public const CATEGORY_GENERAL = 'GEN';
    public const CATEGORY_UI = 'UI';
    public const CATEGORY_ERROR = 'ERR';
    public const CATEGORY_EVENT = 'EVENT';
    public const CATEGORY_REQUEST = 'REQ';

    public const HTML_MODE_DEFAULT = false;
    public const LOGGING_ENABLED_DEFAULT = true;
    public const MEMORY_STORAGE_ENABLED_DEFAULT = true;

    private int $logMode = self::LOG_MODE_DEFAULT;
    private bool $html = self::HTML_MODE_DEFAULT;
    private string $separator;
    private string $logFile;

    /**
     * Stores all log messages.
     * @var string[]
     * @see log()
     */
    private array $log = array();

    /**
     * @var array<string,bool>
     */
    private array $enabledCategories = array();
    private bool $loggingEnabled = self::LOGGING_ENABLED_DEFAULT;
    private bool $logFileInitialized = false;
    private bool $memoryStorageEnabled = self::MEMORY_STORAGE_ENABLED_DEFAULT;

    public function __construct()
    {
        $this->separator = str_repeat('-', self::LINE_LENGTH);
        $this->logFile = sprintf(
            '%s/%s/%s/%s/trace.log',
            $this->getLogFolder(),
            date('Y'),
            date('m'),
            date('d')
        );
    }

    public function reset() : self
    {
        $this->memoryStorageEnabled = self::MEMORY_STORAGE_ENABLED_DEFAULT;
        $this->loggingEnabled = self::LOGGING_ENABLED_DEFAULT;
        $this->enabledCategories = array();
        $this->logMode = self::LOG_MODE_DEFAULT;
        $this->html = self::HTML_MODE_DEFAULT;

        $this->clearLog();

        return $this;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setLoggingEnabled(bool $enabled) : self
    {
        $this->loggingEnabled = $enabled;
        return $this;
    }

    /**
     * Sets whether log messages should be stored in memory (default is yes).
     *
     * This is useful to turn off when using an error log file as target,
     * to keep memory usage low, as it is not otherwise used.
     *
     * NOTE: If memory storage is disabled, the log messages will not be
     * available in the exception screen.
     *
     * @param bool $enabled
     * @return $this
     */
    public function setMemoryStorageEnabled(bool $enabled) : self
    {
        $this->memoryStorageEnabled = $enabled;
        return $this;
    }

    public function isMemoryStorageEnabled() : bool
    {
        return $this->memoryStorageEnabled;
    }

    /**
     * Whether logging is enabled. This includes both
     * the logging configuration from the application
     * configuration constants, and the runtime setting.
     *
     * @param string $category
     * @return bool
     */
    public function isLoggingEnabled(string $category='') : bool
    {
        if($this->loggingEnabled === false) {
            return false;
        }

        return $this->isCategoryEnabled($category);
    }

    public function setCategoryEnabled(string $category, bool $enabled) : self
    {
        $this->enabledCategories[$category] = $enabled;
        return $this;
    }

    public function isCategoryEnabled(string $category) : bool
    {
        if(!empty($category) && isset($this->enabledCategories[$category])) {
            return $this->enabledCategories[$category];
        }

        return true;
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
    public function log($message = null, bool $header=false, string $category=self::CATEGORY_GENERAL) : Application_Logger
    {
        if($header === true)
        {
            return $this->logHeader((string)$message);
        }

        if (empty($message))
        {
            return $this->logEmptyLine();
        }

        if(is_array($message))
        {
            return $this->logData($message);
        }
        
        return $this->addLogMessage((string)$message, $category, true);
    }

    /**
     * @param string $message
     * @param string|null $category
     * @param mixed ...$args
     * @return $this
     */
    public function logSF(string $message, ?string $category=self::CATEGORY_GENERAL, ...$args) : Application_Logger
    {
        return $this->addLogMessage($message, $category, true, ...$args);
    }

    /**
     * @param string $eventName
     * @param string $message
     * @param mixed ...$args
     * @return $this
     */
    public function logEvent(string $eventName, string $message='', ...$args) : Application_Logger
    {
        return $this->addLogMessage($eventName.' | '.$message, self::CATEGORY_EVENT, true, ...$args);
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
        $this->addLogMessage(mb_strtoupper($message), '', false);
        $this->logSeparator();
        
        return $this;
    }

    public function logSeparator() : Application_Logger
    {
        return $this->addLogMessage($this->separator, '', false);
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
            ->addLogMessage($message, '', false)
            ->logEmptyLine();
    }

    public function logEmptyLine() : Application_Logger
    {
        return $this->addLogMessage('', '', false);
    }

    /**
     * Logs a data array.
     *
     * @param array<mixed> $data
     * @param string|NULL $category
     * @param string|NULL $label Label to display above the data dump.
     * @return Application_Logger
     * @throws JsonException
     */
    public function logData(array $data, ?string $category=null, ?string $label=null) : Application_Logger
    {
        if(empty($category)) {
            $category = self::CATEGORY_GENERAL;
        }

        if(empty($label)) {
            $label = 'Data dump:';
        }

        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $json = str_replace('\/', '/', $json);

        $this->addLogMessage($label, $category, true);
        $this->addLogMessage($json, '', false);
        
        return $this;
    }

    /**
     * @param string $message
     * @param mixed ...$args
     * @return $this
     */
    public function logError(string $message, ...$args) : Application_Logger
    {
        return $this->addLogMessage($message, self::CATEGORY_ERROR, true, ...$args);
    }

    public function logUI(string $message, ...$args) : Application_Logger
    {
        return $this->addLogMessage($message, self::CATEGORY_UI, true, ...$args);
    }

    /**
     * @param string $message
     * @param string|NULL $category
     * @param bool|NULL $withTime
     * @param mixed ...$args
     * @return $this
     */
    private function addLogMessage(string $message, ?string $category=self::CATEGORY_GENERAL, ?bool $withTime=true, ...$args) : Application_Logger
    {
        if (empty($category)) {
            $category = self::CATEGORY_GENERAL;
        }

        if(!$this->isLoggingEnabled($category))
        {
            return $this;
        }

        if(!empty($args)) {
            $message = sprintf($message, ...$args);
        }

        if($withTime === null || $withTime === true) {
            $message = $this->getTime().' | '.$message;
        }

        $message = $category . ' | ' . $message;

        if($this->memoryStorageEnabled === true) {
            $this->log[] = $message;
        }

        if($this->logMode === self::LOG_MODE_ECHO)
        {
            echo $this->format($message);
        }
        else if($this->logMode === self::LOG_MODE_FILE)
        {
            if(!$this->logFileInitialized) {
                $this->logFileInitialized = true;
                FileHelper::createFolder(dirname($this->logFile));
            }

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
     * @throws FileHelper_Exception
     */
    public function write() : Application_RequestLog_LogWriter
    {
        return AppFactory::createRequestLog()->writeLog($this);
    }
}
