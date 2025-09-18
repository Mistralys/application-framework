<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

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
                ->setMessage('Failed to read input data')
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

    protected function collectRequestErrorData() : array
    {
        return array(
            JSONRequestInterface::RESPONSE_KEY_ERROR_JSON_REQUEST_DATA => $this->getRequestData()
        );
    }
}
