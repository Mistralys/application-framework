<?php
/**
 * IDE function insight helper.
 *
 * This file is not included anywhere, it is used merely as a helper
 * file for PHP IDEs to be able to discover application settings that
 * are not easily discoverable.
 * 
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */

declare(strict_types=1);

const APP_CONTENT_LOCALES = 'de_DE,en_GB';
const APP_UI_LOCALES = 'de_DE,en_GB';
const APP_AUTH_SALT = '';
const APP_URL = 'https://application.website';
const APP_VENDOR_URL = APP_URL.'/vendor';
const APP_ROOT = '/path/to/application';
const APP_CLASS_NAME = 'DriverClassName';
const APP_REQUEST_LOG_PASSWORD = 'password';
const APP_LOGGING_ENABLED = true;
const APP_SIMULATE_SESSION = false;
const APP_JAVASCRIPT_MINIFIED = true;
const APP_SHOW_QUERIES = false;
const APP_TRACK_QUERIES = false;
const APP_AUTOMATIC_DELETION_DELAY = 60 * 60 * 24 * 5;
const APP_DEVELOPER_MODE = true;
const APP_THEME = 'default';
const APP_ENVIRONMENT = 'local';
const APP_INSTANCE_ID = 'dummy';
const APP_RUN_MODE = Application::RUN_MODE_UI;
const APP_NO_AUTHENTICATION = false;
const APP_FRAMEWORK_TESTS = false;
const APP_TESTS_RUNNING = false;
const APP_DEMO_MODE = false;
const APP_APPSET = 'setname';
const APP_COMPANY_NAME = 'Company Name';
const APP_COMPANY_HOMEPAGE = 'https://company-homepage.website';
const APP_DUMMY_EMAIL = 'dummy@application.website';
const APP_SYSTEM_EMAIL = 'system@application.website';
const APP_SYSTEM_NAME = 'Application Name';
/**
 * List of email addresses that will receive developer-specific
 * information on a running application.
 *
 * Separate multiple addresses with a comma.
 *
 * NOTE: If left empty, no emails will be sent.
 * Errors can still be viewed in the error log.
 */
const APP_RECIPIENTS_DEV = '';

const APP_DB_ENABLED = true;
const APP_DB_HOST = 'localhost';
const APP_DB_NAME = 'application_db';
const APP_DB_USER = 'application_user';
const APP_DB_PASSWORD = 'password';
const APP_DB_PORT = 3306;

const APP_DB_TESTS_NAME = 'application_tests_db';
const APP_DB_TESTS_USER = 'application_tests_user';
const APP_DB_TESTS_PASSWORD = 'password';
const APP_DB_TESTS_HOST = 'localhost';
const APP_DB_TESTS_PORT = 3306;
	
const APP_CAS_HOST = 'login.cas-host.example';
const APP_CAS_PORT = 443;
const APP_CAS_SERVER = 'https://'.APP_CAS_HOST.':'.APP_CAS_PORT.'/servername';
const APP_CAS_LOGOUT_URL = APP_CAS_SERVER.'/logout';
const APP_CAS_NAME = 'Login Service';

const APP_DEEPL_API_KEY = 'apikey';
const APP_DEEPL_PROXY_URL = 'proxy.example:3128';
const APP_DEEPL_PROXY_ENABLED = true;

die('The function insight file may not be included anywhere.');
