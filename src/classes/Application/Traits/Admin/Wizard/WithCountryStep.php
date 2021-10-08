<?php
/**
 * File containing the trait {@see Application_Traits_Admin_Wizard_WithCountryStep}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Traits_Admin_Wizard_WithCountryStep
 */

declare(strict_types=1);

/**
 * @package Application
 * @subpackage Wizards
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Admin_Wizard_WithCountryStep
 * @see Application_Traits_Stubs_Admin_Wizard_WithCountryStepStub
 */
trait Application_Traits_Admin_Wizard_WithCountryStep
{
    protected function addStepCountry() : Application_Admin_Wizard_Step 
    {
        return $this->addStep(Application_Interfaces_Admin_Wizard_SelectCountryStep::STEP_NAME);
    }

    /**
     * @return Application_Interfaces_Admin_Wizard_SelectCountryStep
     *
     * @throws Application_Exception
     * @throws Application_Exception_UnexpectedInstanceType
     */
    public function getStepCountry() : Application_Interfaces_Admin_Wizard_SelectCountryStep
    {
        $step = $this->getStep(Application_Interfaces_Admin_Wizard_SelectCountryStep::STEP_NAME);

        if($step instanceof Application_Interfaces_Admin_Wizard_SelectCountryStep)
        {
            return $step;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Interfaces_Admin_Wizard_SelectCountryStep::class, $step);
    }

    /**
     * Retrieves the country instance selected in the country step.
     *
     * @return Application_Countries_Country
     * @throws Application_Exception_UnexpectedInstanceType
     *
     * @throws Application_Exception
     * @see Application_Interfaces_Admin_Wizard_SelectCountryStep::ERROR_NO_COUNTRY_SELECTED
     */
    public function getSelectedCountry() : Application_Countries_Country
    {
        return $this->getStepCountry()->requireCountry();
    }
}
