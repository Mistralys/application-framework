<?php
/**
 * File containing the trait {@see Application_Traits_Admin_Wizard_Step_Confirmation}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Traits_Admin_Wizard_Step_Confirmation
 */

declare(strict_types=1);

/**
 * Step in a wizard: Confirm the wizard with a summary screen.
 *
 * @package Application
 * @subpackage Wizards
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Admin_Wizard_Step_Confirmation
 */
trait Application_Traits_Admin_Wizard_Step_Confirmation
{
    public function getLabel() : string
    {
        return t('Confirmation');
    }

    public function getIcon() : ?UI_Icon
    {
        return UI::icon()->ok();
    }

    public function getAbstract() : string
    {
        return t('Please make sure that your selection is correct.');
    }

    abstract protected function createReferenceID() : string;

    abstract protected function populateSummaryGrid(UI_PropertiesGrid $grid) : void;

    protected function getDefaultData() : array
    {
        return array(
            Application_Interfaces_Admin_Wizard_Step_Confirmation::PARAM_REFERENCE_ID => null
        );
    }

    public function _process() : bool
    {
        $this->createFormableForm(Application_Interfaces_Admin_Wizard_Step_Confirmation::FORM_NAME);

        $this->injectNavigationButtons();

        if($this->isFormValid())
        {
            $this->setData(Application_Interfaces_Admin_Wizard_Step_Confirmation::PARAM_REFERENCE_ID, $this->createReferenceID());

            return true;
        }

        return false;
    }

    public function getReferenceID() : string
    {
        return (string)$this->getDataKey(Application_Interfaces_Admin_Wizard_Step_Confirmation::PARAM_REFERENCE_ID);
    }

    public function requireReferenceID() : string
    {
        $referenceID = $this->getReferenceID();

        if(!empty($referenceID))
        {
            return $referenceID;
        }

        throw new Application_Admin_WizardException(
            'No reference ID selected.',
            'The reference ID was empty. It must be created in the confirmation step.',
            Application_Interfaces_Admin_Wizard_Step_Confirmation::ERROR_NO_REFERENCE_ID_SET
        );
    }

    public function render() : string
    {
        $tbl = $this->ui->createPropertiesGrid();

        $this->populateSummaryGrid($tbl);

        return $tbl.$this->renderFormable();
    }
}
