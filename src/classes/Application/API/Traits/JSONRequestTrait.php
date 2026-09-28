<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\Application;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;

/**
 * @package API
 * @subpackage Traits
 * @see JSONRequestInterface
 */
trait JSONRequestTrait
{
    private ?ArrayDataCollection $requestData = null;

    protected function collectRequestData(string $version) : void
    {
        try
        {
            $this->requestData = ArrayDataCollection::createFromJSON($this->getRequestBody());
        }
        catch (JSONConverterException)
        {
            $this->errorResponse(self::ERROR_FAILED_TO_READ_INPUT)
                ->setErrorMessage('Failed to read JSON input data.')
                ->send();
        }
    }

    public function getRequestData(): ArrayDataCollection
    {
        if(!isset($this->requestData)) {
            $this->requestData = ArrayDataCollection::create();
        }

        return $this->requestData;
    }

    public function getRequestMime() : string
    {
        return 'application/json';
    }

    /**
     * SECURITY: This echoes the parsed request body verbatim, which is
     * genuinely useful for local debugging but must never reach a client
     * outside a development environment — a request body can legitimately
     * contain sensitive domain data. Gated on {@see Application::isDevelEnvironment()}
     * here at the trait level (an empty array short-circuits the contribution
     * entirely), and independently gated again by {@see \Application\API\ErrorResponse::getErrorData()}
     * on the same check, since this method's return value is routed through
     * {@see \Application\API\ErrorResponse::addRequestData()} — the
     * dedicated, redaction-subject channel — not {@see \Application\API\ErrorResponse::addData()}.
     * An API key never appears here regardless: it is a header-only
     * parameter (see the contract comment on
     * {@see \Application\API\Clients\API\Params\APIKeyParam::getHeaderValue()})
     * and is never part of the request body this trait parses.
     *
     * @return array<string,mixed>
     */
    protected function collectRequestErrorData() : array
    {
        if(!Application::isDevelEnvironment()) {
            return array();
        }

        return array(
            // Serialized explicitly via getData(): ArrayDataCollection is not
            // JsonSerializable, so passing the object itself would currently
            // render as an empty '{}' in the JSON response — accidentally
            // safe, but not something this redaction boundary should rely on.
            JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA => $this->getRequestData()->getData()
        );
    }
}
