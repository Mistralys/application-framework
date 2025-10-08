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
    public function getResponseMime(): string
    {
        return 'application/json';
    }

    protected function _sendSuccessResponse(ArrayDataCollection $data): void
    {
        $this->_sendJSONData(
            JSONResponseInterface::RESPONSE_STATE_SUCCESS,
            array(
                JSONResponseInterface::RESPONSE_KEY_DATA => $data->getData()
            )
        );
    }

    protected function _sendErrorResponse(ErrorResponse $response): void
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

    /**
     * Weights for sorting the response keys in the output.
     * @var array<string,int>
     */
    private const array KEY_WEIGHTS = array(
        JSONResponseInterface::RESPONSE_KEY_STATE => 1,
        JSONResponseInterface::RESPONSE_KEY_CODE => 2,
        JSONResponseInterface::RESPONSE_KEY_MESSAGE => 3,
        JSONResponseInterface::RESPONSE_KEY_DATA => 4,
        JSONResponseInterface::RESPONSE_KEY_API => 5,
    );

    private function _sendJSONData(string $state, array $data): void
    {
        $data[JSONResponseInterface::RESPONSE_KEY_API] = $this->getInfo()->toArray();
        $data[JSONResponseInterface::RESPONSE_KEY_STATE] = $state;

        // Custom sort using KEY_WEIGHTS
        $weights = self::KEY_WEIGHTS;
        $apiKey = JSONResponseInterface::RESPONSE_KEY_API;
        $maxWeight = max($weights);
        $defaultStart = $maxWeight + 1;

        $keys = array_keys($data);
        $sortMap = [];
        $alphaKeys = [];
        foreach ($keys as $key) {
            if ($key === $apiKey) {
                $sortMap[$key] = PHP_INT_MAX; // Always last
            } elseif (isset($weights[$key])) {
                $sortMap[$key] = $weights[$key];
            } else {
                $alphaKeys[] = $key;
            }
        }
        // Sort alphabetically for keys not in weights
        sort($alphaKeys, SORT_STRING);
        foreach ($alphaKeys as $i => $key) {
            $sortMap[$key] = $defaultStart + $i;
        }
        // Sort keys by their assigned sort value
        uksort($data, static function($a, $b) use ($sortMap) {
            return $sortMap[$a] <=> $sortMap[$b];
        });

        header('Content-Type: application/json');
        echo JSONConverter::var2json($data);
    }

    protected function configureValidationErrorResponse(ErrorResponse $response, ParamValidationResults $results): void
    {
        $response->appendErrorMessage(sprintf(
            'Details are available in the response `%s` key.',
            JSONResponseInterface::RESPONSE_KEY_DATA
        ));

        $response->addData(array(
            JSONResponseInterface::RESPONSE_KEY_DATA => $results->serializeErrors()
        ));
    }

    public function renderExample(): string
    {
        return new JSONMethodExample($this)->render();
    }

    public function getReponseKeyDescriptions(): array
    {
        return array();
    }
}
