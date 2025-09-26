<?php
/**
 * @package API
 * @subpackage Parameters
 */

declare(strict_types=1);

namespace Application\API\Parameters\Rules;

use Application\Validation\ValidationLoggableTrait;
use Application\Validation\ValidationResults;
use Application_Traits_Loggable;
use AppUtils\OperationResult_Collection;

/**
 * Abstract base class to implement validation rules.
 *
 * @package API
 * @subpackage Parameters
 */
abstract class BaseRule implements RuleInterface
{
    use Application_Traits_Loggable;
    use ValidationLoggableTrait;

    protected OperationResult_Collection $result;
    private bool $validated = false;
    private string $logIdentifier;

    public function __construct()
    {
        $this->logIdentifier = sprintf('API Parameter Rule [%s]', $this->getID());
        $this->result = new ValidationResults($this);
    }

    public function getLogIdentifier(): string
    {
        return $this->logIdentifier;
    }

    public function apply() : self
    {
        $this->validate();
        return $this;
    }

    public function isValid() : bool
    {
        $this->validate();

        return $this->result->isValid();
    }

    public function getValidationResults(): ValidationResults
    {
        $this->validate();

        return $this->result;
    }

    private function validate() : void
    {
        if($this->validated) {
            return;
        }

        $this->validated = true;

        $this->_validate();
    }

    abstract protected function _validate() : void;
}
