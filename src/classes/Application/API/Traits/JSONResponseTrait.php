<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\API\Documentation\Examples\JSONMethodExample;
use Application\API\ErrorResponse;
use Application\API\Parameters\Validation\ParamValidationResults;
use AppUtils\ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter;

/**
 * Trait used to implement JSON response handling in API methods,
 * by implementing the interface {@see JSONResponseInterface}.
 *
 * @package API
 * @subpackage Traits
 *
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
        $this->_sendJSONData(
            JSONResponseInterface::RESPONSE_STATE_SUCCESS,
            array(
                JSONResponseInterface::RESPONSE_KEY_DATA => $data->getData()
            )
        );
    }

    protected function _sendErrorResponse(ErrorResponse $response) : void
    {
        $this->_sendJSONData(
            JSONResponseInterface::RESPONSE_STATE_ERROR,
            array(
                JSONResponseInterface::RESPONSE_KEY_CODE => $response->getErrorCode(),
                JSONResponseInterface::RESPONSE_KEY_MESSAGE => $response->getErrorMessage(),
                JSONResponseInterface::RESPONSE_KEY_DATA => $response->getErrorData()
            )
        );
    }

    private function _sendJSONData(string $state, array $data) : void
    {
        $data[JSONResponseInterface::RESPONSE_KEY_API] = $this->getInfo()->toArray();
        $data[JSONResponseInterface::RESPONSE_KEY_STATE] = $state;

        ksort($data);

        header('Content-Type: application/json');
        echo JSONConverter::var2json($data);
    }

    protected function configureValidationErrorResponse(ErrorResponse $response, ParamValidationResults $results) : void
    {
        $response->appendErrorMessage(sprintf(
            'Details are available in the response %s key.',
            JSONResponseInterface::RESPONSE_KEY_DATA
        ));

        $response->addData(array(
            JSONResponseInterface::RESPONSE_KEY_DATA => $results->serializeErrors()
        ));
    }

    public function renderExample() : string
    {
        return new JSONMethodExample($this)->render();
    }

    public function getReponseKeyDescriptions() : array
    {
        return array();
    }
}
