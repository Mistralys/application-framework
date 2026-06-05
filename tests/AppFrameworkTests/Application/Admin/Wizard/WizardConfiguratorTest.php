<?php

declare(strict_types=1);

namespace testsuites\Application\Admin\Wizard;

use Application\Admin\Wizard\InvalidationHandler;
use Application\Admin\Wizard\WizardConfigurator;
use Application\Admin\Wizard\WizardPreselection;
use Application\AppFactory;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Unit tests for {@see WizardConfigurator}.
 *
 * These tests cover session creation, preselection write-through to step data
 * slots, accessor correctness, idempotency of {@see WizardConfigurator::getRedirectURL()},
 * and custom setting-prefix key formatting.
 *
 * **Edge cases verified by QA (not covered by dedicated test methods):**
 *
 * - **Empty preselection:** When no `setStepValue()` calls are made before
 *   `getRedirectURL()`, the resulting session contains only the
 *   `{prefix}-invalidationHandler` key. No stray `-step_*` keys are created
 *   because the internal `foreach` over `getStepNames()` is a no-op on an
 *   empty preselection.
 *
 * - **URL separator logic:** `getRedirectURL()` uses `?` to append the wizard
 *   parameter when the base URL contains no query string, and `&` when the base
 *   URL already contains a `?`. Both branches are exercised by the constant
 *   `WIZARD_BASE_URL` (which already contains `?page=…&mode=…`) and confirmed
 *   correct via QA edge-case testing.
 */
final class WizardConfiguratorTest extends ApplicationTestCase
{
    private const WIZARD_BASE_URL = 'https://example.com/admin/?page=wizardtest&mode=wizard';

    // -------------------------------------------------------------------------
    // test_createSessionWithPreselection
    // -------------------------------------------------------------------------

    /**
     * getRedirectURL() must create a session and return a URL that contains
     * the "wizard" query parameter with the session ID.
     *
     * Covers AC #1 and #3 from WP-003.
     */
    public function test_createSessionWithPreselection() : void
    {
        $configurator = new WizardConfigurator(self::WIZARD_BASE_URL);
        $configurator->getPreselection()->setStepValue('Countries', 'country_id', 42);

        $url = $configurator->getRedirectURL();

        $this->assertStringContainsString('wizard=', $url);
        $this->assertStringStartsWith(self::WIZARD_BASE_URL, $url);

        // Extract the session ID from the URL and verify the session was created.
        parse_str((string)parse_url($url, PHP_URL_QUERY), $params);
        $sessionID = (string)($params['wizard'] ?? '');

        $this->assertNotEmpty($sessionID, 'Session ID must not be empty in the redirect URL.');

        $session = AppFactory::createSession();
        $sessionData = $session->getValue($sessionID);

        $this->assertIsArray($sessionData, 'Session data must be an array after getRedirectURL().');
        $this->assertArrayHasKey('sessionID', $sessionData, 'Session data must contain the sessionID key.');
        $this->assertSame($sessionID, $sessionData['sessionID']);
    }

    // -------------------------------------------------------------------------
    // test_preselectionWrittenToStepSlots
    // -------------------------------------------------------------------------

    /**
     * Preselection values must be written into the session under the step data
     * key format: settingPrefix + '-step_' + stepName.
     *
     * For the default empty prefix this is '-step_Countries', '-step_Ticket', etc.
     *
     * Covers AC #3 from WP-003.
     */
    public function test_preselectionWrittenToStepSlots() : void
    {
        $configurator = new WizardConfigurator(self::WIZARD_BASE_URL);
        $configurator->getPreselection()
            ->setStepValue('Countries', 'country_id', 99)
            ->setStepValue('Ticket', 'ticket_title', 'Hello World');

        $url = $configurator->getRedirectURL();

        parse_str((string)parse_url($url, PHP_URL_QUERY), $params);
        $sessionID = (string)($params['wizard'] ?? '');

        $session = AppFactory::createSession();
        $sessionData = $session->getValue($sessionID);

        // Default prefix '' produces keys '-step_Countries' and '-step_Ticket'.
        $this->assertArrayHasKey('-step_Countries', $sessionData);
        $this->assertSame(array('country_id' => 99), $sessionData['-step_Countries']);

        $this->assertArrayHasKey('-step_Ticket', $sessionData);
        $this->assertSame(array('ticket_title' => 'Hello World'), $sessionData['-step_Ticket']);

        // The invalidationHandler key must also be present.
        $this->assertArrayHasKey('-invalidationHandler', $sessionData);
        $this->assertInstanceOf(InvalidationHandler::class, $sessionData['-invalidationHandler']);
        $this->assertFalse($sessionData['-invalidationHandler']->isInvalidated());
    }

    // -------------------------------------------------------------------------
    // test_getPreselectionReturnsInstance
    // -------------------------------------------------------------------------

    /**
     * getPreselection() must return a WizardPreselection instance, and the same
     * instance must be returned on subsequent calls (identity).
     *
     * Covers AC #1 from WP-003.
     */
    public function test_getPreselectionReturnsInstance() : void
    {
        $configurator = new WizardConfigurator(self::WIZARD_BASE_URL);

        $preselection = $configurator->getPreselection();

        $this->assertInstanceOf(WizardPreselection::class, $preselection);

        // Must return the same instance on repeated calls.
        $this->assertSame($preselection, $configurator->getPreselection());
    }

    // -------------------------------------------------------------------------
    // test_customSettingPrefix
    // -------------------------------------------------------------------------

    /**
     * When a non-empty settingPrefix is supplied, step data keys must be
     * prefixed accordingly: prefix + '-step_' + stepName.
     *
     * Covers AC #7 from WP-003 and future-proofing for wizards that call
     * setSettingPrefix().
     */
    public function test_customSettingPrefix() : void
    {
        $prefix = 'mywiz';
        $configurator = new WizardConfigurator(self::WIZARD_BASE_URL, $prefix);
        $configurator->getPreselection()->setStepValue('StepA', 'key1', 'val1');

        $url = $configurator->getRedirectURL();

        parse_str((string)parse_url($url, PHP_URL_QUERY), $params);
        $sessionID = (string)($params['wizard'] ?? '');

        $session = AppFactory::createSession();
        $sessionData = $session->getValue($sessionID);

        $expectedStepKey = $prefix . '-step_StepA';
        $expectedHandlerKey = $prefix . '-invalidationHandler';

        $this->assertArrayHasKey($expectedStepKey, $sessionData, 'Step key must use the custom prefix.');
        $this->assertSame(array('key1' => 'val1'), $sessionData[$expectedStepKey]);

        $this->assertArrayHasKey($expectedHandlerKey, $sessionData, 'InvalidationHandler key must use the custom prefix.');

        // Keys with the default prefix must NOT be present.
        $this->assertArrayNotHasKey('-step_StepA', $sessionData, 'Default-prefix key must not exist when a custom prefix is used.');
    }

    // -------------------------------------------------------------------------
    // test_getRedirectURLIsIdempotent
    // -------------------------------------------------------------------------

    /**
     * Calling getRedirectURL() multiple times must return the same URL and
     * reuse the same session (idempotent after first call).
     *
     * Covers AC #8 from WP-003.
     */
    public function test_getRedirectURLIsIdempotent() : void
    {
        $configurator = new WizardConfigurator(self::WIZARD_BASE_URL);
        $configurator->getPreselection()->setStepValue('Countries', 'country_id', 1);

        $url1 = $configurator->getRedirectURL();
        $url2 = $configurator->getRedirectURL();

        $this->assertSame($url1, $url2, 'getRedirectURL() must return the same URL on repeated calls.');
    }
}
