<?php

declare(strict_types=1);

use AppUtils\BaseException;
use Composer\Autoload\ClassLoader;

class Application_Bootstrap
{
    public const ERROR_INVALID_BOOTSTRAP_CLASS = 28101;
    public const ERROR_AUTOLOADER_NOT_STARTED = 28102; 
    public const ERROR_AUTOLOAD_FILE_NOT_FOUND = 28103; 
    public const ERROR_NON_FRAMEWORK_EXCEPTION = 28104;
    
    /**
     * @var ClassLoader
     */
    private static $autoLoader;

    /**
     * @var bool
     */
    private static $initialized = false;

    /**
     * @var string
     */
    private static $bootClass = '';

    /**
    * Boots from a standard application screen.
    * @param string $screenID
    * @param array $params
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
     * @return string
     */
    public static function getBootClass() : string
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
    * @param array $params
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
                throw new Application_Exception(
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
            // of the error page.
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
    
    protected static $knownSettings = array();
    
    public static function registerOptionalSetting($name, $defaultValue=null)
    {
        if(!isset(self::$knownSettings[$name])) {
            self::$knownSettings[$name] = array(
                'required' => false,
                'defaultValue' => null
            );
        }
        
        self::$knownSettings[$name]['defaultValue'] = $defaultValue;
    }
    
    public static function registerRequiredSetting($name)
    {
        if(!isset(self::$knownSettings[$name])) {
            self::$knownSettings[$name] = array(
                'required' => false,
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
        self::registerRequiredSetting('APP_CLASS_NAME');
        self::registerRequiredSetting('APP_INSTANCE_ID');
        self::registerRequiredSetting('APP_CONTENT_LOCALES');
        self::registerRequiredSetting('APP_URL');
        self::registerRequiredSetting('APP_REQUEST_LOG_PASSWORD');
        
        self::registerOptionalSetting('APP_DB_ENABLED', true);
        
        self::registerOptionalSetting('APP_DEMO_MODE', false);
        self::registerOptionalSetting('APP_LOGGING_ENABLED', false);
        self::registerOptionalSetting('APP_SHOW_QUERIES', false);
        self::registerOptionalSetting('APP_JAVASCRIPT_MINIFIED', true);
        self::registerOptionalSetting('APP_AUTOMATIC_DELETION_DELAY', 60*60*24*5);
        self::registerOptionalSetting('APP_SIMULATE_SESSION', false);
        self::registerOptionalSetting('APP_OPTIMIZE_IMAGES', false);
    }
    
   /**
    * Initializes the application's boot process: sets up
    * configuration settings and loads configuration files,
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

        self::registerConfigSettings();
        self::initAutoLoader();
        self::initIncludePath();
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
                        DBHelper::fetchTableNames();
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
            throw new Exception(
                sprintf('Autoload file not found in [%s]', $autoloadPath),
                self::ERROR_AUTOLOAD_FILE_NOT_FOUND
            );
        }

        // For the autoloader, we use the handy PHP5.2 compatible
        // autoloader that is created by the php52 composer
        // dependency.
        $loader = require $autoloadFile;

        if (!$loader instanceof ClassLoader)
        {
            throw new Exception(
                'Autoloader error: no autoloader instance returned on require.',
                self::ERROR_AUTOLOADER_NOT_STARTED
            );
        }

        self::$autoLoader = $loader;
    }

    private static function initIncludePath() : void
    {
        // Set the include paths after the autoloader: this
        // enables the possibility to have classes from the
        // vendor folder be overridden by those in the application
        // like for example the HTML_Quickform classes.
        $includePaths = array(
            APP_INSTALL_FOLDER . '/classes',
            APP_ROOT . '/assets',
            APP_ROOT . '/assets/classes',
            ini_get('include_path')
        );

        ini_set('include_path', implode(PATH_SEPARATOR, $includePaths));
    }

    private static function initConfiguration() : void
    {
        $localConfig = APP_ROOT . '/config/config-local.php';

        if (!file_exists($localConfig))
        {
            header('Content-Type:text/plain; charset=UTF-8');
            echo 'Local configuration file not found. ' . PHP_EOL;
            echo 'Rename the bundled file config-local.dist.php to config-local.php and adjust the relevant configuration settings in that file, then reload this page.' . PHP_EOL;
            exit;
        }

        /**
         * The local configuration settings
         */
        require $localConfig;

        /**
         * The main app configuration file
         */
        require_once APP_ROOT . '/config/app-config.php';
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

        if (boot_constant('APP_OPTIMIZE_IMAGES') === true)
        {
            self::registerRequiredSetting('APP_OPTIPNG_BINARY');
        }

        // ensure that the DB configuration is required when enabled
        if (Application::isDatabaseEnabled())
        {
            self::registerRequiredSetting('APP_DB_HOST');
            self::registerRequiredSetting('APP_DB_NAME');
            self::registerRequiredSetting('APP_DB_USER');
            self::registerRequiredSetting('APP_DB_PASSWORD');
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

        // automatic install URL detection: use the relative
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
 * boot time (in the app-config.php or config-local.php).
 * 
 * @param string $name
 * @param string|number|bool|NULL $value
 */
function boot_define(string $name, $value)
{
    Application_Bootstrap::registerOptionalSetting($name, $value);
}

/**
 * Adds a configuration setting to the list of
 * required settings that are checked after boot
 * has completed.
 * 
 * @param string $name
 */
function boot_require(string $name)
{
    Application_Bootstrap::registerRequiredSetting($name);
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
