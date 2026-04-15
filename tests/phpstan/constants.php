<?php

declare(strict_types=1);

const APP_ENVIRONMENT = '';

// CAS test configuration constants - defined when tests/application/config/test-cas-config.php exists.
// These stubs allow PHPStan to analyse the CAS session class without the local config file.
const TESTS_CAS_FIELD_EMAIL = '';
const TESTS_CAS_FIELD_FIRST_NAME = '';
const TESTS_CAS_FIELD_LAST_NAME = '';
const TESTS_CAS_FIELD_FOREIGN_ID = '';
