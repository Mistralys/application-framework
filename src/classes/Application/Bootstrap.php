<?php

declare(strict_types=1);

use Application\Bootstrap\BootException;
use Application\ConfigSettings\BaseConfigRegistry;
use AppUtils\BaseException;
use Composer\Autoload\ClassLoader;

const APP_DEVEL_SQL_MODE = 'REAL_AS_FLOAT,PIPES_AS_CONCAT,ANSI_QUOTES,IGNORE_SPACE,ONLY_FULL_GROUP_BY,ANSI,STRICT_TRANS_TABLES,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,TRADITIONAL,NO_ENGINE_SUBSTITUTION';
const APP_FRAMEWORK_DOCUMENTATION_URL = 'https://github.com/Mistralys/application-framework/blob/main/docs/Documentation.md';

/**
 * Must be loaded manually, because autoloading is not ready
 * yet when constant names are accessed.
 */
require_once __DIR__ . '/ConfigSettings/BaseConfigRegistry.php';

class Application_Bootstrap
{
    public const ERROR_INVALID_BOOTSTRAP_CLASS = 28101;
    public const ERROR_AUTOLOADER_NOT_STARTED = 28102; 
    public const ERROR_AUTOLOAD_FILE_NOT_FOUND = 28103; 
    public const ERROR_NON_FRAMEWORK_EXCEPTION = 28104;
    
    private static ClassLoader $autoLoader;
    private static bool $initialized = false;

    /**
     * @var class-string|NULL
     */
    private static ?string $bootClass = null;

    /**
     * Boots from a standard application screen.
     *
     * @param string $screenID
     * @param array<int,mixed> $params
     * @param bool $displayException
     * @throws Application_Exception
     */
    public static function boot(string $screenID, array $params=array(), bool $displayException=true) : void
    {
        $class = Application_Bootstrap_Screen::class.'_'.$screenID;
        self::bootClass($class, $params, $displayException);
    }

    /**
     * Returns the name of the boot screen class that was used
     * to boot the application. Can be used to identify which
     * screen was used.
     *
     * @return class-string|NULL
     */
    public static function getBootClass() : ?string
    {
        return self::$bootClass;
    }

    public static function getAutoLoader() : ClassLoader
    {
        self::init();

        return self::$autoLoader;
    }

    /**
     * Boots from a custom driver screen.
     * @param string $screenID
     * @param array<int,mixed> $params
     * @param bool $displayException
     * @throws Application_Exception
     */
    public static function bootCustom(string $screenID, array $params=array(), bool $displayException=true) : void
    {
        $class = APP_CLASS_NAME.'_Bootstrap_'.$screenID;
        self::bootClass($class, $params, $displayException);
    }
    
   /**
    * Boots an admin screen using its class name.
    * 
    * @param string $class
    * @param array $params
    * @param bool $displayException Whether to automatically show the exception screen, or just pass them on.
    * @throws Application_Exception
    */
    public static function bootClass(string $class, array $params=array(), bool $displayException=true) : void
    {
        self::$bootClass = $class;

        try
        {
            $screen = new $class($params);
            
            if(!$screen instanceof Application_Bootstrap_Screen)
            {
                throw new BootException(
                    'Invalid bootstrap screen',
                    sprintf(
                        'The screen [%s] is not an instance of [%s].',
                        $class,
                        Application_Bootstrap_Screen::class
                    ),
                    self::ERROR_INVALID_BOOTSTRAP_CLASS
                );
            }
            
            // start so we can capture the page's content
            ob_start();
            
            $screen->boot();
            
            ob_end_flush();
        }
        catch(Exception $e)
        {
            // Fetch the content generated up to this point,
            // so we can use it and avoid text output outside 
            // the error page.
            $output = ob_get_clean();

            // Convert non-framework exceptions, so they can
            // be logged in the error log.
            $e = self::convertException($e);

            if($displayException)
            {
                displayError($e, (string)$output);
            }
            else 
            {
                throw $e;
            }
        }
    }

    /**
     * @var array<string,array{required:bool,defaultValue:string|int|float|bool|array|NULL}>
     */
    protected static array $knownSettings = array();

    /**
     * @param string $name
     * @param string|int|float|bool|array|NULL $defaultValue
     * @return void
     */
    public static function registerOptionalSetting(string $name, $defaultValue=null) : void
    {
        if(!isset(self::$knownSettings[$name])) {
            self::$knownSettings[$name] = array(
                'required' => false,
                'defaultValue' => null
            );
        }
        
        self::$knownSettings[$name]['defaultValue'] = $defaultValue;
    }
    
    public static function registerRequiredSetting(string $name) : void
    {
        if(!isset(self::$knownSettings[$name])) {
            self::$knownSettings[$name] = array(
                'required' => true,
                'defaultValue' => null
            );
        }
        
        self::$knownSettings[$name]['required'] = true;
    }
    
   /**
    * Retrieves a constant at boot time, from either an existing
    * constant or the default value of a registered constant.
    * 
    * @param string $name
    * @return string|number|bool|NULL
    */
    public static function getSetting(string $name)
    {
        if(defined($name))
        {
            return constant($name);
        }
        
        if(isset(self::$knownSettings[$name]))
        {
            return self::$knownSettings[$name]['defaultValue'];
        }
        
        return null;
    }
    
   /**
    * Registers all default values for configuration 
    * settings as well as which of those are required.
    */
    protected static function registerConfigSettings() : void
    {
        self::registerRequiredSetting(BaseConfigRegistry::CLASS_NAME);
        self::registerRequiredSetting(BaseConfigRegistry::INSTANCE_ID);
        self::registerRequiredSetting(BaseConfigRegistry::CONTENT_LOCALES);
        self::registerRequiredSetting(BaseConfigRegistry::URL);
        self::registerRequiredSetting(BaseConfigRegistry::REQUEST_LOG_PASSWORD);
        
        self::registerOptionalSetting(BaseConfigRegistry::DB_ENABLED, true);

        self::registerOptionalSetting(BaseConfigRegistry::DEMO_MODE, false);
        self::registerOptionalSetting(BaseConfigRegistry::LOGGING_ENABLED, false);
        self::registerOptionalSetting(BaseConfigRegistry::SHOW_QUERIES, false);
        self::registerOptionalSetting(BaseConfigRegistry::JAVASCRIPT_MINIFIED, true);
        self::registerOptionalSetting(BaseConfigRegistry::AUTOMATIC_DELETION_DELAY, 60*60*24*5);
        self::registerOptionalSetting(BaseConfigRegistry::SIMULATE_SESSION, false);
    }
    
   /**
    * Initializes the application's boot process: sets up
    * configuration settings and includes configuration files,
    * starts the autoloader and include paths.
    *  
    * @throws Exception
    */
    public static function init() : void
    {
        if(self::$initialized)
        {
            return;
        }

        self::$initialized = true;

        self::initAutoLoader();
        self::initIncludePath();
        self::registerConfigSettings();
        self::initConfiguration();
        self::validateConfigSettings();
    }
    
    public static function convertException(Exception $e) : Application_Exception
    {
        // Handle the case where the DB is not installed
        if($e instanceof DBHelper_Exception)
        {
            $code = $e->getCode();
            
            switch ($code)
            {
                case DBHelper::ERROR_FETCHING:
                case DBHelper::ERROR_PREPARING_QUERY:
                case DBHelper::ERROR_EXECUTING_QUERY:
                    try {
                        DBHelper::getTablesList();
                    } catch (Exception $f) {
                        die(sprintf(
                            '<pre><b style="color:#cc0000">Error:</b> There are no tables in the configured database %1$s. Please import the tables structure first.</pre>',
                            '<b>' . APP_DB_NAME . '</b>'
                        ));
                    }
                    break;
            }
        }

        if($e instanceof Application_Exception)
        {
            return $e;
        }

        // Convert AppUtils base exceptions, retaining the
        // details if present.
        if($e instanceof BaseException)
        {
            return new Application_Exception(
                $e->getMessage(),
                $e->getDetails(),
                $e->getCode(),
                $e
            );
        }

        // Convert non-framework exceptions, so we can log them
        // as well - otherwise, they will not be viewable in the
        // error log.
        return new Application_Exception(
            'Non-framework exception: ' . $e->getMessage(),
            sprintf(
                'Encountered an exception of type [%s].',
                get_class($e)
            ),
            self::ERROR_NON_FRAMEWORK_EXCEPTION,
            $e
        );
    }

    public static function getVendorPath() : string
    {
        if(defined('APP_VENDOR_PATH'))
        {
            return APP_VENDOR_PATH;
        }

        return APP_ROOT.'/vendor';
    }

    private static function initAutoLoader() : void
    {
        $autoloadPath = self::getVendorPath() . '/autoload.php';
        $autoloadFile = realpath($autoloadPath);

        if ($autoloadFile === false)
        {
            throw new BootException(
                'Composer autoload file not found',
                sprintf(
                    'Autoloader not found in path [%s].',
                    $autoloadPath
                ),
                self::ERROR_AUTOLOAD_FILE_NOT_FOUND
            );
        }

        // For the autoloader, we use the handy PHP5.2 compatible
        // autoloader that is created by the php52 composer
        // dependency.
        $loader = require $autoloadFile;

        if (!$loader instanceof ClassLoader)
        {
            throw new BootException(
                'Missing autoloader instance',
                'Autoloader error: no autoloader instance returned on require.',
                self::ERROR_AUTOLOADER_NOT_STARTED
            );
        }

        self::$autoLoader = $loader;
    }

    private static function initIncludePath() : void
    {
        // Set include paths after the autoloader: this
        // enables the possibility to have classes from the
        // vendor folder be overridden by those in the application
        // like HTML_QuickForm2 classes, for example.
        $includePaths = array(
            APP_INSTALL_FOLDER . '/classes',
            APP_ROOT . '/assets',
            APP_ROOT . '/assets/classes',
            ini_get('include_path')
        );

        ini_set('include_path', implode(PATH_SEPARATOR, $includePaths));
    }

    private const CONFIG_MISSING_MESSAGE = <<<'EOT'
The configuration file %1$s was not found.

Please create this file first. There is usually a %2$s file
that you can use as template. Adjust the relevant configuration 
settings, then reload this page.
EOT;

    private static function initConfiguration() : void
    {
        $configs = array(
            APP_ROOT . '/config/app-config.php', // First, contains only static settings
            APP_ROOT . '/config/config-local.php' // Second, contains dynamic settings
        );

        foreach ($configs as $configFile)
        {
            if (file_exists($configFile)) {
                require_once $configFile;
            }
        }
    }

    private static function validateConfigSettings() : void
    {
        // set default configuration values
        foreach (self::$knownSettings as $name => $def)
        {
            if (isset($def['defaultValue']) && !defined($name))
            {
                define($name, $def['defaultValue']);
            }
        }

        // ensure that the DB configuration is required when enabled
        if (Application::isDatabaseEnabled())
        {
            self::registerRequiredSetting(BaseConfigRegistry::DB_HOST);
            self::registerRequiredSetting(BaseConfigRegistry::DB_NAME);
            self::registerRequiredSetting(BaseConfigRegistry::DB_USER);
            self::registerRequiredSetting(BaseConfigRegistry::DB_PASSWORD);
        }

        // check required configuration values
        foreach (self::$knownSettings as $name => $def)
        {
            if (!$def['required'])
            {
                continue;
            }

            if (!defined($name))
            {
                header('Content-Type:text/plain; charset=UTF-8');

                die(sprintf(
                    'The %1$s configuration setting is missing. ' .
                    'Please edit the relevant configuration file to add it.',
                    $name
                ));
            }
        }

        // Automated install URL detection: use the relative
        // path after the APP_ROOT setting.
        if (!defined('APP_INSTALL_URL'))
        {
            $relative = ltrim(str_replace(APP_ROOT, '', APP_INSTALL_FOLDER), '/');
            define('APP_INSTALL_URL', APP_URL . '/' . $relative);
        }
    }
}

/**
 * Used to add a configuration setting at app
 * boot time (in the <code>app-config.php</code> or <code>config-local.php</code>).
 * 
 * @param string $name
 * @param string|int|float|bool|array|NULL $value
 */
function boot_define(string $name, $value)
{
    Application_Bootstrap::registerOptionalSetting($name, $value);
}

/**
 * Retrieves a constant value at boot time, either
 * from a registered setting or an actual constant.
 * 
 * @param string $name
 * @return string|number|bool|NULL The value, or NULL if it does not exist.
 */
function boot_constant(string $name)
{
    return Application_Bootstrap::getSetting($name);
}

/**
 * Checks whether the specified constant has been defined 
 * at boot time, either as actual constant or as registered
 * boot define.
 * 
 * @param string $name
 * @return bool
 */
function boot_defined(string $name) : bool
{
    $value = boot_constant($name);
    return $value !== null;
}

/**
 * Adds a setting to the list of required settings that must be
 * defined during the boot process.
 *
 * @param string $name
 * @return void
 */
function boot_require(string $name) : void
{
    Application_Bootstrap::registerRequiredSetting($name);
}
