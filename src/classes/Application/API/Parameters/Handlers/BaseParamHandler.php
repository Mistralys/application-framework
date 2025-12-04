<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\APIParameterException;
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

    public function requireParam() : APIParameterInterface
    {
        $param = $this->getParam();

        if($param !== null) {
            return $param;
        }

        throw new APIParameterException(
            'Requested parameter has not been registered.',
            sprintf(
                'In handler class [%s].',
                get_class($this)
            ),
            APIParameterException::ERROR_PARAM_NOT_REGISTERED
        );
    }

    public function getParams() : array
    {
        if(isset($this->param)) {
            return array($this->param);
        }

        return array();
    }
}
