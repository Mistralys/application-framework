<?php
/**
 * File containing the interface {@see Application_Interfaces_Admin_Wizard_SelectCountryStep}.
 *
 * @package Application
 * @subpackage Wizard
 * @see Application_Interfaces_Admin_Wizard_SelectCountryStep
 */

declare(strict_types=1);

/**
 * @package Application
 * @subpackage Wizard
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard_SelectCountryStep
 * @see Application_Traits_Stubs_Admin_Wizard_SelectCountryStub
 */
interface Application_Interfaces_Admin_Wizard_SelectCountryStep extends Application_Interfaces_Admin_Wizard_Step
{
    const ERROR_NO_COUNTRY_SELECTED = 94401;

    const STEP_NAME = 'Country';
    const PARAM_COUNTRY_ID = 'country_id';

    public function getIcon() : ?UI_Icon;

    public function getAbstract() : string;

    public function isInvariantSelectable() : bool;

    public function getCountry() : ?Application_Countries_Country;

    /**#
     * @return Application_Countries_Country
     * @throws Application_Exception
     * @see Application_Interfaces_Admin_Wizard_SelectCountryStep::ERROR_NO_COUNTRY_SELECTED
     */
    public function requireCountry() : Application_Countries_Country;
}
