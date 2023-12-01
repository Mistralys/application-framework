<?php

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\ConvertHelper;

class Application_ErrorLog_Log
{
    public const ERROR_ENTRY_NUMBER_NOT_FOUND = 43001;
    
   /**
    * @var Application_ErrorLog
    */
    private $errorlog;
    
   /**
    * @var string
    */
    private $file;
    
   /**
    * @var Application_ErrorLog_Log_Entry[]|NULL
    */
    private ?array $entries = null;
    
    public function __construct(Application_ErrorLog $errorlog, string $file)
    {
        $this->errorlog = $errorlog;
        $this->file = $file;
    }
    
    public function getErrorLog() : Application_ErrorLog
    {
        return $this->errorlog;
    }
    
    public function getID() : string
    {
        return md5($this->file);
    }
    
    public function getDate() : DateTime
    {
        return FileHelper::getModifiedDate($this->file);
    }
    
    public function getFilePath() : string
    {
        return $this->file;
    }
    
    public function getFileSize() : int
    {
        return filesize($this->file);
    }
    
    public function getFileSizePretty() : string
    {
        return ConvertHelper::bytes2readable($this->getFileSize());
    }
    
    public function getFileName() : string
    {
        return FileHelper::getFilename($this->file);
    }
    
    public function getMonthNumber() : int
    {
        return intval(substr($this->getFileName(), 0, 2));
    }
    
    public function getMonthName() : string
    {
        return ConvertHelper::month2string($this->getMonthNumber());
    }
    
    public function getAdminViewURL(array $params=array()) : string
    {
        $params['submode'] = 'view';
        $params['eid'] = $this->getID();
        
        return $this->errorlog->getAdminURL($params);
    }
    
   /**
    * Retrieves all log entries.
    * @return Application_ErrorLog_Log_Entry[]
    */
    public function getEntries()
    {
        $this->load();
        
        return $this->entries;
    }
    
    protected function load() : void
    {
        if(isset($this->entries)) {
            return;
        }
        
        $this->entries = array();
        
        $lines = ConvertHelper::explodeTrim('{::}|', FileHelper::readContents($this->file));
        
        // make sure the latest entries are on top
        $lines = array_reverse($lines);
        $logTypes = $this->errorlog->getLogTypes();
        $number = 0;
        
        // build the list entries
        foreach($lines as $line)
        {
            $tokens = explode('|', $line);

            $number++;
            $date = new DateTime(array_shift($tokens));
            $type = array_shift($tokens);
            
            if(!in_array($type, $logTypes)) {
                continue;
            }
            
            $referer = array_shift($tokens);
            $logID = array_shift($tokens);
            $userID = (int)array_shift($tokens);
            
            $class = 'Application_ErrorLog_Log_Entry_'.$type;
            
            $entry = new $class(
                $this, 
                $number,
                $date, 
                $referer, 
                $logID, 
                $userID,
                $tokens
            );
            
            $this->entries[] = $entry;
        }
    }
    
    public function getEntryByRequest() : ?Application_ErrorLog_Log_Entry
    {
        $request = Application_Driver::getInstance()->getRequest();
        
        $number = $request->registerParam('entry_number')
        ->setInteger()
        ->get();
        
        if(!empty($number) && $this->entryNumberExists($number)) {
            return $this->getEntryByNumber($number);
        }
        
        return null;
    }
    
    public function entryNumberExists(int $number)
    {
        $this->load();
        
        foreach($this->entries as $entry) {
            if($entry->getNumber() === $number) {
                return true;
            }
        }
        
        return false;
    }
    
   /**
    * Retrieves a log entry by its number.
    * 
    * @param int $number
    * @throws Application_Exception
    * @return Application_ErrorLog_Log_Entry
    */
    public function getEntryByNumber(int $number) : Application_ErrorLog_Log_Entry
    {
        $this->load();
        
        foreach($this->entries as $entry) {
            if($entry->getNumber() === $number) {
                return $entry;
            }
        }
        
        throw new Application_Exception(
            'No such error log entry found.',
            sprintf(
                'The entry number [%s] does not exist in the log [%s].',
                $number,
                $this->getID()
            ),
            self::ERROR_ENTRY_NUMBER_NOT_FOUND
        );
    }
}
