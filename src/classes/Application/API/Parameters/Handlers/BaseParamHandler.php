<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\APIParameterInterface;

/**
 * Abstract base class used to implement API parameter handlers.
 *
 * See the interface {@see ParamHandlerInterface} for more details.
 *
 * @package API
 * @subpackage Parameters
 */
abstract class BaseParamHandler extends BaseAPIHandler implements ParamHandlerInterface
{
    private ?APIParameterInterface $param = null;

    public function register() : APIParameterInterface
    {
        if(!isset($this->param)) {
            $this->param = $this->createParam();
            $this->manager->registerParam($this->param);
        }

        return $this->param;
    }

    /**
     * Create an instance of the parameter this handler manages.
     * @return APIParameterInterface
     */
    abstract protected function createParam() : APIParameterInterface;

    public function getParam() : ?APIParameterInterface
    {
        return $this->param;
    }
}
