<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Removed;

/**
 * Collection of {@see RemovedParameter} declarations for a single
 * API method. Owns storage, name lookups, request-based detection,
 * and serialization for the removed parameters an
 * {@see \Application\API\Parameters\APIParamManager} has registered.
 *
 * @package API
 * @subpackage Parameters
 */
class RemovedParamCollection
{
    /**
     * @var array<string,RemovedParameter>
     */
    private array $params = array();

    public function add(RemovedParameter $param) : self
    {
        $this->params[$param->getName()] = $param;
        return $this;
    }

    public function has(string $name) : bool
    {
        return isset($this->params[$name]);
    }

    public function isEmpty() : bool
    {
        return empty($this->params);
    }

    /**
     * @return RemovedParameter[] Sorted by parameter name.
     */
    public function getAll() : array
    {
        $params = $this->params;

        ksort($params);

        return array_values($params);
    }

    /**
     * Detects which of the declared removed parameters are present
     * in the given request parameters, regardless of their value
     * (an explicit empty string still counts as "used").
     *
     * @param array<string,mixed> $requestParams
     * @return RemovedParameter[] Sorted by parameter name.
     */
    public function detectUsed(array $requestParams) : array
    {
        $used = array();

        foreach($this->getAll() as $param) {
            if(array_key_exists($param->getName(), $requestParams)) {
                $used[] = $param;
            }
        }

        return $used;
    }

    /**
     * @return array<array<string,string|NULL>>
     */
    public function toArray() : array
    {
        return array_map(
            static fn(RemovedParameter $param) : array => $param->toArray(),
            $this->getAll()
        );
    }
}
