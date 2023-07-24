<?php
/**
 * Configuration file for the test application UI.
 *
 * @package TestDriver
 * @subpackage Config
 */

/**
 * Absolute URL to the framework's root folder in the
 * local webserver. Used throughout the test application
 * to load clientside assets.
 *
 * The UI can be accessed in the browser at this URL,
 * adding the path <code>tests/application</code>.
 */
const TESTS_BASE_URL = 'http://127.0.0.1/application-framework';

/**
 * The type of session to use for the test application.
 *
 * - <code>NoAuth</code> - No authentication, no session (default): {@see \TestDriver\Session\TestSessionNoAuth}.
 * - <code>CAS</code> - CAS authentication, native session: {@see \TestDriver\Session\TestSessionCAS}.
 *                      Also see the CAS configuration settings below.
 */
const TESTS_SESSION_TYPE = 'NoAuth';


// ----------------------------------------------------------------
// CAS AUTHENTICATION
// ----------------------------------------------------------------

/**
 * The CAS server host name, e.g. <code>cas.example.com</code>
 */
const APP_CAS_HOST = 'cas.example.com';

/**
 * The CAS server port. Default is <code>443</code>.
 */
const APP_CAS_PORT = 443;

/**
 *  URL to reach the service, e.g. <code>https://cas.example.com:443/login</code>
 */
const APP_CAS_SERVER = 'https://'.APP_CAS_HOST.':'.APP_CAS_PORT.'/login';

// Field names in the CAS response.
const TESTS_CAS_FIELD_EMAIL = 'email';
const TESTS_CAS_FIELD_FIRST_NAME = 'firstname';
const TESTS_CAS_FIELD_LAST_NAME = 'lastname';
const TESTS_CAS_FIELD_FOREIGN_ID = 'id';