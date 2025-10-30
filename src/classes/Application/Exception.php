<?php
/**
 * File containing the {@see Application_Exception} class.
 * 
 * @package Application
 * @subpackage Core
 * @see Application_Exception
 */

declare(strict_types=1);

use AppUtils\BaseException;

/**
 * Simple exception extension that allows adding developer-oriented
 * debug information that only get displayed in error messages of the
 * application is in developer mode to protect that kind of sensitive
 * information.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Exception extends BaseException
{
    protected string $id;
    
    /**
     * Whether to log the exception in the error log.
     * @var boolean
     */
    private bool $logging = true;
    private static ?string $requestID = null;
    private static int $exceptionCounter = 0;
    private ?string $pageOutput = null;
    private ?string $logID = null;

    /**
     * The additional developer information is only included in
     * error messages if the application is in developer mode.
     *
     * @param string $message
     * @param string $developerInfo
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message, string $developerInfo = '', int $code = 0, ?Throwable $previous = null)
    {
        self::$exceptionCounter++;

        if(!isset(self::$requestID))
        {
            self::$requestID = md5((string)microtime(true));
        }

        $this->id = self::$requestID.'_'.self::$exceptionCounter;

        if(Application::isUnitTestingRunning())
        {
            $message .= PHP_EOL.$developerInfo;

            if($previous)
            {
                $message .= PHP_EOL.
                'Previous exception: [#'.$previous->getCode().'] '.$previous->getMessage();
            }
        }
        else
        {
            Application::logError(sprintf('EXCEPTION #%s: %s', $this->getCode(), $this->getMessage()));
            Application::logError($developerInfo);
        }

        parent::__construct($message, $developerInfo, $code, $previous);
    }
    
   /**
    * Retrieves the exception's unique ID.
    * @return string
    */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Additional developer-oriented information (if provided).
     * @return string
     */
    public function getDeveloperInfo() : string
    {
        return $this->getDetails();
    }

    /**
     * Disables the automatic logging feature of this
     * exception: it will not be logged in the error log.
     */
    public function disableLogging() : void
    {
        $this->logging = false;
    }

    /**
     * Automatically logs the exception in the error log.
     */
    public function __destruct()
    {
        $this->log();
    }

    public function isLoggingEnabled() : bool
    {
        return $this->logging;
    }

    /**
     * Whether the exception has been logged already.
     *
     * @return bool
     */
    public function isLogged(): bool
    {
        return isset($this->logID);
    }

    /**
     * Adds a log entry for this exception in the error log.
     *
     * > NOTE: This is only done once. Calling it multiple times
     * > has no effect.
     *
     * @return $this
     */
    public function log() : self
    {
        if(!$this->logging) 
        {
            return $this;
        }
        
        $this->generateLogID();

        return $this;
    }

    private function generateLogID() : string
    {
        if(!isset($this->logID)) {
            $this->logID = Application_ErrorLog_Log_Entry_Exception::logException($this);
        }

        return $this->logID;
    }

    public function getLogID() : string
    {
        return $this->generateLogID();
    }

    public static function getDeveloperMessage(Throwable $e) : string
    {
        $code = $e->getCode();
        if(empty($code))
        {
           $code = '(none)';
        }

        $details = '';
        if($e instanceof BaseException)
        {
            $details = $e->getDetails();
        }

        return sprintf(
            'Exception [%s] | Code: [%s] | Message: [%s] | Details: [%s]',
            get_class($e),
            $code,
            $e->getMessage(),
            $details
        );
    }

    /**
     * Sets the output of the page that was being generated
     * up to the point the exception was thrown.
     *
     * @param string $output
     * @return $this
     */
    public function setPageOutput(string $output) : self
    {
        $this->pageOutput = $output;
        return $this;
    }

    public function getPageOutput() : ?string
    {
        return $this->pageOutput;
    }
}
