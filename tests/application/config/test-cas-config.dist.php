<?php

declare(strict_types=1);

/**
 * The CAS server host name, e.g. <code>cas.example.com</code>
 */

use AppFrameworkTestClasses\LDAP\LDAPTestCase;

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

const APP_LDAP_HOST = LDAPTestCase::LDAP_HOST;
const APP_LDAP_PORT = LDAPTestCase::LDAP_PORT;
const APP_LDAP_SSL_ENABLED = LDAPTestCase::LDAP_SSL_ENABLED;
const APP_LDAP_DN = LDAPTestCase::LDAP_DN;
const APP_LDAP_USERNAME = LDAPTestCase::LDAP_USERNAME;
const APP_LDAP_PASSWORD = LDAPTestCase::LDAP_PASSWORD;
const APP_LDAP_MEMBER_SUFFIX = LDAPTestCase::LDAP_MEMBER_SUFFIX;
