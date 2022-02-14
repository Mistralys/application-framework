<?php
/**
 * File containing the class {@see Application_Session_Exception}.
 *
 * @package Application
 * @subpackage Sessions
 * @see Application_Session_Exception
 */

declare(strict_types=1);

/**
 * Session-handling-specific exception. Allows setting details
 * of the initial PHP error, since the session handling does not
 * throw exceptions.
 *
 * @package Application
 * @subpackage Sessions
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Session_Exception extends Application_Exception
{
    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var string
     */
    private $errorSource;

    public function setErrorDetails(int $code, string $msg, string $file, int $line) : void
    {
        $this->errorCode = $code;
        $this->errorMessage = $msg;
        $this->errorSource = $file.':'.$line;

        $this->details .= PHP_EOL.sprintf(
            'Code: %1$s'.PHP_EOL.
            'Message: %2$s'.PHP_EOL.
            'File: [%3$s:%4$s]',
            $code,
            $msg,
            $file,
            $line
        );
    }

    public function getErrorCode() : int
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage() : string
    {
        return $this->errorMessage;
    }

    /**
     * @return string
     */
    public function getErrorSource() : string
    {
        return $this->errorSource;
    }
}
