<?php
/**
 * @package AppFramework
 * @subpackage Helpers
 */

declare(strict_types=1);

namespace Mistralys\AppFramework\Helpers;

use Application\Application;
use AppUtils\ConvertHelper\JSONConverter;
use Throwable;

/**
 * JSON unserializer that wraps around the {@see JSONConverter} with
 * added framework-specific logging and exception handling.
 *
 * What this does:
 *
 * - Create a dedicated exception {@see JSONUnserializerException}
 * - Systematically log the exception with the raw JSON data
 * - Optionally not throw the exception
 * - Get the deserialized data or exception afterwards
 *
 * @package AppFramework
 * @subpackage Helpers
 */
class JSONUnserializer
{
    private ?array $deserialized = null;
    private ?JSONUnserializerException $exception = null;
    private string $context;

    /**
     * @param string $json
     * @param string $operationContext The context in which the JSON data is being unserialized, to aid in debugging.
     * @param bool $throw Whether to throw an exception on error, or just log it and continue.
     * @param int|null $errorCode If no error code is provided, {@see JSONUnserializerException::ERROR_CANNOT_UNSERIALIZE_RESPONSE} will be used.
     * @throws JSONUnserializerException
     */
    public function __construct(string $json, string $operationContext, bool $throw=true, ?int $errorCode=null)
    {
        $this->context = $operationContext;

        try
        {
            $this->deserialized = JSONConverter::json2array($json);
        }
        catch(Throwable $e)
        {
            Application::logError(sprintf('JSON unserialization error during operation context [%s]. Details are added to the error log.', $this->context));

            // Log the exception so it is visible in the error log,
            // including the serialized data for debugging purposes.

            // NOTE: The JSON Converter cuts the data if it is too long,
            // so we log it separately as well.
            $this->exception = new JSONUnserializerException($operationContext, $errorCode, $json, $e)->log();

            if($throw)
            {
                throw $this->exception;
            }
        }
    }

    public static function create(string $json, string $errorContext, bool $throw=true, ?int $errorCode=null) : self
    {
        return new self($json, $errorContext, $throw, $errorCode);
    }

    public function getOperationContext() : string
    {
        return $this->context;
    }

    public function getData() : ?array
    {
        return $this->deserialized;
    }

    public function getException(): ?JSONUnserializerException
    {
        return $this->exception;
    }
}
