<?php
/**
 * @package Connectors
 * @subpackage Response
 * @see \Connectors\Response\ResponseError
 */

declare(strict_types=1);

namespace Connectors\Response;

use AppUtils\ConvertHelper_ThrowableInfo;

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

    public function __construct(string $message, string $details, int $code, ?ConvertHelper_ThrowableInfo $exception=null)
    {
        $this->message = $message;
        $this->details = $details;
        $this->code = $code;
        $this->exception = $exception;
    }

    public function getMessage() : string
    {
        return $this->message;
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
}
