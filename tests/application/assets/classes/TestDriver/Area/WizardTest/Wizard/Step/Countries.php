<?php

declare(strict_types=1);

/**
 * @property TestDriver_Area_WizardTest_Wizard $wizard
 */
class TestDriver_Area_WizardTest_Wizard_Step_Countries extends TestDriver_Area_WizardTest_Wizard_Step
{
    public const string STEP_NAME = 'Countries';

    /**
     * The step data key used to store and retrieve the preselected country ID.
     * Referenced by {@see TestDriver_Area_WizardTest_Preselection} when building
     * a {@see \Application\Admin\Wizard\WizardConfigurator} preselection session.
     */
    public const string VALUE_COUNTRY_ID = 'country_id';

    protected Application_Countries $countries;

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
            self::VALUE_COUNTRY_ID => null
        );
    }

    public function _process() : bool
    {
        $preselected = $this->wizard->getPreselectedCountry();

        if($preselected !== null)
        {
            $this->setData(self::VALUE_COUNTRY_ID, $preselected->getID());
            $this->setComplete();
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
