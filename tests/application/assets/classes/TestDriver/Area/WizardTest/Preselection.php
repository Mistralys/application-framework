<?php
/**
 * @package TestDriver
 * @subpackage WizardTest
 */

declare(strict_types=1);

use Application\Admin\Wizard\WizardConfigurator;
use Application\AppFactory;

/**
 * Test-application mode screen that demonstrates the {@see WizardConfigurator}
 * preselection API.
 *
 * Navigating to `?page=wizardtest&mode=preselection&country=GB` creates a wizard
 * session with the `country_id` field on the Countries step pre-populated with the
 * ID that corresponds to the given ISO code, then immediately redirects the browser
 * to the wizard at that session.  Step values are injected via
 * {@see \Application\Admin\Wizard\WizardPreselection::setStepValueByClass()}, which
 * resolves the target step by its `STEP_NAME` constant — keeping step references
 * type-safe and refactoring-friendly.
 *
 * When no valid `?country=` parameter is present, the screen renders a short usage
 * note so the user knows how to exercise it manually — making it a self-contained
 * UI test for the preselection feature.
 *
 * The class is auto-discovered by {@see Application_Admin_Index_AdminScreenIndex}
 * via the PSR-0 filesystem / classmap convention: placing the file under
 * `TestDriver/Area/WizardTest/` is sufficient; no explicit registration in
 * {@see TestDriver_Area_WizardTest} is required.
 *
 * @package TestDriver
 * @subpackage WizardTest
 * @see WizardConfigurator
 */
class TestDriver_Area_WizardTest_Preselection extends Application_Admin_Area_Mode
{
    public const string URL_NAME = 'preselection';

    public const string REQUEST_PARAM_COUNTRY = 'country';

    public function getURLName() : string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode() : string
    {
        return '';
    }

    public function isUserAllowed() : bool
    {
        return true;
    }

    public function getNavigationTitle() : string
    {
        return t('Preselection Test');
    }

    public function getTitle() : string
    {
        return t('Wizard Preselection Test');
    }

    /**
     * Handles the request: if a valid `?country=<ISO>` parameter is present,
     * builds a WizardConfigurator preselection session and redirects to the
     * wizard.  Otherwise falls through to {@see _renderContent()} for manual
     * usage instructions.
     *
     * @return bool
     */
    protected function _handleActions() : bool
    {
        $iso = (string)$this->request->getParam(self::REQUEST_PARAM_COUNTRY, '');

        if(empty($iso)) {
            return true;
        }

        $countries = AppFactory::createCountries();

        if(!$countries->isoExists($iso)) {
            return true;
        }

        $country = $countries->getByISO($iso);

        $wizardBaseURL = $this->area->getURL(array(
            'mode' => TestDriver_Area_WizardTest_Wizard::URL_NAME
        ));

        $configurator = new WizardConfigurator($wizardBaseURL);
        $configurator->getPreselection()->setStepValueByClass(
            TestDriver_Area_WizardTest_Wizard_Step_Countries::class,
            TestDriver_Area_WizardTest_Wizard_Step_Countries::VALUE_COUNTRY_ID,
            $country->getID()
        );

        $this->redirectTo($configurator->getRedirectURL());
    }

    /**
     * Fallthrough render path — only reached when {@see _handleActions()} returns
     * `true` without performing a redirect, i.e. when no valid `?country=` parameter
     * was supplied (parameter absent, empty, or an unrecognised ISO code).
     *
     * Renders a short usage note so developers and testers can see, at a glance,
     * which ISO codes are available and how to exercise the preselection flow without
     * having to read the source.
     *
     * @return string
     */
    protected function _renderContent() : string
    {
        return (string)$this->renderer
            ->appendContent(sb()
                ->para(sb()->bold(t('Wizard Preselection Manual Test')))
                ->para(t(
                    'Append %1$s to the current URL to preselect a country and redirect to the wizard.',
                    '<code>?country=GB</code>'
                ))
                ->para(t(
                    'Supported ISO codes: %1$s, %2$s, %3$s',
                    '<code>GB</code>',
                    '<code>DE</code>',
                    '<code>MX</code>'
                ))
            );
    }
}
