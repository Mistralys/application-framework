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
use Application\Application;
use AppUtils\ArrayDataCollection;
use AppUtils\AttributeCollection;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\Microtime;
use AppUtils\OperationResult;

/**
 * Used to log messages while the application is running.
 *
 * @package Application
 * @subpackage Logging
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @phpstan-type LoggableArgument OperationResult|Microtime|DateTime|AttributeCollection|ArrayDataCollection|FileHelper\PathInfoInterface|array|mixed
 */
class Application_Logger
{
    public const int LOG_MODE_FILE = 1;
    public const int LOG_MODE_ECHO = 2;
    public const int LOG_MODE_NONE = 3;

    public const int LOG_MODE_DEFAULT = self::LOG_MODE_NONE;

    public const int LINE_LENGTH = 65;

    public const string CATEGORY_GENERAL = 'GEN';
    public const string CATEGORY_UI = 'UI';
    public const string CATEGORY_ERROR = 'ERR';
    public const string CATEGORY_EVENT = 'EVENT';
    public const string CATEGORY_REQUEST = 'REQ';

    public const bool HTML_MODE_DEFAULT = false;
    public const bool LOGGING_ENABLED_DEFAULT = true;
    public const bool MEMORY_STORAGE_ENABLED_DEFAULT = true;

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
     * @param mixed|null $message Arrays are automatically dumped.
     * @param bool $header
     * @param string $category
     * @return Application_Logger
     * @throws JsonException
     */
    public function log(mixed $message = null, bool $header=false, string $category=self::CATEGORY_GENERAL) : Application_Logger
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

        return $this->addLogMessage(self::filterArg($message), $category, true);
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

    /**
     * Logs a request log message that will only be added to the
     * log if the request log is active.
     *
     * @param string $message
     * @param mixed ...$args
     * @return $this
     */
    public function logRequestLog(string $message, ...$args) : self
    {
        if(!$this->isLoggingEnabled() || !Application_RequestLog::isActive()) {
            return $this;
        }

        return $this->addLogMessage($message, self::CATEGORY_REQUEST, ...$args);
    }

    /**
     * Logs data that will only be added to the
     * log if the request log is active.
     *
     * @param mixed $data
     * @param string|null $label
     * @return $this
     * @throws JsonException
     */
    public function logRequestLogData(mixed $data, ?string $label) : self
    {
        if(!$this->isLoggingEnabled() || !Application_RequestLog::isActive()) {
            return $this;
        }

        return $this->logData($data, self::CATEGORY_REQUEST, $label);
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
     * Specialized in logging array data sets.
     *
     * @param ArrayDataCollection|array|mixed $data
     * @param string|NULL $category
     * @param string|NULL $label Label to display above the data dump.
     * @return $this
     * @throws JsonException
     */
    public function logData(mixed $data, ?string $category=null, ?string $label=null) : self
    {
        if(empty($category)) {
            $category = self::CATEGORY_GENERAL;
        }

        if(!$this->isLoggingEnabled($category)) {
            return $this;
        }

        if(empty($label)) {
            $label = 'Data dump';
        }

        $this->addLogMessage(trim($label, ':').':', $category, true);
        $this->enqueueMessage(self::filterArg($data));
        
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
     * @param mixed ...$args Will be converted to something readable using {@see self::filterArgs()}.
     * @return $this
     */
    private function addLogMessage(string $message, ?string $category=self::CATEGORY_GENERAL, ?bool $withTime=true, ...$args) : self
    {
        if (empty($category)) {
            $category = self::CATEGORY_GENERAL;
        }

        if(!$this->isLoggingEnabled($category))
        {
            return $this;
        }

        if(!empty($args)) {
            $args = self::filterArgs($args);
            $message = sprintf($message, ...$args);
        }

        if($withTime === null || $withTime === true) {
            $message = $this->getTime().' | '.$message;
        }

        return $this->enqueueMessage($category . ' | ' . $message);
    }

    /**
     * @param string $message
     * @return $this
     * @throws FileHelper_Exception
     */
    private function enqueueMessage(string $message) : self
    {
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

    /**
     * Filters the arguments passed to the log method,
     * so that they are suitable for logging.
     *
     * Converts arrays to JSON strings, objects to their
     * class names, and resources to their type.
     *
     * @param array<string|int,LoggableArgument> $args
     * @return array<string|int,string>
     * @throws JsonException
     */
    public static function filterArgs(array $args) : array
    {
        return array_map(
            static function ($arg) : string {
                return self::filterArg($arg);
            },
            $args
        );
    }

    /**
     * Converts any variable to a string that is suitable for logging.
     * Some known types are automatically converted to a more readable format,
     * like {@see OperationResult}, {@see ArrayDataCollection} and more.
     *
     * @param LoggableArgument $arg
     * @return string
     * @throws JsonException
     */
    public static function filterArg(mixed $arg) : string
    {
        if($arg instanceof OperationResult) {
            return '[operationResult]'.PHP_EOL.$arg.PHP_EOL.'[/operationResult]';
        }

        if($arg instanceof Microtime) {
            return '[microtime="'.$arg->getISODate(true).'"]';
        }

        if($arg instanceof DateTime) {
            return '[dateTime="'.$arg->format(Microtime::FORMAT_ISO_TZ).'"]';
        }

        if($arg instanceof AttributeCollection) {
            return '[attributeCollection]'.PHP_EOL.self::filterArg($arg->getAttributes()).PHP_EOL.'[/attributeCollection]';
        }

        if($arg instanceof ArrayDataCollection) {
            return '[arrayDataCollection]'.PHP_EOL.self::filterArg($arg->getData()).PHP_EOL.'[/arrayDataCollection]';
        }

        if($arg instanceof FileHelper\PathInfoInterface) {
            return '[pathInfo="'.$arg->getPath().'"]';
        }

        if(is_array($arg))
        {
            return str_replace('\/', '/', json_encode($arg, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        }

        if(is_object($arg))
        {
            return '[object="'.get_class($arg).'"]';
        }

        if(is_null($arg))
        {
            return 'NULL';
        }

        if(is_bool($arg))
        {
            return bool2string($arg);
        }

        if(is_resource($arg))
        {
            return '[resource="'.get_resource_type($arg).'"]';
        }

        return (string)$arg;
    }
    
    private function format(string $message) : string
    {
        if($this->html === false)
        {
            return $message.PHP_EOL;
        }
        
        if(str_contains($message, "\n"))
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
