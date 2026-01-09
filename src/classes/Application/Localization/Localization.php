<?php
/**
 * @package Application
 * @subpackage Localization
 */

declare(strict_types=1);

use Application\Application;
use AppLocalize\Localization;
use AppLocalize\Localization\LocalizationException;
use AppLocalize\Localization_Scanner_StringHash;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;

/**
 * Handles the localization layer of the application: configures
 * the Application Localization package for the driver, and handles
 * other common localization related tasks.
 *
 * @package Application
 * @subpackage Localization
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @link https://github.com/Mistralys/application-localization
 */
class Application_Localization
{
    const CUT_LENGTH = 80;
    const REQUEST_PARAM_APPLICATION_LOCALE = 'application_locale';
    const REQUEST_PARAM_CONTENT_LOCALE = 'locale';

    /**
     * @var string[]
     */
    protected static $excludeFolders = array(
        'simpletest',
        'css',
        'img',
        'storage',
        'unit-tests',
        'unit_tests',
        'localization',
        'develdocs',
        'downloads',
        'rygnarok',
        'logs',
        'help',
        'config',
        'api',
        'users',
        'xml',
        'vendor'
    );

    /**
     * @var string[]
     */
    protected static $excludeFiles = array(
        '.min.js',
        'uri.js',
        'bootstrap.',
        'bootstrap-multiselect',
        'bootstrap-datepicker',
        'jquery-',
        'jquery.',
        'plupload',
        'ckeditor',
        'redactor'
    );

    /**
     * Initializes the localization layer during boot.
     *
     * @throws Application_EventHandler_Exception
     * @throws Application_Exception
     * @throws UI_Exception
     * @throws FileHelper_Exception
     *
     * @see Application_Bootstrap_Screen::createEnvironment()
     */
    public static function init() : void
    {
        self::initApplicationLocales();
        self::initContentLocales();
        self::initSources();
 
        $theme = UI::getInstance()->getTheme();
        $storageFile = Application::getTempFile('localization', 'json');
        $clientFolder = $theme->getDriverJavascriptsPath().'/localization';
        
        FileHelper::createFolder($clientFolder);
        
        Localization::configure($storageFile, $clientFolder);
        
        // add an event handler for when the driver object
        // is instantiated.
        Application_EventHandler::addListener(
            Application::EVENT_DRIVER_INSTANTIATED,
            array(self::class, 'handle_driverInstantiated')
        );
    }

    /**
     * Selects the initial locales during boot.
     *
     * @see Application_Bootstrap_Screen::createEnvironment()
     */
    public static function select() : void
    {
        self::selectAppLocale();
        self::selectContentLocale();
    }
    
    protected static function selectAppLocale() : void
    {
        $user = Application::getUser();
        $select = null;
        
        $userLocale = $user->getSetting(self::REQUEST_PARAM_CONTENT_LOCALE);

        if(!empty($userLocale) && Localization::appLocaleExists($userLocale))
        {
            $select = $userLocale;
        }

        if(isset($_REQUEST[self::REQUEST_PARAM_APPLICATION_LOCALE]) && Localization::appLocaleExists($_REQUEST[self::REQUEST_PARAM_APPLICATION_LOCALE]))
        {
            $select = $_REQUEST[self::REQUEST_PARAM_APPLICATION_LOCALE];
        }
        
        if($select !== null)
        {
            Localization::selectAppLocale($select);
        }
    }
    
    protected static function selectContentLocale() : void
    {
        $select = null;
        
        // locale as stored in the session
        $session = Application::getSession();
        $stored = $session->getValue('contentLocale');
        
        if(!empty($stored) && Localization::contentLocaleExists($stored)) {
            $select = $stored;
        }
        
        // locale as selected via the request
        if (isset($_REQUEST[self::REQUEST_PARAM_CONTENT_LOCALE]) && Localization::contentLocaleExists($_REQUEST[self::REQUEST_PARAM_CONTENT_LOCALE])) {
            $select = $_REQUEST[self::REQUEST_PARAM_CONTENT_LOCALE];
        }
        
        // store the selected locale name
        $session->setValue('contentLocale', $select);
        
        if($select !== null) {
            Localization::selectContentLocale($select);
        }
    }
    
   /**
    * Event handler for when the driver class has been instantiated:
    * now the cache key can be initialized since it is tied to the
    * driver's version number. This can only be retrieved once it has
    * been instantiated.
    * 
    * @param Application_EventHandler_Event_DriverInstantiated $event
    */
    public static function handle_driverInstantiated(Application_EventHandler_Event_DriverInstantiated $event) : void
    {
        self::initCacheKey($event->getDriver());
    }

    /**
     * The cache key uses the driver's version string: this way, the
     * localization files are automatically refreshed with each release.
     *
     * @param Application_Driver $driver
     * @throws LocalizationException
     */
    protected static function initCacheKey(Application_Driver $driver) : void
    {
        $key = $driver->getExtendedVersion();
        
        Localization::setClientLibrariesCacheKey($key);
        Localization::writeClientFiles();
    }

    /**
     * Initializes the localization categories available in
     * the translation UI for the application, for both the
     * PHP classes and Theme related files.
     *
     * @throws UI_Exception
     */
    protected static function initSources() : void
    {
        $theme = UI::getInstance()->getTheme();
        
        Localization::addSourceFolder(
            'application',
            'Framework classes and themes',
            'Framework',
            APP_INSTALL_FOLDER.'/localization',
            APP_INSTALL_FOLDER
        )
        ->excludeFolders(self::$excludeFolders)
        ->excludeFiles(self::$excludeFiles);
            
        Localization::addSourceFolder(
            'classes',
            APP_CLASS_NAME.' classes',
            'Application',
            APP_ROOT.'/localization',
            APP_ROOT.'/assets'
        );
            
        Localization::addSourceFolder(
            'themes',
            APP_CLASS_NAME.' themes',
            'Application',
            APP_ROOT.'/localization',
            $theme->getDriverPath()
        )
        ->excludeFolders(self::$excludeFolders)
        ->excludeFiles(self::$excludeFiles);
    }
    
    protected static function initApplicationLocales() : void
    {
        if(!defined('APP_UI_LOCALES')) {
            return;
        }
        
        $tokens = array_map('trim', explode(',', APP_UI_LOCALES));
        foreach ($tokens as $localeName) {
            Localization::addAppLocale($localeName);
        }
    }
    
    protected static function initContentLocales() : void
    {
        // add available content locales
        $tokens = array_map('trim', explode(',', APP_CONTENT_LOCALES));
        foreach ($tokens as $localeName) {
            Localization::addContentLocale($localeName);
        }
    }
    
    public static function getTranslationIcon(Localization\Scanner\StringHash $hash) : UI_Icon
    {
        if($hash->isTranslated()) {
            return UI::icon()->ok()->makeSuccess();
        }
        
        return UI::icon()->notAvailable()->makeDangerous();
    }

    public static function injectJS(Localization\Scanner\StringHash $hash) : void
    {
        $text = $hash->getText();
        $value = '';
        if($text !== null) {
            $value = $text->getText();
        }

        UI::getInstance()->addJavascriptHead(
            sprintf(
                "translator.addStringInfo('%1s', '%0s', '%0s', '%0s')",
                $hash->getHash(),
                addslashes($value),
                addslashes($hash->getTranslatedText()),
                ''
            )
        );
    }
    
    public static function resolveDisplayText(Localization\Scanner\StringHash $hash) : string
    {
        if ($hash->isTranslated())
        {
            $rawText = $hash->getTranslatedText();
        }
        else
        {
            $rawText = $hash->getText();
        }
        
        $text = strip_tags(stripslashes($rawText));

        return ConvertHelper::text_cut($text, self::CUT_LENGTH, ' [...]');
    }
}
