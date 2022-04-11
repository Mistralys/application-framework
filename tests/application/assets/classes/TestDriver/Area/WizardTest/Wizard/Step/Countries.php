<?php

declare(strict_types=1);


class TestDriver_Area_WizardTest_Wizard_Step_Countries extends TestDriver_Area_WizardTest_Wizard_Step
{
   /**
    * @var Application_Countries
    */
    protected $countries;

    public function render() : string
    {
        return $this->renderTemplate(
            'test-wizard/select-country',
            array(
                'wizard-step' => $this
            )
        );
    }

    protected function _init() : void
    {
        $this->countries = Application_Countries::getInstance();
    }

    public function initDone() : void
    {
    }

    protected function preProcess() : void
    {
    }

    public function getLabel() : string
    {
        return 'Country';
    }

    protected function getDefaultData() : array
    {
        return array(
            'country_id' => null
        );
    }

    public function _process() : bool
    {
        $preselected = $this->wizard->getPreselectedCountryID();
        $selected = $this->countries->getByID($preselected);

        if($selected)
        {
            $this->setData('country_id', $selected->getID());
            $this->setComplete(true);
            return true;
        }

        return false;
    }

    public function getAbstract() : string
    {
        return
            'You may choose a different country than that of the mailing:' . ' ' .
            'The wizard will guide you through the conversion of the contents.';
    }
}