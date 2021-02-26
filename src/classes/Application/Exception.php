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
   /**
    * @var string
    */
    protected $id;
    
    /**
     * Whether to log the exception in the error log.
     * @var boolean
     */
    private $logging = true;
    
   /**
    * @var boolean
    */
    private $logged = false;

    /**
     * The additional developer information is only included in
     * error messages if the application is in developer mode.
     *
     * @param string $message
     * @param string $developerInfo
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(string $message, string $developerInfo = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $developerInfo, $code, $previous);

        $this->id = md5(strval(microtime(true)).'-exception-'.$code.'-'.$message);
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
        return $this->logged;
    }

    public function log() : void
    {
        if(!$this->logging) 
        {
            return;
        }
        
        if(!$this->logged)
        {
            Application_ErrorLog_Log_Entry_Exception::logException($this);
            $this->logged = true;
        }
    }
}
