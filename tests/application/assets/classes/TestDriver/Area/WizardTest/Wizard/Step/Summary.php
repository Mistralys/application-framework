<?php

declare(strict_types=1);

class TestDriver_Area_WizardTest_Wizard_Step_Summary extends TestDriver_Area_WizardTest_Wizard_Step
{

    function render() : string
    {
        return $this->renderFormable();
    }

    public function initDone() : void
    {
    }

    protected function preProcess() : void
    {
    }

    public function getLabel() : string
    {
        return 'Summary';
    }

    protected function getDefaultData() : array
    {
        return array(
            'summary_info' => 'Test Wizard Summary'
        );
    }

    public function _process() : bool
    {
        $values = $this->getDefaultData();

        $this->setData('summary_info', $values['summary_info']);

        $this->setComplete();
        return true;
    }

    public function getAbstract() : string
    {
        return '';
    }

    protected function getMonitoredSteps() : array
    {
        return array('Countries');
    }

    protected function _handle_stepUpdated(Application_Admin_Wizard_Step $step) : void
    {
        if($step instanceof TestDriver_Area_WizardTest_Wizard_Step_Countries)
        {
            $this->invalidate(t('The mailing country has changed; Please review the mail settings.'), $step->getNumber());
        }
    }
}