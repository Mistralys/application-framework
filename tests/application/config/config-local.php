<?php
/**
 * Main configuration file for the application framework
 * test suite.
 * 
 * @package TestDriver
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

   /**
    * Error code for an exception.
    * @var int
    */
    define('ERROR_TESTSUITE_CONFIG_MISSING', 29701);

    $testConfig = __DIR__.'/test-db-config.php';
    if(!file_exists($testConfig)) {
        throw new Exception(
            sprintf(
                'To enable the testsuite, please create the [%s] file.',
                basename($testConfig)
            ),
            ERROR_TESTSUITE_CONFIG_MISSING
        );
    }
    
    require_once $testConfig;

	boot_define('APP_URL', '');
    boot_define('APP_INSTANCE_ID', '');
	boot_define('APP_DB_HOST', TESTSUITE_DB_HOST);
	boot_define('APP_DB_NAME', TESTSUITE_DB_NAME);
	boot_define('APP_DB_USER', TESTSUITE_DB_USER);
	boot_define('APP_DB_PASSWORD', TESTSUITE_DB_PASSWORD);
	boot_define('APP_DB_TESTS_HOST', TESTSUITE_DB_HOST);
	boot_define('APP_DB_TESTS_NAME', TESTSUITE_DB_NAME);
	boot_define('APP_DB_TESTS_USER', TESTSUITE_DB_USER);
	boot_define('APP_DB_TESTS_PASSWORD', TESTSUITE_DB_PASSWORD);
	boot_define('APP_FEEDBACK_RECIPIENT_EMAIL', 's.mordziol@gmail.com');
	boot_define('APP_FEEDBACK_SENDFROM_EMAIL', 's.mordziol@gmail.com');
    boot_define('APP_SIMULATE_SESSION', true);
	boot_define('APP_DEVEL_ENVIRONMENT', true);
	boot_define('APP_PROXY_ENABLED', false);
	boot_define('APP_OPTIMIZE_IMAGES', false);
	boot_define('APP_LOGGING_ENABLED', true);
    boot_define('APP_JAVASCRIPT_MINIFIED', false);
    boot_define('APP_SHOW_QUERIES', true);
    boot_define('APP_TRACK_QUERIES', true);
    boot_define('APP_AUTH_SALT', 'dummy_salt');
    boot_define('APP_SYSTEM_EMAIL', 'testsuite@appframework.system');
    boot_define('APP_DUMMY_EMAIL', 'dummy@appframework.system');
    boot_define('APP_SYSTEM_NAME', 'AppFramework');

    boot_define('APP_LDAP_HOST', '');
    boot_define('APP_LDAP_PORT', 0);
    boot_define('APP_LDAP_DN', '');
    boot_define('APP_LDAP_PASSWORD', '');
    boot_define('APP_LDAP_MEMBER_SUFFIX', '');
    
