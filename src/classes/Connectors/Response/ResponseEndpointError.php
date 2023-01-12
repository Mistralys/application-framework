<?php

declare(strict_types=1);

namespace Connectors\Response;

use AppUtils\ConvertHelper_ThrowableInfo;
use Connectors_Response;

class ResponseEndpointError extends ResponseError
{
    private ResponseError $endpointError;

    public function __construct(string $message, string $details, int $code, array $data, ?ConvertHelper_ThrowableInfo $exception=null)
    {
        parent::__construct(
            t('The server returned an error.'),
            sprintf(
                'The error returned by [$response->%1$s()] will be an instance of %2$s. '.PHP_EOL.
                'Use the error\'s [%3$s()] method to get the endpoint error details. ',
                array(Connectors_Response::class, 'getError')[1],
                self::class,
                array(self::class, 'getEndpointError')[1]
            ),
            Connectors_Response::RETURNCODE_ERROR_RESPONSE
        );

        $this->endpointError = new ResponseError(
            $message,
            $details,
            $code,
            $data,
            $exception
        );
    }

    public function getEndpointError() : ResponseError
    {
        return $this->endpointError;
    }

    public function isEndpointError(): bool
    {
        return true;
    }
}
