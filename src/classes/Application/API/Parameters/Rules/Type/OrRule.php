<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules\Type;

use Application\API\APIException;
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\ParamSet;
use Application\API\Parameters\Rules\BaseRule;
use Application\API\Parameters\Rules\RuleInterface;
use UI;

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
     * @var array<int,ParamSet> $sets
     */
    private array $sets = array();
    private ?ParamSet $selectedSet = null;

    public function getID(): string
    {
        return self::RULE_ID;
    }

    public function getTypeLabel(): string
    {
        return t('OR parameter sets');
    }

    public function getTypeDescription(): string
    {
        return (string)sb()
            ->t('One of the parameter sets must be provided.')
            ->t('The sets are evaluated from top to bottom, the first set that is complete and valid is used, all others ignored.');
    }

    /**
     * Add a set of parameters, where at least one set must be complete and valid.
     *
     * @param ParamSet $set
     * @return $this
     */
    public function addSet(ParamSet $set) : self
    {
        $this->sets[] = $set;

        return $this;
    }

    protected function _validate() : void
    {
        $validSet = null;

        $this->log('Validating [%s] parameter sets.', count($this->sets));

        foreach($this->sets as $idx => $set)
        {
            $this->log('Checking set [#%s] | (%s)', $idx + 1, $set);

            // One set is valid, ignore all others
            if ($set->isValid()) {
                $this->log('- Set is valid, using it.', $idx + 1);
                $validSet = $set;
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

        $this->selectedSet = $validSet;

        // Invalidate all parameters that are not part of the valid set,
        // and set the parameters of the valid set as required.
        foreach($this->sets as $set)
        {
            if($set === $validSet) {
                $validSet->apply();
                continue;
            }

            $set->invalidate();
        }
    }

    public function getValidSet() : ?ParamSet
    {
        return $this->selectedSet;
    }

    /**
     * Get the valid parameter set after validation (non-null-safe).
     *
     * > NOTE: This is safe to use after the validation has run.
     * > If no valid set was found, an error response will have been sent,
     * > and this exception will not be thrown.
     *
     * @return ParamSet
     * @throws APIException
     */
    public function requireValidSet() : ParamSet
    {
        $set = $this->getValidSet();

        if($set !== null) {
            return $set;
        }

        throw new APIException(
            'The rule has no valid parameter set selected.',
            'Requiring a valid set should only be done once validation has occurred, at which point an error response will have been sent, and this exception will not be thrown.',
            APIException::ERROR_INTERNAL
        );
    }

    private function listSets() : array
    {
        $result = array();

        foreach($this->sets as $set) {
            $result[] = '(' . $set . ')';
        }

        return $result;
    }

    public function preValidate(): void
    {
        // Make all parameters optional, as only one set needs to be valid,
        // to avoid failing required validations on parameters that are not needed.
        foreach($this->sets as $set) {
            $set->resetRequiredState();
        }
    }

    public function renderDocumentation(UI $ui) : string
    {
        $sel = $ui->createBigSelection();
        $sel->makeSmall();

        foreach($this->sets as $set)
        {
            $sel->addHeader($set->getLabel());

            $list = array();
            foreach($set->getParams() as $param) {
                $list[] = sb()->mono('- '.$param->getName());
            }
            
            $sel->addItem(implode('<br>', $list));
        }

        return $sel->render();
    }
}
