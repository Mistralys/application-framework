<?php

/**
 * The SQL mode string as used on the live servers.
 */
define('APP_DEVEL_SQL_MODE', 'REAL_AS_FLOAT,PIPES_AS_CONCAT,ANSI_QUOTES,IGNORE_SPACE,ONLY_FULL_GROUP_BY,ANSI,STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,TRADITIONAL,NO_ENGINE_SUBSTITUTION');

abstract class Application_Bootstrap_Screen
{
    public const ERROR_CONFIG_SETTING_ALREADY_DEFINED = 28201;
    public const ERROR_DATABASE_WRITE_OPERATION_DURING_EXPORT = 28202;

    public const REQUEST_PARAM_SET_USERSETTING = 'set_usersetting';

    protected array $params = array();
    
    public function __construct($params)
    {
        $this->params = $params;
    }
    
    public function boot() : void
    {
        register_shutdown_function(array($this, 'shutDown'));

        if(!defined('APP_TIME_START'))
        {
            define('APP_TIME_START', microtime(true));
        }
        
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
    * @var Application
    */
    protected Application $app;
    
   /**
    * @var Application_Driver
    */
    protected Application_Driver $driver;
    
   /**
    * @var Application_Session
    */
    protected Application_Session $session;
    
   /**
    * @var Application_User
    */
    protected Application_User $user;

    /**
     * @var bool
     */
    private bool $environmentCreated = false;
    
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
        
        $this->environmentCreated = true;
        
        if(!defined('APP_NO_AUTHENTICATION')) {
            define('APP_NO_AUTHENTICATION', false);
        }
        
        date_default_timezone_set('Europe/Berlin');
        
        // enable the logging for the DB helper
        DBHelper::setLogCallback(array('Application', 'log'));
        
        if(!class_exists(APP_CLASS_NAME)) 
        {
            header('Content-Type:text/plain; charset=UTF-8');
            
            die(sprintf(
                'Could not find the driver class [%s].',
                APP_CLASS_NAME
            ));
        }
        
        // make sure no one tries to set the developer mode manually
        if (defined('APP_DEVELOPER_MODE')) {
            die('<pre><b style="color:#cc0000">Error:</b> The APP_DEVELOPER_MODE constant may not be set manually.<br/>Please remove it from your local configuration file.</pre>');
        }
        
        // default is to run the application in UI mode
        if(!defined('APP_RUN_MODE')) {
            define('APP_RUN_MODE', Application::RUN_MODE_UI);
        }
        
        if(Application::isDatabaseEnabled()) 
        {
            $this->initDatabase();
        }
        
        $this->session = call_user_func(array(APP_CLASS_NAME, 'getSession'));
        $this->user = $this->session->getUser();
        
        $this->app = new Application($this);

        // needs to be done after application has been instantiated, and
        // before the user is authenticated, as that triggers the roles
        // to be initialized, which require translation.
        Application_Localization::init();
        Application_Localization::select();
        
        // some parts of the application do not use the authentication,
        // like the XML export.
        if (!defined('APP_NO_AUTHENTICATION') || !APP_NO_AUTHENTICATION)
        {
            $this->authenticateUser();
        }
        
        if (isset($this->user) && $this->user->isDeveloper() && $this->user->getSetting('developer_mode') === 'yes')
        {
            define('APP_DEVELOPER_MODE', true);
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        }
        
        if (!defined('APP_DEVELOPER_MODE')) {
            define('APP_DEVELOPER_MODE', false);
        }
        
        $driverClass = APP_CLASS_NAME;
        $this->driver = new $driverClass($this->app);
        
        $this->app->start($this->driver);
    }
    
    protected function initDatabase() : void
    {
        DBHelper::init();
        
        // in the development environment, ensure that the SQL mode
        // is the same than on the live servers.
        if(Application::isDevelEnvironment()) {
            DBHelper::execute(DBHelper_OperationTypes::TYPE_SET, "SET SESSION sql_mode = '".APP_DEVEL_SQL_MODE."'");
        }
    }
    
    protected function authenticateUser() : void
    {
        $user = $this->session->requireUser();
        
        if (isset($_REQUEST['develmode_enable']) && in_array($_REQUEST['develmode_enable'], array('yes', 'no')))
        {
            $new = $_REQUEST['develmode_enable'];
            $old = $user->getSetting('developer_mode', 'no');
            $user->setSetting('developer_mode', $new);
            if ($new != $old) {
                if ($new === 'yes') {
                    UI::getInstance()->addInfoMessage('Developer mode is now <b class="text-success">enabled</b>.');
                } else {
                    UI::getInstance()->addInfoMessage('Developer mode is now <b class="text-error">disabled</b>.');
                }
            }
        }
        else if(isset($_REQUEST[self::REQUEST_PARAM_SET_USERSETTING]) && isset($_REQUEST['value']))
        {
            $updated = false;
            $value = $_REQUEST['value'];
            $settings = array();
            
            switch($_REQUEST[self::REQUEST_PARAM_SET_USERSETTING])
            {
                case  'layout_width':
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
                if($user->getSetting($name) != $value) {
                    $user->setSetting($name, $value);
                    $updated = true;
                }
            }
            
            if($updated) {
                UI::getInstance()->addInfoMessage(
                    UI::icon()->information().' '.
                    '<b>'.t('Your user settings have been updated.').'</b>'
                );
            }
        }
        
        $user->saveSettings();
    }
    
    public function getSession()
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
    protected function disableAuthentication()
    {
        $this->setDefine('APP_NO_AUTHENTICATION', true);
    }
    
    private $disallowDBWriteOperations = false;
    
   /**
    * Adds an event handler that will throw an exception
    * whenever a query is run that writes to the database.
    */
    protected function disallowDBWriteOperations()
    {
        if(!$this->disallowDBWriteOperations) {
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
        
        throw new Application_Exception(
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
    protected function enableScriptMode()
    {
        $this->setDefine('APP_RUN_MODE', 'script');
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
            
            throw new Application_Exception(
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
    
    public function shutDown() : void
    {
        Application::log('Bootstrap | The system is shutting down.');

        if(Application_EventHandler::hasListener(Application::EVENT_SYSTEM_SHUTDOWN))
        {
            Application_EventHandler::trigger(
                Application::EVENT_SYSTEM_SHUTDOWN,
                array($this->driver),
                Application_EventHandler_Event_SystemShutDown::class
            );
        }
               
        if(Application::createRequestLog()->getStatus()->isEnabled() === true)
        {
            Application::getLogger()->write();
        }
    }

    protected function createPage() : UI_Page
    {
        return $this->driver->getUI()->createPage(get_class($this));
    }

    protected function createTemplate(string $templateID) : UI_Page_Template
    {
        return $this->createPage()->createTemplate($templateID);
    }
}
