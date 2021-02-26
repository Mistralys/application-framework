<?php
/**
 * Application-specific static configuration settings:
 * These are all settings that do not change between
 * servers (those should be added to the config-local.php).
 *
 * @package TestDriver
 */

    if(!function_exists('boot_define')) {
        die('May not be accessed directly.');
    }
    
    // ------------------------------------------------
    // Static default framework settings
    // ------------------------------------------------
    
    // The class name should be set to the name of the
    // application's driver class, for example "Maileditor"
    boot_define('APP_CLASS_NAME', 'TestDriver');
    
    // Comma-separated list of available locales for the data
    boot_define('APP_CONTENT_LOCALES','de_DE,en_UK');
    
    // demo mode is relevant only for developers: when enabled, the user
    // is not considered a developer and none of the developer buttons
    // will be shown. This is mainly useful for taking screenshots.
    //boot_define('APP_DEMO_MODE', false);
    
    //boot_define('APP_LOGGING_ENABLED', false);
    
    //boot_define('APP_JAVASCRIPT_MINIFIED',true);
    
    //boot_define('APP_AUTOMATIC_DELETION_DELAY', 60*60*24*5);
    
    //boot_define('APP_SIMULATE_SESSION', false);
    
    //boot_define('APP_OPTIMIZE_IMAGES', false);
    