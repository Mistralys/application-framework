<?php

declare(strict_types=1);

require_once 'Application/ErrorLog/Log/Entry.php';

class Application_ErrorLog_Log_Entry_JavaScript extends Application_ErrorLog_Log_Entry
{
    public function getTypeLabel() : string
    {
        return t('JavaScript');
    }
    
    public function getCode() : int
    {
        return (int)$this->getTokenIndex(0);
    }
    
    public function getMessage() : string
    {
        return $this->getTokenIndex(2);
    }
    
    public function getDetails() : string
    {
        return $this->getTokenIndex(3);
    }
    
    public function getReferer() : string
    {
        return $this->getTokenIndex(4);
    }
    
    public function getSource() : string
    {
        return $this->getTokenIndex(5);
    }
    
    public function getLine() : int
    {
        $val = $this->getTokenIndex(6);
        if(!empty($val)) {
            return (int)$val;
        }
        
        return 0;
    }
    
    public function getColumn() : int
    {
        $val = $this->getTokenIndex(7);
        if(!empty($val)) {
            return (int)$val;
        }
        
        return 0;
    }
    
    public function addProperties(UI_PropertiesGrid $grid) : void
    {
        $grid->add(t('Line'), $this->getLine());
        $grid->add(t('Column'), $this->getColumn());
        $grid->add(t('Details'), $this->getDetails());
        $grid->add(t('Source'), $this->getSource())->setHelpText(t('The URL or file in which the error happened - not always available.'));
    }
    
    public static function logError(int $code, string $type, string $message, string $details, string $referer, string $url, int $line, int $column)
    {
        // 0 = code
        // 1 = type
        // 2 = message
        // 3 = details,
        // 4 = referer,
        // 5 = url
        // 6 = line
        // 7 = column
        
        $tokens = array(
            $code,
            $type,
            $message,
            $details,
            $referer,
            $url,
            $line,
            $column
        );
        
        Application::createErrorLog()->logJavascriptError($tokens);
    }
}
