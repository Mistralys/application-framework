<?php
/**
 * File containing the class {@see Application_Traits_Stubs_Admin_Wizard_WithCountryStepStub}.
 *
 * @package Application
 * @subpackage Stubs
 * @see Application_Traits_Stubs_Admin_Wizard_WithCountryStepStub
 */

declare(strict_types=1);

/**
 * Stub for wizards that have a country selection step.
 *
 * @package Application
 * @subpackage Stubs
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard_WithCountryStep
 * @see Application_Interfaces_Admin_Wizard_WithCountryStep
 */
class Application_Traits_Stubs_Admin_Wizard_WithCountryStepStub
    extends Application_Admin_Wizard
    implements Application_Interfaces_Admin_Wizard_WithCountryStep
{
    use Application_Traits_Admin_Wizard_WithCountryStep;

    public function getURLName() : string
    {
        return '';
    }

    public function getNavigationTitle() : string
    {
        return '';
    }

    public function isUserAllowed() : bool
    {
        return false;
    }

    public function getTitle() : string
    {
        return '';
    }

    public function getWizardID() : string
    {
        return '';
    }

    public function getClassBase() : string
    {
        return '';
    }

    public function getSuccessMessage() : string
    {
        return '';
    }

    protected function processCancelCleanup() : void
    {
    }

    protected function _initSteps()
    {
        $this->addStepCountry();
    }
}
