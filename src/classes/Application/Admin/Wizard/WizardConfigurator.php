<?php
/**
 * @package Application
 * @subpackage Administration
 */

declare(strict_types=1);

namespace Application\Admin\Wizard;

use Application\AppFactory;

/**
 * Session orchestrator and URL builder for the Wizard Preselection API.
 *
 * Creates a wizard session pre-populated with preselection values and returns
 * a redirect URL that the consumer can use to send the user directly to the
 * wizard with step fields pre-filled.
 *
 * Usage example:
 *
 * <pre>
 * $configurator = new WizardConfigurator($wizardURL);
 * $configurator->getPreselection()
 *     ->setStepValue('Countries', 'country_id', 'GB');
 *
 * $redirectURL = $configurator->getRedirectURL();
 * // -> "https://example.com/admin/?page=wizardtest&mode=wizard&wizard=WZ12345678"
 * </pre>
 *
 * The `settingPrefix` constructor parameter must match the wizard's own
 * `$settingPrefix` property (defaults to `''`, which is the default for all
 * current wizards). Pass a non-empty prefix only if the target wizard calls
 * `setSettingPrefix()`.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard
 * @see WizardPreselection
 */
class WizardConfigurator
{
    /**
     * @var WizardPreselection
     */
    private WizardPreselection $preselection;

    /**
     * @var string
     */
    private string $wizardBaseURL;

    /**
     * @var string
     */
    private string $settingPrefix;

    /**
     * @var string|null
     */
    private ?string $sessionID = null;

    /**
     * @param string $wizardBaseURL  The base URL of the wizard screen, without the
     *                                wizard session parameter (e.g., obtained via AdminURL).
     * @param string $settingPrefix  Must exactly match the target wizard's `$settingPrefix`
     *                                property (case-sensitive). Defaults to `''`, which is the
     *                                correct value for all current wizards (none call
     *                                `setSettingPrefix()`). A mismatch silently produces
     *                                session keys that the wizard will not find, so preselection
     *                                values will appear as if they were never set.
     */
    public function __construct(string $wizardBaseURL, string $settingPrefix = '')
    {
        $this->wizardBaseURL = $wizardBaseURL;
        $this->settingPrefix = $settingPrefix;
        $this->preselection = new WizardPreselection();
    }

    /**
     * Returns the preselection value store. Use this to set step values
     * before calling {@see getRedirectURL()}.
     *
     * @return WizardPreselection
     */
    public function getPreselection() : WizardPreselection
    {
        return $this->preselection;
    }

    /**
     * Creates the wizard session (if not yet created), writes the preselection
     * values into the session step data slots, and returns a URL with the
     * wizard session ID appended.
     *
     * Calling this method multiple times returns the same URL and reuses the
     * same session (idempotent after first call).
     *
     * @return string The full wizard URL including the ?wizard=<sessionID> parameter.
     */
    public function getRedirectURL() : string
    {
        if ($this->sessionID === null)
        {
            $this->sessionID = $this->createSession();
        }

        $separator = strpos($this->wizardBaseURL, '?') !== false ? '&' : '?';

        return $this->wizardBaseURL . $separator . 'wizard=' . $this->sessionID;
    }

    /**
     * Creates a new wizard session, initialises the invalidation handler key,
     * and writes all preselection values into the session step data slots using
     * the key format the wizard trait already reads:
     * `settingPrefix + '-step_' + stepName`.
     *
     * @return string The newly created session ID.
     */
    private function createSession() : string
    {
        $sessionID = BaseWizardMode::generateNewSessionID();

        $session = AppFactory::createSession();
        $data = $session->getValue($sessionID);

        $invalidationHandler = new InvalidationHandler();
        $invalidationHandler->setIsInvalidated(false);

        $data[$this->settingPrefix . '-invalidationHandler'] = $invalidationHandler;

        foreach ($this->preselection->getStepNames() as $stepName)
        {
            $key = $this->settingPrefix . '-step_' . $stepName;
            $data[$key] = $this->preselection->getStepValues($stepName);
        }

        $session->setValue($sessionID, $data);

        return $sessionID;
    }
}
