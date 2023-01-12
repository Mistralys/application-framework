<?php
/**
 * @package Connectors
 * @subpackage Response
 * @see \Connectors\Response\ResponseError
 */

declare(strict_types=1);

namespace Connectors\Response;

use AppUtils\ConvertHelper_Exception;
use AppUtils\ConvertHelper_ThrowableInfo;
use Connectors_Response;

/**
 * Information on an error that occurred in a response.
 *
 * @package Connectors
 * @subpackage Response
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class ResponseError
{
    private string $message;
    private string $details;
    private int $code;
    private ?ConvertHelper_ThrowableInfo $exception;
    private array $data;

    public function __construct(string $message, string $details, int $code, array $data=array(), ?ConvertHelper_ThrowableInfo $exception=null)
    {
        $this->message = $message;
        $this->details = $details;
        $this->code = $code;
        $this->data = $data;
        $this->exception = $exception;
    }

    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @return array<mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function getCode() : int
    {
        return $this->code;
    }

    public function getDetails() : string
    {
        return $this->details;
    }

    public function getException() : ?ConvertHelper_ThrowableInfo
    {
        return $this->exception;
    }

    public function isEndpointError() : bool
    {
        return false;
    }

    /**
     * Fetches a list of all error codes available in the error,
     * from the error itself to any exceptions (and recursively
     * within their previous exceptions).
     *
     * @return string[]
     * @throws ConvertHelper_Exception
     */
    public function getAllCodes() : array
    {
        $exception = $this->getException();
        $codes = array($this->getCode());

        if($exception !== null) {
            $this->getExceptionCodesRecursive($exception, $codes);
        }

        $codes = array_unique($codes);
        $result = array();

        foreach($codes as $code) {
            if($code !== '' && $code !== '0') {
                $result[] = (string)$code;
            }
        }

        return $result;
    }

    public function hasErrorCode($code) : bool
    {
        return in_array((string)$code, $this->getAllCodes(), true);
    }

    /**
     * @param ConvertHelper_ThrowableInfo $info
     * @param string[] $result
     * @return string[]
     * @throws ConvertHelper_Exception
     */
    protected function getExceptionCodesRecursive(ConvertHelper_ThrowableInfo $info, array &$result=null) : array
    {
        $result[] = (string)$info->getCode();

        if($info->hasPrevious()) {
            $this->getExceptionCodesRecursive($info->getPrevious(), $result);
        }

        return $result;
    }
}
