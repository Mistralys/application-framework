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
 *                      This requires the <code>test-cas-config.php</code> to be present.
 */
const TESTS_SESSION_TYPE = 'NoAuth';

/**
 * List of email addresses to send system emails to.
 */
const TESTS_SYSTEM_EMAIL_RECIPIENTS = 'name@host.domain';
