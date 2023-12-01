<?php


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
