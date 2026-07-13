<?php

declare(strict_types=1);

/**
 * PHPStan memory limit floor.
 *
 * PHPStan 2.x removed the `memoryLimit` neon config key.
 * The `composer analyze` script passes `--memory-limit=900M`,
 * but agents and direct CLI invocations may omit it.
 * Setting it here guarantees the limit regardless of entry point.
 */
ini_set('memory_limit', '900M');

const APP_ENVIRONMENT = '';

// CAS test configuration constants - defined when tests/application/config/test-cas-config.php exists.
// These stubs allow PHPStan to analyse the CAS session class without the local config file.
const TESTS_CAS_FIELD_EMAIL = '';
const TESTS_CAS_FIELD_FIRST_NAME = '';
const TESTS_CAS_FIELD_LAST_NAME = '';
const TESTS_CAS_FIELD_FOREIGN_ID = '';
