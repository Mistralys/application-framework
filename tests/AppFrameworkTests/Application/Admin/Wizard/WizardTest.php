<?php

namespace testsuites\Application\Admin\Wizard;

use Application\Admin\Wizard\WizardConfigurator;
use Application\AppFactory;
use Application_Countries;
use Application_Driver;
use AppLocalize\Localization\Country\CountryDE;
use AppLocalize\Localization\Country\CountryGB;
use AppLocalize\Localization\Country\CountryMX;
use AppFrameworkTestClasses\ApplicationTestCase;
use TestDriver_Area_WizardTest_Wizard;
use TestDriver_Area_WizardTest_Wizard_Step_Countries;

final class WizardTest extends ApplicationTestCase
{
    protected TestDriver_Area_WizardTest_Wizard $wizard;

    private const WIZARD_BASE_URL = 'https://example.com/admin/?page=wizardtest&mode=wizard';

    public function setUp() : void
    {
        parent::setUp();
        $this->startTransaction();

        $this->cleanUpTables(array(Application_Countries::TABLE_NAME));

        $driver = Application_Driver::getInstance();
        $screen = $driver->getScreenByPath('wizardtest.wizard');

        $this->assertNotNull($screen, 'Screen could not be found by path.');
        $this->assertInstanceOf(TestDriver_Area_WizardTest_Wizard::class, $screen);

        $this->wizard = $screen;

        // The wizard screen is a singleton cached by the driver's subscreen
        // registry. Reset its internal state so each test starts with an empty
        // steps array and clean session state.
        $this->wizard->resetForTest();
    }

    public function tearDown() : void
    {
        unset($_REQUEST['wizard']);
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // test_wizardSteps
    // -------------------------------------------------------------------------

    public function test_wizardSteps() : void
    {
        $this->wizard->handleActions();
        $countriesStep = $this->wizard->getStep('Countries');

        $ticketStep = $this->wizard->getStep('Ticket');
        $ticketStep->process();
        $this->assertTrue($ticketStep->isComplete());

        $summaryStep = $this->wizard->getStep('Summary');
        $summaryStep->process();
        $this->assertTrue($summaryStep->isComplete());

        $this->wizard->changeCountry(CountryGB::ISO_CODE);
        $countriesStep->process();
        $this->assertFalse($ticketStep->isComplete());
        $this->assertFalse($summaryStep->isComplete());
    }

    // -------------------------------------------------------------------------
    // test_preselectedValuesAppliedToStep
    // -------------------------------------------------------------------------

    /**
     * Verifies that when a WizardConfigurator preselection session is injected,
     * the Countries step data is initialised with the preselected country_id
     * (merged over its null default) by the WP-002 constructor merge fix.
     *
     * Pattern: create countries manually → build preselection session → inject
     * session ID → initWizard + initSteps → assert step data before any process().
     */
    public function test_preselectedValuesAppliedToStep() : void
    {
        // Ensure the required countries exist in the DB before building the
        // preselection so we have a real country ID to reference.
        $countries = AppFactory::createCountries();
        foreach (array(
            CountryGB::ISO_CODE => 'United Kingdom',
            CountryDE::ISO_CODE => 'Germany',
            CountryMX::ISO_CODE => 'Mexico',
        ) as $iso => $label) {
            if (!$countries->isoExists($iso)) {
                $countries->createNewCountry($iso, $label);
            }
        }

        $gbId = $countries->getByISO(CountryGB::ISO_CODE)->getID();

        // Build a preselection session with GB's country ID in the Countries
        // step data slot.
        $configurator = new WizardConfigurator(self::WIZARD_BASE_URL);
        $configurator->getPreselection()->setStepValue(
            'Countries',
            TestDriver_Area_WizardTest_Wizard_Step_Countries::VALUE_COUNTRY_ID,
            $gbId
        );

        $url = $configurator->getRedirectURL();
        parse_str((string)parse_url($url, PHP_URL_QUERY), $params);
        $sessionId = (string)($params['wizard'] ?? '');

        $this->assertNotEmpty($sessionId, 'Preselection session ID must not be empty.');

        // Inject the preselected session ID so initWizard() picks it up.
        $_REQUEST['wizard'] = $sessionId;

        // Initialise the wizard with the preselected session and build the
        // steps — but do NOT call process() so the step data stays as-set
        // by the constructor merge (preselection wins over default null).
        $this->wizard->initWizard();
        $this->wizard->initSteps('preselection test');

        $countriesStep = $this->wizard->getStep('Countries');

        // The constructor merge (WP-002) must produce country_id = GB id,
        // overriding the step's getDefaultData() null default.
        $this->assertSame(
            $gbId,
            $countriesStep->getDataKey(TestDriver_Area_WizardTest_Wizard_Step_Countries::VALUE_COUNTRY_ID),
            'Countries step must carry the preselected country_id after initSteps().'
        );
    }

    // -------------------------------------------------------------------------
    // test_preselectionOverwrittenAfterStepSave
    // -------------------------------------------------------------------------

    /**
     * Verifies that after the Countries step processes and saveSettings() runs,
     * the preselection slot in the session is replaced by the fresh step data
     * produced by _process() — the preselected GB value is no longer present.
     */
    public function test_preselectionOverwrittenAfterStepSave() : void
    {
        // Ensure countries exist and fetch both GB and DE IDs.
        $countries = AppFactory::createCountries();
        foreach (array(
            CountryGB::ISO_CODE => 'United Kingdom',
            CountryDE::ISO_CODE => 'Germany',
            CountryMX::ISO_CODE => 'Mexico',
        ) as $iso => $label) {
            if (!$countries->isoExists($iso)) {
                $countries->createNewCountry($iso, $label);
            }
        }

        $gbId = $countries->getByISO(CountryGB::ISO_CODE)->getID();
        $deId = $countries->getByISO(CountryDE::ISO_CODE)->getID();

        // Build a preselection session with GB country.
        $configurator = new WizardConfigurator(self::WIZARD_BASE_URL);
        $configurator->getPreselection()->setStepValue(
            'Countries',
            TestDriver_Area_WizardTest_Wizard_Step_Countries::VALUE_COUNTRY_ID,
            $gbId
        );

        $url = $configurator->getRedirectURL();
        parse_str((string)parse_url($url, PHP_URL_QUERY), $params);
        $sessionId = (string)($params['wizard'] ?? '');

        $this->assertNotEmpty($sessionId, 'Preselection session ID must not be empty.');

        // Verify the preselection is present before processing.
        $sessionBefore = AppFactory::createSession()->getValue($sessionId);
        $this->assertSame(
            $gbId,
            $sessionBefore['-step_Countries'][TestDriver_Area_WizardTest_Wizard_Step_Countries::VALUE_COUNTRY_ID],
            'Preselection slot must contain GB country_id before handleActions().'
        );

        // Inject the session ID and run the full wizard action cycle.
        // handleActions() calls initWizard() → initSteps() → process() → saveSettings().
        // _initSteps() calls changeCountry('DE'), so the Countries step _process()
        // will use Germany — overwriting the preselected GB value.
        $_REQUEST['wizard'] = $sessionId;
        $this->wizard->handleActions();

        // After saveSettings(), the session slot must reflect the processed data,
        // not the original preselection.
        $sessionAfter = AppFactory::createSession()->getValue($sessionId);

        $this->assertNotSame(
            $gbId,
            $sessionAfter['-step_Countries'][TestDriver_Area_WizardTest_Wizard_Step_Countries::VALUE_COUNTRY_ID],
            'Preselected GB country_id must be overwritten after step save.'
        );

        $this->assertSame(
            $deId,
            $sessionAfter['-step_Countries'][TestDriver_Area_WizardTest_Wizard_Step_Countries::VALUE_COUNTRY_ID],
            'After step save the Countries step slot must contain the processed DE country_id.'
        );

        $this->assertTrue(
            $sessionAfter['-step_Countries']['completed'] ?? false,
            'After step save the Countries step must be marked as completed.'
        );
    }

    // -------------------------------------------------------------------------
    // test_wizardWithoutPreselectionUnchanged
    // -------------------------------------------------------------------------

    /**
     * Verifies that a wizard initialised without any preselection behaves
     * identically to the baseline: Countries step data defaults to null,
     * Ticket and Summary complete normally, and country-change invalidation
     * still propagates correctly.
     *
     * This is a regression guard — adding the preselection feature must not
     * alter the no-preselection code path in any way.
     */
    public function test_wizardWithoutPreselectionUnchanged() : void
    {
        // No $_REQUEST['wizard'] set — wizard creates its own fresh session.
        $this->wizard->handleActions();

        $countriesStep = $this->wizard->getStep('Countries');

        $ticketStep = $this->wizard->getStep('Ticket');
        $ticketStep->process();
        $this->assertTrue($ticketStep->isComplete(), 'Ticket step must be completable without preselection.');

        $summaryStep = $this->wizard->getStep('Summary');
        $summaryStep->process();
        $this->assertTrue($summaryStep->isComplete(), 'Summary step must be completable without preselection.');

        // Changing the country invalidates downstream steps — regression guard.
        $this->wizard->changeCountry(CountryGB::ISO_CODE);
        $countriesStep->process();
        $this->assertFalse($ticketStep->isComplete(), 'Ticket step must be invalidated after country change.');
        $this->assertFalse($summaryStep->isComplete(), 'Summary step must be invalidated after country change.');
    }

    // -------------------------------------------------------------------------
    // test_stepDefaultsAppliedOnFirstVisit
    // -------------------------------------------------------------------------

    /**
     * Verifies that the WP-002 constructor merge fix ensures step defaults from
     * getDefaultData() are present in step data on first visit, even when there
     * is no preselection and no prior session data.
     *
     * Before the fix, $this->data = $data (empty array) would skip defaults
     * entirely because the dead-code if(!isset($this->data)) branch was
     * unreachable.  After the fix, array_merge(getDefaultData(), []) always
     * seeds the data with the step's own defaults.
     */
    public function test_stepDefaultsAppliedOnFirstVisit() : void
    {
        // Ensure countries exist so _initSteps() does not fail on changeCountry('DE').
        $countries = AppFactory::createCountries();
        foreach (array(
            CountryGB::ISO_CODE => 'United Kingdom',
            CountryDE::ISO_CODE => 'Germany',
            CountryMX::ISO_CODE => 'Mexico',
        ) as $iso => $label) {
            if (!$countries->isoExists($iso)) {
                $countries->createNewCountry($iso, $label);
            }
        }

        // No preselection — initWizard() creates a fresh empty session.
        $this->wizard->initWizard();
        $this->wizard->initSteps('defaults test');

        $ticketStep = $this->wizard->getStep('Ticket');

        // Ticket.getDefaultData() returns ['order_number' => 'Test Ticket', 'order_url' => 'Test URL'].
        // With the array_merge fix, these defaults are present immediately after init
        // without requiring process() to run first.
        $this->assertSame(
            'Test Ticket',
            $ticketStep->getDataKey('order_number'),
            'Ticket step must have the order_number default on first visit.'
        );

        $this->assertSame(
            'Test URL',
            $ticketStep->getDataKey('order_url'),
            'Ticket step must have the order_url default on first visit.'
        );

        // Countries step default is null for country_id (no preselection).
        $countriesStep = $this->wizard->getStep('Countries');
        $this->assertNull(
            $countriesStep->getDataKey(TestDriver_Area_WizardTest_Wizard_Step_Countries::VALUE_COUNTRY_ID),
            'Countries step country_id must be null on first visit without preselection.'
        );
    }
}
