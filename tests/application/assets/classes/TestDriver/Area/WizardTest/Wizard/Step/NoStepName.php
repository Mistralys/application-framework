<?php

declare(strict_types=1);

/**
 * A minimal concrete step class that intentionally does NOT declare a
 * STEP_NAME constant. Used by {@see WizardPreselectionTest} to verify
 * that {@see \Application\Admin\Wizard\WizardPreselection::setStepValueByClass()}
 * throws when the constant is missing.
 */
class TestDriver_Area_WizardTest_Wizard_Step_NoStepName extends TestDriver_Area_WizardTest_Wizard_Step
{
    public function render() : string
    {
        return '';
    }

    public function initDone() : void
    {
    }

    protected function preProcess() : void
    {
    }

    public function getLabel() : string
    {
        return 'NoStepName';
    }

    protected function getDefaultData() : array
    {
        return array();
    }

    public function _process() : bool
    {
        return true;
    }

    public function getAbstract() : string
    {
        return '';
    }
}
