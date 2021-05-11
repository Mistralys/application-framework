<?php
/**
 * File containing the {@see Application_Admin_Skeleton} class.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_Skeleton
 */

/**
 * Base class for administration screens. This has all the 
 * common functionality that screens can use. 
 * 
 * NOTE: Other screen methods are available in the admin
 * screen trait, which is used for common methods that 
 * do not fit into the skeleton.
 * 
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Admin_Area
 * @see Application_Admin_Area_Mode
 * @see Application_Admin_Area_Mode_Submode
 * @see Application_Admin_Area_Mode_Submode_Action
 *
 * @see Application_Admin_ScreenInterface
 * @see Application_Traits_Admin_Screen
 */
abstract class Application_Admin_Skeleton extends Application_Formable implements Application_Admin_ScreenInterface, Application_Interfaces_Admin_LockableScreen
{
    use Application_Traits_Loggable;

    const ERROR_NO_LOCKING_PRIMARY = 13001;
    const ERROR_NO_LOCK_LABEL_METHOD_PRESENT = 13002;
    const ERROR_NO_SUCH_CHILD_ADMIN_SCREEN = 13003;
    const ERROR_CANNOT_RESOLVE_INCLUDE_PATH = 13004;
    
    /**
     * @var Application_Driver
     */
    protected $driver;

    /**
     * @var Application_User
     */
    protected $user;

    /**
     * @var Application_Request
     */
    protected $request;

    /**
     * @var UI_Page
     */
    protected $page;

    /**
     * @var UI
     */
    protected $ui;

    /**
     * @var UI_Page_Breadcrumb
     */
    protected $breadcrumb;

   /**
    * @var Application_Session
    */
    protected $session;
    
   /**
    * @var Application_LockManager
    */
    protected $lockManager;

    /**
     * @var UI_Page_Help
     */
    protected $help;
    
   /**
    * Whether to run the screen in admin mode: if this is
    * disabled, no actions will be run, and redirects are
    * ignored.
    * 
    * @var boolean
    */
    protected $adminMode = true;
    
   /**
    * @var string
    */
    protected $instanceID;
    
   /**
    * @var Application_Admin_ScreenInterface|NULL
    */
    protected $parentScreen;
    
    const LOCK_MODE_PRIMARYLESS = 'primaryless';
    
    const LOCK_MODE_PRIMARYBASED = 'primarybased';
    
    public function __construct(Application_Driver $driver, ?Application_Admin_ScreenInterface $parent=null)
    {
        $this->instanceID = nextJSID();
        $this->driver = $driver;
        $this->user = $this->driver->getUser();
        $this->request = $this->driver->getRequest();
        $this->session = Application::getSession();
        $this->parentScreen = $parent;
        
        if($this->adminMode) {
            $this->startUI();
            $this->startSimulation();
            $this->startLocking();
        }
    }
    
   /**
    * Checks whether this administration screen supports locking,
    * and if it does, initializes the locking mechanisms.
    * 
    * @throws Application_Exception
    */
    protected function startLocking()
    {
        if(!$this->isLockable() || !Application_LockManager::isEnabled() || !$this->adminMode) {
            return;
        }
        
        $this->lockManager = new Application_LockManager();
        $this->lockManager->bindScreen($this);
        
        Application::log('Locking enabled', true);
        
        $primary = '';
        if($this->getLockMode()== self::LOCK_MODE_PRIMARYBASED) 
        {
            $this->log('Using primary locking mode.');
            
            $primary = $this->getLockManagerPrimary();
            
            if(empty($primary)) 
            {
                throw new Application_Exception(
                    'No primary available for locking',
                    sprintf(
                        'The administration screen [%s] is set to use primary based locking, but no primary is specified.',
                        $this->getURLPath()
                    ),
                    self::ERROR_NO_LOCKING_PRIMARY
                );    
            }
            
            $this->lockManager->setPrimary($primary);
        }
        
        $this->log(sprintf('Administration screen: [%s]', $this->getURLPath()));
        $this->log(sprintf('Locking primary: [%s]', $this->lockManager->getPrimary()));
        
        // try to lock the screen. This will fail silently if another
        // user already locks the page - that's fine, since the page
        // will adjust to this automatically.
        $this->lockManager->lock();
    }
    
    public function getLockManagerPrimary()
    {
        return null;
    }
    
    public function getLockMode()
    {
        return self::LOCK_MODE_PRIMARYLESS;
    }
    
   /**
    * Retrieves the label of the record being locked in
    * this screen. By default this uses the {@link getLockManagerPrimary()}
    * method to retrieve the item of the type {@link Application_LockableRecord_Interface},
    * to use its label. Otherwise, it is expected to 
    * override this method to provide a label.
    * 
    * @throws Application_Exception
    * @return string
    */
    public function getLockLabel()
    {
        $primary = $this->getLockManagerPrimary();
        if($primary instanceof Application_LockableRecord_Interface) {
            return $primary->getLabel();
        }
        
        throw new Application_Exception(
            'No label method set',
            sprintf(
                'The class [%s] needs to override the [getLockLabel] method to use the locking, because the [getLockManagerPrimary] method does not return a lockable record instance.',
                get_class($this)
            ),
            self::ERROR_NO_LOCK_LABEL_METHOD_PRESENT
        );
    }
    
    protected $uiStarted = false;
    
   /**
    * @var UI_Themes_Theme_ContentRenderer
    */
    protected $renderer;
    
    public function startUI()
    {
        if($this->uiStarted) {
            return;
        }
        
        $this->uiStarted = true;
        $this->ui = UI::getInstance();
        $this->page = $this->driver->getPage();
        
        if(isset($this->page)) 
        {
            $this->renderer = $this->page->getRenderer();
            $this->breadcrumb = $this->page->getBreadcrumb();
        }
    }
    
    public function getRenderer() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer;
    }

    /**
     * @return Application_Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }
    
   /**
    * @return UI_Page_Breadcrumb
    */
    public function getBreadcrumb()
    {
        return $this->breadcrumb;
    }

   /**
    * 
    * @param string $redirectType
    * @param string|number|UI_Renderable_Interface $message
    * @param string|array $paramsOrURL
    */
    protected function simulationRedirect(string $redirectType, $message, $paramsOrURL) : void
    {
        if(!$this->isSimulationEnabled()) {
            return;
        }
        
        $url = $paramsOrURL;
        if(is_array($paramsOrURL)) {
            $url = $this->request->buildURL($paramsOrURL);
        }

        $sep = '&';
        if(!strstr($url, '?'))
        {
            $sep = '?';
        }

        $url .= $sep.'simulate_only=yes';

        Application::log('Redirect', true);
        Application::log('Memory usage: '.memory_get_usage(true));
        Application::log($redirectType . ' message: ' . toString($message));
        Application::log('Redirect to: <a href="' . $url . '">'.$url.'</a>');
        
        $this->endSimulation();
        
        Application::exit();
    }
    
   /**
    * Adds a success message, and redirects to the target URL.
    * 
    * @param string|number|UI_Renderable_Interface $message
    * @param array|string $paramsOrURL
    */
    public function redirectWithSuccessMessage($message, $paramsOrURL) : void
    {
        if(!$this->adminMode) {
            return;
        }
        
        $this->simulationRedirect('Success', $message, $paramsOrURL);
        
        $this->driver->redirectWithSuccessMessage($message, $paramsOrURL);
    }

   /**
    * Adds an error message, and redirects to the target URL.
    *
    * @param string|number|UI_Renderable_Interface $message
    * @param array|string $paramsOrURL
    */
    public function redirectWithErrorMessage($message, $paramsOrURL) : void
    {
        if(!$this->adminMode) {
            return;
        }
        
        $this->simulationRedirect('Error', $message, $paramsOrURL);
        
        $this->driver->redirectWithErrorMessage($message, $paramsOrURL);
    }

   /**
    * Adds an informational message, and redirects to the target URL.
    *
    * @param string|number|UI_Renderable_Interface $message
    * @param array|string $paramsOrURL
    */
    public function redirectWithInfoMessage($message, $paramsOrURL) : void
    {
        if(!$this->adminMode) {
            return;
        }
        
        $this->simulationRedirect('Info', $message, $paramsOrURL);
        
        $this->driver->redirectWithInfoMessage($message, $paramsOrURL);
    }

    /**
     * @param string|array<string,string|int|float> $paramsOrURL
     */
    public function redirectTo($paramsOrURL)
    {
        if(!$this->adminMode) {
            return;
        }
        
        $this->simulationRedirect('', '(Simple redirect without message)', $paramsOrURL);
        
        $this->driver->redirectTo($paramsOrURL);
    }

    protected function setCookie($name, $value)
    {
        $this->driver->setCookie($name, $value);
    }

    protected function getCookie($name, $default = null)
    {
        return $this->driver->getCookie($name, $default);
    }

    protected function renderTemplate($templateID, $vars = array())
    {
        return $this->driver->renderTemplate($templateID, $vars);
    }

    protected function renderInfoMessage($message, $options = array())
    {
        return $this->ui->getPage()->renderInfoMessage($message, $options);
    }

    protected function renderErrorMessage($message, $options = array())
    {
        return $this->ui->getPage()->renderErrorMessage($message, $options);
    }

    protected function renderSuccessMessage($message, $options = array())
    {
        return $this->ui->getPage()->renderSuccessMessage($message, $options);
    }

    /**
     * Renders the generic unauthorized information message that is
     * displayed if someone tries to access a page that he is not
     * authorized to view (by copy+pasting an URL for example).
     *
     * @return string
     * @template content.unauthorized
     */
    protected function renderUnauthorized()
    {
        return $this->ui->createTemplate('content/unauthorized')->render();
    }

    protected function renderDatagrid($pageTitle, UI_DataGrid $grid, $entries = array(), $withSidebar = true)
    {
        return $this->renderer
        ->setWithSidebar($withSidebar)
        ->setTitle($pageTitle)
        ->appendDataGrid($grid, $entries)
        ->render();
    }

    protected function renderForm($pageTitle, UI_Form $form, $withSidebar = true)
    {
        if($this->isLocked()) {
            $form->makeReadonly();
        }
        
        return $this->renderer
        ->appendForm($form)
        ->setTitle($pageTitle)
        ->setWithSidebar($withSidebar)
        ->render();
    }
    
    protected function createFormRenderer(UI_Form $form, $title=null)
    {
        if(empty($title)) {
            $title = $this->getTitle();
        }
        
        return $this->driver->createFormRenderer($form, $title);
    }

    public function renderTitleSubline($text)
    {
        return '<br/><small>' . $text . '</small>';
    }

    protected function renderContentWithSidebar($content, $title = null)
    {
        if(!empty($title)) {
            $this->renderer->setTitle($title);
        }
        
        return $this->renderer
        ->makeWithSidebar()
        ->setContent($content)
        ->render();
    }

    protected function renderContentWithoutSidebar($content, $title = null)
    {
        if(!empty($title)) {
            $this->renderer->setTitle($title);
        }
        
        return $this->renderer
        ->makeWithoutSidebar()
        ->setContent($content)
        ->render();
    }
    
    protected function renderSection($content, $title = null, $options=array())
    {
        return $this->driver->renderSection($content, $title, $options);
    }
    
   /**
    * Renders a styled error page containing one content section with details
    * about the error that occurred.
    * 
    * @param string $pageTitle
    * @param string $errorMessage
    * @param string $errorDetails
    * @param string $errorCode
    * @return string
    */
    protected function renderErrorPage($pageTitle, $errorMessage, $errorDetails, $errorCode=null)
    {
        $title = 
        UI::icon()->warning().' '.
        t('An error occurred:').' '.
        $errorMessage;
        
        if(!empty($errorCode)) {
            $title =
            '<span class="error-code">'.
                '#'.$errorCode.
            '</span>'.
            $title;
        }
        
        $section = $this->createSection()
            ->setContent($errorDetails) 
            ->setTitle($title)
            ->addClass('error');
        
        return $this->renderContentWithoutSidebar($section, $pageTitle);
    }
    
   /**
    * @return UI_Page_Section
    * @see Application_Driver::createSection()
    */
    protected function createSection()
    {
        return $this->driver->createSection();
    }

    /**
     * Creates a form, and automatically adds hidden variables for
     * the current administration screen so the form gets submitted
     * to the same page.
     *
     * NOTE: You still have to add hidden variables for IDs that may
     * be required, this only adds the mode / submode / action values.
     *
     * @param string $id
     * @param array $defaultData
     * @return UI_Form
     */
    protected function configureForm($id, $defaultData = array())
    {
        $form = $this->ui->createForm($id, $defaultData);
        $form->addHiddenVars($this->getPageParams());
        
        return $form;
    }
    
   /**
    * Retrieves the page request parameters specific to this
    * administration screen (mode / submode, etc...)
    * 
    * @return array<string,string>
    */
    public function getPageParams()
    {
        $vars = array();
        
        if ($this instanceof Application_Admin_Area) 
        {
            $vars['page'] = $this->getURLName();
        } 
        else if ($this instanceof Application_Admin_Area_Mode) 
        {
            $vars['page'] = $this->area->getURLName();
            $vars['mode'] = $this->getURLName();
        } 
        else if ($this instanceof Application_Admin_Area_Mode_Submode) 
        {
            $vars['page'] = $this->area->getURLName();
            $vars['mode'] = $this->mode->getURLName();
            $vars['submode'] = $this->getURLName();
        } 
        else if ($this instanceof Application_Admin_Area_Mode_Submode_Action) 
        {
            $vars['page'] = $this->area->getURLName();
            $vars['mode'] = $this->mode->getURLName();
            $vars['submode'] = $this->submode->getURLName();
            $vars['action'] = $this->getURLName();
        }
        
        return $vars;
    }

    
   /**
    * Adds a quick selection menu that is placed on the right hand side 
    * of the page, in the page title area. Note that this must still be
    * configured to be shown at all.
    * 
    * @return UI_QuickSelector
    */
    protected function addTitleQuickSelector() : UI_QuickSelector
    {
        $UIPage = $this->ui->getPage();
        
        if($UIPage->hasQuickSelector('title-right')) 
        {
            return $UIPage->getQuickSelector('title-right');
        }
        
        return $UIPage->addQuickSelector('title-right');
    }
    
   /**
    * Renders the title for a revisionable item, which includes
    * a badge for the item's current state.
    * 
    * @param Application_Revisionable $revisionable
    * @param string $subline
    */
    protected function renderRevisionableTitle(Application_Revisionable $revisionable, $subline='')
    {
        $title = $this->createRevisionableTitle($revisionable);
        $title->setSubline($subline);
        
        return $title->render();
    }
    
   /**
    * Creates a new instance of the revisionable title helper.
    * This allows for more customization than the {@link renderRevisionableTitle}
    * method, using the helper's API.
    * 
    * @param Application_RevisionableStateless $revisionable
    * @return UI_Page_RevisionableTitle
    */
    protected function createRevisionableTitle(Application_RevisionableStateless $revisionable)
    {
        return $this->page->createRevisionableTitle($revisionable);
    }
    
   /**
    * Forces to start the simulation mode even if the request parameter is not 
    * present or inactive.
    * 
    * @param boolean $outputToConsole Whether to display the output in the developer console instead of echoing it.
    */
    protected function forceStartSimulation($outputToConsole=false)
    {
        Application::setSimulation();
        return $this->startSimulation($outputToConsole);
    }

   /**
    * Checks whether simulation mode is active, which can be enabled by
    * setting the <code>simulate_only</code> request parameter to <code>yes</code>.
    * The use that is logged in additionally needs to be a developer for this
    * to work.
    * 
    * @return boolean
    */
    public function isSimulationEnabled()
    {
        return Application::isSimulation();
    }
    
   /**
    * Whether this administration screen can be locked using the lock manager.
    * @return boolean
    */
    public function isLockable() : bool
    {
        return false;
    }
    
    public function isLocked() : bool
    {
        if(isset($this->lockManager)) {
            return $this->lockManager->isLocked();
        }
        
        return false;
    }
    
   /**
    * @return Application_LockManager|NULL
    */
    public function getLockManager() : ?Application_LockManager
    {
        return $this->lockManager;
    }
    
    protected static $simulationStarted = false;
    
    protected $outputToConsole = false;
    
   /**
    * If the simulation mode is active, starts the simulation mode which
    * echos all application log messages. Includes a dump of the current 
    * request variables for debugging purposes. 
    *  
    * @return boolean
    * @param boolean $outputToConsole Whether to display the output in the developer console instead of echoing it. 
    * @see endSimulation()
    */
    protected function startSimulation($outputToConsole=false)
    {
        if(self::$simulationStarted || !$this->isSimulationEnabled()) {
            return false;
        }

        self::$simulationStarted = true;

        header('Content-Type:text/html; charset=UTF-8');
        Application::getLogger()->enableHTML()->logModeEcho();
        
        Application::logHeader('Simulation mode active');
        Application::log('Memory usage: '.memory_get_usage(true));
        Application::log('Request variables:');
        Application::logData($_REQUEST);
        Application::log('');
        return true;
    }
    
   /**
    * Ends the simulation output and exits the script, but only
    * if simulation is active.
    * 
    * @see startSimulation()
    */
    protected function endSimulation()
    {
        if(!$this->isSimulationEnabled()) {
            return;
        }
        
        Application::logHeader('All done.');
        Application::exit();
    }
    
   /**
    * Starts a DB transaction safely, using current simulation settings.
    */
    public function startTransaction()
    {
        if(!DBHelper::isTransactionStarted()) {
            DBHelper::startTransaction();
        }
    }
    
   /**
    * Ends a DB transaction safely, using current simulation settings.
    */
    public function endTransaction()
    {
        if(!DBHelper::isTransactionStarted()) {
            return;
        }
        
        if($this->isSimulationEnabled()) {
            DBHelper::rollbackTransaction(); 
            return;
        }
        
        DBHelper::commitTransaction();
    }
    
    protected function renderInfoPage($title, $message)
    {
        return $this->renderContentWithoutSidebar(
            $this->page->renderInfoMessage(
                UI::icon()->information().' '.$message,
                array('dismissable' => false)
            ),
            $title
        );
    }
    
    protected function initFormable(UI_Form $form, ?HTML_QuickForm2_Container $defaultContainer=null) : Application_Formable
    {
        $formable = parent::initFormable($form, $defaultContainer);
        
        $primary = $this->getLockManagerPrimary();
        
        if($primary instanceof Application_RevisionableStateless && !$primary->isEditable()) {
            $this->formableForm->makeReadonly();
        }
        
        return $formable;
    }
    
    public function isAdminMode()
    {
        return $this->adminMode;
    }
    
   /**
    * Retrieves the request-unique instance ID of the screen instance. 
    * @return string
    */
    public function getInstanceID()
    {
        return $this->instanceID;
    }

   /**
    * Sets an application setting specifically for this administration screen.
    *  
    * @param string $name
    * @param string $value
    * @return Application_Admin_Skeleton
    */
    protected function setSetting($name, $value)
    {
        $this->driver->setSetting($this->getSettingName($name), $value);
        return $this;
    }
    
   /**
    * Retrieves a setting previously stored for this administration screen.
    * 
    * @param string $name
    * @param mixed $default
    * @return mixed
    */
    protected function getSetting(string $name, $default=null)
    {
        return $this->driver->getSetting($this->getSettingName($name), $default);
    }
    
   /**
    * Removes a setting stored for this administration screen. Has no 
    * effect if the setting does not exist.
    * 
    * @param string $name
    */
    protected function removeSetting($name)
    {
        $this->driver->deleteSetting($this->getSettingName($name));
    }
    
   /**
    * Sets a user setting specifically for this administration screen.
    * 
    * @param string $name
    * @return Application_Admin_Skeleton
    */
    protected function setUserSetting($name, $value)
    {
        $this->user->setSetting($this->getSettingName($name), $value);
        $this->user->saveSettings();
        return $this;
    }
    
   /**
    * Retrieves a setting previously stored for this user and this specific administration screen.
    *  
    * @param string $name
    * @param string|NULL $default
    * @return string|NULL
    */
    protected function getUserSetting($name, $default=null)
    {
        return $this->user->getSetting($this->getSettingName($name), $default);
    }

   /**
    * Removes a previously set user setting for this specific administration screen.
    * Has no effect if the setting does not exist.
    *  
    * @param string $name
    */
    protected function removeUserSetting(string $name) : void
    {
        $this->user->removeSetting($this->getSettingName($name));
    }
    
   /**
    * Retrieves the name under which the admin screen setting
    * is stored in the application settings.
    * 
    * @param string $name The name of the setting
    * @return string
    */
    protected function getSettingName($name)
    {
        $path = 'Screen['.$this->getURLPath().']['.$name.']';
        return md5($path);
    }
    
   /**
    * For administration screens that support it: retrieves 
    * the help object instance and renders it.
    * 
    * @return string
    */
    public function renderHelp() : string
    {
        $help = null;
        $screen = $this; // too fool the IDE
        
        if($screen instanceof Application_Admin_Area) 
        {
            $help = $screen->getHelp();
        }
        else if($screen instanceof Application_Admin_Area_Mode) 
        {
            $help = $screen->getHelp();
        }
        else if($screen instanceof Application_Admin_Area_Mode_Submode)
        {
            $help = $screen->getHelp();
        }
        else if($screen instanceof Application_Admin_Area_Mode_Submode_Action)
        {
            $help = $screen->getHelp();
        }
        
        if($help) {
            return $help->render();
        }
        
        return '';
    }
    
   /**
    * Creates a data grid instance, configured with all
    * available page parameters needed for the screen 
    * (page, mode, submode and action).
    * 
    * @param string $id Optional grid ID, uses automatically generated one if empty.
    * @return UI_DataGrid
    */
    protected function configureDataGrid(string $id = '') : UI_DataGrid
    {
        $params = $this->getPageParams();
        
        if(empty($id)) 
        {
            $id = md5('DataGrid_'.serialize($params).get_class($this));
        }
        
        $grid = $this->ui->createDataGrid($id);

        $grid->addHiddenVars($params);
        
        return $grid;
    }
    
    public function getActiveScreen() : Application_Admin_ScreenInterface
    {
        return $this->driver->getActiveScreen();
    }
}
