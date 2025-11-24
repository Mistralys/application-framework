<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Handlers;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\APIParamManager;

/**
 * Abstract base class used to implement API parameter handlers.
 *
 * See the interface {@see ParamHandlerInterface} for more details.
 *
 * @package API
 * @subpackage Parameters
 */
abstract class BaseParamHandler implements ParamHandlerInterface
{
    private APIParamManager $manager;
    private ?APIParameterInterface $param = null;
    protected mixed $selectedValue = null;

    public function __construct(APIParamManager $manager)
    {
        $this->manager = $manager;
    }

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

    public function selectValue(mixed $value) : self
    {
        $this->selectedValue = $value;
        return $this;
    }

    public function resolveValue() : mixed
    {
        if(isset($this->selectedValue)) {
            return $this->selectedValue;
        }

        if(isset($this->param)) {
            return $this->resolveValueByParam();
        }

        return null;
    }

    /**
     * This is called when no value has been selected directly.
     * The value must be resolved from the parameter itself.
     *
     * @return mixed
     */
    abstract protected function resolveValueByParam() : mixed;
}
