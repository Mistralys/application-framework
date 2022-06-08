<?php
/**
 * File containing the trait {@see Application_Traits_Admin_Wizard_WithCountryStep}.
 *
 * @package Application
 * @subpackage Wizards
 * @see Application_Traits_Admin_Wizard_WithCountryStep
 */

declare(strict_types=1);

use Application\ClassFinder;
use Application\Exception\ClassNotExistsException;
use Application\Exception\UnexpectedInstanceException;

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
     * @throws ClassNotExistsException
     * @throws UnexpectedInstanceException
     */
    public function getStepCountry() : Application_Interfaces_Admin_Wizard_SelectCountryStep
    {
        return ClassFinder::requireInstanceOf(
            Application_Interfaces_Admin_Wizard_SelectCountryStep::class,
            $this->getStep(Application_Interfaces_Admin_Wizard_SelectCountryStep::STEP_NAME)
        );
    }

    /**
     * Retrieves the country instance selected in the country step.
     *
     * @return Application_Countries_Country
     *
     * @throws Application_Exception
     * @throws ClassNotExistsException
     * @throws UnexpectedInstanceException
     *
     * @see Application_Interfaces_Admin_Wizard_SelectCountryStep::ERROR_NO_COUNTRY_SELECTED
     */
    public function getSelectedCountry() : Application_Countries_Country
    {
        return $this->getStepCountry()->requireCountry();
    }
}
