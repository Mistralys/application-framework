<?php

declare(strict_types=1);

class TestDriver_Area_WizardTest_Wizard_Step_Ticket extends TestDriver_Area_WizardTest_Wizard_Step
{

    public function render() : string
    {
        return $this->renderFormable();
    }

    public function initDone() : void
    {
    }

    protected function preProcess() : void
    {
        // TODO: Implement preProcess() method.
    }

    public function getLabel() : string
    {
        return 'Ticket';
    }

    protected function getDefaultData() : array
    {
        return array(
            'order_number' => 'Test Ticket',
            'order_url' => 'Test URL'
        );
    }

    public function _process() : bool
    {
        $values = $this->getDefaultData();

        $this->setData('order_number', $values['order_number']);
        $this->setData('order_url', $values['order_url']);

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