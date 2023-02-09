<?php

declare(strict_types=1);

use Application\AppFactory;
use AppUtils\ConvertHelper_ThrowableInfo;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;

abstract class Application_ErrorLog_Log_Entry
{
   /**
    * @var Application_ErrorLog_Log
    */
    protected $log;
    
   /**
    * @var string[]
    */
    protected $tokens;
    
   /**
    * @var DateTime
    */
    protected $time;
    
    protected $referer;
    
    protected $logID;
    
   /**
    * @var integer
    */
    protected $userID;
    
    protected $user;
    
    protected $number;
    
    public function __construct(Application_ErrorLog_Log $log, int $number, DateTime $time, string $referer, string $logID, int $userID, array $tokens)
    {
        $this->log = $log;
        $this->number = $number;
        $this->tokens = $tokens;
        $this->time = $time;
        $this->referer = $referer;
        $this->logID = $logID;
        $this->userID = $userID;
    }
    
    abstract public function getTypeLabel() : string;
    
    abstract public function addProperties(UI_PropertiesGrid $grid) : void;
    
    public function getNumber() : int
    {
        return $this->number;
    }
    
    public function getCode() : int
    {
        return 0;
    }
    
    public function hasCode() : bool
    {
        return $this->getCode() !== 0;
    }
    
    public function getMessage() : string
    {
        return $this->getTokenIndex(0);
    }
    
    public function getUserID() : int
    {
        return $this->userID;
    }
    
    public function getUserName() : string
    {
        $user = $this->getUser();
        
        if($user) {
            return $user->getName();
        }
        
        return t('Unknown user');
    }
    
    public function getUser() : ?Application_Users_User
    {
        if(!isset($this->user)) 
        {
            $users = AppFactory::createUsers();
            
            if($users->idExists($this->userID)) {
                $this->user = $users->getByID($this->userID);
            } else {
                $this->user = false;
            }
        }
        
        if($this->user !== false) {
            return $this->user;
        }
        
        return null;
    }
    
    public function getTime() : DateTime
    {
        return $this->time;
    }
    
    public function getTimePretty() : string
    {
        return \AppUtils\ConvertHelper::date2listLabel($this->getTime(), true, true);
    }
    
    public function getReferer() : string
    {
        return $this->referer;
    }
    
    public function hasReferer() : bool
    {
        return !empty($this->referer);
    }
    
    public function getLogID() : string
    {
        return $this->logID;
    }
    
    public function getText() : string
    {
        return implode('|', $this->tokens);
    }
    
    public function getTokenIndex(int $index) : string
    {
        if(isset($this->tokens[$index])) {
            return (string)$this->tokens[$index];
        }
        
        return '';
    }
    
    public function getApplogPath() : string
    {
        return $this->log->getErrorLog()->getLogFilePath(
            $this->logID.'.log', 
            (int)$this->time->format('Y'), 
            (int)$this->time->format('m')
        );
    }
    
    public function hasApplog()
    {
        return file_exists($this->getApplogPath());
    }
    
    public function getApplog() : string
    {
        if(!$this->hasApplog()) {
            return '';
        }

        $log = \AppUtils\FileHelper::readLines($this->getApplogPath());
        
        $keep = array();
        foreach($log as $line) 
        {
            $line = trim($line);
            if(empty($line)) {
                continue;
            }
            
            $tokens = explode('|', $line);
            array_shift($tokens);
            
            $keep[] = implode('<span class="muted">|</span>', $tokens);
        }
        
        return '<div>'.implode('</div><div>', $keep).'</div>';
    }
    
    public function getAdminApplogURL(array $params=array()) : string
    {
        $params['viewlog'] = $this->logID;
        
        return $this->log->getAdminViewURL($params);
    }

    public function getAdminViewURL(array $params=array()) : string
    {
        $params['entry_number'] = $this->getNumber();
        
        return $this->log->getAdminViewURL($params);
    }

    public function getTracePath() : string
    {
        return $this->log->getErrorLog()->getLogFilePath(
            $this->logID.'.trace',
            (int)$this->time->format('Y'),
            (int)$this->time->format('m')
        );
    }
    
    public function hasTrace() : bool
    {
        return file_exists($this->getTracePath());
    }
    
    public function getTrace() : ConvertHelper_ThrowableInfo
    {
        $data = FileHelper::parseJSONFile($this->getTracePath());
        
        return ConvertHelper_ThrowableInfo::fromSerialized($data);
    }
    
    protected function getJSONToken(int $index) : array
    {
        $json = $this->getTokenIndex($index);
        $decoded = json_decode($json, true);
        
        if(is_array($decoded)) {
            return $decoded;
        }
        
        return array();
    }
}
