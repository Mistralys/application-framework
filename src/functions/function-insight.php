<?php
/**
 * Function insight helper. This file is not included anywhere,
 * it is used merely as a helper file for PHP IDEs to be able
 * to discover application settings that are not easily discoverable.
 * 
 * @package Maileditor
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

	// default defines
	define('APP_CONTENT_LOCALES', '');
	define('APP_AUTH_SALT', '');
	define('APP_URL', '');
	define('APP_ROOT', '');
	define('APP_CLASS_NAME', '');
	define('APP_LOGGING_ENABLED', true);
	define('APP_SIMULATE_SESSION', false);
	define('APP_OPTIMIZE_IMAGES', true);
	define('APP_JAVASCRIPT_MINIFIED', true);
	define('APP_SHOW_QUERIES', false);
	define('APP_AUTOMATIC_DELETION_DELAY', 5);
	define('APP_DEVELOPER_MODE', true);
	define('APP_GRAPHICSMAGIC_PATH', '');
	define('APP_THEME', 'default');
	
   /**
    * Can be "access" or "hosting"
    */
	define('APP_INSTANCE_ID', 'dummy');
	define('APP_RUN_MODE', 'mode');
	define('APP_NO_AUTHENTICATION', false);
	define('APP_DEMO_MODE', false);
	
   /**
    * The application set to use. If not defined, defaults to all admin areas.
    * @optional
    */	
	define('APP_APPSET', null);
	
	define('APP_DB_ENABLED', null);
	define('APP_DB_HOST', null);
	define('APP_DB_NAME', null);
	define('APP_DB_USER', null);
	define('APP_DB_PASSWORD', null);
	define('APP_DB_PORT', null);
	
	define('APP_DB_TESTS_NAME', null);
	define('APP_DB_TESTS_USER', null);
    define('APP_DB_TESTS_PASSWORD', null);
	define('APP_DB_TESTS_HOST', null);
    define('APP_DB_TESTS_PORT', null);
	
	// OMS SVN
	define('APP_OMSSVN_LOCAL_PATH', null);
	define('APP_OMSSVN_REMOTE_URL', null);
	define('APP_SVN_BINARIES_PATH', null);
	define('APP_OMSSVN_TEST_REQUESTS_PATH', null);
	
	// rygnarok
	define('APP_RYGNAROK_URL', null);
	define('APP_RYGNAROK_MEDIA_PATH', null);
	define('APP_RYGNAROK_MEDIA_BASEURL', null);
	define('APP_RYGNAROK_OPCACHE_DELAY', null);	
	
	define('PROMS_SERVER', null);
	define('PROMS_USERNAME', null);
	define('PROMS_PASSWORD', null);