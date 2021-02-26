<?php

class Application_Localization
{
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
    
    public static function init()
    {
        self::initApplicationLocales();
        self::initContentLocales();
        self::initSources();
 
        $theme = UI::getInstance()->getTheme();
        $storageFile = Application::getTempFile('localization', 'json');
        $clientFolder = $theme->getDriverJavascriptsPath().'/localization';
        
        \AppUtils\FileHelper::createFolder($clientFolder);
        
        \AppLocalize\Localization::configure($storageFile, $clientFolder);
        
        // add an event handler for when the driver object
        // is instantiated.
        Application_EventHandler::addListener(
            'DriverInstantiated', 
            array(self::class, 'handle_driverInstantiated')
        );
    }
    
    public static function select()
    {
        self::selectAppLocale();
        self::selectContentLocale();
    }
    
    protected static function selectAppLocale()
    {
        $user = Application::getUser();
        $select = null;
        
        if($user instanceof Application_User)
        {
            $userLocale = $user->getSetting('locale');
            
            if(!empty($userLocale) && \AppLocalize\Localization::appLocaleExists($userLocale))
            {
                $select = $userLocale;
            }
        }
        
        if(isset($_REQUEST['application_locale']) && \AppLocalize\Localization::appLocaleExists($_REQUEST['application_locale']))
        {
            $select = $_REQUEST['application_locale'];
        }
        
        if($select !== null) {
            \AppLocalize\Localization::selectAppLocale($select);
        }
    }
    
    protected static function selectContentLocale()
    {
        $select = null;
        
        // locale as stored in the session
        $session = Application::getSession();
        $stored = $session->getValue('contentLocale');
        
        if(!empty($stored) && \AppLocalize\Localization::contentLocaleExists($stored)) {
            $select = $stored;
        }
        
        // locale as selected via the request
        if (isset($_REQUEST['locale']) && \AppLocalize\Localization::contentLocaleExists($_REQUEST['locale'])) {
            $select = $_REQUEST['locale'];
        }
        
        // store the selected locale name
        $session->setValue('contentLocale', $select);
        
        if($select !== null) {
            \AppLocalize\Localization::selectContentLocale($select);
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
    public static function handle_driverInstantiated(Application_EventHandler_Event_DriverInstantiated $event)
    {
        self::initCacheKey($event->getDriver());
    }
    
   /**
    * The cache key uses the driver's version string: this way, the
    * localization files are automatically refreshed with each release.
    * 
    * @param Application_Driver $driver
    */
    protected static function initCacheKey(Application_Driver $driver)
    {
        $key = $driver->getExtendedVersion();
        
        \AppLocalize\Localization::setClientLibrariesCacheKey($key);
        \AppLocalize\Localization::writeClientFiles();
    }
    
    protected static function initSources()
    {
        $theme = UI::getInstance()->getTheme();
        
        \AppLocalize\Localization::addSourceFolder(
            'application',
            'Classes and themes',
            'Framework',
            APP_INSTALL_FOLDER.'/localization',
            APP_INSTALL_FOLDER
        )
        ->excludeFolders(self::$excludeFolders)
        ->excludeFiles(self::$excludeFiles);
            
        \AppLocalize\Localization::addSourceFolder(
            'classes',
            'Classes',
            'Application',
            APP_ROOT.'/localization',
            APP_ROOT.'/assets'
        );
            
        \AppLocalize\Localization::addSourceFolder(
            'themes',
            'Themes',
            'Application',
            APP_ROOT.'/localization',
            $theme->getDriverPath()
        )
        ->excludeFolders(self::$excludeFolders)
        ->excludeFiles(self::$excludeFiles);
    }
    
    protected static function initApplicationLocales()
    {
        if(!defined('APP_UI_LOCALES')) {
            return;
        }
        
        $tokens = array_map('trim', explode(',', APP_UI_LOCALES));
        foreach ($tokens as $localeName) {
            \AppLocalize\Localization::addAppLocale($localeName);
        }
    }
    
    protected static function initContentLocales()
    {
        // add available content locales
        $tokens = array_map('trim', explode(',', APP_CONTENT_LOCALES));
        foreach ($tokens as $localeName) {
            \AppLocalize\Localization::addContentLocale($localeName);
        }
    }
    
    public static function getTranslationIcon(\AppLocalize\Localization_Scanner_StringHash $hash)
    {
        if($hash->isTranslated()) {
            return UI::icon()->ok()->makeSuccess();
        }
        
        return UI::icon()->notAvailable()->makeDangerous();
    }

    public static function injectJS(\AppLocalize\Localization_Scanner_StringHash $hash)
    {
        UI::getInstance()->addJavascriptHead(
            sprintf(
                "translator.addStringInfo('%1s', '%0s', '%0s', '%0s')",
                $hash->getHash(),
                addslashes($hash->getText()),
                addslashes($hash->getTranslatedText()),
                ''
            )
        );
    }
    
    const CUT_LENGTH = 80;
    
    public static function resolveDisplayText(\AppLocalize\Localization_Scanner_StringHash $hash)
    {
        $rawText = '';
        
        if ($hash->isTranslated())
        {
            $rawText = $hash->getTranslatedText();
        }
        else
        {
            $rawText = $hash->getText();
        }
        
        $text = strip_tags(stripslashes($rawText));
        $text = \AppUtils\ConvertHelper::text_cut($text, self::CUT_LENGTH, ' [...]');
        
        return $text;
    }
}