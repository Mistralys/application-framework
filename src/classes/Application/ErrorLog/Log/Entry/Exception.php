<?php

declare(strict_types=1);

use function AppUtils\parseThrowable;

class Application_ErrorLog_Log_Entry_Exception extends Application_ErrorLog_Log_Entry
{
    public function getTypeLabel() : string
    {
        return t('PHP Exception');
    }
    
    public function getExceptionID() : string
    {
        return $this->getTokenIndex(5);
    }
    
    public function getClassName() : string
    {
        return $this->getTokenIndex(4);
    }

    public function getCode() : int
    {
        return (int)$this->getTokenIndex(0);
    }
    
    public function getMessage() : string
    {
        return $this->getTokenIndex(1);
    }
    
    public function getDeveloperInfo() : string
    {
        return $this->getTokenIndex(6);
    }
    
    public function getFile() : string
    {
        return $this->getTokenIndex(2);
    }
    
    public function getFileRelative()
    {
        return \AppUtils\FileHelper::relativizePath(
            $this->getFile(), 
            APP_ROOT
        );
    }
    
    public function getLine() : int
    {
        return (int)$this->getTokenIndex(3);
    }
    
    public function hasPrevious() : bool
    {
        $file = $this->getPrevFile();
        return !empty($file);
    }
    
    public function getPrevExceptionID() : string
    {
        return $this->getTokenIndex(12);
    }
    
    public function getPrevDevelInfo() : string
    {
        return $this->getTokenIndex(13);
    }
    
    public function getPrevCode() : int
    {
        $code = $this->getTokenIndex(7);
        if(!empty($code)) {
            return (int)$code;
        }
        
        return 0;
    }
    
    public function getPrevMessage() : string
    {
        return $this->getTokenIndex(8);
    }
    
    public function getPrevFile() : string
    {
        return $this->getTokenIndex(9);
    }
    
    public function getPrevFileRelative() : string
    {
        return \AppUtils\FileHelper::relativizePath(
            $this->getPrevFile(), 
            APP_ROOT
        );
    }
    
    public function getPrevLine() : int
    {
        $line = $this->getTokenIndex(10);
        if(!empty($line)) {
            return (int)$line;
        }
        
        return 0;
    }
    
    public function getPrevClassName() : string
    {
        return $this->getTokenIndex(11);
    }
    
    public function addProperties(UI_PropertiesGrid $grid) : void
    {
        $grid->add(t('Class name'), $this->getClassName());
        $grid->add(t('File'), $this->getFileRelative());
        $grid->add(t('Line'), $this->getLine());
        $grid->add(t('Exception ID'), $this->getExceptionID());
        $grid->add(t('Developer info'), $this->getDeveloperInfo());

        if($this->hasPrevious()) 
        {
            $grid->addHeader(t('Previous exception'));
            $grid->add(t('Class name'), $this->getPrevClassName());
            $grid->add(t('Code'), $this->getPrevCode());
            $grid->add(t('Message'), $this->getPrevMessage());
            $grid->add(t('File'), $this->getPrevFileRelative());
            $grid->add(t('Line'), $this->getPrevLine());
            $grid->add(t('Exception ID'), $this->getPrevExceptionID());
            $grid->add(t('Developer info'), $this->getPrevDevelInfo());
        }
    }
    
    public static function logException(Application_Exception $exception)
    {
        // INDEX MAPPING
        
        //  0 = code
        //  1 = message
        //  2 = file
        //  3 = line
        //  4 = classname
        //  5 = id
        //  6 = develinfo
        //  7 = prev code
        //  8 = prev message
        //  9 = prev file
        // 10 = prev line
        // 11 = prev classname
        // 12 = prev id
        // 13 = prev develinfo
        
        $tokens = array();
        self::addTokens($tokens, $exception);

        $prev = $exception->getPrevious();
        if(is_object($prev)) {
            self::addTokens($tokens, $prev);            
        }
        
        $logger = Application::createErrorLog();
        $logID = $logger->logException(
            $tokens, 
            $exception->getID() // the ID to tie the app log to the exception
        );
        
        $logger->logTrace(
            $logID,
            parseThrowable($exception)
        );
    }
    
    protected static function addTokens(&$tokens, Throwable $exception)
    {
        $tokens[] = $exception->getCode();
        $tokens[] = $exception->getMessage();
        $tokens[] = $exception->getFile();
        $tokens[] = $exception->getLine();
        $tokens[] = get_class($exception);
        
        if($exception instanceof Application_Exception)
        {
            $tokens[] = $exception->getID();
            $tokens[] = $exception->getDeveloperInfo();
        }
        else
        {
            $tokens[] = '0';
            $tokens[] = '';
        }
    }
}
