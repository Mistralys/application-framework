<?php
/**
 * File containing the {@link Application_Driver} class.
 *
 * @see Application_Driver
 * @package Application
 */

use Application\AppFactory;
use Application\Driver\DriverException;
use Application\WhatsNew;
use Application\Driver\DriverSettings;
use AppLocalize\Localization;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use Mistralys\VersionParser\VersionParser;
use UI\Page\Navigation\NavConfigurator;

/**
 * Base class for the application "driver", which is where the
 * actual implementation of the application is done.
 *
 * NOTE: The driver instance is created in the bootstrap:
 * {@see Application_Bootstrap_Screen::initDriver()}.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Driver implements Application_Driver_Interface
{
    use Application_Traits_Loggable;

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
    public const ERROR_CUSTOM_PROPERTY_OWNER_METHOD_MISSING = 333014;
    public const ERROR_CUSTOM_PROPERTY_OWNER_INVALID = 333015;
    public const ERROR_UNHANDLED_SCREEN_TYPE = 333019;
    public const ERROR_NO_ACTIVE_AREA_AVAILABLE = 333020;
    public const ERROR_DRIVER_INSTANCE_NOT_READY_YET = 333021;
    public const ERROR_CANNOT_START_SECOND_INSTANCE = 333022;

    public const SETTING_ROLE_PERSISTENT = 'persistent';
    public const SETTING_ROLE_CACHE = 'cache';
    public const SETTING_NAME_MAX_LENGTH = 80;
    public const SETTING_USER_LAST_USED_VERSION = 'last_used_version';

    public const STORAGE_TYPE_DB = 'DB';
    public const STORAGE_TYPE_FILE = 'File';

    protected Application $app;
    protected ?UI_Page $page = null;
    protected Application_User $user;
    protected Application_Request $request;
    protected static ?Application_Driver $instance = null;
    protected UI $ui;
    protected Application_Driver_Storage $storage;
    protected DriverSettings $settings;
    protected ?UI_Page_Navigation $mainNav = null;
    protected bool $started = false;
    protected ?Application_Admin_Area $activeArea = null;
    private ?Application_OAuth $oauth = null;

    /**
     * The available URL parameters and the corresponding admin
     * screen base classes.
     *
     * @var array<string,string>
     */
    protected array $screensChain = array(
        Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE => Application_Admin_Area::class,
        Application_Admin_ScreenInterface::REQUEST_PARAM_MODE => Application_Admin_Area_Mode::class,
        Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE => Application_Admin_Area_Mode_Submode::class,
        Application_Admin_ScreenInterface::REQUEST_PARAM_ACTION => Application_Admin_Area_Mode_Submode_Action::class
    );

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->user = $app->getUser();
        $this->ui = $app->getUI();

        $this->initInstance();
        $this->initRequest();
        $this->initStorage();
        $this->initSettings();
        $this->initAppSets();

        $this->registerEventHandlers();
        $this->registerRequestParameters();
    }

    private function initRequest() : void
    {
        $requestClass = ClassHelper::requireResolvedClass($this->getID() . '_Request');

        $this->request = ClassHelper::requireObjectInstanceOf(
            Application_Request::class,
            new $requestClass($this->getApplication())
        );
    }

    /**
     * @return void
     * @throws ClassHelper\ClassNotExistsException
     * @throws ClassHelper\ClassNotImplementsException
     */
    private function initStorage() : void
    {
        $storageClass = ClassHelper::requireResolvedClass(Application_Driver_Storage::class. '_' . self::getStorageType());

        $this->storage = ClassHelper::requireObjectInstanceOf(
            Application_Driver_Storage::class,
            new $storageClass()
        );
    }

    private function initInstance() : void
    {
        if(isset(self::$instance))
        {
            throw new DriverException(
                'Cannot start another driver instance.',
                '',
                self::ERROR_CANNOT_START_SECOND_INSTANCE
            );
        }

        self::$instance = $this;
    }

    private function initSettings() : void
    {
        $this->settings = new DriverSettings($this->storage);
    }

    private function initAppSets() : void
    {
        if (!defined('APP_APPSET'))
        {
            define('APP_APPSET', '__default');
        }

        $sets = AppFactory::createAppSets();

        if ($sets->idExists(APP_APPSET))
        {
            $this->appset = $sets->getByID(APP_APPSET);
            return;
        }

        throw new DriverException(
            'The selected application set does not exist.',
            sprintf(
                'The application set [%s] set using the [APP_APPSET] configuration setting does not exist.',
                APP_APPSET
            ),
            self::ERROR_APPLICATION_SET_DOES_NOT_EXIST
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
    protected function registerRequestParameters() : void
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
    protected function registerEventHandlers() : void
    {
    }

    public function getUser() : Application_User
    {
        return $this->user;
    }

    /**
     * @return Application_Driver
     * @throws DriverException
     */
    public static function getInstance() : Application_Driver
    {
        if(isset(self::$instance))
        {
            return self::$instance;
        }

        throw new DriverException(
            'Driver instance not ready yet',
            '',
            self::ERROR_DRIVER_INSTANCE_NOT_READY_YET
        );
    }

    /**
     * Retrieves the application object used by the driver.
     * @return Application
     */
    public function getApplication() : Application
    {
        return $this->app;
    }

    public function getUI() : UI
    {
        return $this->ui;
    }

    public function setPage(UI_Page $page) : void
    {
        $this->page = $page;
    }

    /**
     * Retrieves the page object for the page currently being rendered.
     * @return UI_Page|NULL
     */
    public function getPage() : ?UI_Page
    {
        return $this->page;
    }

    /**
     * Creates/returns the application's session instance used
     * to handle session data. It is an alias for the method
     * {@see Application::getSession()}.
     *
     * Ignore the fact that this is unused: It is in use in applications.
     *
     * @return Application_Session
     *
     * @throws DriverException
     * @throws Application_Exception
     */
    public static function getSession() : Application_Session
    {
        return Application::getSession();
    }

    /**
     * Retrieves the application's numeric version, e.g. "3.3.7".
     * If the version has a release name appended, it is stripped
     * off, e.g. "3.3.7-alpha" will return "3.3.7".
     *
     * @return string
     * @throws DriverException
     * @see Application_Driver::getExtendedVersion()
     */
    public function getVersion() : string
    {
        $driver = self::getInstance();
        $version = trim($driver->getExtendedVersion());

        if (strpos($version, '-') !== false)
        {
            $tokens = explode('-', $version);

            return array_shift($tokens);
        }

        return $version;
    }

    /**
     * Retrieves the application's minor version, e.g. "3.3.0"
     * The patch version (last part) is always zero.
     *
     * @return string
     */
    public function getMinorVersion() : string
    {
        return (string)VersionParser::create($this->getExtendedVersion())
            ->getMinorVersion();
    }

    public function getRequest() : Application_Request
    {
        return $this->request;
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
     * @param array<string,string|number>|string|NULL $paramsOrURL
     * @return never
     * @throws DriverException
     */
    public function redirectTo($paramsOrURL = null) : void
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

    /**
     * @param UI_Page $page
     * @param array<string,string|number> $params
     * @return string
     */
    public function getPageURL(UI_Page $page, array $params = array()) : string
    {
        $params = array_merge($this->getPageParams($page), $params);

        return $this->getRequest()->buildURL($params);
    }

    protected ?Application_AjaxHandler $ajaxHandler = null;

    /**
     * @return Application_AjaxHandler
     */
    public function getAjaxHandler() : Application_AjaxHandler
    {
        if (!isset($this->ajaxHandler))
        {
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
     *
     * @throws ClassHelper\ClassNotExistsException
     * @throws ClassHelper\ClassNotImplementsException
     * @throws ConvertHelper_Exception
     * @throws DriverException
     */
    protected function _createArea(string $id, bool $adminMode = false) : Application_Admin_Area
    {
        $this->buildAreasIndex();

        $lcID = strtolower($id);

        if (!isset($this->areaIndex[$lcID]))
        {
            throw new DriverException(
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

        $key = $areaName . ConvertHelper::bool2string($adminMode);

        if (isset($this->areas[$key]))
        {
            return $this->areas[$key];
        }

        $className = ClassHelper::requireResolvedClass(sprintf(
            '%s_Area_%s',
            APP_CLASS_NAME,
            $areaName
        ));

        $this->areas[$key] = ClassHelper::requireObjectInstanceOf(
            Application_Admin_Area::class,
            new $className($this, $adminMode)
        );

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
        // a case-insensitive way, as well as by its URL name
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

    /**
     * Retrieves the driver's ID (its class name).
     *
     * @return string
     */
    public function getID() : string
    {
        return APP_CLASS_NAME;
    }

    /**
     * Retrieves a persistent application setting by its name.
     * @param string $name
     * @param string|NULL $default
     * @return string|NULL
     * @deprecated Use the createSettings() API instead.
     */
    public static function getSetting(string $name, ?string $default = null) : ?string
    {
        return self::createSettings()->get($name, $default);
    }

    /**
     * Creates / gets the driver settings utility,
     * which is used to access and modify the global,
     * persistent application settings.
     *
     * @return DriverSettings
     * @throws DriverException
     */
    public static function createSettings() : DriverSettings
    {
        return self::getInstance()->getSettings();
    }

    public function getSettings() : DriverSettings
    {
        return $this->settings;
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
     * @param string|int|float|bool|NULL $value
     * @param string $role
     * @throws DriverException
     * @deprecated Use the createSettings() API instead.
     */
    public static function setSetting(string $name, $value, string $role = self::SETTING_ROLE_PERSISTENT) : void
    {
        self::createSettings()->set($name, $value, $role);
    }

    /**
     * Deletes an application setting by its name. Has no
     * effect if the setting has already been deleted.
     *
     * @param string $name
     * @throws DriverException
     * @deprecated Use the createSettings() API instead.
     */
    public static function deleteSetting(string $name) : void
    {
        self::createSettings()->delete($name);
    }

    /**
     * Retrieves an application cache item.
     *
     * @param string $name
     * @param string|NULL $default
     * @return string|NULL
     */
    public function getCache(string $name, ?string $default = null) : ?string
    {
        return self::createSettings()->get($name, $default);
    }

    /**
     * Sets an application cache item: this behaves like setting
     * an application setting, with the difference that these may
     * be periodically deleted.
     *
     * @param string $name
     * @param string|int|float|bool|NULL $value
     */
    public function setCache(string $name, $value) : void
    {
        self::createSettings()->set($name, $value, self::SETTING_ROLE_CACHE);
    }

    /**
     * Checks whether the application is currently in maintenance mode.
     *
     * @return boolean
     */
    public static function isMaintenanceMode() : bool
    {
        return AppFactory::createMaintenance()->isEnabled();
    }

    /**
     * Adds an informational message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array<string,string|number>|NULL $paramsOrURL Target URL or parameters for an internal page
     * @return never
     *
     * @throws DriverException
     * @throws UI_Exception
     */
    public function redirectWithInfoMessage($message, $paramsOrURL = null) : void
    {
        $this->getUI()->addInfoMessage($message);

        $this->redirectTo($paramsOrURL);
    }

    /**
     * Adds an error message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array<string,string|number>|NULL $paramsOrURL Target URL or parameters for an internal page
     * @return never
     *
     * @throws UI_Exception
     * @throws DriverException
     */
    public function redirectWithErrorMessage($message, $paramsOrURL = null) : void
    {
        $this->ui->addErrorMessage($message);
        $this->redirectTo($paramsOrURL);
    }

    /**
     * Adds a success message and redirects to the target URL.
     * The message is displayed on the target page.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @param string|array<string,string|number>|NULL $paramsOrURL Target URL or parameters for an internal page
     * @return never
     *
     * @throws UI_Exception
     * @throws DriverException
     */
    public function redirectWithSuccessMessage($message, $paramsOrURL = null) : void
    {
        $this->ui->addSuccessMessage($message);
        $this->redirectTo($paramsOrURL);
    }

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

    protected bool $prepared = false;

    /**
     * Execute pre-start tasks.
     */
    final public function prepare() : void
    {
        if ($this->prepared)
        {
            throw new DriverException(
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

    final public function start() : void
    {
        if (!$this->prepared)
        {
            throw new DriverException(
                'Cannot start driver, not prepared.',
                'The driver start() method must be called after the prepare() method.',
                self::ERROR_DRIVER_NOT_PREPARED
            );
        }

        if ($this->started)
        {
            throw new DriverException(
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
            echo AppFactory::createMaintenance()->renderScreen($this->getPage());
            Application::exit('Maintenance is enabled');
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

        $this->startMainNavigation();

        $this->activeArea->handleUI();
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
    protected function _start() : void
    {

    }

    protected function getMainNav() : UI_Page_Navigation
    {
        if (isset($this->mainNav))
        {
            return $this->mainNav;
        }

        throw new DriverException(
            'Main navigation not configured',
            'The main navigation instance is not present. This should be set up in the driver\'s [setUpUI] method.',
            self::ERROR_MAIN_NAVIGATION_NOT_CONFIGURED
        );
    }

    final protected function startMainNavigation() : void
    {
        $nav = $this->getMainNav();
        $configClass = ClassHelper::resolveClassName(APP_CLASS_NAME.'_UI_'. NavConfigurator::DRIVER_CONFIGURATOR_CLASS_NAME);

        // No specialized navigation configurator present?
        // We build the navigation automatically.
        if($configClass === null)
        {
            // Add the main navigation items. The admin areas
            // themselves know if the current user has the necessary
            // rights to view them.
            foreach ($this->enabledAreas as $area)
            {
                $area->addToNavigation($nav);
            }

            return;
        }

        ClassHelper::requireObjectInstanceOf(
            NavConfigurator::class,
            new $configClass($this, $nav)
        )
            ->configure();
    }

    /**
     * Retrieves the ID of the current page, or the default if
     * none is set specifically in the request.
     *
     * @return string
     * @throws DriverException
     *
     * @see Application_Driver::ERROR_CANNOT_GET_PAGEID_BEFORE_PREPARE
     * @see Application_Driver::ERROR_USER_NOT_AUTHORIZED_FOR_ANY_AREA
     */
    public function getPageID() : string
    {
        if (!$this->prepared)
        {
            throw new DriverException(
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
     * @throws DriverException
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

        throw new DriverException(
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

    public function getLogIdentifier() : string
    {
        return sprintf(
            'Driver [%s]',
            $this->getID()
        );
    }

    /**
     * Retrieves all areas the current user is authorized to see.
     * @return Application_Admin_Area[]
     */
    public function getAllowedAreas() : array
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
    public function getAreas() : array
    {
        return $this->enabledAreas;
    }

    /**
     * Retrieves the application sets manager.
     * @return Application_Sets
     * @deprecated Use the AppFactory instead.
     */
    public function getApplicationSets() : Application_Sets
    {
        return AppFactory::createAppSets();
    }

    abstract protected function setUpUI() : void;

    /**
     * Retrieves the instance of the currently active administration area.
     * @return Application_Admin_Area
     * @throws DriverException
     */
    public function getActiveArea() : Application_Admin_Area
    {
        if (!isset($this->activeArea))
        {
            throw new DriverException(
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
     * @throws DriverException
     */
    public function getActiveScreen() : Application_Admin_ScreenInterface
    {
        return $this->getActiveArea()->getActiveScreen();
    }

    /**
     * Shorthand for checking the user's "Login" right.
     * @return bool
     */
    public function allowedToLogin() : bool
    {
        if (!Application::isAuthenticationEnabled())
        {
            return true;
        }

        return $this->getUser()->canLogin();
    }

    public function renderContent() : string
    {
        if (!isset($this->activeArea))
        {
            $this->redirectWithInfoMessage(
                t('The requested page does not exist.')
            );
        }

        return $this->activeArea->renderContent();
    }

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
            $this->mainNav = $this->page->getHeader()->addMainNavigation();
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
        if ($lastVersion !== $minorVersion)
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
    protected function configureScripts() : void
    {
        $this->ui->addJavascriptHeadHeading('Application setup');

        $this->ui->addJavascriptHeadVariable('FormHelper.ID_PREFIX', UI_Form::ID_PREFIX);
        $this->ui->addJavascriptHeadVariable('UI.BOOTSTRAP_VERSION', UI::getBoostrapVersion());
        $this->ui->addJavascriptHeadVariable('Driver.version', $this->getVersion());

        $this->ui->addJavascriptHead('application.setUp()');
        $this->ui->addJavascriptOnload('application.start()');
        $this->ui->addJavascriptHeadVariable('application.locale', Localization::getAppLocale()->getName());
        $this->ui->addJavascriptHeadVariable('application.url', APP_URL);
        $this->ui->addJavascriptHeadVariable('application.host', parse_url(APP_URL, PHP_URL_HOST));
        $this->ui->addJavascriptHeadVariable('application.className', APP_CLASS_NAME);
        $this->ui->addJavascriptHeadVariable('application.deletionDelay', ConvertHelper::time2string(APP_AUTOMATIC_DELETION_DELAY));
        $this->ui->addJavascriptHeadVariable('application.appNameShort', $this->getAppNameShort());
        $this->ui->addJavascriptHeadVariable('application.environment', boot_constant('APP_ENVIRONMENT'));
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
    protected function configureStyleIncludes() : void
    {
        $counter = 6000;

        $this->ui->addStylesheet('ui-core.css', 'all', $counter--);
        $this->ui->addStylesheet('ui-colors.css', 'all', $counter--);
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
     * @throws DriverException
     */
    public function getRevisionable($type, $primary)
    {
        $types = $this->getRevisionableTypes();
        if (!in_array($type, $types))
        {
            throw new DriverException(
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
            throw new DriverException(
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
            throw new DriverException(
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
    public function getAdminAreaObjects(bool $includeCore = true) : array
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

    /**
     * Retrieves the URL screen request parameter names
     * in the order from area > action.
     *
     * @return string[]
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
     * @throws DriverException
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

        throw new DriverException(
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
    public function getScreenByPath(string $path, bool $adminMode=true) : ?Application_Admin_ScreenInterface
    {
        $tokens = explode('.', $path);
        $screen = $this->createArea(array_shift($tokens));

        foreach ($tokens as $token)
        {
            if ($screen->hasSubscreen($token))
            {
                $screen = $screen->getSubscreenByID($token, $adminMode);
            }
            else
            {
                return null;
            }
        }

        return $screen;
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
            throw new DriverException(
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

        throw new DriverException(
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
     * @return Application_Maintenance
     * @deprecated Use the AppFactory instead.
     */
    public static function createMaintenance() : Application_Maintenance
    {
        return AppFactory::createMaintenance();
    }

    /**
     * @var array<string,DBHelper_BaseCollection|Application_RevisionableCollection>
     */
    protected static array $collections = array();

    /**
     * Creates an instance of a generic collection, like
     * a DBHelper collection, and returns it. Ensures that
     * only a singleton is returned every time.
     *
     * @param string $className
     * @param array<mixed> $parameters Any parameters the collection may need to be instantiated
     * @return DBHelper_BaseCollection|Application_RevisionableCollection
     */
    protected static function createCollection(string $className, array $parameters = array())
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
     * @deprecated Use the AppFactory instead.
     */
    public static function createCountries() : Application_Countries
    {
        return AppFactory::createCountries();
    }

    /**
     * Creates a new instance of the API to access the information
     * from the WHATSNEW.xml file.
     *
     * @return WhatsNew
     * @deprecated Use the AppFactory instead.
     */
    public static function createWhatsnew() : WhatsNew
    {
        return AppFactory::createWhatsNew();
    }

    /**
     * @return UI_Themes_Theme
     */
    public function getTheme() : UI_Themes_Theme
    {
        return $this->getUI()->getTheme();
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
     * @deprecated Use the AppFactory instead.
     */
    public static function createUsers() : Application_Users
    {
        return AppFactory::createUsers();
    }

    /**
     * Creates a new instance of the database dumps manager, which
     * is used to create SQL dumps of the application's
     * database, as well access information on existing dumps.
     *
     * @return Application_DBDumps
     * @deprecated Use the AppFactory instead.
     */
    public static function createDBDumps() : Application_DBDumps
    {
        return AppFactory::createDBDumps();
    }

    /**
     * Retrieves the absolute path to the folder in which
     * the database dumps are stored.
     *
     * @return string
     * @see Application_DBDumps::getStoragePath()
     */
    public function getDBDumpsPath() : string
    {
        return AppFactory::createDBDumps()->getStoragePath();
    }

    /**
     * Creates a new incremental DB dump in the global database
     * dumps folder of the application.
     *
     * Note: this is disabled on a windows host and will have
     * no effect.
     *
     * @return Application_DBDumps_Dump The dump instance that was created.
     * @see Application_DBDumps::createDump()
     */
    public function createIncrementalDBDump() : Application_DBDumps_Dump
    {
        return AppFactory::createDBDumps()->createDump();
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
     * @throws OAuth_Exception
     */
    public function createOAuth() : Application_OAuth
    {
        if (!isset($this->oauth))
        {
            $this->oauth = new Application_OAuth($this);
        }

        return $this->oauth;
    }

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    public function getAdminURLChangelog(array $params=array()) : string
    {
        return $this->getRequest()
            ->buildURL($params, Application_Bootstrap_Screen_Changelog::DISPATCHER);
    }
}
