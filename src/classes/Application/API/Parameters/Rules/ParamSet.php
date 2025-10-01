<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters;

use Application\API\Parameters\Rules\Type\OrRule;
use Application_Interfaces_Loggable;
use Application_Traits_Loggable;

/**
 * Class used to hold a set of parameters for use in rules.
 * For example, the `OR`rule: {@see OrRule::addSet()}.
 *
 * @package API
 * @subpackage Parameters
 */
class ParamSet implements ParamSetInterface, Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    private string $id;

    /**
     * @var APIParameterInterface[]
     */
    private array $params;
    private string $logIdentifier;
    private ?string $label = null;

    /**
     * @param string|NULL $id
     * @param APIParameterInterface[]|APIParameterInterface ...$parameters
     */
    public function __construct(?string $id=null, ...$parameters)
    {
        if(empty($id)) {
            $id = 'param'.nextJSID();
        }

        $this->id = $id;
        $this->logIdentifier = sprintf('API | ParamSet [%s]', $this->id);

        $params = array();
        foreach($parameters as $param) {
            if(is_array($param)) {
                array_push($params, ...$param);
            } else {
                $params[] = $param;
            }
        }

        $this->params = $params;
    }

    public function getLabel() : string
    {
        return $this->label ?? sprintf('Parameter set %s', $this->getID());
    }

    public function setLabel(?string $label) : self
    {
        $this->label = $label;
        return $this;
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function getID(): string
    {
        return $this->id;
    }

    /**
     * @return APIParameterInterface[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function isValid() : bool
    {
        foreach($this->params as $param) {
            if (!$param->isValid() || !$param->hasValue()) {
                $this->log('- Parameter [%s] is not valid or empty, skipping set.', $param->getName());
                return false;
            }
        }

        return true;
    }

    public function apply() : self
    {
        foreach ($this->params as $param)
        {
            $param->makeRequired();
        }

        return $this;
    }

    public function invalidate() : self
    {
        foreach ($this->params as $param) {
            $param->invalidate();
        }

        return $this;
    }

    public function __toString()
    {
        return implode(
            ', ',
            array_map(static fn(APIParameterInterface $p) => $p->getName(), $this->params)
        );
    }

    public function resetRequiredState() : self
    {
        foreach($this->params as $param) {
            $param->makeRequired(false);
        }

        return $this;
    }
}