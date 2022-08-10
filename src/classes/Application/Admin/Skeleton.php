<?php
/**
 * File containing the {@see Application_Admin_Skeleton} class.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Admin_Skeleton
 */

use Application\Traits\Admin\ScreenAccessTrait;
use AppUtils\ConvertHelper;

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
abstract class Application_Admin_Skeleton
    extends Application_Formable
    implements
    Application_Admin_ScreenInterface,
    Application_Interfaces_Admin_LockableScreen
{
    use Application_Traits_Loggable;
    use ScreenAccessTrait;

    public const ERROR_NO_LOCKING_PRIMARY = 13001;
    public const ERROR_NO_LOCK_LABEL_METHOD_PRESENT = 13002;
    public const ERROR_NO_SUCH_CHILD_ADMIN_SCREEN = 13003;
    public const ERROR_LOCK_MANAGER_NOT_SET = 13004;

    public const LOCK_MODE_PRIMARYLESS = 'primaryless';
    public const LOCK_MODE_PRIMARYBASED = 'primarybased';

    protected Application_Driver $driver;
    protected Application_User $user;
    protected Application_Request $request;
    protected ?UI_Page $page = null;
    protected UI $ui;
    protected ?UI_Page_Breadcrumb $breadcrumb = null;
    protected Application_Session $session;
    protected ?Application_LockManager $lockManager = null;
    protected ?Application_Admin_ScreenInterface $parentScreen = null;
    protected static bool $simulationStarted = false;
    protected bool $outputToConsole = false;

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
    protected bool $adminMode = true;
    
    public function __construct(Application_Driver $driver, ?Application_Admin_ScreenInterface $parent=null)
    {
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

    public function getUser() : Application_User
    {
        return $this->user;
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
    * @return never
    */
    public function redirectWithSuccessMessage($message, $paramsOrURL) : void
    {
        /* TODO Redirect control needs review
        if(!$this->adminMode) {
            return;
        }
        */

        $this->simulationRedirect('Success', $message, $paramsOrURL);
        
        $this->driver->redirectWithSuccessMessage($message, $paramsOrURL);
    }

   /**
    * Adds an error message, and redirects to the target URL.
    *
    * @param string|number|UI_Renderable_Interface $message
    * @param array|string $paramsOrURL
    * @return never
    */
    public function redirectWithErrorMessage($message, $paramsOrURL) : void
    {
        /* TODO Redirect control needs review
        if(!$this->adminMode) {
            return;
        }
        */

        $this->simulationRedirect('Error', $message, $paramsOrURL);

        $this->driver->redirectWithErrorMessage($message, $paramsOrURL);
    }

   /**
    * Adds an informational message, and redirects to the target URL.
    *
    * @param string|number|UI_Renderable_Interface $message
    * @param array|string $paramsOrURL
    * @return never
    */
    public function redirectWithInfoMessage($message, $paramsOrURL) : void
    {
        /* TODO Redirect control needs review
        if(!$this->adminMode) {
            return;
        }
        */
        
        $this->simulationRedirect('Info', $message, $paramsOrURL);
        
        $this->driver->redirectWithInfoMessage($message, $paramsOrURL);
    }

    /**
     * @param string|array<string,string|int|float> $paramsOrURL
     * @return never
     * @throws Application_Exception
     */
    public function redirectTo($paramsOrURL) : void
    {
        /* TODO Redirect control needs review
        if(!$this->adminMode) {
            return;
        }
        */
        
        $this->simulationRedirect('', '(Simple redirect without message)', $paramsOrURL);
        
        $this->driver->redirectTo($paramsOrURL);
    }

    protected function renderTemplate($templateID, $vars = array())
    {
        return $this->getPage()->renderTemplate($templateID, $vars);
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
     * authorized to view (by copy+pasting a URL for example).
     *
     * @return string
     */
    protected function renderUnauthorized()
    {
        return $this->ui->createTemplate('content/unauthorized')->render();
    }

    protected function renderDatagrid($pageTitle, UI_DataGrid $grid, $entries = array(), $withSidebar = true)
    {
        $grid->configureForScreen($this);

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
    */
    protected function createSection() : UI_Page_Section
    {
        return $this->getUI()->createSection();
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
    public function getPageParams() : array
    {
        $vars = array();
        
        if ($this instanceof Application_Admin_Area) 
        {
            $vars[self::REQUEST_PARAM_PAGE] = $this->getURLName();
        } 
        else if ($this instanceof Application_Admin_Area_Mode) 
        {
            $vars[self::REQUEST_PARAM_PAGE] = $this->area->getURLName();
            $vars[self::REQUEST_PARAM_MODE] = $this->getURLName();
        } 
        else if ($this instanceof Application_Admin_Area_Mode_Submode) 
        {
            $vars[self::REQUEST_PARAM_PAGE] = $this->area->getURLName();
            $vars[self::REQUEST_PARAM_MODE] = $this->mode->getURLName();
            $vars[self::REQUEST_PARAM_SUBMODE] = $this->getURLName();
        } 
        else if ($this instanceof Application_Admin_Area_Mode_Submode_Action) 
        {
            $vars[self::REQUEST_PARAM_PAGE] = $this->area->getURLName();
            $vars[self::REQUEST_PARAM_MODE] = $this->mode->getURLName();
            $vars[self::REQUEST_PARAM_SUBMODE] = $this->submode->getURLName();
            $vars[self::REQUEST_PARAM_ACTION] = $this->getURLName();
        }
        
        return $vars;
    }

    /**
     * Retrieves a list of all available page request parameter names.
     *
     * @return string[]
     */
    public static function getPageParamNames() : array
    {
        return array(
            self::REQUEST_PARAM_PAGE,
            self::REQUEST_PARAM_MODE,
            self::REQUEST_PARAM_SUBMODE,
            self::REQUEST_PARAM_ACTION
        );
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
        return $this->requirePage()->createRevisionableTitle($revisionable);
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
    public function isSimulationEnabled() : bool
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

    public function requireLockManager() : Application_LockManager
    {
        $manager = $this->getLockManager();

        if($manager !== null)
        {
            return $manager;
        }

        throw new Application_Exception(
            'No lock manager available',
            '',
            self::ERROR_LOCK_MANAGER_NOT_SET
        );
    }
    
   /**
    * If the simulation mode is active, starts the simulation mode which
    * echos all application log messages. Includes a dump of the current 
    * request variables for debugging purposes. 
    *  
    * @return boolean
    * @param boolean $outputToConsole Whether to display the output in the developer console instead of echoing it. 
    * @see endSimulation()
    */
    protected function startSimulation(bool $outputToConsole=false) : bool
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
    protected function endSimulation() : void
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
    public function startTransaction() : void
    {
        if(!DBHelper::isTransactionStarted()) {
            DBHelper::startTransaction();
        }
    }

    /**
     * Check transaction is started or not.
     *
     * @return bool
     */
    public function isTransactionStarted() : bool
    {
        return DBHelper::isTransactionStarted();
    }
    
   /**
    * Ends a DB transaction safely, using current simulation settings.
    */
    public function endTransaction() : void
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

    /**
     * @param string $title
     * @param string|number|UI_Renderable_Interface $message
     * @return string
     */
    protected function renderInfoPage(string $title, $message) : string
    {
        if(!isset($this->page))
        {
            return '';
        }

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
    
    public function isAdminMode() : bool
    {
        return $this->adminMode;
    }

    /**
     * Sets an application setting specifically for this administration screen.
     *
     * @param string $name
     * @param string|int|float|bool|NULL $value
     * @return $this
     * @throws Application_Exception
     */
    protected function setSetting(string $name, $value) : self
    {
        Application_Driver::createSettings()->set($this->getSettingName($name), $value);
        return $this;
    }
    
   /**
    * Retrieves a setting previously stored for this administration screen.
    * 
    * @param string $name
    * @param string|NULL $default
    * @return string|NULL
    */
    protected function getSetting(string $name, ?string $default=null) : ?string
    {
        return Application_Driver::createSettings()->get($this->getSettingName($name), $default);
    }

    /**
     * Removes a setting stored for this administration screen. Has no
     * effect if the setting does not exist.
     *
     * @param string $name
     * @throws Application_Exception
     * @return $this
     */
    protected function removeSetting(string $name) : self
    {
        Application_Driver::createSettings()->delete($this->getSettingName($name));
        return $this;
    }

    /**
     * Sets a user setting specifically for this administration screen.
     *
     * @param string $name
     * @param string $value
     * @return $this
     * @throws Application_Exception
     */
    protected function setUserSetting(string $name, string $value) : self
    {
        $this->user->setSetting($this->getSettingName($name), $value);
        $this->user->saveSettings();
        return $this;
    }
    
   /**
    * Retrieves a setting previously stored for this user and this specific administration screen.
    *  
    * @param string $name
    * @param string $default
    * @return string
    */
    protected function getUserSetting(string $name, string $default='') : string
    {
        return $this->user->getSetting($this->getSettingName($name), $default);
    }

    /**
     * Removes a previously set user setting for this specific administration screen.
     * Has no effect if the setting does not exist.
     *
     * @param string $name
     * @return $this
     */
    protected function removeUserSetting(string $name) : self
    {
        $this->user->removeSetting($this->getSettingName($name));
        return $this;
    }
    
   /**
    * Retrieves the name under which the admin screen setting
    * is stored in the application settings.
    * 
    * @param string $name The name of the setting
    * @return string
    */
    protected function getSettingName(string $name) : string
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
