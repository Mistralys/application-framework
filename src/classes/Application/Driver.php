<?php
/**
 * File containing the {@link Application_Driver} class.
 *
 * @see Application_Driver
 * @package Application
 */

use AppLocalize\Localization;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;

/**
 * Base class for the application "driver", which is where the
 * actual implementation of the application is done.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Driver implements Application_Driver_Interface
{
    public const ERROR_SETTING_VALUE_NOT_A_STRING = 333001;
    public const ERROR_INVALID_REVISIONABLE_TYPE = 333002;
    public const ERROR_MISSING_REVISIONABLE_METHOD = 333003;
    public const ERROR_NOT_A_REVISIONABLE = 333004;
    public const ERROR_APPLICATION_SET_DOES_NOT_EXIST = 333005;
    public const ERROR_DRIVER_ALREADY_PREPARED = 333006;
    public const ERROR_DRIVER_NOT_PREPARED = 333007;
    public const ERROR_DRIVER_ALREADY_STARTED = 333008;
    public const ERROR_CANNOT_GET_PAGEID_BEFORE_PREPARE = 333009;
    public const ERROR_USER_NOT_AUTHORIZED_FOR_ANY_AREA = 333010;
    public const ERROR_MAIN_NAVIGATION_NOT_CONFIGURED = 333011;
    public const ERROR_UNKNOWN_ADMINISTRATION_AREA = 333012;
    public const ERROR_UNSUPPORTED_ADMIN_AREA_CLASS = 333013;
    public const ERROR_CUSTOM_PROPERTY_OWNER_METHOD_MISSING = 333014;
    public const ERROR_CUSTOM_PROPERTY_OWNER_INVALID = 333015;
    public const ERROR_VERSION_METHOD_NOT_IMPLEMENTED = 333016;
    public const ERROR_CANNOT_LOAD_ADMIN_AREA_CLASS = 333017;
    public const ERROR_DEEPL_API_KEY_NOT_SET = 333018;
    public const ERROR_UNHANDLED_SCREEN_TYPE = 333019;
    public const ERROR_NO_ACTIVE_AREA_AVAILABLE = 333020;

    const SETTING_ROLE_PERSISTENT = 'persistent';
    const SETTING_ROLE_CACHE = 'cache';
    const SETTING_NAME_MAX_LENGTH = 80;

    const STORAGE_TYPE_DB = 'DB';
    const STORAGE_TYPE_FILE = 'File';
    const SETTING_USER_LAST_USED_VERSION = 'last_used_version';

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var UI_Page
     */
    protected $page;

    /**
     * @var Application_User
     */
    protected $user;

    /**
     * @var Application_Request
     */
    protected $request;

    /**
     * @var Application_Driver
     */
    protected static $instance;

    /**
     * @var UI
     */
    protected $ui;

    /**
     * @var Application_Driver_Storage
     */
    protected static $storage;

    /**
     * The available URL parameters and the corresponding admin
     * screen base classes.
     *
     * @var array
     */
    protected $screensChain = array
    (
        'page' => Application_Admin_Area::class,
        'mode' => Application_Admin_Area_Mode::class,
        'submode' => Application_Admin_Area_Mode_Submode::class,
        'action' => Application_Admin_Area_Mode_Submode_Action::class
    );

    /**
     * @var Application_OAuth
     */
    private $oauth;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->user = $app->getUser();
        $this->ui = $app->getUI();

        $requestClass = $this->getID() . '_Request';
        $this->request = new $requestClass($app);

        self::initStorage();
        self::$instance = $this;

        $this->registerEventHandlers();
        $this->registerRequestParameters();

        if (!defined('APP_APPSET'))
        {
            define('APP_APPSET', '__default');
        }

        $sets = $this->getApplicationSets();

        if (!$sets->idExists(APP_APPSET))
        {
            throw new Application_Exception(
                'The selected application set does not exist.',
                sprintf(
                    'The application set [%s] set using the [APP_APPSET] configuration setting does not exist.',
                    APP_APPSET
                ),
                self::ERROR_APPLICATION_SET_DOES_NOT_EXIST
            );
        }

        $this->appset = $sets->getByID(APP_APPSET);
    }

    protected static function initStorage() : void
    {
        if (isset(self::$storage))
        {
            return;
        }

        $storageClass = Application_Driver_Storage::class. '_' . self::getStorageType();
        $storage = new $storageClass();

        if($storage instanceof Application_Driver_Storage) {
            self::$storage = $storage;
            return;
        }

        throw new Application_Exception_UnexpectedInstanceType(
            Application_Driver_Storage::class,
            $storage
        );
    }

    public static function getStorageType() : string
    {
        if (Application::isDatabaseEnabled())
        {
            return self::STORAGE_TYPE_DB;
        }

        return self::STORAGE_TYPE_FILE;
    }

    /**
     * Retrieves the current application set of the application.
     * @return Application_Sets_Set
     */
    public function getAppSet()
    {
        return $this->appset;
    }

    /**
     * Registers all request parameters commonly used throughout the
     * application to add a first measure of validation. All data coming
     * from the request is treated with a good dose of paranoia anyway,
     * but this already allows filtering a fair bit without extra work.
     *
     * Overwrite this in your driver class as needed.
     */
    protected function registerRequestParameters()
    {
    }

    /**
     * Registers any event handlers for the application.
     * Overwrite this in your driver class as needed.
     *
     * Known application events:
     *
     * - FormCreated(UI_Form)
     *   Called after a new form has been created.
     *
     */
    protected function registerEventHandlers()
    {
    }

    /**
     * @return Application_User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return Application_Driver
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Retrieves the application object used by the driver.
     * @return Application
     */
    public function getApplication()
    {
        return $this->app;
    }

    /**
     * @return UI
     */
    public function getUI()
    {
        return $this->ui;
    }

    public function setPage(UI_Page $page)
    {
        $this->page = $page;
    }

    /**
     * Retrieves the page object for the page currently being rendered.
     * @return UI_Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Creates/returns the driver's session instance used
     * to handle session data.
     *
     * @return Application_Session
     */
    public static function getSession()
    {
        $className = APP_CLASS_NAME . '_Session';

        static $sessionObj = null;

        if ($sessionObj instanceof $className)
        {
            return $sessionObj;
        }

        Application::requireClass($className);

        $sessionObj = new $className();

        return $sessionObj;
    }

    /**
     * Retrieves the application's numeric version, e.g. "3.3.7".
     * If the version has a release name appended, it is stripped
     * off, e.g. "3.3.7-alpha" will return "3.3.7".
     *
     * @return string
     * @see Application_Driver::getExtendedVersion()
     */
    public function getVersion()
    {
        $driver = Application_Driver::getInstance();
        $version = trim($driver->getExtendedVersion());
        if (stristr($version, '-'))
        {
            $tokens = explode('-', $version);

            return array_shift($tokens);
        }

        return $version;
    }

    /**
     * Retrieves the full application version string, e.g. "3.3.7-SNAPSHOT".
     *
     * @return string
     * @see Application_Driver::getVersion()
     */
    public function getExtendedVersion()
    {
        throw new Application_Exception(
            'Version method not implemented',
            'The getExtendedVersion() method must be implemented in the driver class.',
            self::ERROR_VERSION_METHOD_NOT_IMPLEMENTED
        );
    }

    /**
     * Retrieves the application's minor version, e.g. "3.3.0"
     * The patch version (last part) is always zero.
     *
     * @return string
     */
    public function getMinorVersion()
    {
        $driver = Application_Driver::getInstance();
        $version = $driver->getExtendedVersion();
        $matches = array();
        if (preg_match('#([0-9]*)\.([0-9]*)\..*#', $version, $matches))
        {
            $version = $matches[1] . '.' . $matches[2] . '.0';
        }

        return $version;
    }

    /**
     * @return Application_Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Renders the specified template and returns the generated content.
     *
     * @param string $templateID
     * @return string
     * @see createTemplate()
     */
    public function renderTemplate($templateID, $vars = array())
    {
        return $this->app->renderTemplate($templateID, $vars);
    }

    /**
     * Renders a typical data grid page.
     *
     * @param string $pageTitle
     * @param UI_DataGrid $grid
     * @param array $entries
     * @return string
     */
    public function renderDatagrid($pageTitle, UI_DataGrid $grid, $entries, $withSidebar = true)
    {
        return $this->page->getRenderer()
            ->setWithSidebar($withSidebar)
            ->setTitle($pageTitle)
            ->appendDataGrid($grid, $entries)
            ->render();
    }

    /**
     * Renders a typical form.
     *
     * @param string $pageTitle
     * @param UI_Form $form
     * @param boolean $withSidebar
     * @return string
     */
    public function renderForm($pageTitle, UI_Form $form, $withSidebar = true)
    {
        return $this->createFormRenderer($form, $pageTitle)
            ->setWithSidebar($withSidebar);
    }

    public function createFormRenderer(UI_Form $form, $title)
    {
        return $this->page->getRenderer()
            ->appendForm($form)
            ->setTitle($title);
    }

    public function renderContentWithSidebar($content, $title = null)
    {
        return $this->page->getRenderer()
            ->setTitle($title)
            ->setContent($content)
            ->makeWithSidebar()
            ->render();
    }

    public function renderContentWithoutSidebar($content, $title = null)
    {
        return $this->page->getRenderer()
            ->setTitle($title)
            ->setContent($content)
            ->render();
    }

    /**
     * Renders a content section with the specified content and
     * optional title.
     *
     * @param string $content
     * @param string $title
     * @param string $abstract
     * @return string
     */
    public function renderSection($content, $title = null, $abstract = null)
    {
        return $this->createSection()
            ->setTitle($title)
            ->setAbstract($abstract)
            ->setContent($content)
            ->render();
    }

    /**
     * Creates and returns a content section helper class instance.
     * @return UI_Page_Section
     */
    public function createSection()
    {
        return $this->page->createSection();
    }

    /**
     * @param string $templateID
     * @return UI_Page_Template
     * @see renderTemplate()
     */
    public function createTemplate($templateID)
    {
        return $this->app->createTemplate($templateID);
    }

    /**
     * Redirects the user either to the specified URL if the parameter
     * is a string, or builds the target application URL from a list
     * of parameters is the parameter is an array.
     *
     * Examples:
     *
     * // redirect to an external URL or any absolute URL
     * redirectTo('http://www.disney.com');
     *
     * // redirect to an application internal URL by specifying parameters
     * redirectTo(array('page' => 'home'));
     *
     * @param array|string|NULL $paramsOrURL
     * @return never-returns
     * @throws Application_Exception
     */
    public function redirectTo($paramsOrURL = null)
    {
        if (is_array($paramsOrURL))
        {
            $url = $this->app->getRequest()->buildURL($paramsOrURL);
        }
        else
        {
            $url = $paramsOrURL;
            if (empty($url))
            {
                $url = APP_URL;
            }
        }

        $url = str_replace('&amp;', '&', $url);

        Application::redirect($url);
    }

    public function getPageURL(UI_Page $page, $params = array())
    {
        $params = array_merge($this->getPageParams($page), $params);

        return $this->app->getRequest()->buildURL($params);
    }

    /**
     * @var Application_AjaxHandler
     */
    protected $ajaxHandler;

    /**
     * @return Application_AjaxHandler
     */
    public function getAjaxHandler()
    {
        if (!isset($this->ajaxHandler))
        {
            Application::requireClass('Application_AjaxHandler');
            $this->ajaxHandler = new Application_AjaxHandler($this);
        }

        return $this->ajaxHandler;
    }

    /**
     * Indexed array with administration area objects.
     * @see createArea()
     * @var array
     */
    protected $areas = array();

    protected $areaIndex;

    /**
     * Creates an area instance. This area will not be in admin mode,
     * so no actions will be handled.
     *
     * @param string $id
     * @return Application_Admin_Area
     */
    public function createArea(string $id) : Application_Admin_Area
    {
        return $this->_createArea($id);
    }

    /**
     * Creates the area in admin mode, which enables handling
     * actions and rendering content.
     *
     * @param string $id
     * @return Application_Admin_Area
     */
    protected function createAdminArea(string $id) : Application_Admin_Area
    {
        return $this->_createArea($id, true);
    }

    /**
     * Creates an administration area and returns the created instance.
     * If an instance already exists, it will return that.
     *
     * @param string $id
     * @param boolean $adminMode Whether to run the screen as the active administration screen. Set this to false to just retrieve information about the screen.
     * @return Application_Admin_Area
     * @throws Application_Exception
     */
    protected function _createArea(string $id, bool $adminMode = false) : Application_Admin_Area
    {
        $this->buildAreasIndex();

        $lcID = strtolower($id);

        if (!isset($this->areaIndex[$lcID]))
        {
            throw new Application_Exception(
                'Unknown administration area',
                sprintf(
                    'The administration area [%s] does not exist. Available areas are: [%s].',
                    $id,
                    implode(', ', array_values($this->areaIndex))
                ),
                self::ERROR_UNKNOWN_ADMINISTRATION_AREA
            );
        }

        $areaName = $this->areaIndex[$lcID];

        $key = $areaName . AppUtils\ConvertHelper::bool2string($adminMode);

        if (isset($this->areas[$key]))
        {
            return $this->areas[$key];
        }

        $className = $this->getID() . '_Area_' . $areaName;
        $result = Application::requireClass($className, false);
        if ($result !== true)
        {
            throw new Application_Exception(
                'Cannot load administration area class',
                sprintf(
                    'The class file for administration area [%s] cannot be loaded. Reason given: [%s].',
                    $id,
                    $result
                ),
                self::ERROR_CANNOT_LOAD_ADMIN_AREA_CLASS
            );
        }

        $area = new $className($this, $adminMode);

        if (!$area instanceof Application_Admin_Area)
        {
            throw new Application_Exception(
                'Unsupported administration area',
                sprintf(
                    'The administration area [%1$s] is not of the expected type [%2$s].',
                    $id,
                    'Application_Admin_Area'
                ),
                self::ERROR_UNSUPPORTED_ADMIN_AREA_CLASS
            );
        }

        $this->areas[$key] = $area;

        return $this->areas[$key];
    }

    protected function buildAreasIndex() : void
    {
        if (isset($this->areaIndex))
        {
            return;
        }

        // create the lookup index for areas if it does not
        // exist yet: this allows us to specify an area in
        // a case insensitive way, as well as by its URL name
        // or actual name.
        $this->areaIndex = array();
        $areas = $this->getAdminAreas();

        foreach ($areas as $aid => $name)
        {
            if (!isset($this->areaIndex[$aid]))
            {
                $this->areaIndex[$aid] = $name;
            }

            $lcName = strtolower($name);
            if (!isset($this->areaIndex[$lcName]))
            {
                $this->areaIndex[$lcName] = $name;
            }
        }
    }

    protected $id;

    /**
     * Retrieves the driver's ID (its class name).
     *
     * @return string
     */
    public function getID()
    {
        if (!isset($this->id))
        {
            $this->id = get_class($this);
        }

        return $this->id;
    }

    static protected $tempFolder;

    /**
     * Retrieves the full path to the application's temporary files folder.
     * Creates the folder as needed if it does not exist.
     *
     * @return string
     * @throws Application_Exception
     */
    public static function getTempFolder()
    {
        return Application::getTempFolder();
    }

    /**
     * Retrieves the full path to a temporary file.
     *
     * @param string $name Leave empty to use an automatically generated name.
     * @param string $extension
     * @return string
     */
    public static function getTempFile(string $name = '', string $extension = 'tmp') : string
    {
        return Application::getTempFile($name, $extension);
    }

    /**
     * Retrieves a persistent application setting by its name.
     * @param string $name
     * @param string|NULL $default
     * @return string|NULL
     */
    public static function getSetting(string $name, $default = null)
    {
        $value = self::$storage->get($name);

        if ($value !== null)
        {
            return $value;
        }

        return $default;
    }

    /**
     * @param string $name
     * @param bool $default
     * @return bool
     *
     * @throws ConvertHelper_Exception
     * @see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
     */
    public static function getBoolSetting(string $name, bool $default=false) : bool
    {
        $value = self::getSetting($name);
        if($value !== null) {
            return ConvertHelper::string2bool($value);
        }

        return $default;
    }

    /**
     * Sets an application setting. Can optionally set the role: default
     * is adding a persistent setting, can be set as a cache setting
     * (which may occasionally be deleted).
     *
     * Note: only strings allowed, so serialize arrays and objects
     * beforehand as needed.
     *
     * @param string $name
     * @param string $value
     * @param string $role
     * @throws Application_Exception
     */
    public static function setSetting($name, $value, $role = self::SETTING_ROLE_PERSISTENT)
    {
        if (strlen($name) > self::SETTING_NAME_MAX_LENGTH)
        {
            throw new Application_Exception(
                'Setting name too long',
                sprintf(
                    'Tried setting the setting %1$s, but the name exceeds the maximum %2$s characters.',
                    $name,
                    self::SETTING_NAME_MAX_LENGTH
                )
            );
        }

        if (is_numeric($value))
        {
            $value = $value . '';
        }

        if (!is_string($value))
        {
            throw new Application_Exception(
                'Setting value is not a string',
                sprintf(
                    'Only string values are allowed for application settings, tried setting [%s]',
                    gettype($value)
                ),
                self::ERROR_SETTING_VALUE_NOT_A_STRING
            );
        }

        self::$storage->set($name, $value, $role);
    }

    /**
     * Deletes an application setting by its name. Has no
     * effect if the setting has already been deleted.
     *
     * @param string $name
     * @throws Application_Exception
     */
    public static function deleteSetting($name)
    {
        if (strlen($name) > self::SETTING_NAME_MAX_LENGTH)
        {
            throw new Application_Exception(
                'Setting name too long',
                sprintf(
                    'Tried deleting the setting %1$s, but the name exceeds the maximum %2$s characters.',
                    $name,
                    self::SETTING_NAME_MAX_LENGTH
                )
            );
        }

        self::$storage->delete($name);
    }

    /**
     * @param string $name
     * @param DateTime $date
     * @return bool
     */
    public static function setSettingExpiry(string $name, DateTime $date) : bool
    {
        self::$storage->setExpiry($name, $date);

        return false;
    }

    /**
     * Retrieves an application cache item.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getCache($name, $default = null)
    {
        return self::getSetting($name, $default);
    }

    /**
     * Sets an application cache item: this behaves like setting
     * an application setting, with the difference that these may
     * be periodically deleted.
     *
     * @param string $name
     * @param string $value
     */
    public function setCache(string $name, $value) : void
    {
        self::setSetting($name, $value, self::SETTING_ROLE_CACHE);
    }

    /**
     * Checks whether the application is currently in maintenance mode.
     *
     * @return boolean
     */
    public static function isMaintenanceMode()
    {
        $driver = Application_Driver::getInstance();
        return $driver->getMaintenance()->isEnabled();
    }

    /**
     * Adds an informational message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array $paramsOrURL Target URL or parameters for an internal page
     * @throws Application_Exception
     * @return never-returns
     */
    public function redirectWithInfoMessage($message, $paramsOrURL = null)
    {
        $this->ui->addInfoMessage($message);
        $this->redirectTo($paramsOrURL);
    }

    /**
     * Adds an error message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array $paramsOrURL Target URL or parameters for an internal page
     * @throws Application_Exception
     * @return never-returns
     */
    public function redirectWithErrorMessage($message, $paramsOrURL = null)
    {
        $this->ui->addErrorMessage($message);
        $this->redirectTo($paramsOrURL);
    }

    /**
     * Adds a success message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array $paramsOrURL Target URL or parameters for an internal page
     * @throws Application_Exception
     * @return never-returns
     */
    public function redirectWithSuccessMessage($message, $paramsOrURL = null)
    {
        $this->ui->addSuccessMessage($message);
        $this->redirectTo($paramsOrURL);
    }

    /**
     * Sets a cookie by name.
     *
     * @param string $name
     * @param string $value
     */
    public function setCookie($name, $value)
    {
        $cookieName = $this->getCookieName($name);
        $_COOKIE[$cookieName] = $value;
        if (!@setcookie($cookieName, $value, time() + 60 * 60 * 24 * 360, '/'))
        {
            Application::log('Could not write cookie ' . $cookieName);
        }
    }

    /**
     * Retrieves a cookie's value by name. Returns
     * the specified default value if not set.
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function getCookie($name, $default = null)
    {
        $cookieName = $this->getCookieName($name);
        if (isset($_COOKIE[$cookieName]))
        {
            return $_COOKIE[$cookieName];
        }

        return $default;
    }

    /**
     * Determines the name of the cookie to use
     * by prepending an identifier to the specified
     * name.
     *
     * @param string $name
     * @return string
     */
    protected function getCookieName($name)
    {
        return $this->getCookieNamespace() . '_' . $name;
    }

    /**
     * Must return a unique identifying string for cookies set
     * by the application.
     *
     * @return string
     */
    abstract protected function getCookieNamespace();

    /**
     * @var UI_Page_Sidebar
     */
    protected $sidebar;

    /**
     * @var Application_Sets_Set
     */
    protected $appset;

    /**
     * Keys in the array are the area URL names.
     * @var Application_Admin_Area[]
     */
    protected $enabledAreas;

    protected $prepared = false;

    /**
     * Execute pre-start tasks.
     */
    public function prepare()
    {
        if ($this->prepared)
        {
            throw new Application_Exception(
                'Driver has already been prepared',
                'The prepare() method has already been called, and may not be called multiple times.',
                self::ERROR_DRIVER_ALREADY_PREPARED
            );
        }

        $this->prepared = true;

        if (!Application::isUIEnabled())
        {
            return;
        }

        $areaIDs = array_keys($this->getAdminAreas());
        foreach ($areaIDs as $areaID)
        {
            $area = $this->createArea($areaID);
            if ($this->appset->isAreaEnabled($area))
            {
                $this->enabledAreas[$areaID] = $area;
            }
        }
    }

    /**
     * @var UI_Page_Navigation
     */
    protected $mainNav;

    protected $started = false;

    /**
     * (non-PHPdoc)
     * @see Application_Driver_Interface::start()
     */
    public function start()
    {
        if (!$this->prepared)
        {
            throw new Application_Exception(
                'Cannot start driver, not prepared.',
                'The driver start() method must be called after the prepare() method.',
                self::ERROR_DRIVER_NOT_PREPARED
            );
        }

        if ($this->started)
        {
            throw new Application_Exception(
                'Cannot start driver again',
                'The driver has already been started, and may not be started a second time.',
                self::ERROR_DRIVER_ALREADY_STARTED
            );
        }

        $this->started = true;

        $this->_start();

        if (!Application::isUIEnabled())
        {
            return;
        }

        if (self::isMaintenanceMode() && !$this->user->isDeveloper())
        {
            echo $this->renderMaintenanceScreen();
            Application::exit('Maintenance enabled');
        }

        Application::log('Starting driver.');

        // in developer mode, it is possible to turn off the lock manager
        if (isDevelMode())
        {
            if ($this->request->getBool('lockmanager_enable') === false)
            {
                Application_LockManager::disable();
            }
            else
            {
                Application_LockManager::enable();
            }
        }

        // determine the administration area we
        // need to work with, and let it handle its
        // actions before we do anything UI-related.
        $this->activeArea = $this->createAdminArea($this->getPageID());
        $this->activeArea->startUI();
        $this->activeArea->handleActions();

        Application_LockManager::start();

        $this->setUpUI();

        if (!$this->allowedToLogin())
        {
            $user = $this->getUser();

            $this->log(sprintf(
                'User [%s] is missing the [Login] right. User has the following rights: [%s].',
                $user->getID(),
                implode(', ', $user->getRights())
            ));

            Application::getSession()->logOut(
                Application_Session_Base::LOGOUT_REASON_LOGIN_NOT_ALLOWED
            );
        }

        if (!isset($this->mainNav))
        {
            throw new Application_Exception(
                'Main navigation not configured',
                'The main navigation instance is not present. This should be set up in the driver\'s [setUpUI] method.',
                self::ERROR_MAIN_NAVIGATION_NOT_CONFIGURED
            );
        }

        // Add the main navigation items. The admin areas
        // themselves know if the current user has the necessary
        // rights to view them.
        foreach ($this->enabledAreas as $area)
        {
            $area->addToNavigation($this->mainNav);
        }

        $this->activeArea->handleUI();

        return true;
    }

    /**
     * Can be extended in the driver class: it is called
     * right after all initializations have completed,
     * but before the UI layer starts. No admin areas
     * are available at this time.
     *
     * NOTE: This is called even if the UI layer is
     * turned off with the APP_RUN_MODE.
     */
    protected function _start()
    {

    }

    protected function renderMaintenanceScreen()
    {
        $this->page->selectFrame('maintenance');

        $maintenance = $this->getMaintenance();
        $plan = $maintenance->getActivePlan();

        return $this->page->renderTemplate(
            'maintenance',
            array(
                'plan' => $plan
            )
        );
    }

    /**
     * Retrieves the ID of the current page, or the default if
     * none is set specifically in the request.
     *
     * @return string
     * @throws Application_Exception
     *
     * @see Application_Driver::ERROR_CANNOT_GET_PAGEID_BEFORE_PREPARE
     * @see Application_Driver::ERROR_USER_NOT_AUTHORIZED_FOR_ANY_AREA
     */
    public function getPageID()
    {
        if (!$this->prepared)
        {
            throw new Application_Exception(
                'Driver has not been prepared yet.',
                'Cannot retrieve the page ID before the driver has been prepared.',
                self::ERROR_CANNOT_GET_PAGEID_BEFORE_PREPARE
            );
        }

        $default = $this->user->getSetting('startup_tab', $this->appset->getDefaultArea()->getURLName());

        $areaID = $this->request->getParam('page');
        if (empty($areaID) || !isset($this->enabledAreas[$areaID]))
        {
            $this->log(sprintf('Requested page [%s] | Empty or not enabled, using default [%s].', $areaID, $default));
            $areaID = $default;
        }

        // if the user can not view the area that is specified via
        // request, use the first one from the allowed ones.
        $area = $this->createArea($areaID);
        if (!$area->isUserAllowed())
        {
            $this->log(sprintf('User [%s] is not allowed to access area [%s]. Choosing one of those they are allowed for instead.', $this->user->getID(), $areaID));

            $areaID = $this->resolveDefaultArea()->getURLName();

            $this->log(sprintf('Using area [%s] instead.', $areaID));
        }

        $this->request->setParam('page', $areaID);
        return $areaID;
    }

    /**
     * @return Application_Admin_Area
     * @throws Application_Exception
     *
     * @see Application_Driver::ERROR_USER_NOT_AUTHORIZED_FOR_ANY_AREA
     */
    private function resolveDefaultArea() : Application_Admin_Area
    {
        $areas = $this->getAllowedAreas();
        if (!empty($areas))
        {
            $this->log(sprintf('Found [%s] areas that the user is allowed for.', count($areas)));
            return array_shift($areas);
        }

        $this->log(sprintf('The user is not allowed for any of the [%s] enabled areas.', count($this->enabledAreas)));

        $ids = array_keys($this->enabledAreas);

        throw new Application_Exception(
            'User is not authorized for any administration screens',
            sprintf(
                'The user [%s] (ID [%s]) with the rights [%s] is not authorized for any administration screen. Available screens: [%s].',
                $this->user->getName(),
                $this->user->getID(),
                implode(', ', $this->user->getRights()),
                implode(', ', $ids)
            ),
            self::ERROR_USER_NOT_AUTHORIZED_FOR_ANY_AREA
        );
    }

    protected function log($message)
    {
        Application::log(sprintf(
            'Driver [%s] | %s',
            $this->getID(),
            $message
        ));
    }

    /**
     * Retrieves all areas the current user is authorized to see.
     * @return Application_Admin_Area[]
     */
    public function getAllowedAreas()
    {
        $result = array();
        foreach ($this->enabledAreas as $area)
        {
            if ($area->isUserAllowed())
            {
                $result[] = $area;
            }
        }

        return $result;
    }

    /**
     * @return Application_Admin_Area[]
     */
    public function getAreas()
    {
        return $this->enabledAreas;
    }

    /**
     * Retrieves the application sets manager.
     * @return Application_Sets
     */
    public function getApplicationSets()
    {
        return $this->app->getSets();
    }

    /**
     * Must return an associative array with page name => administration class name
     * pairs to generate the main administration tree.
     */
    abstract public function getAdminAreas();

    abstract protected function setUpUI();

    /**
     * @var Application_Admin_Area
     */
    protected $activeArea;

    /**
     * Retrieves the instance of the currently active administration area.
     * @return Application_Admin_Area
     */
    public function getActiveArea() : Application_Admin_Area
    {
        if (!isset($this->activeArea))
        {
            throw new Application_Exception(
                'An active area is not available at this time.',
                '',
                self::ERROR_NO_ACTIVE_AREA_AVAILABLE
            );
        }

        return $this->activeArea;
    }

    /**
     * Retrieves the currently active administration screen instance.
     *
     * @return Application_Admin_ScreenInterface
     */
    public function getActiveScreen() : Application_Admin_ScreenInterface
    {
        $area = $this->getActiveArea();
        return $area->getActiveScreen();
    }

    /**
     * Shorthand for checking the user's "Login" right.
     * @return bool
     */
    public function allowedToLogin()
    {
        if (!Application::isAuthenticationEnabled())
        {
            return true;
        }

        return $this->getUser()->canLogin();
    }

    /**
     * (non-PHPdoc)
     * @see Application_Driver_Interface::renderContent()
     */
    public function renderContent()
    {
        if (!isset($this->activeArea))
        {
            $this->redirectWithInfoMessage(
                t('The requested page does not exist.')
            );
        }

        return $this->activeArea->renderContent();
    }

    abstract public function getAppName();

    abstract public function getAppNameShort();

    /**
     * @var boolean
     */
    protected $uiFrameworkConfigured = false;

    public function isUIFrameworkConfigured() : bool
    {
        return $this->uiFrameworkConfigured;
    }

    public function configureAdminUIFramework() : void
    {
        if ($this->uiFrameworkConfigured)
        {
            return;
        }

        $this->uiFrameworkConfigured = true;

        if (isset($this->page))
        {
            $this->sidebar = $this->page->getSidebar();
            $this->mainNav = $this->page->getHeader()->addNavigation('main');
        }

        // using md5 because the version itself is not enough to force the browser to reload it correctly
        $this->ui->setIncludesLoadKey(md5(self::getBuildNumber()));

        $theme = $this->getTheme();
        $theme->injectDependencies();

        $this->configureStyleIncludes();
        $this->configureScriptIncludes();
        $this->configureScripts();

        $lastVersion = $this->user->getSetting(self::SETTING_USER_LAST_USED_VERSION);
        $minorVersion = $this->getMinorVersion();

        // handle the what's new? Dialog: only if the user has used the
        // app before and his last used version does not fit the current one.
        if ($lastVersion != $minorVersion)
        {
            $this->ui->addInfoMessage(t(
                '%1$s has been updated to v%2$s. %3$s.',
                $this->getAppNameShort(),
                $this->getVersion(),
                "<a href=\"javascript:void(0);\" onclick=\"application.dialogWhatsnew('" . $lastVersion . "')\">" . t('See what\'s new') . "</a>"
            ));
        }

        $this->user->setSetting(self::SETTING_USER_LAST_USED_VERSION, $minorVersion);
        $this->user->saveSettings();
    }

    /**
     * Configures the clientside scripts and objects with the required data.
     */
    protected function configureScripts()
    {
        $this->ui->addJavascriptHeadVariable('FormHelper.ID_PREFIX', UI_Form::ID_PREFIX);
        $this->ui->addJavascriptHeadVariable('UI.BOOTSTRAP_VERSION', $this->ui->getBoostrapVersion());
        $this->ui->addJavascriptHeadVariable('Driver.version', $this->getVersion());

        $this->ui->addJavascriptHead('application.setUp()');
        $this->ui->addJavascriptOnload('application.start()');
        $this->ui->addJavascriptHeadVariable('application.locale', Localization::getAppLocale()->getName());
        $this->ui->addJavascriptHeadVariable('application.url', APP_URL);
        $this->ui->addJavascriptHeadVariable('application.host', parse_url(APP_URL, PHP_URL_HOST));
        $this->ui->addJavascriptHeadVariable('application.className', APP_CLASS_NAME);
        $this->ui->addJavascriptHeadVariable('application.deletionDelay', AppUtils\ConvertHelper::time2string(APP_AUTOMATIC_DELETION_DELAY));
        $this->ui->addJavascriptHeadVariable('application.appNameShort', $this->getAppNameShort());
        $this->ui->addJavascriptHeadVariable('application.environment', APP_ENVIRONMENT);
        $this->ui->addJavascriptHeadVariable('application.appName', $this->getAppName());
        $this->ui->addJavascriptHeadVariable('application.demoMode', Application::isDemoMode());
        $this->ui->addJavascriptHead('application.handle_JavaScriptError()');

        if (isset($this->activeArea))
        {
            if ($this->activeArea->getMode())
            {
                $this->ui->addJavascriptHeadVariable('application.mode', $this->activeArea->getMode()->getURLName());
            }

            if ($this->activeArea->getSubmode())
            {
                $this->ui->addJavascriptHeadVariable('application.submode', $this->activeArea->getSubmode()->getURLName());
            }
        }

        $this->ui->addJavascriptHeadVariable('User.id', $this->user->getID());
        $this->ui->addJavascriptHeadVariable('User.name', $this->user->getName());
        $this->ui->addJavascriptHeadVariable('User.firstname', $this->user->getFirstname());
        $this->ui->addJavascriptHeadVariable('User.lastname', $this->user->getLastname());

        $rights = $this->user->getRights();
        if (!empty($rights))
        {
            $this->ui->addJavascriptHead('User.addRights(' . json_encode($rights) . ')');
        }

        $contentLocales = Localization::getContentLocales();
        foreach ($contentLocales as $locale)
        {
            $this->ui->addJavascriptHeadStatement("application.addContentLocale", $locale->getName(), $locale->getLabel());
        }

        $activeContentLocale = Localization::getContentLocale();

        $this->ui->addJavascriptHeadStatement('application.selectLocale', $activeContentLocale->getName());

        $lastVersion = $this->user->getSetting(self::SETTING_USER_LAST_USED_VERSION);
        $this->ui->addJavascriptHeadVariable('User.last_used_version', $lastVersion);
    }

    /**
     * Adds all core stylesheets required for the interface.
     *
     * @see Application_Driver::configureAdminUIFramework()
     */
    protected function configureStyleIncludes()
    {
        $counter = 6000;

        $this->ui->addStylesheet('ui-core.css', 'all', $counter--);
        $this->ui->addStylesheet('ui-sections.css', 'all', $counter--);
        $this->ui->addStylesheet('ui-sidebar.css', 'all', $counter--);
        $this->ui->addStylesheet('ui-dialogs.css', 'all', $counter--);
        $this->ui->addStylesheet('ui-icons.css', 'all', $counter--);
        $this->ui->addStylesheet('ui-forms.css', 'all', $counter--);
        $this->ui->addStylesheet('ui/notepad.css', 'all', $counter--);
        $this->ui->addStylesheet('ui-print.css', 'print', $counter--);
        $this->ui->addStylesheet('driver.css', 'all', $counter--);
    }

    protected $coreScripts = array
    (
        // -----------------------------------------------------------
        // CORE SCRIPTS
        // -----------------------------------------------------------

        'class.js',
        'global_functions.js',
        'application.js',
        'application/exception.js',
        'application/base_renderable.js',
        'application/ajax.js',
        'sidebar.js',
        'user.js',
        'driver.js',

        // -----------------------------------------------------------
        // USER INTERFACE
        // -----------------------------------------------------------

        'ui.js',
        'ui/icon.js',
        'ui/label.js',
        'ui/text.js',
        'ui/button.js',
        'ui/buttongroup.js',
        'ui/link.js',
        'ui/section.js',
        'ui/menu.js',
        'ui/menu/item.js',
        'ui/menu/separator.js',
        'ui/menu/submenu.js',
        'ui/dropmenu.js',
        'ui/bigselection.js',
        'application/notepad.js',
        'application/notepad/note.js',

        // -----------------------------------------------------------
        // DIALOGS
        // -----------------------------------------------------------

        'dialog.js',
        'dialog/basic.js',
        'dialog/generic.js',
        'dialog/select_items.js',
        'dialog/confirmation.js',
        'dialog/tabbed.js',
        'dialog/tabbed/tab.js',
        'application/dialog/logging.js',
        'application/dialog/savecomments.js',

        // -----------------------------------------------------------
        // FORMS
        // -----------------------------------------------------------

        'forms.js',
        'forms/registry.js',
        'forms/registry/section.js',
        'forms/registry/element.js',
        'forms/radio.js',
        'forms/radiogroup.js',
        'forms/switch.js',
        'forms/form.js',
        'forms/form/element.js',
        'forms/form/element/text.js',
        'forms/form/element/textarea.js',
        'forms/form/element/hidden.js',
        'forms/form/element/select.js',
        'forms/form/element/switch.js',
        'forms/form/element/html.js',
        'forms/form/element/static.js',
        'forms/form/element/checkbox.js',
        'forms/form/header.js',
    );

    /**
     * Adds all javascript include files required for the interface.
     *
     * @see Application_Driver::configureAdminUIFramework()
     */
    protected function configureScriptIncludes()
    {
        $counter = 9000;

        // -----------------------------------------------------------
        // LOCALIZATION
        // -----------------------------------------------------------

        $locale = Localization::getAppLocale();

        $this->ui->addJavascript('localization/md5.min.js', $counter--);
        $this->ui->addJavascript('localization/translator.js', $counter--);

        // only add the language file if the selected locale is not the default one.
        if (!$locale->isNative())
        {
            $this->ui->addJavascript('localization/locale-' . $locale->getShortName() . '.js', $counter--);
        }

        // -----------------------------------------------------------
        // CORE SCRIPTS
        // -----------------------------------------------------------

        $this->ui->addVendorJavascript('medialize/uri.js', 'src/URI.min.js', $counter--);
        $this->ui->addVendorJavascript('ccampbell/mousetrap', 'mousetrap.min.js', $counter--);

        // Used to observe elements being resized. Used by the Notepad.
        // Search for `new ResizeSensor` to find occurrences.
        $this->ui->addVendorJavascript('marcj/css-element-queries', 'src/ResizeSensor.js', $counter--);
        $this->ui->addVendorJavascript('desandro/masonry', 'dist/masonry.pkgd.js');

        foreach ($this->coreScripts as $fileName)
        {
            $this->ui->addJavascript($fileName, $counter--);
        }
    }

    protected static $buildNumber;

    /**
     * Retrieves the build number of the current application installation.
     * This is now the same as the extended version of the application.
     * @return string
     */
    public static function getBuildNumber()
    {
        if (!isset(self::$buildNumber))
        {
            $driver = Application_Driver::getInstance();
            self::$buildNumber = trim($driver->getExtendedVersion());
        }

        return self::$buildNumber;
    }

    abstract public function getRevisionableTypes();

    /**
     * Retrieves the instance of a revisionable object for the
     * specified revisionable type and primary values. This is
     * used primarily by the changelog handling to access the
     * original revisionable instance of changelog entries.
     *
     * The driver has to implement a method for each available
     * revisionable type to retrieve the according instance.
     * It's up to the method to implement any validation required
     * regarding the primary key values. However, the return value
     * is validated automatically.
     *
     * Example for a revisionable of type <code>Page</code>:
     *
     * <pre>
     * // retrieve the revisionable
     * getRevisionable('Page', array('page_id' => 8));
     *
     * // internally calls the method:
     * getRevisionable_Page(array('page_id' => 8));
     * </pre>
     *
     * @param string $type
     * @param array $primary
     * @return Application_RevisionableStateless
     * @throws Application_Exception
     */
    public function getRevisionable($type, $primary)
    {
        $types = $this->getRevisionableTypes();
        if (!in_array($type, $types))
        {
            throw new Application_Exception(
                'Unknown revisionable type',
                sprintf(
                    'The revisionable type [%s] does not exist. Available types are: [%s].',
                    $type,
                    implode(', ', $types)
                ),
                self::ERROR_INVALID_REVISIONABLE_TYPE
            );
        }

        $method = 'getRevisionable_' . $type;
        if (!method_exists($this, $method))
        {
            throw new Application_Exception(
                'Missing revisionable support',
                sprintf(
                    'The revisionable type method [%s] does not exist.',
                    $method
                ),
                self::ERROR_MISSING_REVISIONABLE_METHOD
            );
        }

        $revisionable = $this->$method($primary);
        if (!$revisionable instanceof Application_RevisionableStateless)
        {
            throw new Application_Exception(
                'Not a revisionable',
                sprintf(
                    'The revisionable method [%s] did not return a valid revisionable object instance.',
                    $method
                ),
                self::ERROR_NOT_A_REVISIONABLE
            );
        }

        return $revisionable;
    }

    /**
     * Retrieves all available administration area instances.
     * @param boolean $includeCore Whether to include core areas, which cannot be disabled.
     * @return Application_Admin_Area[]
     */
    public function getAdminAreaObjects($includeCore = true)
    {
        $ids = array_keys($this->getAdminAreas());

        $adminAreas = array();
        foreach ($ids as $id)
        {
            $area = $this->createArea($id);
            if (!$includeCore && $area->isCore())
            {
                continue;
            }

            $adminAreas[] = $area;
        }

        return $adminAreas;
    }

    public function getCurrentURLPath() : string
    {
        return $this->getActiveScreen()->getURLPath();
    }

    /**
     * Retrieves the URL screen request parameter names
     * in the order from area > action.
     *
     * @return array
     */
    public function getURLParamNames() : array
    {
        return array_keys($this->screensChain);
    }

    /**
     * Determines the name of the URL parameter to use for the
     * screen, by checking which screen type it is an instance of.
     *
     * @param Application_Admin_ScreenInterface $screen
     * @return string
     * @throws Application_Exception
     */
    public function resolveURLParam(Application_Admin_ScreenInterface $screen) : string
    {
        foreach ($this->screensChain as $paramName => $class)
        {
            if ($screen instanceof $class)
            {
                return $paramName;
            }
        }

        throw new Application_Exception(
            'Unhandled admin screen type.',
            sprintf(
                'The screen [%s] is not an instance of any of the known classes: [%s].',
                get_class($screen),
                implode(', ', array_values($this->screensChain))
            ),
            self::ERROR_UNHANDLED_SCREEN_TYPE
        );
    }

    /**
     * Retrieves an administration screen instance by its path.
     *
     * @param string $path e.g. "products.edit.settings"
     * @return Application_Admin_ScreenInterface
     */
    public function getScreenByPath(string $path) : ?Application_Admin_ScreenInterface
    {
        $tokens = explode('.', $path);
        $screen = $this->createArea(array_shift($tokens));

        foreach ($tokens as $token)
        {
            if ($screen->hasSubscreen($token))
            {
                $screen = $screen->getSubscreenByID($token);
            }
            else
            {
                return null;
            }
        }

        return $screen;
    }

    /**
     * Checks whether the specified administration screen path exists.
     *
     * @param string $path
     * @return bool
     */
    public function screenPathExists(string $path) : bool
    {
        $tokens = explode('.', $path);

        $screen = $this->createArea(array_shift($tokens));

        foreach ($tokens as $token)
        {
            if ($screen->hasSubscreen($token))
            {
                $screen = $screen->getSubscreenByID($token);
            }
            else
            {
                return false;
            }
        }

        return true;
    }

    protected $cachedPropertyOwners = array();

    /**
     * Used for custom properties, to fetch the instance of the owner
     * of a property by the owner type and key. For each type, a matching
     * method has to be added.
     *
     * The method is named after this scheme:
     *
     * <code>resolveCustomPropertyOwner_OwnerType($ownerKey)</code>
     *
     * Where <code>OwnerType</code> is the type as returned by the
     * propertizable interface's <code>getPropertiesOwnerKey</code>
     * method.
     *
     * @param string $ownerType
     * @param string $ownerKey
     * @return Application_Interfaces_Propertizable|NULL If no matching record can be found, returns NULL.
     */
    public function resolveCustomPropertiesOwner($ownerType, $ownerKey)
    {
        $cacheKey = $ownerType . $ownerKey;
        if (isset($this->cachedPropertyOwners[$cacheKey]))
        {
            return $this->cachedPropertyOwners[$cacheKey];
        }

        $method = 'resolveCustomPropertyOwner_' . $ownerType;
        if (!method_exists($this, $method))
        {
            throw new Application_Exception(
                'Cannot resolve custom property owner',
                sprintf(
                    'The method [%s] required to resolve the owner instance of a custom property is missing. ' .
                    'Tried to fetch the owner key [%s].',
                    $method,
                    $ownerKey
                ),
                self::ERROR_CUSTOM_PROPERTY_OWNER_METHOD_MISSING
            );
        }

        $owner = $this->$method($ownerKey);
        if (empty($owner))
        {
            return null;
        }

        if ($owner instanceof Application_Interfaces_Propertizable)
        {
            $this->cachedPropertyOwners[$cacheKey] = $owner;
            return $owner;
        }

        throw new Application_Exception(
            'Invalid propertizable item',
            sprintf(
                'The property owner returned by method [%s] for owner key [%s] did not return an instance of a [%s] object.',
                $method,
                $ownerKey,
                'Application_Interfaces_Propertizable'
            ),
            self::ERROR_CUSTOM_PROPERTY_OWNER_INVALID
        );
    }

    /**
     * @var Application_Maintenance
     */
    protected $maintenance;

    /**
     * @return Application_Maintenance
     */
    public function getMaintenance()
    {
        if (!isset($this->maintenance))
        {
            $this->maintenance = new Application_Maintenance($this);
        }

        return $this->maintenance;
    }

    protected static $collections = array();

    /**
     * Creates an instance of a generic collection, like
     * a DBHelper collection, and returns it. Ensures that
     * only a singleton is returned every time.
     *
     * @param string $className
     * @param array $parameters Any parameters the collection may need to be instantiated
     * @return DBHelper_BaseCollection|Application_RevisionableCollection
     */
    protected static function createCollection($className, $parameters = array())
    {
        if (!isset(self::$collections[$className]))
        {
            self::$collections[$className] = new $className(...$parameters);
        }

        return self::$collections[$className];
    }

    /**
     * Returns the countries collection. Creates the object as needed.
     * @return Application_Countries
     */
    public static function createCountries() : Application_Countries
    {
        return ensureType(Application_Countries::class, self::createCollection('Application_Countries'));
    }

    /**
     * Creates a new instance of the API to access the information
     * from the WHATSNEW.xml file.
     *
     * @return Application_Whatsnew
     */
    public static function createWhatsnew() : Application_Whatsnew
    {
        return new Application_Whatsnew();
    }

    /**
     * @return UI_Themes_Theme
     */
    public function getTheme() : UI_Themes_Theme
    {
        return $this->ui->getTheme();
    }

    /**
     * Retrieves the absolute path to the folder in
     * which the driver's class files are stored.
     *
     * @return string
     */
    public function getClassesFolder() : string
    {
        return APP_ROOT . '/assets/classes/' . APP_CLASS_NAME;
    }

    public function getConfigFolder() : string
    {
        return APP_ROOT . '/config';
    }

    /**
     * @return Application_Users
     */
    public static function createUsers() : Application_Users
    {
        return ensureType(Application_Users::class, self::createCollection('Application_Users'));
    }

    /**
     * @var Application_DBDumps
     */
    protected static $dbdumps;

    /**
     * Creates a new instance of the database dumps manager, which
     * is used to create SQL dumps of the application's
     * database, as well access information on existing dumps.
     *
     * @return Application_DBDumps
     */
    public static function createDBDumps() : Application_DBDumps
    {
        if (!isset(self::$dbdumps))
        {
            self::$dbdumps = new Application_DBDumps(Application_Driver::getInstance());
        }

        return self::$dbdumps;
    }

    /**
     * Retrieves the absolute path to the folder in which
     * the database dumps are stored.
     *
     * @return string
     * @throws Application_Exception
     * @see Application_DBDumps::getStoragePath()
     */
    public function getDBDumpsPath() : string
    {
        return self::createDBDumps()->getStoragePath();
    }

    /**
     * Creates a new incremental DB dump in the global database
     * dumps folder of the application.
     *
     * Note: this is disabled on a windows host and will have
     * no effect.
     *
     * @return Application_DBDumps_Dump The dump instance that was created.
     * @throws Application_Exception
     * @see Application_DBDumps::createDump()
     */
    public function createIncrementalDBDump() : Application_DBDumps_Dump
    {
        return self::createDBDumps()->createDump();
    }

    /**
     * Parses the specified application request URL to access
     * information about it.
     *
     * @param string $url
     * @return Application_URL
     */
    public function parseURL(string $url) : Application_URL
    {
        return new Application_URL($url);
    }

    /**
     * Retrieves the filter criteria for the custom application settings.
     * These settings are not used by the application itself, but can be
     * used to store arbitrary data.
     *
     * @return Application_FilterCriteria_AppSettings
     */
    public static function createAppSettings()
    {
        return new Application_FilterCriteria_AppSettings();
    }

    /**
     *
     * @param Application_Countries_Country $fromCountry
     * @param Application_Countries_Country $toCountry
     * @return \DeeplXML\Translator
     * @throws Application_Exception
     */
    public static function createDeeplHelper(Application_Countries_Country $fromCountry, Application_Countries_Country $toCountry)
    {
        if (!defined('APP_DEEPL_API_KEY'))
        {
            throw new Application_Exception(
                'Missing DeepL API key',
                'The configuration setting [APP_DEEPL_API_KEY] is not defined.',
                self::ERROR_DEEPL_API_KEY_NOT_SET
            );
        }

        return new \DeeplXML\Translator(
            APP_DEEPL_API_KEY,
            $fromCountry->getLanguageCode(),
            $toCountry->getLanguageCode()
        );
    }

    /**
     * Retrieves information on all available administration
     * area classes, including all submodes, actions, etc.
     *
     * NOTE: Instantiates each of the admin classes, so this
     * is a memory hungry operation. It can be used in unit
     * tests to ensure all admin areas can be created.
     *
     * @return Application_Driver_AdminInfo
     */
    public function describeAdminAreas() : Application_Driver_AdminInfo
    {
        return new Application_Driver_AdminInfo();
    }

    /**
     * Creates/returns the driver's OAuth instance, which is
     * used to handle OAuth authentication.
     *
     * @return Application_OAuth
     */
    public function createOAuth() : Application_OAuth
    {
        if (!isset($this->oauth))
        {
            $this->oauth = new Application_OAuth($this);
        }

        return $this->oauth;
    }
}
