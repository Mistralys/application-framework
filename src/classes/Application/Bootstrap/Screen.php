<?php

/**
 * The SQL mode string as used on the live servers.
 */

use Application\AppFactory;
use Application\Bootstrap\BootException;
use Application\ConfigSettings\BaseConfigRegistry;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\FileHelper_Exception;

abstract class Application_Bootstrap_Screen implements Application_Interfaces_Loggable
{
    use Application_Traits_Loggable;

    public const ERROR_CONFIG_SETTING_ALREADY_DEFINED = 28201;
    public const ERROR_DATABASE_WRITE_OPERATION_DURING_EXPORT = 28202;

    public const REQUEST_PARAM_SET_USERSETTING = 'set_usersetting';
    public const REQUEST_PARAM_DEVELMODE_ENABLE = 'develmode_enable';

    protected array $params = array();
    protected Application $app;
    protected Application_Driver $driver;
    protected Application_Session $session;
    protected Application_User $user;
    private bool $environmentCreated = false;

    public function __construct($params)
    {
        $this->params = $params;
    }
    
    public function boot() : void
    {
        $this->log('Booting the screen.');

        $this->_boot();
    }
    
   /**
    * Retrieves the relative path to the dispatcher
    * file handling this screen, e.g. "index.php".
    * 
    * @return string
    */
    abstract public function getDispatcher();

    abstract protected function _boot();
    
    /**
     * Creates the environment by instantiating the
     * application and driver. Stores the instances
     * in the according properties.
     */
    protected function createEnvironment() : void
    {
        if($this->environmentCreated) {
            return;
        }

        $this->log('SETUP | Creating the environment.');

        $this->environmentCreated = true;

        date_default_timezone_set('Europe/Berlin');

        $this->initConfigurationDefaults();
        $this->initDatabase();
        $this->initSession();

        $this->app = new Application($this);

        $this->authenticateUser();
        $this->initUserSettingsInRequest();
        $this->initDeveloperMode();
        $this->initLocalization();
        $this->initDriver();

        $this->app->start($this->driver);
    }

    private function initConfigurationDefaults() : void
    {
        if(!defined('APP_NO_AUTHENTICATION'))
        {
            define('APP_NO_AUTHENTICATION', false);
        }

        // make sure no one tries to set the developer mode manually
        if (defined('APP_DEVELOPER_MODE'))
        {
            die('<pre><b style="color:#cc0000">Error:</b> The APP_DEVELOPER_MODE constant may not be set manually.<br/>Please remove it from your local configuration file.</pre>');
        }

        // default is to run the application in UI mode
        if(!defined('APP_RUN_MODE'))
        {
            define('APP_RUN_MODE', Application::RUN_MODE_UI);
        }
    }

    /**
     * NOTE: Must be done after application has been instantiated,
     * and before the user is authenticated, as that triggers the
     * roles to be initialized, which require translation.
     *
     * @return void
     * @throws Application_EventHandler_Exception
     * @throws Application_Exception
     * @throws UI_Exception
     * @throws FileHelper_Exception
     */
    private function initLocalization() : void
    {
        Application_Localization::init();
        Application_Localization::select();
    }

    public function isDeveloperModeEnabled() : bool
    {
        return
        Application_Driver::isGlobalDevelModeEnabled()
        ||
        (
            isset($this->user)
            &&
            $this->user->isDeveloper()
            &&
            $this->user->isDeveloperModeEnabled()
        );
    }

    private function initDeveloperMode() : void
    {
        if ($this->isDeveloperModeEnabled())
        {
            define('APP_DEVELOPER_MODE', true);
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }

        if (!defined('APP_DEVELOPER_MODE'))
        {
            define('APP_DEVELOPER_MODE', false);
        }
    }

    private function initDriver() : void
    {
        if(!class_exists(APP_CLASS_NAME))
        {
            header('Content-Type:text/plain; charset=UTF-8');

            die(sprintf(
                'Could not find the driver class [%s].',
                APP_CLASS_NAME
            ));
        }

        $driverClass = APP_CLASS_NAME;

        $this->driver = ClassHelper::requireObjectInstanceOf(
            Application_Driver::class,
            new $driverClass($this->app)
        );
    }

    private static ?string $sessionClass = null;

    /**
     * @param class-string|null $class
     * @return void
     */
    public static function setSessionClass(?string $class) : void
    {
        self::$sessionClass = $class;
    }

    /**
     * @return void
     * @see Application_Session_Base
     * @throws BaseClassHelperException
     */
    private function initSession() : void
    {
        $class = self::$sessionClass ?? ClassHelper::requireResolvedClass(APP_CLASS_NAME . '_Session');

        $this->session = ClassHelper::requireObjectInstanceOf(
            Application_Session::class,
            new $class()
        );
    }

    private bool $dbInitialized = false;

    private function initDatabase() : void
    {
        if($this->dbInitialized) {
            return;
        }

        $this->dbInitialized = true;

        if(!Application::isDatabaseEnabled())
        {
            $this->log('SETUP | Database is disabled.');
            return;
        }

        $this->log('SETUP | Initializing the database.');

        // enable the logging for the DB helper
        DBHelper::setLogCallback(array('Application', 'log'));

        DBHelper::init();
        
        // in the development environment, ensure that the SQL mode
        // is the same as on the live servers.
        if(Application::isDevelEnvironment())
        {
            DBHelper::execute(
                DBHelper_OperationTypes::TYPE_SET,
                "SET SESSION sql_mode = '".APP_DEVEL_SQL_MODE."'"
            );
        }
    }
    
    private function authenticateUser() : void
    {
        $this->log('SETUP | Authenticating the user.');

        $this->session->authenticate();
    }

    public function getSession() : Application_Session
    {
        return $this->session;
    }
    
   /**
    * Disables the authentication, so the user does not
    * need to log in to view the screen. 
    * 
    * NOTE: Can only be done before the environment has
    * been configured.
    * 
    * @throws Application_Exception
    */
    protected function disableAuthentication() : void
    {
        $this->log('SETUP | Disabling authentication.');

        $this->setDefine(BaseConfigRegistry::NO_AUTHENTICATION, true);
    }
    
    private $disallowDBWriteOperations = false;
    
   /**
    * Adds an event handler that will throw an exception
    * whenever a query is run that writes to the database.
    */
    protected function disallowDBWriteOperations()
    {
        if(!$this->disallowDBWriteOperations)
        {
            $this->log('SETUP | Disallowing database write operations.');

            DBHelper::onBeforeWriteOperation(array($this, 'handleEvent_beforeDBWriteOperation'));
        }
    }

    /**
     * Called for each database query that is
     * @param DBHelper_Event $event
     * @throws Application_Exception
     */
    public function handleEvent_beforeDBWriteOperation(DBHelper_Event $event)
    {
        if(!$event->isWriteOperation()) {
            return;
        }
        
        throw new BootException(
            'Database write operation detected.',
            sprintf(
                'Database write operations have been disallowed. Statement: %s',
                $event->getStatement(true)
            ),
            self::ERROR_DATABASE_WRITE_OPERATION_DURING_EXPORT
        );
    }
    
    public function getParam($name, $default=null)
    {
        if(isset($this->params[$name])) {
            return $this->params[$name];
        }
        
        return $default;
    }
    
    /**
     * Force the application into script mode, which makes
     * it bypass all the UI layer initialization.
     */
    protected function enableScriptMode() : void
    {
        $this->log('SETUP | Switching to script mode.');

        $this->setDefine(BaseConfigRegistry::RUN_MODE, Application::RUN_MODE_SCRIPT);
    }
    
   /**
    * Defines a constant, with a check for existing
    * constants. If the constant already exists but
    * has a different value, an exception is thrown.
    * 
    * @param string $name
    * @param mixed $value
    * @throws Application_Exception
    */
    protected function setDefine($name, $value)
    {
        if(defined($name))
        {
            if(constant($name) === $value) {
                return;
            }
            
            throw new BootException(
                'Cannot overwrite a configuration setting',
                sprintf(
                    'Cannot set [%s] to [%s], it has already been set to [%s].',
                    $name,
                    var_dump_get($value),
                    var_dump_get(constant($name))
                ),
                self::ERROR_CONFIG_SETTING_ALREADY_DEFINED
            );
        }
        
        define($name, $value);
    }
    
    protected function createPage() : UI_Page
    {
        return $this->driver->getUI()->createPage(get_class($this));
    }

    protected function createTemplate(string $templateID) : UI_Page_Template
    {
        return $this->createPage()->createTemplate($templateID);
    }

    private ?string $logIdentifier = null;

    public function getLogIdentifier(): string
    {
        if(!isset($this->logIdentifier)) {
            $this->logIdentifier = sprintf(
                'Bootstrap screen [%s]',
                ClassHelper::getClassTypeName($this)
            );
        }

        return $this->logIdentifier;
    }

    private function handleDeveloperMode(Application_User $user) : void
    {
        if (!isset($_REQUEST[self::REQUEST_PARAM_DEVELMODE_ENABLE])) {
            return;
        }
        
        $new = string2bool($_REQUEST[self::REQUEST_PARAM_DEVELMODE_ENABLE]) === true;
        $old = $user->isDeveloperModeEnabled();

        if ($new === $old) {
            return;
        }

        $user->setDeveloperModeEnabled($new);

        if ($new === true) {
            UI::getInstance()->addInfoMessage('Developer mode is now <b class="text-success">enabled</b>.');
        } else {
            UI::getInstance()->addInfoMessage('Developer mode is now <b class="text-error">disabled</b>.');
        }
    }

    private function handleQuickSettings(Application_User $user) : void
    {
        if(!isset($_REQUEST[self::REQUEST_PARAM_SET_USERSETTING], $_REQUEST['value'])) {
            return;
        }

        $updated = false;
        $value = $_REQUEST['value'];
        $settings = array();

        switch($_REQUEST[self::REQUEST_PARAM_SET_USERSETTING])
        {
            case 'layout_width':
                if(in_array($value, array('standard', 'maximized'))) {
                    $settings['layout_width'] = $value;
                }
                break;

            case 'layout_fontsize':
                if(in_array($value, array('standard', 'bigger'))) {
                    $settings['layout_fontsize'] = $value;
                }
                break;
        }

        foreach($settings as $name => $value) {
            if($user->getSetting($name) !== $value) {
                $user->setSetting($name, $value);
                $updated = true;
            }
        }

        if($updated) {
            UI::getInstance()->addInfoMessage(sb()
                ->icon(UI::icon()->information())
                ->bold(t('Your user settings have been updated.'))
            );
        }
    }

    private function initUserSettingsInRequest() : void
    {
        $user = $this->session->requireUser();

        $this->handleDeveloperMode($user);
        $this->handleQuickSettings($user);

        $user->saveSettings();
    }
}
