<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\API\ErrorResponse;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;

/**
 * @package API
 * @subpackage Traits
 * @see JSONResponseInterface
 */
trait JSONResponseTrait
{
    public function getResponseMime() : string
    {
        return 'application/json';
    }

    protected function _sendSuccessResponse(ArrayDataCollection $data) : void
    {
        $this->sendJSONResponse(
            JSONResponseInterface::RESPONSE_STATE_SUCCESS,
            array(
                JSONResponseInterface::RESPONSE_KEY_DATA => $data->getData()
            )
        );
    }

    protected function _sendErrorResponse(ErrorResponse $response) : void
    {
        $this->sendJSONResponse(
            JSONResponseInterface::RESPONSE_STATE_ERROR,
            array(
                JSONResponseInterface::RESPONSE_KEY_CODE => $response->getErrorCode(),
                JSONResponseInterface::RESPONSE_KEY_MESSAGE => $response->getErrorMessage(),
                JSONResponseInterface::RESPONSE_KEY_DATA => $response->getErrorData()
            )
        );
    }

    private function sendJSONResponse(string $state, array $data) : void
    {
        $data[JSONResponseInterface::RESPONSE_KEY_API] = $this->getInfo()->toArray();
        $data[JSONResponseInterface::RESPONSE_KEY_STATE] = $state;

        ksort($data);

        header('Content-Type: application/json');
        echo JSONConverter::var2json($data);
    }
}
