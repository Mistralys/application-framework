<?php

declare(strict_types=1);

use AppUtils\FileHelper;
use AppUtils\Microtime;

class Application_RequestLog_LogName
{
    /**
     * @var int
     */
    private $minutes;

    /**
     * @var int
     */
    private $seconds;

    /**
     * @var int
     */
    private $microseconds;

    /**
     * @var string
     */
    private $sessionID;

    /**
     * @var string
     */
    private $requestID;

    /**
     * @var string
     */
    private $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;

        $this->parseName();
    }

    public static function generateName(Microtime $time, string $sessionID, string $requestID) : string
    {
        return sprintf(
            '%s%s-%s-%s.log',
            $time->format('is'),
            $time->getMicroseconds(),
            $sessionID,
            $requestID
        );
    }

    private function parseName() : void
    {
        $parts = explode('-', FileHelper::removeExtension($this->fileName));

        $this->minutes = (int)substr($parts[0], 0, 2);
        $this->seconds = (int)substr($parts[0], 2, 4);
        $this->microseconds = (int)substr($parts[0], 4);
        $this->sessionID = $parts[1];
        $this->requestID = $parts[2];
    }

    public function getFileName() : string
    {
        return $this->fileName;
    }

    /**
     * @return int
     */
    public function getMicroseconds() : int
    {
        return $this->microseconds;
    }

    /**
     * @return int
     */
    public function getMinutes() : int
    {
        return $this->minutes;
    }

    /**
     * @return string
     */
    public function getRequestID() : string
    {
        return $this->requestID;
    }

    /**
     * @return int
     */
    public function getSeconds() : int
    {
        return $this->seconds;
    }

    /**
     * @return string
     */
    public function getSessionID() : string
    {
        return $this->sessionID;
    }

    public function isSimulatedSession() : bool
    {
        return $this->getSessionID() === Application_RequestLog::SESSION_ID_SIMULATED;
    }

    public function isNoSession() : bool
    {
        return $this->getSessionID() === Application_RequestLog::SESSION_ID_NONE;
    }

    public function hasSession() : bool
    {
        return !$this->isSimulatedSession() && !$this->isNoSession();
    }
}
