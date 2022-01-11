<?php
/**
 * File containing the Application class.
 * @see Application
 * @subpackage Core
 * @package Application
 */

use AppLocalize\Localization;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

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

    const RUN_MODE_UI = 'ui';
    const RUN_MODE_SCRIPT = 'script';

    const SCRIPT_TYPE_AJAX = 'ajax';
    const SCRIPT_TYPE_EXPORT_AUTHENTICATED = 'export_authenticated';
    const SCRIPT_TYPE_EXPORT_PUBLIC = 'export_public';
    const SCRIPT_TYPE_MONITOR = 'monitor';

    const USER_ID_SYSTEM = 1;
    const USER_ID_DUMMY = 2;
    const EVENT_DRIVER_INSTANTIATED = 'DriverInstantiated';
    const EVENT_REDIRECT = 'Redirect';

    /**
     * @var UI
     */
    private $ui;

    /**
     * @var Application_Driver
     */
    private $driver;

    /**
     * @var Application_Request
     */
    private $request;

    /**
     * @var Application_Session
     */
    private static $session;

    /**
     * @var AppLocalize\Localization_Locale
     */
    private $locale;

    /**
     * @var integer
     */
    private static $counter = 0;

    /**
     * @var boolean
     */
    private $started = false;

    /**
     * @var Application_Bootstrap_Screen
     */
    private $bootScreen;

    /**
     * @see Application::getLogger()
     * @var Application_Logger
     */
    private static $logger;

    /**
     * @var UI_Page
     */
    private $page;

    /**
     * @var boolean
     */
    private static $simulation = false;

    /**
     * @var boolean
     */
    private static $develEnvironment;

    /**
     * @see Application::getStorageFolder()
     * @var string
     */
    private static $storageFolder = '';

    /**
     * @see Application::getStorageSubfolderPath()
     * @var string[]string
     */
    private static $knownStorageFolders = array();

    /**
     * @see Application::getMessageLog()
     * @var Application_Messagelogs
     */
    private static $messagelogs;

    /**
     * @see Application::createMedia()
     * @var Application_Media
     */
    private static $media;

    /**
     * @see Application::createMessaging()
     * @var Application_Messaging
     */
    private static $messaging;

    /**
     * @see Application::createLookupItems()
     * @var Application_LookupItems
     */
    private static $lookupItems;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private static $exitEnabled = true;

    /**
     * @var Application_ErrorLog
     */
    protected static $errorLog;

    public function __construct(Application_Bootstrap_Screen $bootScreen)
    {
        self::$session = $bootScreen->getSession();
        self::$counter++;

        $this->bootScreen = $bootScreen;
        $this->id = self::$counter;
        $this->ui = UI::createInstance($this);
        $this->locale = Localization::getAppLocale();
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
        return isset(self::$session);
    }

    /**
     * @return Application_Feedback
     * @throws Application_Exception_UnexpectedInstanceType
     * @throws DBHelper_Exception
     */
    public static function createFeedback() : Application_Feedback
    {
        $collection = DBHelper::createCollection(Application_Feedback::class);

        if($collection instanceof Application_Feedback)
        {
            return $collection;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Feedback::class, $collection);
    }

    /**
     * @return Application_Bootstrap_Screen
     */
    public function getBootScreen()
    {
        return $this->bootScreen;
    }

    public function getID()
    {
        return $this->id;
    }

    /**
     * Starts (configures) the application in preparation for
     * rendering the output.
     *
     * @param Application_Driver $driver
     * @event Application_Event ApplicationStarted
     */
    public function start(Application_Driver $driver) : void
    {
        Application::log(sprintf('Starting application.'));

        if (!defined('APP_RUN_MODE'))
        {
            throw new Application_Exception(
                'No application run mode set',
                'The [APP_RUN_MODE] configuration must be present to start the application.',
                self::ERROR_NO_RUN_MODE_SET
            );
        }

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

        if (APP_RUN_MODE == self::RUN_MODE_UI)
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

            Application::log(sprintf('Determined active page to be [%s].', $pageID));

            $this->page = $this->ui->createPage($pageID);
            $this->ui->setPage($this->page);
            $this->driver->setPage($this->page);
        }
        else
        {
            if (APP_RUN_MODE == self::RUN_MODE_SCRIPT)
            {
            }
            else
            {
                throw new Application_Exception(
                    'Invalid application run mode',
                    sprintf(
                        'The run mode [%s] is not a valid application run mode.',
                        APP_RUN_MODE
                    ),
                    self::ERROR_INVALID_RUN_MODE
                );
            }
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
     */
    public static function getLogger() : Application_Logger
    {
        if (!isset(self::$logger))
        {
            self::$logger = new Application_Logger();
        }

        return self::$logger;
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
        return self::getLogger()->log($message, $header);
    }

    public static function logSF(string $message, ...$args) : Application_Logger
    {
        return self::getLogger()->logSF($message, ...$args);
    }

    public static function logEvent(string $eventName, string $message = '', ...$args) : Application_Logger
    {
        return self::getLogger()->logEvent($eventName, $message, ...$args);
    }

    /**
     * Logs a message styled as a header.
     *
     * @param string $message
     * @return Application_Logger
     */
    public static function logHeader(string $message) : Application_Logger
    {
        return self::getLogger()->logHeader($message);
    }

    /**
     * Logs a data array.
     *
     * @param array $data
     * @return Application_Logger
     */
    public static function logData(array $data) : Application_Logger
    {
        return self::getLogger()->logData($data);
    }

    public static function logError(string $message) : Application_Logger
    {
        return self::getLogger()->logError($message);
    }

    /**
     * @return UI
     */
    public function getUI()
    {
        return $this->ui;
    }

    /**
     * @return UI_Themes_Theme
     */
    public function getTheme()
    {
        return $this->ui->getTheme();
    }

    /**
     * @return Application_Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @return Application_Session
     */
    public static function getSession()
    {
        if (isset(self::$session))
        {
            return self::$session;
        }

        throw new Application_Exception(
            'Session not available yet',
            'The session instance has not been created yet.',
            self::ERROR_SESSION_NOT_AVAILABLE_YET
        );
    }

    /**
     * @return Application_User
     * @throws Application_Exception
     * @see Application::ERROR_NO_USER_PRIOR_TO_SESSION
     */
    public static function getUser() : Application_User
    {
        if (!isset(self::$session))
        {
            throw new Application_Exception(
                'User management error',
                'No session set yet, user object is not available at this point.',
                self::ERROR_NO_USER_PRIOR_TO_SESSION
            );
        }

        return self::$session->getUser();
    }

    /**
     * Renders the current page and outputs the generated content.
     */
    public function display() : void
    {
        if (!$this->started)
        {
            throw new Application_Exception(
                'Application not started',
                'Cannot display the rendered contents, the application [start] method was not called.',
                self::ERROR_APPLICATION_NOT_STARTED
            );
        }

        $content = $this->driver->renderContent();

        if (empty($content))
        {
            throw new Application_Exception(
                'No content to display',
                'The driver did not generate any contents to display.',
                self::ERROR_DRIVER_DID_NOT_GENERATE_CONTENT
            );
        }

        $this->page->setContent($content);
        $this->page->display();
    }

    /**
     * Loads the specified template and returns its rendered content.
     *
     * @param string $templateID
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
    public function createTemplate($templateID)
    {
        return $this->page->createTemplate($templateID);
    }

    /**
     * @return Application_Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Determines the full path to the include file automatically,
     * and requires it. Also checks that the class name exists after
     * loading the file.
     *
     * @param string $className
     * @param boolean $exception Whether to throw an exception if the class cannot be loaded. If disabled, this will return a boolean true on success or an error message otherwise.
     * @return boolean|string
     * @throws Application_Exception
     * @deprecated Autoloading is used now
     */
    public static function requireClass($className, $exception = true, $checkSyntax = false)
    {
        if (class_exists($className))
        {
            return true;
        }

        $filename = str_replace('_', '/', $className) . '.php';

        $filePath = stream_resolve_include_path($filename);

        if (!$filePath)
        {
            $message = 'Class file not found';
            if (!$exception)
            {
                return $message;
            }

            throw new Application_Exception(
                $message,
                sprintf(
                    'Could not find class file for class [%s] in [%s].',
                    $className,
                    $filePath
                ),
                self::ERROR_CLASS_FILE_NOT_FOUND
            );
        }

        if ($checkSyntax)
        {
            $checkResult = AppUtils\FileHelper::checkPHPFileSyntax($filePath);

            if ($checkResult !== true)
            {
                $message = 'Syntax error in file';
                if (!$exception)
                {
                    return $message;
                }

                throw new Application_Exception(
                    $message,
                    sprintf(
                        'The file for class [%s] has syntax errors in [%s]. Validation messages: %s',
                        $className,
                        $filePath,
                        json_encode($checkResult)
                    ),
                    self::ERROR_PHP_FILE_SYNTAX_ERRORS
                );
            }
        }

        require_once $filePath;

        if (!class_exists($className))
        {
            $message = 'Class not found';
            if (!$exception)
            {
                return $message;
            }
            throw new Application_Exception(
                $message,
                sprintf(
                    'Class file loaded, but class [%s] not present in file [%s].',
                    $className,
                    $filePath
                ),
                self::ERROR_CLASS_NOT_FOUND
            );
        }

        return true;
    }

    /**
     * @param bool $html
     * @return Application_Logger
     * @deprecated Use the logger instead
     */
    public static function logModeEcho(bool $html = false) : Application_Logger
    {
        return self::getLogger()->enableHTML($html)->logModeEcho();
    }

    /**
     * @param bool $html
     * @return Application_Logger
     * @deprecated Use the logger instead
     */
    public static function logModeFile(bool $html = false) : Application_Logger
    {
        return self::getLogger()->enableHTML($html)->logModeFile();
    }

    /**
     * @return Application_Logger
     * @deprecated Use the logger instead
     */
    public static function logModeNone() : Application_Logger
    {
        return self::getLogger()->logModeNone();
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

        $env = Application_Environments::getInstance()->getDetected();

        if($env !== null && $env->isDev())
        {
            self::$develEnvironment = true;
        }

        return self::$develEnvironment;
    }

    /**
     * Returns the global instance of the API manager class,
     * creating it as needed.
     *
     * @return Application_API
     */
    public static function createAPI() : Application_API
    {
        return Application_API::getInstance();
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
        return self::getStorageSubfolderPath('temp');
    }

    public static function getCacheFolder() : string
    {
        return self::getStorageSubfolderPath('cache');
    }

    /**
     * Generates a temporary file path.
     *
     * Note: the file is not created. This just creates a file path.
     *
     * @param string $name Specific name to use (without extension), or auto-generated if empty.
     * @param string $extension The extension to use
     */
    public static function getTempFile(string $name = '', string $extension = 'tmp') : string
    {
        if (empty($name))
        {
            $name = md5('tmp' . microtime(true));
        }

        return self::getTempFolder() . '/' . $name . '.' . $extension;
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

        self::$storageFolder = APP_ROOT . '/storage';

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
     * Retrieves the absolute path to a storage subfolder. Attempts
     * to create the folder if it does not exist.
     *
     * @param string $subfolderName
     * @return string
     * @throws Application_Exception
     */
    public static function getStorageSubfolderPath($subfolderName) : string
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

    public static function getMessageLog() : Application_Messagelogs
    {
        if (!isset(self::$messagelogs))
        {
            self::$messagelogs = new Application_Messagelogs();
        }

        return self::$messagelogs;
    }

    /**
     * Creates the media manager instance, including the files as needed.
     * @return Application_Media
     */
    public static function createMedia() : Application_Media
    {
        if (!isset(self::$media))
        {
            self::$media = Application_Media::getInstance();
        }

        return self::$media;
    }

    /**
     * Creates the specified API connector and returns its instance.
     * The type is the filename of the connector minus the extension.
     *
     * @param string $type
     * @return Connectors_Connector
     */
    public static function createConnector($type) : Connectors_Connector
    {
        return Connectors::createConnector($type);
    }

    protected static $devuser;

    /**
     * Checks whether the application is in simulation mode, which
     * can be triggered either by setting the simulation_only request
     * parameter, or setting it explicitly with {@link setSimulation()}.
     *
     * @return boolean
     */
    public static function isSimulation() : bool
    {
        if (Application::isSessionReady() && !self::isUserDev())
        {
            return false;
        }

        if (self::$simulation)
        {
            return true;
        }

        if (isset($_REQUEST['simulate_only']) && ($_REQUEST['simulate_only'] == 'yes' || $_REQUEST['simulate_only'] == 'true'))
        {
            return true;
        }

        return false;
    }

    public static function isUserDev() : bool
    {
        if (!isset(self::$devuser))
        {
            self::$devuser = self::getUser()->isDeveloper();
        }

        return self::$devuser !== null;
    }

    /**
     * Explicity starts the simulation mode, in which all relevant queries or
     * operations are not committed.
     *
     * @param boolean $simulation
     */
    public static function setSimulation(bool $simulation = true) : void
    {
        self::$simulation = $simulation;

        $_REQUEST['simulate_only'] = ConvertHelper::bool2string($simulation, true);

        self::log(sprintf(
            'Application | Setting simulation mode to %1$s.',
            strtoupper($_REQUEST['simulate_only'])
        ));
    }

    /**
     * @return Application_Sets
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
     */
    public static function createLookupItems() : Application_LookupItems
    {
        if (!isset(self::$lookupItems))
        {
            $driver = Application_Driver::getInstance();
            self::$lookupItems = new Application_LookupItems($driver);
        }

        return self::$lookupItems;
    }

    /**
     * Creates/returns the instance of the application ratings,
     * which are used to handle user ratings of application screens.
     *
     * @return Application_Ratings
     * @throws Application_Exception_UnexpectedInstanceType
     * @throws DBHelper_Exception
     */
    public static function createRatings() : Application_Ratings
    {
        $collection = DBHelper::createCollection(Application_Ratings::class);

        if($collection instanceof Application_Ratings)
        {
            return $collection;
        }

        throw new Application_Exception_UnexpectedInstanceType(Application_Ratings::class, $collection);
    }

    /**
     * Attempts to set the PHP execution time limit. Throws an
     * exception if this fails.
     *
     * @param integer $seconds The limit to set. Use 0 for no limit.
     * @param string $operation Human readable label of the operation that needs the time limit, shown in the exception.
     * @throws Application_Exception
     */
    public static function setTimeLimit($seconds, $operation)
    {
        if (set_time_limit($seconds) === false)
        {
            throw new Application_Exception(
                'Cannot change the execution time.',
                sprintf(
                    'Tried changing the execution time to [%s] seconds for operation [%s].',
                    $seconds,
                    $operation
                ),
                self::ERROR_CANNOT_SET_EXECUTION_TIME
            );
        }
    }

    /**
     * Attempts to set the PHP memory limit. Throws an exception
     * if this fails.
     *
     * @param integer $megabytes The amount of memory in megabytes. Set to -1 for no limit.
     * @param string $operation Human readable label of the operation that needs the memory limit, shown in the exception.
     * @throws Application_Exception
     */
    public static function setMemoryLimit($megabytes, $operation)
    {
        if ($megabytes != -1 || $megabytes != '-1')
        {
            $megabytes = $megabytes . 'M';
        }

        if (ini_set('memory_limit', $megabytes) === false)
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

    public static function createErrorLog() : Application_ErrorLog
    {
        if (!isset(self::$errorLog))
        {
            self::$errorLog = new Application_ErrorLog();
        }

        return self::$errorLog;
    }

    /**
     * @return never
     * @todo Handle shutdown tasks here.
     */
    public static function exit(string $reason = '')
    {
        /* TODO: Review the exit bypass handling
        if (self::$exitEnabled)
        {

        }
        */

        self::log(sprintf('Exiting application. Reason given: [%s].', $reason));

        exit;
    }

    public static function setExitEnabled(bool $enabled = true) : bool
    {
        $previous = self::$exitEnabled;

        self::$exitEnabled = $enabled;

        return $previous;
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
                \AppUtils\parseVariable($callable)->enableType()->toString()
            ),
            $errorCode
        );
    }

    public static function getRunMode() : string
    {
        return strval(boot_constant('APP_RUN_MODE'));
    }

    public static function isUIEnabled() : bool
    {
        return self::getRunMode() === Application::RUN_MODE_UI;
    }

    public static function isAuthenticationEnabled() : bool
    {
        return boot_constant('APP_NO_AUTHENTICATION') !== true;
    }

    public static function isSessionSimulated() : bool
    {
        return boot_constant('APP_SIMULATE_SESSION') === true;
    }

    public static function isDemoMode() : bool
    {
        return boot_constant('APP_DEMO_MODE') === true;
    }

    /**
     * Checks whether the database layer is enabled.
     *
     * @return bool
     * @see APP_DB_ENABLED
     */
    public static function isDatabaseEnabled() : bool
    {
        return boot_constant('APP_DB_ENABLED') === true;
    }

    public static function createLDAP() : Application_LDAP
    {
        $conf = new Application_LDAP_Config(
            APP_LDAP_HOST,
            (int)APP_LDAP_PORT,
            APP_LDAP_DN,
            APP_LDAP_USERNAME,
            APP_LDAP_PASSWORD
        );

        $conf->setMemberSuffix(APP_LDAP_MEMBER_SUFFIX);

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
     * @param string $url
     * @return never
     *
     * @throws Application_Exception
     * @see Application::ERROR_REDIRECT_EVENTS_FAILED
     */
    public static function redirect(string $url) : void
    {
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

        $simulation = Application::isSimulation();

        if (!headers_sent() && !$simulation)
        {
            header('Location:' . $url);
            Application::exit(sprintf('Redirected to [%s]', $url));
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
            Application::getLogger()->printLog(true);
        }

        Application::exit(sprintf('Redirected to [%s]', $url));
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
        $result = DBHelper::fetchKey(
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
        if ($result !== null)
        {
            return intval($result);
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

        $result = DBHelper::fetchKey(
            'user_id',
            "SELECT
                user_id
            FROM
                known_users
            WHERE
                user_id=:user_id",
            array(
                'user_id' => $userID
            )
        );

        return $result !== null;
    }

    /**
     * Retrieves the data set from the database for the
     * specified user ID. Also handles dummy and system
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

    public static function isSessionReady() : bool
    {
        return isset(self::$session);
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
}
