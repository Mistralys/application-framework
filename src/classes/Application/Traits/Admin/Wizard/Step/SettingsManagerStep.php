<?php
/**
 * File containing the {@see Application_Traits_Admin_Wizard_SettingsManagerStep} trait.
 *
 * @package Application
 * @subpackage Wizard
 * @see Application_Traits_Admin_Wizard_SettingsManagerStep
 */

declare(strict_types=1);

/**
 * Step in a wizard: Displays a settings form using a collection's
 * settings manager instance, and stores the submitted form values.
 *
 * @package Application
 * @subpackage Wizard
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Admin_Wizard_SettingsManagerStep
 * @see Application_Traits_Stubs_Admin_Wizard_SettingsManagerStub
 */
trait Application_Traits_Admin_Wizard_SettingsManagerStep
{
    /**
     * @var Application_Formable_RecordSettings
     */
    protected $settings;

    public function getLabel() : string
    {
        return t('Settings');
    }

    public function getIcon() : ?UI_Icon
    {
        return UI::icon()->settings();
    }

    // region: Abstract interface methods

    abstract public function createSettingsManager() : Application_Formable_RecordSettings;

    // endregion

    /**
     * @return array<string,mixed>
     */
    public function getSettingValues() : array
    {
        $values = $this->getDataKey(Application_Interfaces_Admin_Wizard_SettingsManagerStep::KEY_FORM_VALUES);

        if(is_array($values))
        {
            return $values;
        }

        return array();
    }

    /**
     * @param string $setting
     * @return mixed|null
     */
    protected function getValue(string $setting)
    {
        $values = $this->getSettingValues();

        if(isset($values[$setting]))
        {
            return $values[$setting];
        }

        return null;
    }

    protected function getDefaultData() : array
    {
        return array(
            Application_Interfaces_Admin_Wizard_SettingsManagerStep::KEY_FORM_VALUES => array()
        );
    }

    public function _process() : bool
    {
        $this->setUpForm();

        if($this->isFormValid())
        {
            $this->setData(Application_Interfaces_Admin_Wizard_SettingsManagerStep::KEY_FORM_VALUES, $this->getFormValues());

            return true;
        }

        return false;
    }

    public function render() : string
    {
        return $this->renderFormable();
    }

    protected function setUpForm() : void
    {
        $this->settings = $this->createSettingsManager();

        $this->createFormableForm($this->getFormName(), $this->resolveDefaultValues());

        $this->settings->inject();

        $this->injectNavigationButtons();
    }

    protected function resolveDefaultValues() : array
    {
        $values = $this->getSettingValues();

        if(!empty($values))
        {
            return $values;
        }

        return $this->settings->getDefaultValues();
    }
}
