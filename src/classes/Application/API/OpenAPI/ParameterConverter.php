<?php
/**
 * @package API
 * @subpackage OpenAPI
 */

declare(strict_types=1);

namespace Application\API\OpenAPI;

use Application\API\APIMethodInterface;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\APIParamManager;
use Application\API\Parameters\Flavors\APIHeaderParameterInterface;
use Application\API\Parameters\ValueLookup\SelectableValueParamInterface;
use Application\API\Traits\JSONRequestInterface;

/**
 * Converts framework `APIParameterInterface` instances into OpenAPI 3.1
 * parameter objects or request body schema property definitions.
 *
 * ## Parameter location rules
 *
 * - **`in: "header"`** — parameter implements {@see APIHeaderParameterInterface}.
 * - **`in: "query"`** — non-header parameter on a method implementing {@see \Application\API\Traits\RequestRequestInterface}.
 * - **Request body schema property** — non-header parameter on a method implementing {@see JSONRequestInterface}.
 *
 * ## Reserved parameters
 *
 * The global `method` and `apiVersion` parameters are documented at the path
 * level by the `MethodConverter` and must not appear in per-method parameter
 * lists. {@see convertParameter()} returns `null` for reserved parameters.
 * {@see convertParameters()} skips them automatically.
 *
 * @package API
 * @subpackage OpenAPI
 */
class ParameterConverter
{
    private APIMethodInterface $method;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;
    }

    /**
     * Converts a single API parameter into an OpenAPI 3.1 representation.
     *
     * Returns:
     * - A full OpenAPI **parameter object** `{name, in, required, description?, schema}`
     *   for `"query"` and `"header"` parameters.
     * - A JSON Schema **property definition** `{type, format?, description?, enum?, default?}`
     *   for parameters that are part of a JSON request body.
     * - `null` for reserved parameters (`method`, `apiVersion`).
     *
     * @param APIParameterInterface $param
     * @return array<string,mixed>|null
     */
    public function convertParameter(APIParameterInterface $param) : ?array
    {
        if($this->isReservedParam($param)) {
            return null;
        }

        if($param instanceof APIHeaderParameterInterface) {
            return $this->buildOpenAPIParam($param, 'header');
        }

        if($this->method instanceof JSONRequestInterface) {
            return $this->buildSchemaProperty($param);
        }

        return $this->buildOpenAPIParam($param, 'query');
    }

    /**
     * Batch-converts all parameters from an `APIParamManager`, separating them
     * into OpenAPI `parameters[]` entries (query/header) and request body schema
     * properties (JSON body).
     *
     * Also collects required body property names for use in the `required` array
     * of the request body schema.
     *
     * Reserved parameters (`method`, `apiVersion`) are silently excluded.
     *
     * @param APIParamManager $manager The parameter manager from the API method.
     * @return array{
     *     parameters: array<int, array<string,mixed>>,
     *     requestBodyProperties: array<string, array<string,mixed>>,
     *     requiredBodyProperties: string[]
     * }
     */
    public function convertParameters(APIParamManager $manager) : array
    {
        $parameters = array();
        $requestBodyProperties = array();
        $requiredBodyProperties = array();

        foreach($manager->getParams() as $param)
        {
            $converted = $this->convertParameter($param);

            if($converted === null) {
                continue;
            }

            // Body properties (JSON request) go into requestBodyProperties,
            // all other params (query and header) go into the parameters array.
            if(!($param instanceof APIHeaderParameterInterface) && $this->method instanceof JSONRequestInterface) {
                $requestBodyProperties[$param->getName()] = $converted;

                if($param->isRequired()) {
                    $requiredBodyProperties[] = $param->getName();
                }
            } else {
                $parameters[] = $converted;
            }
        }

        return array(
            'parameters' => $parameters,
            'requestBodyProperties' => $requestBodyProperties,
            'requiredBodyProperties' => $requiredBodyProperties,
        );
    }

    /**
     * Builds an OpenAPI parameter object for a query or header parameter.
     *
     * @param APIParameterInterface $param
     * @param string $in Either `"query"` or `"header"`.
     * @return array<string,mixed>
     */
    private function buildOpenAPIParam(APIParameterInterface $param, string $in) : array
    {
        $result = array(
            'name' => $param->getName(),
            'in' => $in,
            'required' => $param->isRequired(),
            'schema' => $this->buildTypeSchema($param),
        );

        if($param->hasDescription()) {
            $result['description'] = $param->getDescription();
        }

        return $result;
    }

    /**
     * Builds an OpenAPI JSON Schema property definition for a request body parameter.
     *
     * @param APIParameterInterface $param
     * @return array<string,mixed>
     */
    private function buildSchemaProperty(APIParameterInterface $param) : array
    {
        $schema = $this->buildTypeSchema($param);

        if($param->hasDescription()) {
            $schema['description'] = $param->getDescription();
        }

        return $schema;
    }

    /**
     * Builds the inner OpenAPI type schema for a parameter (type, format, enum, default).
     *
     * @param APIParameterInterface $param
     * @return array<string,mixed>
     */
    private function buildTypeSchema(APIParameterInterface $param) : array
    {
        $schema = TypeMapper::mapType($param->getTypeLabel());

        $default = $param->getDefaultValue();
        if($default !== null) {
            $schema['default'] = $default;
        }

        if($param instanceof SelectableValueParamInterface) {
            $enum = array();
            foreach($param->getSelectableValues() as $selectableValue) {
                $enum[] = $selectableValue->getValue();
            }
            $schema['enum'] = $enum;
        }

        return $schema;
    }

    /**
     * Returns whether the parameter name is a reserved global parameter
     * (`method` or `apiVersion`) that should not appear in per-method parameter lists.
     *
     * @param APIParameterInterface $param
     * @return bool
     */
    private function isReservedParam(APIParameterInterface $param) : bool
    {
        return in_array(
            $param->getName(),
            APIParameterInterface::RESERVED_PARAM_NAMES,
            true
        );
    }
}
