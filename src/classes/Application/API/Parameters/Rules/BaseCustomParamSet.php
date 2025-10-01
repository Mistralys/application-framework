<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules;

use Application\API\APIMethodInterface;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\ParamSet;
use Application\API\Parameters\Rules\Type\OrRule;

/**
 * Helper abstract class to create custom parameter sets:
 * Instead of instantiating {@see ParamSet} directly, extend
 * this class to work in a more structured way.
 *
 * This is especially useful when working with the {@see OrRule},
 * for example: Getting the valid parameter set with {@see OrRule::getValidSet()}
 * then returns an instance of the custom parameter set class,
 * allowing to add custom methods to retrieve values in a type-safe way.
 *
 * @package API
 * @subpackage Parameters
 */
abstract class BaseCustomParamSet extends ParamSet
{
    private APIMethodInterface $method;

    public function __construct(APIMethodInterface $method)
    {
        $this->method = $method;

        $this->initParams();

        parent::__construct($this->_getID(), $this->params);
    }

    /**
     * Initialize parameters for the custom parameter set.
     * Register them using {@see self::registerParam()}.
     *
     * @return void
     */
    abstract protected function initParams() : void;

    abstract protected function _getID() : string;

    /**
     * @var APIParameterInterface[]
     */
    private array $params = array();

    protected function registerParam(APIParameterInterface $param) : void
    {
        $this->params[] = $param;
    }

    final public function getMethod() : APIMethodInterface
    {
        return $this->method;
    }
}
