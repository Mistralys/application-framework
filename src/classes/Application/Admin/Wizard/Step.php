<?php
/**
 * Class containing the {@link Application_Admin_Wizard_Step} class.
 *  
 * @package Application
 * @subpackage Administration
 * @see Application_Admin_Wizard_Step
 */

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
    const ERROR_STEP_MUST_RETURN_BOOLEAN_VALUE = 558001;
    const ERROR_CANNOT_UPDATE_FROM_UNMONITORED_STEP = 558002;
    const ERROR_UNHANDLED_STEP_UPDATE = 558003;
    const ERROR_STEP_MUST_BE_COMPLETE_FOR_OPERATION = 558004;
    const ERROR_WIZARD_STEPS_HAVE_NO_SUBSCREENS = 558005;
    
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
    public function __construct(Application_Interfaces_Admin_Wizardable $wizard, int $number, array $data=array())
    {
        parent::__construct(Application_Driver::getInstance());
        
        $this->wizard = $wizard;
        $this->number = $number;
        $this->data = $data;
        $this->instanceID = nextJSID();
        $this->monitoredSteps = $this->getMonitoredSteps();
        $this->id = str_replace($this->wizard->getClassBase().'_Step_', '', get_class($this));
        
        if(!isset($this->data)) {
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
    abstract public function initDone();
    
   /**
    * Called right after instatiation of the step class.
    * Used to set up the base environment. Note: the wizard
    * is not finished initializing at this point. Use the
    * {@link preProcess()} method otherwise.
    */
    abstract protected function init();
    
   /**
    * Called before the step is processed, used for 
    * any initialization routines the step may need.
    * At this time, all steps in the wizard have been
    * initialized and can be accessed.
    */
    abstract protected function preProcess();

    abstract public function getLabel();
    
    abstract protected function getDefaultData();
    
    abstract public function _process();
    
    abstract public function render();
    
    abstract public function getAbstract();

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
    public function getID()
    {
        return $this->id;
    }
    
    public function getTitle()
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
        
        if(!is_bool($result)) {
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
        if($result) 
        {
            // The step has not been completed before: 
            // we set it to completed.
            if(!$this->isComplete()) {
                $this->setComplete();
            }
            // the step has been completed before, and
            // some of the data has changed: we need to
            // let the other steps adjust to the changes.
            else if($this->updateRequired) 
            {
                $this->wizard->handle_stepUpdated($this);
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
    protected function getMonitoredSteps()
    {
        return array();
    }    
    
   /**
    * The URL to switch to this step.
    * @return string
    */
    public function getURL(array $params=array()) : string
    {
        $params['step'] = $this->getID();
        return $this->wizard->getURL($params);
    }
    
   /**
    * The URL to review this step when it has been completed.
    * @param array $params
    * @return string
    */
    public function getURLReview(array $params=array()) : string
    {
        $params['review'] = 'yes';
        return $this->getURL($params);
    }
   
   /**
    * The step number (begins at 1).
    * @return int
    */
    public function getNumber()
    {
        return $this->number;
    }
    
   /**
    * Whether this is the active step.
    * @return boolean
    */
    public function isActive() : bool
    {
        if($this->wizard->getActiveStep()->getID() == $this->getID()) {
            return true;
        }
        
        return false;
    }
    
   /**
    * Whether this step can be switched to. This is true for
    * all steps that have been completed.
    * 
    * @return boolean
    */
    public function isEnabled()
    {
        if($this->isComplete()) {
            return true;
        }
        
        return false;
    }
    
   /**
    * Checks if this step has been completed.
    * @return boolean
    */
    public function isComplete()
    {
        return isset($this->data['completed']) && $this->data['completed'] === true;
    }
    
   /**
    * Sets the completed state of the step.
    * @param boolean $complete
    * @return Application_Admin_Wizard_Step
    */
    public function setComplete($complete=true)
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
    * @return array
    */
    public function getData()
    {
        return $this->data;
    }
    
   /**
    * Retrieves the URL to cancel the wizard: cleans up the session
    * data and redirects to the main campaigns management screen.
    * 
    * @return string
    */
    protected function getCancelURL()
    {
        return $this->wizard->getCancelURL();
    }
    
   /**
    * Adds the next/previous buttons to the current formable
    * form. Automatically detects where the step is in the
    * queue and adjusts the buttons accordingly.
    */
    protected function injectNavigationButtons()
    {
        $this->requireFormableInitialized();
        
        $prev = $this->wizard->getPreviousStep();
        if($prev) 
        {
            $this->formableForm->addLinkButton(
                $prev->getURLReview(), 
                UI::icon()->previous().' '.t('Back'),
                t('Go back one step to %1$s.', $prev->getLabel())
            );
        }
        
        $next = $this->wizard->getNextStep();
        if($next) 
        { 
            $this->formableForm->addPrimarySubmit(
                t('Next').' '.UI::icon()->next(),
                'save',
                t('Go forward to %1$s.', $next->getLabel())
            );        
        }
        else
        {
            $this->formableForm->addPrimarySubmit(
                UI::icon()->ok().' '.$this->getButtonConfirmLabel(),
                'save',
                $this->getButtonConfirmTooltip()
            );  
        }
        
        $this->formableForm->addLinkButton(
            $this->getCancelURL(), 
            UI::icon()->cancel().' '.t('Cancel'),
            t('Cancels the wizard session.')
        )
        ->addClass('btn-warning')
        ->addClass('wizard-cancel');

        if($this->user->isDeveloper()) 
        {
            $this->formableForm->addDevPrimarySubmit(t('Submit'))
            ->addClass('wizard-dev-submit');
        }
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
    * @return Application_Admin_Wizard_Step
    */
    protected function setData($name, $value)
    {
        $old = null;
        if(isset($this->data[$name])) {
            $old = $this->data[$name];
        }
        
        // If this step has already been completed previously and
        // a data key is modified, an update of the wizard is required.
        if($this->isComplete() && !$this->updateRequired && $old !== $value) {
            if($this->isComplete()) {
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
        if(!$this->isComplete()) {
            return;
        }
        
        if(!$this->isMonitoring($step)) {
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
        
        if($this->updateRequired) {
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
    protected function _handle_stepUpdated(Application_Admin_Wizard_Step $step)
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
    public function isMonitoring(Application_Admin_Wizard_Step $step)
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
    */
    protected function invalidate($reasonMessage)
    {
        $this->data = $this->getDefaultData();
        $this->updateRequired = true;
        $this->wizard->handle_stepInvalidated($this, $reasonMessage);
    }

    /**
     * {@inheritDoc}
     * @see Application_Admin_Skeleton::getURLName()
     */
    public function getURLName()
    {
        return strtolower($this->getID());
    }
    
    /**
     * {@inheritDoc}
     * @see Application_Admin_Skeleton::getURLPath()
     */
    public function getURLPath()
    {
        return $this->wizard->getURLPath();
    }
    
    protected function initStepForm()
    {
        $this->createFormableForm($this->getFormName(), $this->getFormData());
    }
    
    protected function getFormName() : string
    {
        return 'wizard_'.$this->wizard->getWizardID().'_'.$this->getID();
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
        if(isset($data[$name])) {
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
    * @param string $id
    * @return UI_DataGrid
    */
    protected function createDataGrid($id=null)
    {
        $grid = $this->ui->createDataGrid($this->getSessionID(), true);
        
        $grid->addHiddenVar('step', $this->getID());
        
        if($this->wizard instanceof Application_Admin_Area_Mode_Submode) {
            $grid->addHiddenVar('submode', $this->wizard->getURLName());
        }
        
        if($this->wizard instanceof Application_Admin_Area_Mode_Submode_Action) {
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
    protected function getSessionID()
    {
        return $this->wizard->getSessionID().'-'.$this->getID();
    }
    
    public function getParent()
    {
        return null;
    }

    public function getDataKeyNames()
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
    * @return array
    */
    protected function getDefaultFormData()
    {
        if($this->isComplete()) {
            return $this->getData();
        }
        
        return $this->_getDefaultFormData();
    }
    
    protected function _getDefaultFormData()
    {
        return $this->getDefaultData();
    }
    
    protected function requireStepComplete()
    {
        if($this->isComplete()) {
            return;
        }
        
        throw new Application_Exception(
            'Operation not allowed: wizard step is not complete.',
            '',
            self::ERROR_STEP_MUST_BE_COMPLETE_FOR_OPERATION
        );
    }

    // ----------------------------------------------------------------
    // ADMIN SCREEN INTERFACE IMPLEMENTATION
    // ----------------------------------------------------------------

    //region: Admin screen interface methods

    public function getLogIdentifier(): string
    {
        return $this->wizard->getLogIdentifier().' | '.sprintf('Step [%s]', $this->getID());
    }

    public function getArea() : Application_Admin_Area
    {
        return $this->wizard->getArea();
    }
    
    public function getNavigationTitle()
    {
        return $this->getTitle();
    }
    
    public function getParentScreen() : ?Application_Admin_ScreenInterface
    {
        return $this->wizard;
    }
    
    public function handleActions() {}
    public function renderContent() : string { return ''; }
    public function getURLParam(): string { return ''; }
    public function handleBreadcrumb() {}
    public function getDefaultSubscreenID(): ?string { return null; }
    public function handleSidebar(UI_Page_Sidebar $sidebar) {}
    public function hasActiveSubscreen(): bool { return false; }
    public function handleTabs(UI_Bootstrap_Tabs $tabs) {}
    public function handleContextMenu(UI_Bootstrap_DropdownMenu $menu) {}
    public function handleSubnavigation(UI_Page_Navigation $subnav) {}
    public function isUserAllowed() {return true; }
    public function isArea(): bool { return false; }
    public function handleHelp(UI_Page_Help $help) {}

    public function getActiveSubscreenID(): ?string { return null; }
    public function getActiveSubscreen(): ?Application_Admin_ScreenInterface {return null;}
    public function hasSubscreen(string $id): bool { return false; }
    public function getSubscreenIDs(): array { return array(); }
    public function hasSubscreens(): bool { return false; }
    public function getSubscreenByID(string $id): Application_Admin_ScreenInterface 
    {
        throw new Application_Exception(
            'Wizard steps have no subscreens.',
            'Cannot get a subscreen by its ID, wizard steps have no subscreens.',
            self::ERROR_WIZARD_STEPS_HAVE_NO_SUBSCREENS
        );
    }

    // endregion
}
