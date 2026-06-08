<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

namespace Application\Admin\Wizard;

/**
 * Typed key/value container that stores preselected step values as a
 * nested map (stepName → key → value). Used to pre-populate wizard
 * step properties before redirecting a user to the wizard.
 *
 * Usage example — build the preselection, then pass it to the wizard:
 *
 * <pre>
 * $preselection = (new WizardPreselection())
 *     ->setStepValue('step-account', 'userID', 42)
 *     ->setStepValue('step-account', 'role', 'admin')
 *     ->setStepValue('step-confirm', 'sendEmail', true);
 *
 * // Read back values for a specific step
 * if ($preselection->hasStepValues('step-account')) {
 *     $values = $preselection->getStepValues('step-account');
 *     // $values === array('userID' => 42, 'role' => 'admin')
 * }
 *
 * // Inspect all steps at once
 * $allSteps   = $preselection->getStepNames(); // array('step-account', 'step-confirm')
 * $allValues  = $preselection->toArray();       // full nested map
 * $hasNothing = $preselection->isEmpty();       // false
 * </pre>
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class WizardPreselection
{
    public const int ERROR_STEP_CLASS_MISSING_STEP_NAME = 558101;

    /**
     * @var array<string,array<string,mixed>>
     */
    private array $values = array();

    /**
     * Stores a value for a specific step and key.
     *
     * @param string $stepName The wizard step name. Must exactly match the string passed to
     *                         `addStep()` in the target wizard's `_initSteps()` method
     *                         (e.g., `'Countries'`, not `'country'` or `'step-countries'`).
     *                         A mismatch will silently produce preselection values that the
     *                         wizard step never receives.
     * @param string $key The key within the step.
     * @param mixed $value The value to preselect.
     * @return $this
     */
    public function setStepValue(string $stepName, string $key, mixed $value): self
    {
        if (!isset($this->values[$stepName])) {
            $this->values[$stepName] = array();
        }

        $this->values[$stepName][$key] = $value;

        return $this;
    }

    /**
     * Stores a value for a step identified by its class name. The step name
     * is resolved from the class's `STEP_NAME` constant.
     *
     * This method provides type-safe step identification: using `StepClass::class`
     * gives IDE auto-completion, refactoring support, and autoloader validation
     * that string literals cannot offer.
     *
     * @param class-string<\Application_Admin_Wizard_Step> $stepClass Fully-qualified step class name.
     *        The class must declare a public `STEP_NAME` constant whose value matches
     *        the string passed to `addStep()` in the target wizard's `_initSteps()`.
     * @param string $key The key within the step.
     * @param mixed $value The value to preselect.
     * @return $this
     *
     * @throws \Application_Exception When the step class does not declare a `STEP_NAME` constant.
     *         {@see self::ERROR_STEP_CLASS_MISSING_STEP_NAME}
     */
    public function setStepValueByClass(string $stepClass, string $key, mixed $value): self
    {
        return $this->setStepValue(
            $this->resolveStepNameByClass($stepClass),
            $key,
            $value
        );
    }

    /**
     * Resolves a step name from the given step class's `STEP_NAME` constant.
     *
     * @param class-string<\Application_Admin_Wizard_Step> $stepClass
     * @return string
     *
     * @throws \Application_Exception When the class does not declare a `STEP_NAME` constant.
     */
    private function resolveStepNameByClass(string $stepClass): string
    {
        if (defined($stepClass . '::STEP_NAME')) {
            return (string)constant($stepClass . '::STEP_NAME');
        }

        throw new \Application_Exception(
            'Cannot resolve step name from class',
            sprintf(
                'The step class [%s] must declare a public STEP_NAME constant to be used with setStepValueByClass().',
                $stepClass
            ),
            self::ERROR_STEP_CLASS_MISSING_STEP_NAME
        );
    }

    /**
     * Returns all preselection values for the given step.
     *
     * @param string $stepName The wizard step name.
     * @return array<string,mixed> The stored key/value pairs, or an empty array if none are set.
     */
    public function getStepValues(string $stepName): array
    {
        if (isset($this->values[$stepName])) {
            return $this->values[$stepName];
        }

        return array();
    }

    /**
     * Returns whether any preselection values exist for the given step.
     *
     * @param string $stepName The wizard step name.
     * @return bool True if at least one value has been set for this step.
     */
    public function hasStepValues(string $stepName): bool
    {
        return isset($this->values[$stepName]) && !empty($this->values[$stepName]);
    }

    /**
     * Returns the full nested values map.
     *
     * @return array<string,array<string,mixed>>
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * Returns whether no preselection values have been set at all.
     *
     * @return bool True when the values map is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->values);
    }

    /**
     * Returns the list of step names that have preselection values.
     *
     * @return string[]
     */
    public function getStepNames(): array
    {
        return array_keys($this->values);
    }
}
