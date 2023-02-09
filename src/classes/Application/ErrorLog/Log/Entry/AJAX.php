<?php

declare(strict_types=1);

use Application\AppFactory;
use function AppUtils\parseVariable;

class Application_ErrorLog_Log_Entry_AJAX extends Application_ErrorLog_Log_Entry
{
    public function getTypeLabel() : string
    {
        return t('AJAX request');
    }
    
    public function getCode() : int
    {
        $code = $this->getTokenIndex(0);
        if(!empty($code)) {
            return (int)$code;
        }
        
        return 0;
    }
    
    public function getMessage() : string
    {
        return $this->getTokenIndex(2);
    }
    
    public function getReferer() : string
    {
        return $this->getTokenIndex(4);
    }
    
    public function getDetails() : string
    {
        return $this->getTokenIndex(3);
    }
    
    public function getPayload() : array
    {
        return $this->getJSONToken(5);
    }

    public function getData() : array
    {
        return $this->getJSONToken(6);
    }
    
    public function getMethod() : string
    {
        return $this->getTokenIndex(1);
    }
    
    public function addProperties(UI_PropertiesGrid $grid) : void
    {
        $grid->add(t('Method'), $this->getMethod());
        $grid->add(t('Details'), $this->getDetails());
        $grid->add(t('Payload'), parseVariable($this->getPayload())->toHTML());
        $grid->add(t('Data'), parseVariable($this->getData())->toHTML());
    }
    
    public static function logError(string $method, string $url, int $code, string $message, string $details, array $payload, array $data) : void
    {
        // 0 = code
        // 1 = method
        // 2 = message
        // 3 = details
        // 4 = url
        // 5 = payload
        // 6 = data
        
        AppFactory::createErrorLog()->logAjaxError(
            array(
                $code,
                $method,
                $message,
                $details,
                $url,
                json_encode($payload, JSON_THROW_ON_ERROR),
                json_encode($data, JSON_THROW_ON_ERROR)
            )
        );
    }
}
