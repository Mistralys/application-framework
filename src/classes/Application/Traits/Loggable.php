<?php

trait Application_Traits_Loggable
{
    abstract public function getLogIdentifier() : string;
    
    protected function log(string $message) : void
    {
        Application::log(sprintf(
            '%s | %s',
            $this->getLogIdentifier(),
            $message
        ));
    }
    
    protected function logData(array $data) : void
    {
        Application::logData($data);
    }
    
    protected function logError(string $message) : void
    {
        Application::logError('ERROR | '.$message);
    }

    protected function logEvent(string $eventName, string $message='') : void
    {
        $sep = ' | ';

        if(empty($message))
        {
            $sep = '';
        }

        Application::logEvent($eventName, $this->getLogIdentifier().$sep.$message);
    }

    protected function logHeader(string $message) : void
    {
        Application::log($message, true);
    }
}
