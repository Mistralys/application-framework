<?php
/**
 * Class containing the {@link Application_Admin_Wizard_Step} class.
 *
 * @package Application
 * @subpackage Administration
 * @see Application_Admin_Wizard_Step
 */

use AppUtils\OutputBuffering;
use function AppUtils\parseVariable;

/**
 * Base class for individual steps in a wizard. Based on the application
 * skeleton for administration pages, this allows for easy form handling
 * and the base structure handles all the data flow and necessary updates.
 *
 * @package Application
 * @subpackage Administration
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Admin_Wizard
 */
abstract class Application_Admin_Wizard_Step extends Application_Admin_Skeleton
{
    public const ERROR_STEP_MUST_RETURN_BOOLEAN_VALUE = 558001;
    public const ERROR_CANNOT_UPDATE_FROM_UNMONITORED_STEP = 558002;
    public const ERROR_UNHANDLED_STEP_UPDATE = 558003;
    public const ERROR_STEP_MUST_BE_COMPLETE_FOR_OPERATION = 558004;
    public const ERROR_WIZARD_STEPS_HAVE_NO_SUBSCREENS = 558005;

    /**
     * @var Application_Interfaces_Admin_Wizardable
     */
    protected $wizard;

    /**
     * The step number in the queue
     * @var integer
     */
    protected $number;

    /**
     * @var array<string,mixed>
     */
    protected $data;

    /**
     * @var string[]
     */
    protected $monitoredSteps;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $instanceID;

    /**
     * @param Application_Interfaces_Admin_Wizardable $wizard
     * @param int $number
     * @param array<string,mixed> $data
     */
    public function __construct(Application_Interfaces_Admin_Wizardable $wizard, int $number, array $data = array())
    {
        parent::__construct(Application_Driver::getInstance());

        $this->wizard = $wizard;
        $this->number = $number;
        $this->data = $data;
        $this->instanceID = nextJSID();
        $this->monitoredSteps = $this->getMonitoredSteps();
        $this->id = str_replace($this->wizard->getClassBase() . '_Step_', '', get_class($this));

        if (!isset($this->data))
        {
            $this->data = $this->getDefaultData();
            $this->setComplete(false);
        }

        $this->init();
    }

    /**
     * Called when all steps in the wizard have been
     * initialized, and before the step is processed.
     * Use this to set up the step's environment.
     */
    abstract public function initDone() : void;

    /**
     * Called right after instantiation of the step class.
     * Used to set up the base environment. Note: the wizard
     * is not finished initializing at this point. Use the
     * {@link preProcess()} method otherwise.
     */
    abstract protected function init() : void;

    /**
     * Called before the step is processed, used for
     * any initialization routines the step may need.
     * At this time, all steps in the wizard have been
     * initialized and can be accessed.
     */
    abstract protected function preProcess() : void;

    abstract public function getLabel() : string;

    /**
     * @return array<string,mixed>
     */
    abstract protected function getDefaultData() : array;

    abstract public function _process() : bool;

    abstract public function getAbstract() : string;

    /**
     * Optional icon for the step.
     * @return UI_Icon|NULL
     */
    public function getIcon() : ?UI_Icon
    {
        return null;
    }

    /**
     * Retrieves the name of the step, e.g. "StepName".
     *
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

    public function getTitle() : string
    {
        return $this->getLabel();
    }

    /**
     * Called before rendering the step's contents. Must return
     * a boolean value indicating whether the step has been
     * completed, in which case the wizard can jump to the next step.
     *
     * @return boolean
     */
    public function process() : bool
    {
        $this->log('Process | Pre-Process');

        $this->preProcess();

        $result = $this->_process();

        $this->log(sprintf('Process | Processed: [%s].', parseVariable($result)));

        if (!is_bool($result))
        {
            throw new Application_Exception(
                'Not a boolean value',
                sprintf(
                    'The [_process] method of a step must return a boolean value in step class [%s].',
                    get_class($this)
                ),
                self::ERROR_STEP_MUST_RETURN_BOOLEAN_VALUE
            );
        }

        // processing was successful
        if ($result)
        {
            // The step has not been completed before:
            // we set it to completed.
            if (!$this->isComplete())
            {
                $this->setComplete();
            }
            // the step has been completed before, and
            // some data has changed: we need to
            // let the other steps adjust to the changes.
            else
            {
                if ($this->updateRequired)
                {
                    $this->wizard->handle_stepUpdated($this);
                }
            }
        }

        return $result;
    }

    /**
     * Returns an indexed array with step IDs to monitor
     * changes from: if one of these is modified the
     * {@link handle_stepUpdated()} method is called.
     *
     * @return string[]
     */
    protected function getMonitoredSteps() : array
    {
        return array();
    }

    /**
     * The URL to switch to this step.
     * @return string
     */
    public function getURL(array $params = array()) : string
    {
        $params['step'] = $this->getID();
        return $this->wizard->getURL($params);
    }

    /**
     * The URL to review this step when it has been completed.
     * @param array $params
     * @return string
     */
    public function getURLReview(array $params = array()) : string
    {
        $params['review'] = 'yes';
        return $this->getURL($params);
    }

    /**
     * The step number (begins at 1).
     * @return int
     */
    public function getNumber() : int
    {
        return $this->number;
    }

    /**
     * Whether this is the active step.
     * @return boolean
     */
    public function isActive() : bool
    {
        return $this->wizard->getActiveStep()->getID() === $this->getID();
    }

    /**
     * Whether this step can be switched to. This is true for
     * all steps that have been completed.
     *
     * @return boolean
     */
    public function isEnabled() : bool
    {
        return $this->isComplete();
    }

    /**
     * Checks if this step has been completed.
     * @return boolean
     */
    public function isComplete() : bool
    {
        return isset($this->data['completed']) && $this->data['completed'] === true;
    }

    /**
     * Sets the completed state of the step.
     * @param boolean $complete
     * @return $this
     */
    public function setComplete(bool $complete = true)
    {
        $this->data['completed'] = $complete;
        return $this;
    }

    /**
     * Overridden to add the required hidden form variables.
     * @see Application_Formable::createFormableForm()
     */
    public function createFormableForm(string $name, array $defaultData = array()) : void
    {
        parent::createFormableForm($name, $defaultData);

        $this->addFormablePageVars();

        $this->addHiddenVars(array(
            'wizard' => $this->wizard->getSessionID(),
            'step' => $this->wizard->getActiveStep()->getID()
        ));
    }

    /**
     * Retrieves the step's session data collection, which is stored
     * by the wizard itself and restored on every request.
     *
     * @return array<string,mixed>
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Retrieves the URL to cancel the wizard.
     *
     * @return string
     */
    protected function getCancelURL() : string
    {
        return $this->wizard->getCancelURL();
    }

    /**
     * Adds the next/previous buttons to the current formable
     * form. Automatically detects where the step is in the
     * queue and adjusts the buttons accordingly.
     */
    protected function injectNavigationButtons() : void
    {
        $this->requireFormableInitialized();

        $this->injectNavigationBack();
        $this->injectNavigationPrimary();

        $this->formableForm->addLinkButton(
            $this->getCancelURL(),
            UI::icon()->cancel() . ' ' . t('Cancel'),
            t('Cancels the wizard session.')
        )
            ->addClass('btn-warning')
            ->addClass('wizard-cancel');

        if ($this->user->isDeveloper())
        {
            $this->formableForm->addDevPrimarySubmit(t('Submit'))
                ->addClass('wizard-dev-submit');
        }
    }

    private function injectNavigationPrimary() : void
    {
        $next = $this->wizard->getNextStep();

        if ($next)
        {
            $this->formableForm->addPrimarySubmit(
                t('Next') . ' ' . UI::icon()->next(),
                'save',
                t('Go forward to %1$s.', $next->getLabel())
            );

            return;
        }

        $el = $this->formableForm->addPrimarySubmit(
            $this->getButtonConfirmIcon() . ' ' . $this->getButtonConfirmLabel(),
            'save',
            $this->getButtonConfirmTooltip()
        );

        $el->setAttribute(
            'onclick',
            sprintf(
                "application.showLoader(%s)",
                JSHelper::phpVariable2AttributeJS($this->getButtonConfirmLoadingText())
            )
        );
    }

    private function injectNavigationBack() : void
    {
        $prev = $this->wizard->getPreviousStep();

        if (!$prev)
        {
            return;
        }

        $this->formableForm->addLinkButton(
            $prev->getURLReview(),
            UI::icon()->previous() . ' ' . t('Back'),
            t('Go back one step to %1$s.', $prev->getLabel())
        );
    }

    protected function getButtonConfirmIcon() : UI_Icon
    {
        return UI::icon()->ok();
    }

    protected function getButtonConfirmLoadingText() : string
    {
        return t('Please wait, processing tasks...');
    }

    protected function getButtonConfirmLabel() : string
    {
        return t('Confirm');
    }

    protected function getButtonConfirmTooltip() : string
    {
        return t('Confirms the wizard session, and executes all planned tasks.');
    }

    protected $updateRequired = false;

    /**
     * Sets a data key. This should be used when the step has been submitted,
     * as it checks if the data has been modified. If it has been modified,
     * all steps will have the occasion to review whether this invalidates
     * their own data.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    protected function setData(string $name, $value)
    {
        $old = null;
        if (isset($this->data[$name]))
        {
            $old = $this->data[$name];
        }

        // If this step has already been completed previously and
        // a data key is modified, an update of the wizard is required.
        if ($this->isComplete() && !$this->updateRequired && $old !== $value)
        {
            if ($this->isComplete())
            {
                $this->updateRequired = true;
            }
        }

        $this->data[$name] = $value;

        return $this;
    }

    /**
     * This is called when a step in the wizard has been modified
     * that comes before this one. Allows the step to adjust its
     * status according to the new data.
     *
     * @param Application_Admin_Wizard_Step $step
     */
    public function handle_stepUpdated(Application_Admin_Wizard_Step $step)
    {
        if (!$this->isComplete())
        {
            return;
        }

        if (!$this->isMonitoring($step))
        {
            throw new Application_Exception(
                'Cannot update from non-monitored step',
                sprintf(
                    'The step [%s] is not monitored by the [%s] step.',
                    $step->getID(),
                    $this->getID()
                ),
                self::ERROR_CANNOT_UPDATE_FROM_UNMONITORED_STEP
            );
        }

        $this->_handle_stepUpdated($step);

        if ($this->updateRequired)
        {
            $this->wizard->handle_stepUpdated($this);
        }
    }

    public function handle_cancelWizardCleanup() : void
    {

    }

    /**
     * Called automatically when a step is updated that this step is
     * monitoring. Allows adjusting any data as required. Must be
     * implemented in the class if a step is being monitored.
     *
     * @param Application_Admin_Wizard_Step $step
     * @throws Application_Exception
     */
    protected function _handle_stepUpdated(Application_Admin_Wizard_Step $step) : void
    {
        throw new Application_Exception(
            'Unhandled step update operation',
            sprintf(
                'The step [%s] is set to update automatically when the step [%s] is modified, but the [_handle_stepUpdated] method is not implemented.',
                $this->getID(),
                $step->getID()
            ),
            self::ERROR_UNHANDLED_STEP_UPDATE
        );
    }

    /**
     * Checks whether this step monitors changes to the target step.
     *
     * @param Application_Admin_Wizard_Step $step
     * @return bool
     */
    public function isMonitoring(Application_Admin_Wizard_Step $step) : bool
    {
        return in_array($step->getID(), $this->monitoredSteps);
    }

    /**
     * Invalidates the step: resets its data collection and sets
     * it as not completed to force the step to be completed anew.
     * The reason message is shown in the UI to inform of the change.
     *
     * WARNING: This must not be called when a step is being updated,
     * as it would trigger an infinite loop, which is handled with a
     * specific exception.
     *
     * @param string $reasonMessage
     * @param int $callingStep
     */
    protected function invalidate(string $reasonMessage, int $callingStep = 0) : void
    {
        $this->data = $this->getDefaultData();
        $this->updateRequired = true;
        $this->wizard->handle_stepInvalidated($this, $reasonMessage, $callingStep);
    }

    public function getURLName() : string
    {
        return strtolower($this->getID());
    }

    public function getURLPath() : string
    {
        return $this->wizard->getURLPath();
    }

    protected function initStepForm() : void
    {
        $this->createFormableForm($this->getFormName(), $this->getFormData());
    }

    public function getFormName() : string
    {
        return 'wizard_' . $this->wizard->getWizardID() . '_' . $this->getID();
    }

    /**
     * @return array<string,mixed>
     */
    protected function getFormData() : array
    {
        return $this->getData();
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getDataKey(string $name)
    {
        $data = $this->getData();
        if (isset($data[$name]))
        {
            return $data[$name];
        }

        return null;
    }

    /**
     * Creates a data grid compatible for use with the
     * wizard step: since steps are created and re-created
     * on the fly, the ID is an issue for example, since those
     * must usually be unique.
     *
     * Also adds all necessary hidden variables for the current
     * page to be able to use all grid functions.
     *
     * @return UI_DataGrid
     */
    protected function createDataGrid() : UI_DataGrid
    {
        $grid = $this->ui->createDataGrid($this->getSessionID(), true);

        $grid->addHiddenVar('step', $this->getID());

        if ($this->wizard instanceof Application_Admin_Area_Mode_Submode)
        {
            $grid->addHiddenVar('submode', $this->wizard->getURLName());
        }

        if ($this->wizard instanceof Application_Admin_Area_Mode_Submode_Action)
        {
            $grid->addHiddenVar('submode', $this->wizard->getSubmode()->getURLName());
            $grid->addHiddenVar('action', $this->wizard->getURLName());
        }

        return $grid;
    }

    /**
     * Retrieves the step's unique ID for this session of the
     * wizard, which will stay the same for its duration.
     * Entirely unrelated to the PHP session ID.
     *
     * @return string
     */
    protected function getSessionID() : string
    {
        return $this->wizard->getSessionID() . '-' . $this->getID();
    }

    public function getParent()
    {
        return null;
    }

    /**
     * @return string[]
     */
    public function getDataKeyNames() : array
    {
        $def = $this->getDefaultData();
        return array_keys($def);
    }

    /**
     * Retrieves the default data for a form:
     *
     * - In the initial state, uses the result of _getDefaultFormData.
     * - When submitted, the form uses the submitted data.
     * - When in completed state, uses the current data.
     *
     * @return array<string,mixed>
     */
    protected function getDefaultFormData() : array
    {
        if ($this->isComplete())
        {
            return $this->getData();
        }

        return $this->_getDefaultFormData();
    }

    /**
     * @return array<string,mixed>
     */
    protected function _getDefaultFormData() : array
    {
        return $this->getDefaultData();
    }

    protected function requireStepComplete() : void
    {
        if ($this->isComplete())
        {
            return;
        }

        throw new Application_Exception(
            'Operation not allowed: wizard step is not complete.',
            '',
            self::ERROR_STEP_MUST_BE_COMPLETE_FOR_OPERATION
        );
    }

    public function postInit() : void
    {
        $this->renderCompletedSteps();
    }

    /**
     * @var array<int,array{icon:UI_Icon|null,label:string,value:string}>
     */
    protected $completedSteps = array();

    protected function registerCompletedStep(string $label, string $value, ?UI_Icon $icon) : void
    {
        $this->completedSteps[] = array(
            'icon' => $icon,
            'label' => $label,
            'value' => $value
        );
    }

    protected function renderCompletedSteps() : void
    {
        if (empty($this->completedSteps))
        {
            return;
        }

        OutputBuffering::start();

        ?>
        <table class="wizard-completed-steps">
            <tbody>
            <?php
            foreach ($this->completedSteps as $item)
            {
                ?>
                <tr>
                    <td class="align-center"><?php echo $item['icon'] ?></td>
                    <td><?php echo $item['label'] . ' ' . $item['value'] ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <?php

        $this->renderer->setTitleSubline(OutputBuffering::get());
    }

    // ----------------------------------------------------------------
    // ADMIN SCREEN INTERFACE IMPLEMENTATION
    // ----------------------------------------------------------------

    //region: Admin screen interface methods

    public function getLogIdentifier() : string
    {
        return $this->wizard->getLogIdentifier() . ' | ' . sprintf('Step [%s]', $this->getID());
    }

    public function getArea() : Application_Admin_Area
    {
        return $this->wizard->getArea();
    }

    public function getSidebar() : ?UI_Page_Sidebar
    {
        return $this->wizard->getSidebar();
    }

    public function requireSidebar() : UI_Page_Sidebar
    {
        return $this->wizard->requireSidebar();
    }

    public function getNavigationTitle() : string
    {
        return $this->getTitle();
    }

    public function getParentScreen() : ?Application_Admin_ScreenInterface
    {
        return $this->wizard;
    }

    public function handleActions() : bool
    {
        return true;
    }

    public function renderContent() : string
    {
        return '';
    }

    public function getURLParam() : string
    {
        return '';
    }

    public function handleBreadcrumb() : void
    {
    }

    public function getDefaultSubscreenID() : string
    {
        return '';
    }

    public function handleSidebar(UI_Page_Sidebar $sidebar) : void
    {
    }

    public function hasActiveSubscreen() : bool
    {
        return false;
    }

    public function handleTabs(UI_Bootstrap_Tabs $tabs) : void
    {
    }

    public function handleContextMenu(UI_Bootstrap_DropdownMenu $menu) : void
    {
    }

    public function handleSubnavigation(UI_Page_Navigation $subnav) : void
    {
    }

    public function isUserAllowed() : bool
    {
        return true;
    }

    public function isArea() : bool
    {
        return false;
    }

    public function handleHelp(UI_Page_Help $help) : void
    {
    }

    public function getActiveSubscreenID() : ?string
    {
        return null;
    }

    public function getActiveSubscreen() : ?Application_Admin_ScreenInterface
    {
        return null;
    }

    public function hasSubscreen(string $id) : bool
    {
        return false;
    }

    public function getSubscreenIDs() : array
    {
        return array();
    }

    public function hasSubscreens() : bool
    {
        return false;
    }

    public function getSubscreenByID(string $id) : Application_Admin_ScreenInterface
    {
        throw new Application_Exception(
            'Wizard steps have no subscreens.',
            'Cannot get a subscreen by its ID, wizard steps have no subscreens.',
            self::ERROR_WIZARD_STEPS_HAVE_NO_SUBSCREENS
        );
    }

    // endregion
}
