<?php
/**
 * File containing the Application class.
 * @see Application
 * @subpackage Core
 * @package Application
 */

use Application\API\APIManager;
use Application\AppFactory;
use Application\ConfigSettings\AppConfig;
use Application\ConfigSettings\BaseConfigRegistry;
use Application\Environments;
use Application\DeploymentRegistry;
use Application\Driver\DriverException;
use Application\Exception\UnexpectedInstanceException;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper_Exception;
use UI\AdminURLs\AdminURLInterface;
use function AppUtils\parseVariable;

/**
 * Underlying structure for the application, dispatches rendering
 * tasks and acts as a hub for common tasks. Uses a so-called
 * driver class to do the grunt work of the application itself,
 * containing the functionality.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class Application
{
    public const ERROR_CANNOT_CREATE_TEMP_FOLDER = 1199543001;
    public const ERROR_CLASS_FILE_NOT_FOUND = 1199543002;
    public const ERROR_NO_USER_PRIOR_TO_SESSION = 1199543003;
    public const ERROR_CLASS_NOT_FOUND = 1199543004;
    public const ERROR_EMPTY_PAGE_ID = 1199543005;
    public const ERROR_APPLICATION_NOT_STARTED = 1199543006;
    public const ERROR_DRIVER_DID_NOT_GENERATE_CONTENT = 1199543007;
    public const ERROR_CANNOT_CREATE_STORAGE_FOLDER = 1199543008;
    public const ERROR_TRAIT_FILE_NOT_FOUND = 1199543009;
    public const ERROR_TRAIT_CLASS_NOT_FOUND = 1199543010;
    public const ERROR_CANNOT_SET_EXECUTION_TIME = 1199543011;
    public const ERROR_CANNOT_SET_MEMORY_LIMIT = 1199543012;
    public const ERROR_INVALID_RUN_MODE = 1199543013;
    public const ERROR_SCRIPT_RUN_MODE_NO_TYPE_SET = 1199543014;
    public const ERROR_NO_RUN_MODE_SET = 1199543015;
    public const ERROR_PHP_FILE_SYNTAX_ERRORS = 1199543016;
    public const ERROR_CALLBACK_NOT_CALLABLE = 1199543017;
    public const ERROR_SESSION_NOT_AVAILABLE_YET = 1199543018;
    public const ERROR_USER_CLASS_DOES_NOT_EXIST = 1199543019;
    public const ERROR_USER_DATA_NOT_FOUND = 1199543020;
    public const ERROR_INVALID_USER_CLASS = 1199543021;
    public const ERROR_REDIRECT_EVENTS_FAILED = 1199543022;

    public const RUN_MODE_UI = 'ui';
    public const RUN_MODE_SCRIPT = 'script';

    public const SCRIPT_TYPE_AJAX = 'ajax';
    public const SCRIPT_TYPE_EXPORT_AUTHENTICATED = 'export_authenticated';
    public const SCRIPT_TYPE_EXPORT_PUBLIC = 'export_public';
    public const SCRIPT_TYPE_MONITOR = 'monitor';

    public const USER_ID_SYSTEM = 1;
    public const USER_ID_DUMMY = 2;

    public const EVENT_DRIVER_INSTANTIATED = 'DriverInstantiated';
    public const EVENT_REDIRECT = 'Redirect';
    public const EVENT_SYSTEM_SHUTDOWN = 'SystemShutdown';

    public const REQUEST_VAR_SIMULATION = 'simulate_only';
    public const REQUEST_VAR_QUERY_SUMMARY = 'query_summary';
    public const STORAGE_FOLDER_NAME = 'storage';
    public const TEMP_FOLDER_NAME = 'temp';
    public const CACHE_FOLDER_NAME = 'cache';
    public const DEFAULT_TEST_FILE_EXTENSION = 'tmp';

    private UI $ui;
    private ?Application_Driver $driver = null;
    private ?Application_Request $request = null;
    private static ?Application_Session $session = null;
    private static int $counter = 0;
    private bool $started = false;
    private Application_Bootstrap_Screen $bootScreen;
    private ?UI_Page $page = null;
    private static bool $simulation = false;
    private static ?bool $develEnvironment = null;
    private static string $storageFolder = '';
    private static array $knownStorageFolders = array();
    private static ?Application_Messaging $messaging = null;
    private int $id;
    private static bool $exitEnabled = true;

    public function __construct(Application_Bootstrap_Screen $bootScreen)
    {
        self::$session = $bootScreen->getSession();
        self::$counter++;

        $this->bootScreen = $bootScreen;
        $this->id = self::$counter;
        $this->ui = UI::createInstance($this);
    }

    /**
     * Whether the application has been started and is active.
     * This determines whether methods like `getSession()` and
     * `getUser()` are available.
     *
     * @return bool
     */
    public static function isActive() : bool
    {
        return self::isSessionReady();
    }

    /**
     * @return Application_Feedback
     * @throws UnexpectedInstanceException
     * @throws DBHelper_Exception
     */
    public static function createFeedback() : Application_Feedback
    {
        $collection = DBHelper::createCollection(Application_Feedback::class);

        if($collection instanceof Application_Feedback)
        {
            return $collection;
        }

        throw new UnexpectedInstanceException(Application_Feedback::class, $collection);
    }

    /**
     * @return Application_Bootstrap_Screen
     */
    public function getBootScreen() : Application_Bootstrap_Screen
    {
        return $this->bootScreen;
    }

    /**
     * @return UI_Page
     *
     * @throws Application_Exception
     * @see Application::ERROR_APPLICATION_NOT_STARTED
     */
    public function getPage() : UI_Page
    {
        if(isset($this->page))
        {
            return $this->page;
        }

        throw $this->createNotStartedException();
    }

    public function getID() : int
    {
        return $this->id;
    }

    /**
     * Starts (configures) the application in preparation for
     * rendering the output.
     *
     * @param Application_Driver $driver
     * @return void
     *
     * @throws Application_EventHandler_Exception
     * @throws Application_Exception
     * @throws UI_Exception
     * @throws DriverException
     *
     * @event Application_Event ApplicationStarted
     */
    public function start(Application_Driver $driver) : void
    {
        self::log('Starting application.');

        $runMode = self::getRunMode();

        $this->started = true;
        $this->driver = $driver;
        $this->request = $driver->getRequest();

        // let any tasks run that have to be done once the
        // driver object is ready.
        if (Application_EventHandler::hasListener(self::EVENT_DRIVER_INSTANTIATED))
        {
            Application_EventHandler::trigger(
                self::EVENT_DRIVER_INSTANTIATED,
                array($this, $driver),
                Application_EventHandler_Event_DriverInstantiated::class
            );
        }

        // let the driver prepare the startup, namely determine which
        // administration areas are available. The driver's getPageID
        // method cannot be called before this is done.
        $this->driver->prepare();

        if ($runMode === self::RUN_MODE_UI)
        {
            $pageID = $driver->getPageID();
            if (empty($pageID))
            {
                throw new Application_Exception(
                    'Empty page ID',
                    'The driver [getPageID] method returned an empty page ID.',
                    self::ERROR_EMPTY_PAGE_ID
                );
            }

            self::log(sprintf('Determined active page to be [%s].', $pageID));

            $this->page = $this->ui->createPage($pageID);
            $this->ui->setPage($this->page);
            $this->driver->setPage($this->page);
        }
        else if ($runMode !== self::RUN_MODE_SCRIPT)
        {
            throw new Application_Exception(
                'Invalid application run mode',
                sprintf(
                    'The run mode [%s] is not a valid application run mode.',
                    $runMode
                ),
                self::ERROR_INVALID_RUN_MODE
            );
        }

        $this->driver->start();

        if (Application_EventHandler::hasListener('ApplicationStarted'))
        {
            Application_EventHandler::trigger(
                'ApplicationStarted',
                array($this, $driver),
                Application_EventHandler_Event_ApplicationStarted::class
            );
        }
    }

    /**
     * Retrieves the global instance of the logger, used to
     * log application messages and/or write them to disk.
     *
     * @return Application_Logger
     * @deprecated Use the AppFactory instead.
     */
    public static function getLogger() : Application_Logger
    {
        return AppFactory::createLogger();
    }

    /**
     * Logs a message, but only if the application is
     * in developer mode.
     *
     * @param string|NULL|array $message Arrays are automatically dumped.
     * @param bool $header Whether to display it styled as a header.
     */
    public static function log($message = null, bool $header = false) : Application_Logger
    {
        return AppFactory::createLogger()->log($message, $header);
    }

    public static function logSF(string $message, ?string $category=Application_Logger::CATEGORY_GENERAL, ...$args) : Application_Logger
    {
        return AppFactory::createLogger()->logSF($message, $category, ...$args);
    }

    public static function logEvent(string $eventName, string $message = '', ...$args) : Application_Logger
    {
        return AppFactory::createLogger()->logEvent($eventName, $message, ...$args);
    }

    /**
     * Logs a message styled as a header.
     *
     * @param string $message
     * @return Application_Logger
     */
    public static function logHeader(string $message) : Application_Logger
    {
        return AppFactory::createLogger()->logHeader($message);
    }

    /**
     * Logs a data array.
     *
     * @param array $data
     * @return Application_Logger
     */
    public static function logData(array $data) : Application_Logger
    {
        return AppFactory::createLogger()->logData($data);
    }

    public static function logError(string $message) : Application_Logger
    {
        return AppFactory::createLogger()->logError($message);
    }

    /**
     * @return UI
     */
    public function getUI() : UI
    {
        return $this->ui;
    }

    /**
     * @return UI_Themes_Theme
     */
    public function getTheme() : UI_Themes_Theme
    {
        return $this->getUI()->getTheme();
    }

    /**
     * @return Application_Driver
     *
     * @throws Application_Exception
     * @see Application::ERROR_APPLICATION_NOT_STARTED
     */
    public function getDriver() : Application_Driver
    {
        if(isset($this->driver))
        {
            return $this->driver;
        }

        throw $this->createNotStartedException();
    }

    /**
     * Gets the active session instance.
     *
     * NOTE: Will throw an exception if trying to
     * use this method before the session has been
     * initialized. Use the method {@see Application::isSessionReady()}
     * to check if it is available.
     *
     * @return Application_Session
     * @see Application::isSessionReady()
     *
     * @throws Application_Session_Exception
     * @see Application::ERROR_SESSION_NOT_AVAILABLE_YET
     */
    public static function getSession() : Application_Session
    {
        if (isset(self::$session))
        {
            return self::$session;
        }

        throw new Application_Session_Exception(
            'Session not available yet',
            'The session instance has not been created yet.',
            self::ERROR_SESSION_NOT_AVAILABLE_YET
        );
    }

    /**
     * @return Application_User
     * @throws Application_Session_Exception
     * @see Application::ERROR_NO_USER_PRIOR_TO_SESSION
     */
    public static function getUser() : Application_User
    {
        return self::getSession()->requireUser();
    }

    /**
     * Renders the current page and outputs the generated content.
     */
    public function display() : void
    {
        $page = $this->getPage();
        $content = $this->getDriver()->renderContent();

        if (empty($content))
        {
            throw new Application_Exception(
                'No content to display',
                'The driver did not generate any contents to display.',
                self::ERROR_DRIVER_DID_NOT_GENERATE_CONTENT
            );
        }

        $page->setContent($content);
        $page->display();
    }

    /**
     * Loads the specified template and returns its rendered content.
     *
     * @param string $templateID
     * @param array<string,mixed> $vars
     * @return string
     */
    public function renderTemplate(string $templateID, array $vars = array()) : string
    {
        $tpl = $this->createTemplate($templateID);
        $tpl->setVars($vars);

        return $tpl->render();
    }

    /**
     * @param string $templateID
     * @return UI_Page_Template
     */
    public function createTemplate(string $templateID) : UI_Page_Template
    {
        return $this->getPage()->createTemplate($templateID);
    }

    /**
     * @return Application_Request
     * @throws Application_Exception
     */
    public function getRequest() : Application_Request
    {
        if(isset($this->request))
        {
            return $this->request;
        }

        throw $this->createNotStartedException();
    }

    private function createNotStartedException() : Application_Exception
    {
        throw new Application_Exception(
            'Application has not been started yet',
            '',
            self::ERROR_APPLICATION_NOT_STARTED
        );
    }

    /**
     * Checks whether the current application environment is
     * a development environment or a production environment.
     * This can be used to conditionally switch features that
     * may not work in a development environment.
     *
     * @return boolean
     */
    public static function isDevelEnvironment() : bool
    {
        if (isset(self::$develEnvironment))
        {
            return self::$develEnvironment;
        }

        self::$develEnvironment = false;

        $env = Environments::getInstance()->getDetected();

        if($env !== null && $env->isDev())
        {
            self::$develEnvironment = true;
        }

        return self::$develEnvironment;
    }

    // 1: src/
    // 2: {packageName}/
    // 3: {vendorName}/
    // 4: vendor
    // 5: root
    //                                             1  2  3  4  5
    private const ROOT_PATH_DEPENDENCY = __DIR__.'/../../../../../';

    // 1: src/
    // 2: root
    //                                          1  2
    private const ROOT_PATH_PACKAGE = __DIR__.'/../../';

    private static ?bool $isInstalledAsDependency = null;

    /**
     * Checks whether the application is installed as a
     * Composer dependency in a `vendor` folder.
     *
     * @return bool
     */
    public static function isInstalledAsDependency() : bool
    {
        if(!isset(self::$isInstalledAsDependency)) {
            self::$isInstalledAsDependency = is_dir(self::ROOT_PATH_DEPENDENCY.'/vendor');
        }

        return self::$isInstalledAsDependency;
    }

    private static ?FolderInfo $rootFolder = null;

    /**
     * Automatically detects the framework's root folder
     * depending on whether it is installed as a dependency.
     *
     * > NOTE: This does not work for the test application,
     * > since it does not follow the usual folder structure.
     *
     * @return FolderInfo
     * @throws FileHelper_Exception
     */
    public static function detectRootFolder() : FolderInfo
    {
        if(isset(self::$rootFolder)) {
            return self::$rootFolder;
        }

        if(self::isInstalledAsDependency()) {
            $root = FolderInfo::factory(self::ROOT_PATH_DEPENDENCY);
        } else {
            $root = FolderInfo::factory(self::ROOT_PATH_PACKAGE);
        }

        self::$rootFolder = $root->requireExists();

        return self::$rootFolder;
    }

    /**
     * Returns the global instance of the API manager class,
     * creating it as needed.
     *
     * @return APIManager
     */
    public static function createAPI() : APIManager
    {
        return APIManager::getInstance();
    }

    /**
     * Retrieves the absolute path to the temporary folder
     * where all temporary files can be saved.
     *
     * @return string
     * @throws Application_Exception
     */
    public static function getTempFolder() : string
    {
        return self::getStorageSubfolderPath(self::TEMP_FOLDER_NAME);
    }

    public static function getTempFolderURL() : string
    {
        return self::getStorageSubfolderURL(self::TEMP_FOLDER_NAME);
    }

    public static function getCacheFolder() : string
    {
        return self::getStorageSubfolderPath(self::CACHE_FOLDER_NAME);
    }

    /**
     * Generates a temporary file path.
     *
     * > Note: the file is not created. This just creates a file path.
     *
     * @param string|NULL $name Specific name to use (without extension), or auto-generated if empty.
     * @param string|NULL $extension The extension to use, if empty {@see self::DEFAULT_TEST_FILE_EXTENSION} is used.
     */
    public static function getTempFile(?string $name = null, ?string $extension = self::DEFAULT_TEST_FILE_EXTENSION) : string
    {
        return self::getTempFolder() . '/' . self::resolveTempFileName($name, $extension);
    }

    public static function getTempFileURL(?string $name = null, ?string $extension = self::DEFAULT_TEST_FILE_EXTENSION) : string
    {
        return self::getTempFolderURL() . '/' . self::resolveTempFileName($name, $extension);
    }

    private static function resolveTempFileName(?string $name = null, ?string $extension = self::DEFAULT_TEST_FILE_EXTENSION) : string
    {
        if (empty($name)) {
            $name = md5('tmp' . microtime(true));
        }

        if(empty($extension)) {
            $extension = self::DEFAULT_TEST_FILE_EXTENSION;
        }

        return $name . '.' . $extension;
    }

    /**
     * Retrieves the absolute path to the application's storage
     * folder. Attempts to create it if it does not exist.
     *
     * @return string
     * @throws Application_Exception
     */
    public static function getStorageFolder() : string
    {
        if (self::$storageFolder !== '')
        {
            return self::$storageFolder;
        }

        self::$storageFolder = APP_ROOT . '/'. self::STORAGE_FOLDER_NAME;

        try
        {
            FileHelper::createFolder(self::$storageFolder);
        }
        catch (Exception $e)
        {
            throw new Application_Exception(
                'Storage folder does not exist and cannot be created.',
                sprintf(
                    'Tried creating folder [%s].',
                    self::$storageFolder
                ),
                self::ERROR_CANNOT_CREATE_STORAGE_FOLDER,
                $e
            );
        }

        return self::$storageFolder;
    }

    /**
     * Sets the base storage folder path used for all permanent
     * storage operations. Folders accessed via {@see self::getStorageSubfolderPath()}
     * will use this as parent path.
     *
     * @param string $path
     * @return void
     */
    public static function setStoragePath(string $path) : void
    {
        self::$storageFolder = $path;
    }

    /**
     * Retrieves the absolute path to a storage subfolder. Attempts
     * to create the folder if it does not exist.
     *
     * @param string $subfolderName
     * @return string
     * @throws Application_Exception
     */
    public static function getStorageSubfolderPath(string $subfolderName) : string
    {
        if (isset(self::$knownStorageFolders[$subfolderName]))
        {
            return self::$knownStorageFolders[$subfolderName];
        }

        $folder = self::getStorageFolder() . '/' . $subfolderName;

        try
        {
            FileHelper::createFolder($folder);
        }
        catch (FileHelper_Exception $e)
        {
            throw new Application_Exception(
                sprintf(
                    'Storage subfolder [%s] does not exist and cannot be created.',
                    $subfolderName
                ),
                sprintf(
                    'Tried creating folder [%s].',
                    $folder
                ),
                self::ERROR_CANNOT_CREATE_TEMP_FOLDER,
                $e
            );
        }

        self::$knownStorageFolders[$subfolderName] = $folder;

        return $folder;
    }

    public static function getStorageSubfolderURL(string $subfolderName) : string
    {
        return sprintf(
            '%s/%s/%s',
            APP_URL,
            self::STORAGE_FOLDER_NAME,
            $subfolderName
        );
    }

    /**
     * @return Application_Messagelogs
     * @deprecated Use the AppFactory instead.
     */
    public static function getMessageLog() : Application_Messagelogs
    {
        return AppFactory::createMessageLog();
    }

    /**
     * Creates the media manager instance, including the files as needed.
     * @return Application_Media
     * @deprecated Use the AppFactory instead.
     */
    public static function createMedia() : Application_Media
    {
        return AppFactory::createMedia();
    }

    /**
     * @return DeeplHelper
     * @deprecated Use the AppFactory instead.
     */
    public static function createDeeplHelper() : DeeplHelper
    {
        return AppFactory::createDeeplHelper();
    }

    /**
     * @return DeploymentRegistry
     * @deprecated Use the AppFactory instead.
     */
    public static function createDeploymentRegistry() : DeploymentRegistry
    {
        return AppFactory::createDeploymentRegistry();
    }

    /**
     * Creates the specified API connector and returns its instance.
     * The type is the filename of the connector minus the extension.
     *
     * @param string|class-string $typeOrClass
     * @return Connectors_Connector
     * @throws BaseClassHelperException
     */
    public static function createConnector(string $typeOrClass) : Connectors_Connector
    {
        return Connectors::createConnector($typeOrClass);
    }

    protected static ?bool $isDevUser = null;

    /**
     * Checks whether the application is in simulation mode, which
     * can be triggered either by setting the simulation_only request
     * parameter, or setting it explicitly with {@link setSimulation()}.
     *
     * @return boolean
     */
    public static function isSimulation() : bool
    {
        // Avoid checks if it has already been determined.
        // Also, important in case the simulation mode has
        // been set explicitly via setSimulation().
        if (self::$simulation === true)
        {
            return true;
        }

        // Only developer users may enable the simulation mode
        if (self::isSessionReady() && !self::isUserDev())
        {
            return false;
        }

        return
            isset($_REQUEST[self::REQUEST_VAR_SIMULATION])
            &&
            (
                $_REQUEST[self::REQUEST_VAR_SIMULATION] === 'yes'
                ||
                $_REQUEST[self::REQUEST_VAR_SIMULATION] === 'true'
            );
    }

    public static function isUserDev() : bool
    {
        // Use the session to get the user, as the application's
        // getUser() method triggers authentication.
        $user = self::getSession()->getUser();

        if($user !== null) {
            return $user->isDeveloper();
        }

        return false;
    }

    /**
     * Explicitly starts the simulation mode, in which all relevant queries or
     * operations are not committed.
     *
     * @param boolean $simulation
     */
    public static function setSimulation(bool $simulation = true) : void
    {
        self::$simulation = $simulation;

        // Simulate the parameter being present in the request
        // for methods that check the request.
        $_REQUEST[self::REQUEST_VAR_SIMULATION] = ConvertHelper::boolStrict2string($simulation, true);

        self::log(sprintf(
            'Application | Setting simulation mode to %1$s.',
            strtoupper($_REQUEST[self::REQUEST_VAR_SIMULATION])
        ));
    }

    /**
     * @return Application_Sets
     * @deprecated Use the AppFactory instead.
     */
    public function getSets() : Application_Sets
    {
        return Application_Sets::getInstance();
    }

    /**
     * Retrieves the global messaging management instance, which is
     * used to handle sending messages between users.
     *
     * @return Application_Messaging
     */
    public static function createMessaging() : Application_Messaging
    {
        if (!isset(self::$messaging))
        {
            self::$messaging = new Application_Messaging();
        }

        return self::$messaging;
    }

    /**
     * Retrieves the lookup items manager: this provides access to
     * all items that can be searched for using the lookup dialog.
     *
     * @return Application_LookupItems
     * @deprecated Use the AppFactory instead.
     */
    public static function createLookupItems() : Application_LookupItems
    {
        return AppFactory::createLookupItems();
    }

    /**
     * Creates/returns the instance of the application ratings,
     * which are used to handle user ratings of application screens.
     *
     * @return Application_Ratings
     * @deprecated Use the AppFactory instead.
     */
    public static function createRatings() : Application_Ratings
    {
        return AppFactory::createRatings();
    }

    /**
     * Attempts to set the PHP execution time limit. Throws an
     * exception if this fails.
     *
     * @param integer $seconds The limit to set. Use 0 for no limit.
     * @param string $operation Human-readable label of the operation that needs the time limit, shown in the exception.
     */
    public static function setTimeLimit(int $seconds, string $operation) : void
    {
        self::logSF(
            'ExecutionTime | Setting to [%s] seconds for operation [%s].',
            null,
            $seconds,
            $operation
        );

        set_time_limit($seconds);
    }

    /**
     * Attempts to set the PHP memory limit. Throws an exception
     * if this fails.
     *
     * @param integer $megabytes The amount of memory in megabytes. Set to -1 for no limit.
     * @param string $operation Human-readable label of the operation that needs the memory limit, shown in the exception.
     * @throws Application_Exception
     */
    public static function setMemoryLimit(int $megabytes, string $operation) : void
    {
        if ($megabytes === -1)
        {
            $value = '-1';
        }
        else
        {
            $value = $megabytes . 'M';
        }

        if (ini_set('memory_limit', $value) === false)
        {
            throw new Application_Exception(
                'Cannot change the memory limit.',
                sprintf(
                    'Tried changing the memory limit to [%s] for operation [%s].',
                    $megabytes,
                    $operation
                ),
                self::ERROR_CANNOT_SET_MEMORY_LIMIT
            );
        }
    }

    /**
     * Retrieves the path to the application's class files folder.
     * @return string
     */
    public function getClassesFolder() : string
    {
        return APP_INSTALL_FOLDER . '/classes';
    }

    public function getVendorFolder() : string
    {
        return APP_ROOT . '/vendor';
    }

    /**
     * @return Application_RequestLog
     * @deprecated Use the AppFactory instead.
     */
    public static function createRequestLog() : Application_RequestLog
    {
        return AppFactory::createRequestLog();
    }

    /**
     * @return Application_ErrorLog
     * @deprecated Use the AppFactory instead.
     */
    public static function createErrorLog() : Application_ErrorLog
    {
        return AppFactory::createErrorLog();
    }

    /**
     * Exit the application and handle shutdown tasks.
     * @return never
     */
    public static function exit(string $reason = '')
    {
        self::log(sprintf('Exiting application. Reason given: [%s].', $reason));

        Application_Bootstrap::handleShutDown();
        exit;
    }

    /**
     * Ensures that the specified callback is callable,
     * and throws an exception otherwise.
     *
     * @param mixed $callable
     * @throws Application_Exception
     */
    public static function requireCallableValid($callable, int $errorCode = 0) : void
    {
        if (is_callable($callable))
        {
            return;
        }

        if ($errorCode === 0)
        {
            $errorCode = self::ERROR_CALLBACK_NOT_CALLABLE;
        }

        throw new Application_Exception(
            'Invalid callback',
            sprintf(
                'The callback is not callable: [%s].',
                parseVariable($callable)->enableType()->toString()
            ),
            $errorCode
        );
    }

    public static function getRunMode() : string
    {
        return (string)boot_constant(BaseConfigRegistry::RUN_MODE);
    }

    public static function isUIEnabled() : bool
    {
        return self::getRunMode() === self::RUN_MODE_UI;
    }

    public static function isAuthenticationEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::NO_AUTHENTICATION) !== true;
    }

    public static function isSessionSimulated() : bool
    {
        return boot_constant(BaseConfigRegistry::SIMULATE_SESSION) === true;
    }

    public static function isDemoMode() : bool
    {
        return AppConfig::isDemoMode();
    }

    /**
     * Checks whether the database layer is enabled.
     *
     * @return bool
     * @see APP_DB_ENABLED
     */
    public static function isDatabaseEnabled() : bool
    {
        return boot_constant(BaseConfigRegistry::DB_ENABLED) === true;
    }

    public static function createLDAP() : Application_LDAP
    {
        $conf = (new Application_LDAP_Config(
            AppConfig::getLDAPHost(),
            AppConfig::getLDAPPort(),
            AppConfig::getLDAPDN(),
            AppConfig::getLDAPUsername(),
            AppConfig::getLDAPPassword()
        ))
            ->setMemberSuffix(AppConfig::getLDAPMemberSuffix())
            ->setSSLEnabled(AppConfig::isLDAPSSLEnabled());

        return new Application_LDAP($conf);
    }

    /**
     * Adds an event listener for the redirect event, which is
     * triggered every time a redirect is made to a target URL.
     *
     * @param callable $callback
     * @return Application_EventHandler_Listener
     */
    public static function addRedirectListener(callable $callback) : Application_EventHandler_Listener
    {
        return Application_EventHandler::addListener(self::EVENT_REDIRECT, $callback);
    }

    /**
     * @param string|AdminURLInterface $url
     * @return never
     *
     * @throws Application_Exception
     * @see Application::ERROR_REDIRECT_EVENTS_FAILED
     */
    public static function redirect($url)
    {
        $url = (string)$url;

        try
        {
            Application_EventHandler::trigger(
                self::EVENT_REDIRECT,
                array($url)
            );
        }
        catch (Application_Exception $e)
        {
            throw new Application_Exception(
                'Error while running redirect event handling.',
            'Tried running the Redirect event, but an exception occurred.',
                self::ERROR_REDIRECT_EVENTS_FAILED,
                $e
            );
        }

        $message = sprintf('Redirected to [%s]', $url);

        if(isCLI()) {
            self::exit($message);
        }

        $simulation = self::isSimulation();

        if (!$simulation && !headers_sent())
        {
            header('Location:' . $url);
            self::exit($message);
        }

        ?>
        <style>
            .redirect-link {
                font-family: Arial, sans-serif;
                font-size: 12pt;
                color: #444;
                background: #fff;
                padding: 14px 20px;
            }

            .redirect-link A {
                color: #0B2A63;
            }
        </style>
        <div class="redirect-link">
            <p>
                <a href="<?php echo $url ?>">
                    <?php pt('Please click here to continue...') ?>
                </a>
            </p>
            <p>
                Target: <code><?php echo $url ?></code>
            </p>
        </div>
        <?php

        if ($simulation)
        {
            AppFactory::createLogger()->printLog(true);
        }

        self::exit(sprintf('Redirected to [%s]', $url));
    }

    public static function getUserClass() : string
    {
        $userClass = APP_CLASS_NAME . '_User';

        if (class_exists($userClass))
        {
            return $userClass;
        }

        throw new Application_Exception(
            'User class does not exist.',
            sprintf(
                'The expected class [%s] could not be found.',
                $userClass
            ),
            self::ERROR_USER_CLASS_DOES_NOT_EXIST
        );
    }

    /**
     * @var array<int,Application_User>
     */
    private static $knownUsers = array();

    /**
     * Creates a user instance for the specified ID.
     *
     * @param int $userID
     * @return Application_User
     * @throws Application_Exception
     *
     * @see Application::ERROR_USER_CLASS_DOES_NOT_EXIST
     * @see Application::ERROR_USER_DATA_NOT_FOUND
     * @see Application::ERROR_INVALID_USER_CLASS
     */
    public static function createUser(int $userID) : Application_User
    {
        if (isset(self::$knownUsers[$userID]))
        {
            return self::$knownUsers[$userID];
        }

        $userClass = self::getUserClass();

        $user = new $userClass($userID, self::getUserData($userID));

        if ($user instanceof Application_User)
        {
            return $user;
        }

        throw new Application_Exception(
            'Invalid user class',
            sprintf(
                'The user class [%s] does not extend the [%s] class.',
                $userClass,
                Application_User::class
            ),
            self::ERROR_INVALID_USER_CLASS
        );
    }

    /**
     * Retrieves the user(Application_User) from the database for the
     * specified user foreign ID.
     *
     * @param string $foreignID
     * @return Application_User
     * @throws Application_Exception
     */
    public static function getUserByForeignID(string $foreignID) : Application_User
    {
        $userID = self::getUserIDByForeignID($foreignID);
        if ($userID !== null)
        {
            return self::createUser($userID);
        }

        throw new Application_Exception(
            'Cannot find user data',
            sprintf(
                'Tried loading data for user foreign id [%s], but it does not exist in the database.',
                $foreignID
            ),
            self::ERROR_USER_DATA_NOT_FOUND
        );
    }

    /**
     * Retrieves the user ID from the database for the
     * specified user foreign ID.
     *
     * @param string $foreignID
     * @return int|null
     */
    public static function getUserIDByForeignID(string $foreignID) : ?int
    {
        $result = DBHelper::fetchKeyInt(
            'user_id',
            "SELECT
                user_id
            FROM
                known_users
            WHERE
                foreign_id=:foreign_id",
            array(
                'foreign_id' => $foreignID
            )
        );

        if ($result !== 0)
        {
            return $result;
        }

        return null;
    }

    /**
     * Checks whether the specified user foreign ID exists
     * in the database.
     *
     * @param string $foreignID
     * @return bool
     */
    public static function userForeignIDExists(string $foreignID) : bool
    {
        return self::getUserIDByForeignID($foreignID) !== null;
    }

    public static function createSystemUser() : Application_User
    {
        return self::createUser(self::USER_ID_SYSTEM);
    }

    public static function createDummyUser() : Application_User
    {
        return self::createUser(self::USER_ID_DUMMY);
    }

    /**
     * Checks whether the specified user ID exists
     * in the database.
     *
     * @param int $userID
     * @return bool
     */
    public static function userIDExists(int $userID) : bool
    {
        if (isset(self::$knownUsers[$userID]))
        {
            return true;
        }

        return AppFactory::createUsers()->idExists($userID);
    }

    /**
     * Retrieves the data set from the database for the
     * specified user ID. Also handles stub and system
     * user data.
     *
     * @param int $userID
     * @return array<string,string>
     * @throws Application_Exception
     *
     * @see Application::ERROR_USER_DATA_NOT_FOUND
     */
    private static function getUserData(int $userID) : array
    {
        if ($userID === self::USER_ID_SYSTEM)
        {
            return self::getSystemUserData();
        }

        if ($userID === self::USER_ID_DUMMY)
        {
            return self::getDummyUserData();
        }

        try
        {
            $data = DBHelper::fetch(
                'SELECT
                *
                FROM
                    known_users
                WHERE
                    user_id=:user_id',

                array(
                    ':user_id' => $userID
                )
            );

            if (!empty($data))
            {
                return $data;
            }
        }
        catch (DBHelper_Exception $e)
        {
        }

        throw new Application_Exception(
            'Cannot find user data',
            sprintf(
                'Tried loading data for user [%s], but it does not exist in the database.',
                $userID
            ),
            self::ERROR_USER_DATA_NOT_FOUND
        );
    }

    public static function getDummyUserData() : array
    {
        return array(
            'email' => APP_DUMMY_EMAIL,
            'firstname' => 'Dummy',
            'lastname' => 'User',
            'foreign_id' => '__dummy'
        );
    }

    public static function getSystemUserData() : array
    {
        return array(
            'email' => APP_SYSTEM_EMAIL,
            'firstname' => APP_SYSTEM_NAME,
            'lastname' => 'Application',
            'foreign_id' => '__system'
        );
    }

    public function isStarted() : bool
    {
        return $this->started;
    }

    public static function isSessionReady() : bool
    {
        return isset(self::$session) && self::$session->isStarted();
    }

    public static function isUserReady() : bool
    {
        return self::isSessionReady() && self::getSession()->getUser() !== null;
    }

    public static function isSystemUserID(int $userID) : bool
    {
        return in_array($userID, self::getSystemUserIDs());
    }

    public static function getSystemUserIDs() : array
    {
        return array(
            self::USER_ID_SYSTEM,
            self::USER_ID_DUMMY
        );
    }

    public static function createInstaller() : Application_Installer
    {
        return new Application_Installer();
    }

    public static function isUnitTestingRunning() : bool
    {
        return
            (defined('APP_TESTS_RUNNING') && APP_TESTS_RUNNING === true)
            ||
            (defined('APP_FRAMEWORK_TESTS') && APP_FRAMEWORK_TESTS === true);
    }

    public static function getTimeStarted() : float
    {
        if(defined('APP_TIME_START'))
        {
            return APP_TIME_START;
        }

        return 0;
    }

    public static function getTimePassed() : float
    {
        return microtime(true) - self::getTimeStarted();
    }
}
