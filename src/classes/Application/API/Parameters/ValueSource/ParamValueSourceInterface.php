<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\ValueSource;

/**
 * Interface for the raw-value backing store a {@see \Application\API\Parameters\BaseAPIParameter}
 * resolves itself against, injected by {@see \Application\API\Parameters\APIParamManager::getValueSource()}.
 *
 * Decouples a parameter's {@see \Application\API\Parameters\BaseAPIParameter::resolveValue()}
 * implementation from where its raw value physically comes from: today, either the
 * request (query/POST, {@see RequestValueSource}) or a JSON request body falling back
 * to the request ({@see JSONBodyValueSource}).
 *
 * @package API
 * @subpackage Parameters
 */
interface ParamValueSourceInterface
{
    /**
     * Whether a value is present for the given parameter name in this source.
     *
     * @param string $name
     * @return bool
     */
    public function hasKey(string $name) : bool;

    /**
     * Retrieves the raw, unvalidated value for the given parameter name,
     * or `null` when it is not present in this source.
     *
     * @param string $name
     * @return mixed
     */
    public function getRawValue(string $name) : mixed;

    /**
     * The full list of parameter names known to this source, used as the
     * key source for removed-parameter detection
     * (see {@see \Application\API\BaseMethods\BaseAPIMethod::rejectRemovedParams()}).
     *
     * @return string[]
     */
    public function getKeys() : array;
}
