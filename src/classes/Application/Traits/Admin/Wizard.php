<?php
/**
 * File containing the {@see Application_Traits_Admin_Wizard} trait.
 *
 * @package Application
 * @subpackage Wizard
 * @see Application_Traits_Admin_Wizard
 */

declare(strict_types=1);

use Application\Admin\Wizard\InvalidationHandler;
use Application\AppFactory;

/**
 * Trait for adding a wizard to an administration screen.
 *
 * Usage:
 *
 * - Extend the admin wizard class
 *
 * @package Application
 * @subpackage Wizard
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Interfaces_Admin_Wizardable
 * @see Application_Admin_Wizard
 * @see template_default_content_wizard
 *
 * @property Application_Request $request
 * @property Application_User $user
 * @property Application_Session $session
 * @property UI_Themes_Theme_ContentRenderer $renderer
 */
trait Application_Traits_Admin_Wizard
{
    /**
     * @var string
     */
    protected $wizardID;

    /**
     * @var string
     */
    protected $sessionID;

    /**
     * @var string
     */
    protected $classBase;

    /**
     * @var array
     */
    protected $sessionData = array();

    /**
     * @var array<string,Application_Admin_Wizard_Step>
     */
    protected $steps = array();

    /**
     * @var string
     */
    protected $initialStepName;

    /**
     * @var Application_Admin_Wizard_Step
     */
    protected $activeStep;

    /**
     * Set when a specific step has been requested via the request.
     * @var Application_Admin_Wizard_Step|NULL
     */
    protected $requestedStep;

    /**
     * @var string
     */
    protected $settingPrefix = '';

    /**
     * @var InvalidationHandler
     */
    protected InvalidationHandler $invalidationHandler;

    /**
     * @var bool
     */
    protected bool $hasPrevTransaction = true;

    abstract public function getWizardID() : string;

    abstract public function getClassBase() : string;

    abstract public function getSuccessMessage() : string;

    protected function getTemplateName() : string
    {
        return 'content/wizard';
    }

    public function initWizard() : void
    {
        $this->wizardID = $this->getWizardID();
        $this->classBase = $this->getClassBase();

        // create or get the wizard session ID: this is used to
        // identify a specific wizard session, and enables the
        // same user to use several parallel wizards. The ID is
        // included in all URLs and forms.
        $this->sessionID = $this->request->getParam('wizard');

        if (empty($this->sessionID))
        {
            $this->createSession();
        }

        $data = $this->session->getValue($this->sessionID);

        if(empty($data))
        {
            $this->createSession();
        }

        $this->log('Using existing wizard ID [%s].', $this->getWizardID());

        $this->sessionData = $this->session->getValue($this->sessionID);
        $this->invalidationHandler = $this->getWizardSetting('invalidationHandler');

        if ($this->sessionData['userID'] !== $this->user->getID())
        {
            $this->redirectWithInfoMessage(
                UI::icon()->information() . ' ' .
                t('Wizards can not be shared between users.'),
                $this->getCanceledURL()
            );
        }
    }

    public static function generateNewSessionID() : string
    {
        $user = Application::getUser();
        $sessionID = 'WZ' . crc32(microtime(true) . '-wizard-' . $user->getID());

        $session = AppFactory::createSession();

        $session->setValue(
            $sessionID,
            array(
                'sessionID' => $sessionID,
                'userID' => $user->getID(),
                'lastActive' => time()
            )
        );

        return $sessionID;
    }

    /**
     * @return never
     * @throws Application_Exception
     */
    private function createSession()
    {
        $this->sessionID = self::generateNewSessionID();

        $this->log(sprintf('Generated the wizard ID [%s].', $this->sessionID));

        $this->sessionData = $this->session->getValue($this->sessionID);

        $this->invalidationHandler = new InvalidationHandler();
        $this->invalidationHandler->setIsInvalidated(false);
        $this->setWizardSetting('invalidationHandler', $this->invalidationHandler);

        $this->saveSettings();

        $this->redirectTo($this->getURL($this->request->getRefreshParams()));
    }

    public function getSuccessURL() : string
    {
        return APP_URL;
    }

    /**
     * The session duration in minutes before it expires and must be started anew.
     * @var integer
     */
    protected int $sessionDuration = 200;

    public function reset() : void
    {
        if ($this->isSimulationEnabled())
        {
            $this->log('RESET | Simulation mode: ignoring the reset.');
            return;
        }

        $this->log('RESET | Resetting the wizard\'s session.');

        $this->session->unsetValue($this->sessionID);

        $this->_reset();
    }

    protected function _reset() : void
    {

    }

    protected function startWizardTransaction() : void
    {
        if (!$this->isTransactionStarted())
        {
            $this->startTransaction();
            $this->hasPrevTransaction = false;
        }
    }

    protected function endWizardTransaction() : void
    {
        if (!$this->hasPrevTransaction)
        {
            $this->endTransaction();
        }
    }

    protected function _handleActions() : bool
    {
        $this->checkWizardExpiry();

        $this->startWizardTransaction();

        // Initialize the steps before cancelling, since the
        // cancel URL may need to access the existing step's data.
        $this->initSteps('Initial setup');

        // the user has requested to cancel: we reset all session data.
        if ($this->request->getBool('cancel') === true)
        {
            $this->processCancelWizard();
        }

        $success = $this->activeStep->process();
        $this->saveSettings();

        $this->addDebugData('process');

        if ($success && !$this->request->getBool('review'))
        {
            // we re-create all the steps with the adjusted new data.
            $this->steps = array();
            $this->initSteps(sprintf('Step [%s] has processed successfully, re-initializing.', $this->activeStep->getID()));

            $this->addDebugData('post-process');
            $nextStep = $this->getNextStep();

            // we've reached the end
            if (!$nextStep)
            {
                $this->processCompleteWizard();
            }

            $this->endWizardTransaction();

            $this->redirectTo($this->getURL(array(
                'step' => $nextStep->getID()
            )));
        }

        $this->endWizardTransaction();

        return true;
    }

    public function processCancelWizard() : void
    {
        $redirectURL = $this->getCanceledURL(); // do this before the reset

        $this->processCancelCleanup();

        foreach ($this->steps as $step)
        {
            $step->handle_cancelWizardCleanup();
        }

        $this->reset();

        $this->redirectWithSuccessMessage(
            t('The wizard session has been cancelled.'),
            $redirectURL
        );
    }

    /**
     * This is called when the wizard is cancelled, and should
     * do any custom cleanup tasks the wizard needs to do. The
     * session and the like are cleaned automatically.
     */
    abstract protected function processCancelCleanup() : void;

    public function getCanceledURL() : string
    {
        return $this->getSuccessURL();
    }

    public function processCompleteWizard() : void
    {
        $successURL = $this->getSuccessURL(); // do this before the reset
        $message = $this->getSuccessMessage();

        $this->endTransaction();

        $this->reset();

        $this->redirectWithSuccessMessage(
            $message,
            $successURL
        );
    }

    /**
     * Retrieves a unique identifier for this wizard session.
     * @return string
     */
    public function getSessionID() : string
    {
        return $this->sessionData['sessionID'];
    }

    protected function collectDebugData(string $timelineLabel) : array
    {
        $debugData = array();
        foreach ($this->steps as $step)
        {
            $debugData['steps'][] = $step->getID();
        }

        $next = $this->getNextStep();

        $debugData['activeStep'] = $this->getActiveStep()->getID();
        $debugData['nextStep'] = null;
        if ($next)
        {
            $debugData['nextStep'] = $next->getID();
        }

        $nextIncomp = $this->getNextIncompleteStep();
        if ($nextIncomp)
        {
            $debugData['nextIncompleteStep'] = $nextIncomp->getID();
        }
        else
        {
            $debugData['nextIncompleteStep'] = '(none)';
        }
        $debugData['data'] = $this->sessionData;

        $debug = array();
        $debug[$timelineLabel] = $debugData;

        return $debug;
    }

    protected function addDebugData(string $timelineLabel) : void
    {
        $debug = $this->collectDebugData($timelineLabel);
        $this->session->setValue('wizard-debug-' . $this->getWizardID(), $debug);
    }

    protected function _handleBreadcrumb() : void
    {
        $this->breadcrumb->appendArea($this->area);
        $this->breadcrumb->appendItem($this->getTitle());
    }

    /**
     * @return UI_Themes_Theme_ContentRenderer
     * @see template_default_content_wizard
     */
    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $this->log(sprintf('Rendering content with active step [%s].', $this->activeStep->getID()));

        $content = $this->renderTemplate(
            $this->getTemplateName(),
            array(
                'wizard' => $this,
                'steps' => $this->steps,
                'activeStep' => $this->activeStep
            )
        );

        return $this->renderer->appendContent($content)
            ->setTitle($this->getTitle())
            ->setAbstract($this->activeStep->getAbstract());
    }

    /**
     * Retrieves the next step after the currently active
     * step, if any. If this returns null, the end of the
     * queue has been reached.
     *
     * @return Application_Admin_Wizard_Step|NULL
     */
    public function getNextStep() : ?Application_Admin_Wizard_Step
    {
        $next = $this->activeStep->getNumber() + 1;
        foreach ($this->steps as $step)
        {
            if ($step->getNumber() === $next)
            {
                return $step;
            }
        }

        return null;
    }

    /**
     * Retrieves the next step in line that has not been completed yet.
     * @return Application_Admin_Wizard_Step|NULL
     */
    public function getNextIncompleteStep() : ?Application_Admin_Wizard_Step
    {
        foreach ($this->steps as $step)
        {
            if (!$step->isComplete())
            {
                return $step;
            }
        }

        return null;
    }

    /**
     * Retrieves the previous step before the currently active
     * step, if any. Returns null at the beginning of the steps.
     *
     * @return Application_Admin_Wizard_Step|NULL
     */
    public function getPreviousStep() : ?Application_Admin_Wizard_Step
    {
        $previous = $this->activeStep->getNumber() - 1;
        if ($previous < 1)
        {
            return null;
        }

        foreach ($this->steps as $step)
        {
            if ($step->getNumber() === $previous)
            {
                return $step;
            }
        }

        return null;
    }

    protected function initSteps(string $reason) : void
    {
        $this->log('Steps init | ' . $reason);

        $this->_initSteps();

        reset($this->steps);
        $this->initialStepName = key($this->steps);

        $this->log(sprintf('Registered steps [%s].', implode(', ', array_keys($this->steps))));

        $activeStepName = (string)$this->getWizardSetting('activeStep', $this->initialStepName);
        if (!$this->stepExists($activeStepName))
        {
            $activeStepName = $this->initialStepName;
        }

        $this->activeStep = $this->steps[$activeStepName];

        $this->request->registerParam('step')->setEnum(array_keys($this->steps));
        $requestedStepName = strval($this->request->getParam('step'));

        $this->log(sprintf('Requested step is [%s].', $requestedStepName));

        if (!empty($requestedStepName) && $this->isValidStep($requestedStepName))
        {
            $this->activeStep = $this->steps[$requestedStepName];
            $this->requestedStep = $this->activeStep;
        }

        foreach ($this->steps as $step)
        {
            $step->initDone();
            $step->postInit();
        }
    }

    abstract protected function _initSteps() : void;

    public function hasStep(string $name) : bool
    {
        return isset($this->steps[$name]);
    }

    public function isValidStep(string $name) : bool
    {
        if (!$this->hasStep($name))
        {
            return false;
        }

        if ($name === $this->activeStep->getID())
        {
            return true;
        }

        $nextIncomplete = $this->getNextIncompleteStep();
        if ($nextIncomplete && $name === $nextIncomplete->getID())
        {
            return true;
        }

        return $this->steps[$name]->isComplete();
    }

    public function getActiveStep() : Application_Admin_Wizard_Step
    {
        return $this->activeStep;
    }

    /**
     * Retrieves all steps in the wizard, in the order they are processed.
     * @return Application_Admin_Wizard_Step[]
     */
    public function getSteps() : array
    {
        return array_values($this->steps);
    }

    /**
     * Retrieves a specific step by its name, e.g. "StepName".
     * @param string $name
     * @return Application_Admin_Wizard_Step
     */
    public function getStep(string $name) : Application_Admin_Wizard_Step
    {
        if (!isset($this->steps[$name]))
        {
            throw new Application_Exception(
                'Unknown step',
                sprintf(
                    'The step [%s] does not exist. Available steps are [%s].',
                    $name,
                    implode(', ', array_keys($this->steps))
                ),
                Application_Interfaces_Admin_Wizardable::ADMIN_WIZARD_ERROR_UNKNOWN_STEP
            );
        }

        return $this->steps[$name];
    }

    /**
     * Checks whether the specified step exists.
     * @param string $name
     * @return boolean
     */
    public function stepExists(string $name) : bool
    {
        return isset($this->steps[$name]);
    }

    /**
     * Adds a mew step to the wizard. Loads the class,
     * instantiates the instance and stores it in the
     * steps list.
     *
     * @param string $name
     * @return Application_Admin_Wizard_Step
     */
    protected function addStep(string $name) : Application_Admin_Wizard_Step
    {
        if (isset($this->steps[$name]))
        {
            throw new Application_Exception(
                'Wizard step already exists.',
                sprintf(
                    'The step [%s] has already been added, and cannot be added a second time.',
                    $name
                ),
                Application_Interfaces_Admin_Wizardable::ADMIN_WIZARD_ERROR_STEP_ALREADY_EXISTS
            );
        }

        $className = $this->classBase . '_Step_' . $name;

        $number = count($this->steps) + 1;

        $step = new $className(
            $this,
            $number,
            $this->getStepData($name)
        );

        $this->steps[$name] = $step;
        return $step;
    }

    private function getStepData(string $name) : array
    {
        $data = $this->getWizardSetting('step_' . $name);

        if (is_array($data))
        {
            return $data;
        }

        return array();
    }

    /**
     * Retrieves a setting from the session.
     *
     * @param string $name
     * @param mixed|NULL $default
     * @return mixed
     */
    protected function getWizardSetting(string $name, $default = null)
    {
        $key = $this->settingPrefix . '-' . $name;

        if (isset($this->sessionData[$key]))
        {
            return $this->sessionData[$key];
        }

        return $default;
    }

    /**
     * Sets a setting of the steps wizard.
     *
     * @param string $name
     * @param mixed|NULL $value
     * @return $this
     */
    protected function setWizardSetting(string $name, $value) : self
    {
        $key = $this->settingPrefix . '-' . $name;

        $this->sessionData[$key] = $value;
        return $this;
    }

    protected function saveSettings() : void
    {
        // get a fresh copy of the step data
        foreach ($this->steps as $step)
        {
            $this->setWizardSetting('step_' . $step->getID(), $step->getData());
        }

        $this->session->setValue($this->sessionID, $this->sessionData);
    }

    protected function setSettingPrefix(string $prefix) : void
    {
        $this->settingPrefix = $prefix;
    }

    public function getCancelURL() : string
    {
        return $this->getURL(array(
            'cancel' => 'yes'
        ));
    }

    public function getURL(array $params = array()) : string
    {
        $params['wizard'] = $this->getSessionID();

        return parent::getURL($params);
    }

    /**
     * Finds all steps that are monitoring changes to the
     * target step that has been updated, and gives them the
     * possibility to adjust their own settings.
     *
     * @param Application_Admin_Wizard_Step $updatedStep
     */
    public function handle_stepUpdated(Application_Admin_Wizard_Step $updatedStep) : void
    {
        $number = $updatedStep->getNumber();
        foreach ($this->steps as $step)
        {
            if ($step->getNumber() > $number && $step->isMonitoring($updatedStep))
            {
                $step->handle_stepUpdated($updatedStep);
            }
        }
        if ($this->invalidationHandler->isInvalidated() && $number === $this->invalidationHandler->getInvalidationCallingStep())
        {
            $this->redirectWithErrorMessage($this->invalidationHandler->getInvalidationMessage(), $this->invalidationHandler->getInvalidationURL());
        }
    }

    /**
     * Handles a step being invalidated: updates all steps that are
     * dependent on this step, saves all step data, and redirects
     * to the step so the user can complete it anew.
     *
     * @param Application_Admin_Wizard_Step $step
     * @param string $reasonMessage
     * @param int $callingStep
     */
    public function handle_stepInvalidated(Application_Admin_Wizard_Step $step, string $reasonMessage, int $callingStep = 0) : void
    {
        $this->handle_stepUpdated($step);
        $step->setComplete(false);
        $this->saveSettings();
        $this->checkInvalidation($step->getURL(), $reasonMessage, $callingStep);
    }

    /**
     * Determine first invalidated step for redirecting to this page at the end of all steps' checks.
     *
     * @param string $stepURL
     * @param string $reasonMessage
     * @param int $callingStep
     */
    protected function checkInvalidation(string $stepURL, string $reasonMessage, int $callingStep) : void
    {
        if (!$this->invalidationHandler->isInvalidated())
        {
            $this->invalidationHandler->setInvalidationMessage($reasonMessage)
                ->setInvalidationURL($stepURL)
                ->setIsInvalidated(true)
                ->setInvalidationCallingStep($callingStep);
        }
    }

    /**
     * Checks whether the specified step is complete.
     * Will return false if the step does not exist.
     *
     * @param string $name
     * @return boolean
     * @throws Application_Exception
     */
    public function isStepComplete(string $name) : bool
    {
        if ($this->hasStep($name))
        {
            return $this->getStep($name)->isComplete();
        }

        return false;
    }

    public function getLastActive() : int
    {
        return (int)$this->sessionData['lastActive'];
    }

    private function checkWizardExpiry() : void
    {
        // check when this wizard session was last active,
        // and reset it if it is too old.
        $expiry = $this->getLastActive() + ($this->sessionDuration * 60);
        if ($expiry < time())
        {
            $this->reset();
            $this->redirectWithInfoMessage(
                t('The previously started wizard session expired, please start anew.'),
                $this->area->getURL()
            );
        }

        // the user is active: reset the expiry timer
        $this->sessionData['lastActive'] = time();
    }

    /**
     * A wizard has no sub-screens.
     * @return array<string,string>
     */
    public function getSubscreenIDs() : array
    {
        return array();
    }
}
