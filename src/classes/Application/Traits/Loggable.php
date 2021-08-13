<?php

declare(strict_types=1);

trait Application_Traits_Loggable
{
    abstract public function getLogIdentifier() : string;

    /**
     * @var bool|null
     */
    protected $loggableLoggingEnabled = null;

    public function isLoggingEnabled() : bool
    {
        if($this->loggableLoggingEnabled === null)
        {
            $this->loggableLoggingEnabled = Application::getLogger()->isLoggingEnabled();
        }

        return $this->loggableLoggingEnabled;
    }

    /**
     * Logs a message. If additional parameters are present,
     * they will be injected into the message using the PHP
     * `sprintf` function.
     *
     * @param string $message
     * @param mixed ...$args
     */
    protected function log(string $message, ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        Application::getLogger()->logSF(
            sprintf(
            '%s | %s',
                $this->getLogIdentifier(),
                $message
            ),
            ...$args
        );
    }
    
    protected function logData(array $data) : void
    {
        Application::getLogger()->logData($data);
    }
    
    protected function logError(string $message, ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        Application::getLogger()->logError(
            sprintf(
                '%s | %s',
                $this->getLogIdentifier(),
                $message
            ),
            ...$args
        );
    }

    protected function logEvent(string $eventName, string $message='', ...$args) : void
    {
        if($this->isLoggingEnabled() === false)
        {
            return;
        }

        $sep = ' | ';

        if(empty($message))
        {
            $sep = '';
        }

        Application::getLogger()->logEvent($eventName, $this->getLogIdentifier().$sep.$message, ...$args);
    }

    protected function logHeader(string $message, ...$args) : void
    {
        Application::getLogger()->logHeader($message, ...$args);
    }
}
