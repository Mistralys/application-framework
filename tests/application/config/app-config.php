<?php
/**
 * Application-specific static configuration settings:
 * These are all settings that do not change between
 * servers (those should be added to the config-local.php).
 *
 * @package TestDriver
 * @subpackage Config
 */

if(!function_exists('boot_define')) {
    die('May not be accessed directly.');
}

boot_define('APP_CLASS_NAME', 'TestDriver');
boot_define('APP_CONTENT_LOCALES','de_DE,en_UK');
boot_define('APP_UI_LOCALES', 'de_DE,en_UK');
boot_define('APP_REQUEST_LOG_PASSWORD', 'unit-tests');
