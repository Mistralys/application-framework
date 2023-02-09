<?php
/**
 * File containing the {@see Application_ErrorLog} class.
 * 
 * @package Application
 * @subpackage Logging
 * @see Application_ErrorLog
 */

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\FileHelper;
use AppUtils\ConvertHelper_ThrowableInfo;

/**
 * File containing the {@see Application_ErrorLog} class.
 *
 * @package Application
 * @subpackage Logging
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_ErrorLog
{
    public const ERROR_UNKNOWN_LOG = 42701;

    const LOG_TYPE_EXCEPTION = 'Exception';
    const LOG_TYPE_AJAX = 'AJAX';
    const LOG_TYPE_JAVASCRIPT = 'JavaScript';
    const LOG_TYPE_GENERAL = 'General';
    
   /**
    * @var string
    */
    private $folder;
    
   /**
    * @var Application_ErrorLog_Log[]
    */
    private $logs = array();
    
   /**
    * @var boolean
    */
    private $loaded = false;
    
   /**
    * @var int
    */
    private $year;
    
   /**
    * @var string[]
    */
    private $logTypes;
    
   /**
    * @var string
    */
    private $defaultLogType = self::LOG_TYPE_GENERAL;
    
    public function __construct(int $year=0)
    {
        if($year <= 0) 
        {
            $year = (int)date('Y');
        }
        
        $this->year = $year;
        $this->folder = $this->getBaseFolder($this->year);
        
        $this->logTypes = array(
            self::LOG_TYPE_AJAX,
            self::LOG_TYPE_EXCEPTION,
            self::LOG_TYPE_GENERAL,
            self::LOG_TYPE_JAVASCRIPT
        );
    }
    
   /**
    * Retrieves the full path to the error logs folder. If no
    * year is specified, the current year is used.
    * 
    * @param int $year
    * @return string
    */
    public function getBaseFolder(int $year=0) : string
    {
        if($year <= 0) 
        {
            $year = (int)date('Y');
        }
        
        $folder = sprintf(
            '%s/error/%s',
            AppFactory::createLogger()->getLogFolder(),
            $year
        );
        
        FileHelper::createFolder($folder);
        
        return $folder;
    }
    
   /**
    * Retrieves a list of all valid log entry types.
    * @return array
    *
    * @see Application_ErrorLog::LOG_TYPE_AJAX
    * @see Application_ErrorLog::LOG_TYPE_EXCEPTION
    * @see Application_ErrorLog::LOG_TYPE_GENERAL
    * @see Application_ErrorLog::LOG_TYPE_JAVASCRIPT
    */
    public function getLogTypes() : array
    {
        return $this->logTypes;
    }
    
    public function getYear() : int
    {
        return $this->year;
    }
    
    public function getFolder() : string
    {
        return $this->folder;
    }
    
   /**
    * Retrieves a list of all available error logs
    * (one log per month).
    * 
    * @return Application_ErrorLog_Log[]
    */
    public function getLogs()
    {
        $this->load();
        
        return $this->logs;
    }
    
    private function load() : void
    {
        if($this->loaded) 
        {
            return;
        }
        
        $entries = array();
        
        $d = new DirectoryIterator($this->folder);
        foreach ($d as $item)
        {
            if(!$item->isFile()) {
                continue;
            }
            
            $ext = \AppUtils\FileHelper::getExtension($item);
            if($ext != 'log') {
                continue;
            }
            
            $entries[] = new Application_ErrorLog_Log($this, $item->getPathname());
        }
        
        usort($entries, function(Application_ErrorLog_Log $a, Application_ErrorLog_Log $b) 
        {
            if($a->getDate() > $b->getDate()) {
                return -1;
            }
            
            if($a->getDate() < $b->getDate()) {
                return 1;
            }
            
            return 0;
        });
        
        $this->logs = $entries;
    }
    
    public function getByRequest() : ?Application_ErrorLog_Log
    {
        $request = Application_Driver::getInstance()->getRequest();
        
        $eid = $request->registerParam('eid')->setMD5()->get();
        if(!empty($eid) && $this->idExists($eid)) {
            return $this->getByID($eid);
        }
        
        return null;
    }
    
    public function idExists(string $eid) : bool
    {
        $this->load();
        
        foreach($this->logs as $log) 
        {
            if($log->getID() === $eid) 
            {
                return true;
            }
        }
        
        return false;
    }
    
   /**
    * Retrieves an error log by its ID. Throws an
    * exception if not found.
    * 
    * @param string $eid
    * @throws Application_Exception
    * @return Application_ErrorLog_Log
    */
    public function getByID(string $eid) : Application_ErrorLog_Log
    {
        $this->load();
        
        foreach($this->logs as $log) 
        {
            if($log->getID() === $eid) 
            {
                return $log;
            }
        }
        
        throw new Application_Exception(
            'Unknown error log ID',
            sprintf(
                'The log with ID [%s] could not be found.',
                $eid
            ),
            self::ERROR_UNKNOWN_LOG
        );
    }

    public function getAdminListURL(array $params=array()) : string
    {
        $params['submode'] = 'list';
        
        return $this->getAdminURL($params);
    }

    public function getAdminDeleteAllURL(array $params=array()) : string
    {
        $params['deleteall'] = 'yes';
        
        return $this->getAdminListURL($params);
    }

    public function getAdminTriggerExceptionURL(array $params=array()) : string
    {
        $params['trigger_exception'] = 'yes';
        
        return $this->getAdminListURL($params);
    }
    
    public function getAdminURL(array $params=array()) : string
    {
        $request = Application_Driver::getInstance()->getRequest();
        
        $params['page'] = 'devel';
        $params['mode'] = 'errorlog';
        
        return $request->buildURL($params);
    }
    
    public function deleteLog(Application_ErrorLog_Log $log) : void
    {
        // clean up stored app logs
        $entries = $log->getEntries();
        
        foreach($entries as $entry)
        {
            if($entry->hasApplog()) {
                FileHelper::deleteFile($entry->getApplogPath());
            }
        }
        
        FileHelper::deleteFile($log->getFilePath());
        
        // force reloading logs
        $this->unload();
    }
    
    private function unload() : void
    {
        $this->loaded = false;
        $this->logs = array();
    }
    
   /**
    * Logs an error message to file. Organizes logs per month,
    * and if the writeLog parameter is set to true all messages
    * logged up to this point will be written to a separate file
    * to be viewed later.
    *
    * @param string $message
    * @param boolean $writeLog Whether to write the application log to file for this error
    * @param string $logID The ID to use for the logfile. Defaults to an auto generated ID.
    * @return string The log ID used to save the error.
    */
    public function logError(string $type, string $message, bool $writeLog = false, string $logID='') : string
    {
        $userID = 0;
        
        $logFile = sprintf(
            '%s/%s.log',
            $this->getFolder(),
            date('m')
        );
        
        if(empty($logID)) 
        {
            $logID = md5(microtime(true).'-applog');
        }
        
        if(!in_array($type, $this->logTypes))
        {
            $type = $this->defaultLogType;
        }
        
        try
        {
            if(Application::isSessionReady())
            {
                $user = Application::getUser();
                $userID = $user->getID();
            }
        }
        catch (Application_Exception $e)
        {
            $e->disableLogging();
        }
        
        $tokens = array(
            '{::}', // this is used to parse the log
            date('d.m.Y H:i:s'),
            $type,
            getRequestURI(),
            $logID,
            $userID,
            $message
        );
        
        $line = implode('|', $tokens);
        
        error_log($line . PHP_EOL, 3, $logFile);
        
        if($writeLog)
        {
            $log = AppFactory::createLogger()->getLog();

            error_log(implode(PHP_EOL, $log), 3, $this->getLogFilePath($logID.'.log'));
        }
        
        return $logID;
    }
    
    public function getLogFilePath(string $fileName, int $year=0, int $month=0) : string
    {
        if($month <= 0) 
        {
            $month = (int)date('m');
        }
        
        if($year <= 0)
        {
            $year = (int)date('Y');
        }
            
        $folder = $this->getBaseFolder($year).'/'.sprintf('%02d', $month*1);
        
        FileHelper::createFolder($folder);
        
        return $folder.'/'.$fileName;
    }
    
    public function logAjaxError(array $data) : string
    {
        return $this->logData(self::LOG_TYPE_AJAX, $data, false);
    }
    
    public function logJavascriptError(array $data) : string
    {
        return $this->logData(self::LOG_TYPE_JAVASCRIPT, $data, false);
    }
    
    public function logException(array $data, string $logID) : string
    {
        return $this->logData(self::LOG_TYPE_EXCEPTION, $data, true, $logID);
    }
    
    public function logGeneralError(array $data, bool $writeLog=false, string $logID='') : string
    {
        return $this->logData(self::LOG_TYPE_GENERAL, $data, $writeLog, $logID);
    }
    
    public function logTrace(string $logID, ConvertHelper_ThrowableInfo $info) : void
    {
        $path = $this->getLogFilePath($logID.'.trace');
        
        FileHelper::saveAsJSON($info->serialize(), $path);
    }
    
    private function logData(string $type, array $data, bool $writeLog=true, string $logID='') : string
    {
        $data = array_values($data);
        
        return $this->logError($type, implode('|', $data), $writeLog, $logID);
    }
}
