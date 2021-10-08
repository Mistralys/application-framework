<?php
/**
 * File containing the interface {@see Application_Interfaces_Admin_Wizard_WithCountryStep}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Interfaces_Admin_Wizard_WithCountryStep
 */

declare(strict_types=1);

/**
 * @package Application
 * @subpackage Wizards
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard_WithCountryStep
 * @see Application_Traits_Stubs_Admin_Wizard_WithCountryStepStub
 */
interface Application_Interfaces_Admin_Wizard_WithCountryStep
{
    public function getStepCountry() : Application_Interfaces_Admin_Wizard_SelectCountryStep;

    /**
     * Retrieves the country instance selected in the country step.
     *
     * @return Application_Countries_Country
     *
     * @throws Application_Exception_UnexpectedInstanceType
     * @throws Application_Exception
     * @see Application_Interfaces_Admin_Wizard_SelectCountryStep::ERROR_NO_COUNTRY_SELECTED
     */
    public function getSelectedCountry() : Application_Countries_Country;
}
