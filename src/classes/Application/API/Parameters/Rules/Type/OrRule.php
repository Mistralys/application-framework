<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules\Type;

use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Rules\BaseRule;
use Application\API\Parameters\Rules\RuleInterface;

/**
 * Handles switching between different sets of parameters,
 *
 * Validates from top to bottom: The first set of parameters that
 * are all present and valid will be accepted, all others ignored.
 *
 * > **NOTE**: It is best to add this rule before all other rules,
 * > so that the invalidation of parameters is handled correctly.
 *
 * @package API
 * @subpackage Parameters
 */
class OrRule extends BaseRule
{
    public const string RULE_ID = 'OR';

    /**
     * @var array<int,APIParameterInterface[]> $sets
     */
    private array $sets = array();

    public function getID(): string
    {
        return self::RULE_ID;
    }

    public function orParam(APIParameterInterface $parameter) : self
    {
        return $this->orParams(array($parameter));
    }

    /**
     * @param APIParameterInterface[]|APIParameterInterface ...$parameters
     * @return $this
     */
    public function orParams(...$parameters) : self
    {
        $params = array();
        foreach($parameters as $param) {
            if(is_array($param)) {
                array_push($params, ...$param);
            } else {
                $params[] = $param;
            }
        }

        $this->sets[] = $params;

        return $this;
    }

    protected function _validate() : void
    {
        $validSet = null;

        $this->log('Validating [%s] parameter sets.', count($this->sets));

        foreach($this->sets as $idx => $set)
        {
            $this->log('Checking set [#%s] | (%s)', $idx + 1, $this->set2string($set));

            $allValid = true;
            foreach($set as $param) {
                if (!$param->isValid() || !$param->hasValue()) {
                    $this->log('- Parameter [%s] is not valid or empty, skipping set.', $param->getName());
                    $allValid = false;
                    break;
                }
            }

            // One set is valid, ignore all others
            if ($allValid) {
                $this->log('- Set is valid, using it.', $idx + 1);
                $validSet = $idx;
                break;
            }
        }

        if($validSet === null)
        {
            $this->logError('No valid parameter set found.');

            $this->result->makeError(
                sprintf(
                    'At least one of the following parameter sets must be provided and be valid: %s',
                    implode(' | ', $this->listSets())
                ),
                RuleInterface::VALIDATION_NO_PARAM_SET_MATCHED
            );
            return;
        }

        // Invalidate all parameters that are not part of the valid set,
        // and set the parameters of the valid set as required.
        foreach($this->sets as $idx => $set)
        {
            foreach ($set as $param)
            {
                if ($idx === $validSet) {
                    $param->makeRequired();
                } else {
                    $param->invalidate();
                }
            }
        }
    }

    private function set2string(array $set) : string
    {
        return implode(
            ', ',
            array_map(static fn(APIParameterInterface $p) => $p->getName(), $set)
        );
    }

    private function listSets() : array
    {
        $result = array();

        foreach($this->sets as $set) {
            $result[] = '(' . $this->set2string($set) . ')';
        }

        return $result;
    }

    public function preValidate(): void
    {
        // Make all parameters optional, as only one set needs to be valid,
        // to avoid failing required validations on parameters that are not needed.
        foreach($this->sets as $set) {
            foreach($set as $param) {
                $param->makeRequired(false);
            }
        }
    }
}
