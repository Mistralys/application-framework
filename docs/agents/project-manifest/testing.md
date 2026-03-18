# Testing

Comprehensive guide to the Application Framework's test infrastructure, conventions, and execution.

---

## Overview

| Aspect | Detail |
|---|---|
| **Framework** | PHPUnit >= 13.0 |
| **PHP version** | 8.4+ |
| **Config file** | `phpunit.xml` (project root) |
| **Bootstrap** | `tests/bootstrap.php` |
| **Test count** | ~155 unit test files + 2 integration test files |
| **Test suite** | Single suite: `Framework Tests` (all tests under `tests/AppFrameworkTests/`) |

---

## Directory Structure

```
tests/
├── bootstrap.php                    # PHPUnit bootstrap — boots the framework test application
├── AppFrameworkTests/               # All unit test files (one folder per module)
│   ├── Admin/
│   ├── Ajax/
│   ├── API/
│   ├── AppFactory/
│   ├── Application/
│   ├── AppSets/
│   ├── AppSettings/
│   ├── Collection/
│   ├── Composer/
│   ├── Connectors/
│   ├── Countries/
│   ├── DataGrids/
│   ├── DBHelper/
│   ├── DeploymentRegistry/
│   ├── Disposables/
│   ├── Driver/
│   ├── ErrorLog/
│   ├── EventHandling/
│   ├── Eventables/
│   ├── Forms/
│   ├── Functions/
│   ├── Global/
│   ├── Helpers/
│   ├── Installer/
│   ├── LDAP/
│   ├── Locales/
│   ├── MarkdownParser/
│   ├── Media/
│   ├── News/
│   ├── OAuth/
│   ├── Ratings/
│   ├── RequestLogTests/
│   ├── Revisionables/
│   ├── SessionTests/
│   ├── SourceFolders/
│   ├── SystemMail/
│   ├── Tags/
│   ├── TypeHinter/
│   ├── UI/
│   ├── User/
│   ├── Users/
│   └── Validatable/
├── AppFrameworkIntegrationTests/    # Integration tests
│   ├── LDAP/
│   └── Logging/
├── AppFrameworkTestClasses/         # Base test cases, traits, mocks, stubs
│   ├── API/                         # API test cases and stubs
│   ├── Collection/
│   ├── Stubs/                       # Stub implementations for testing
│   ├── Traits/                      # Shared test traits (paired with interfaces)
│   ├── ApplicationTestCase.php      # Primary base test case (extends PHPUnit TestCase)
│   ├── ApplicationTestCaseInterface.php
│   └── ...                          # Domain-specific base test cases
├── application/                     # Framework Test Application (working implementation)
│   └── assets/classes/              # Test application classes
├── assets/                          # Test fixtures
├── files/                           # Test-related files
├── phpstan/                         # PHPStan test-related config
└── sql/                             # Source SQL files for the test database
```

---

## Running Tests

### Composer Scripts

All test execution goes through Composer scripts defined in `composer.json`:

| Command | Purpose | Example |
|---|---|---|
| `composer test-file -- <path>` | Run a single test file | `composer test-file -- tests/AppFrameworkTests/DBHelper/CollectionTest.php` |
| `composer test-filter -- <pattern>` | Run tests matching a name filter | `composer test-filter -- CollectionTest::testSomeMethod` |
| `composer test-suite -- <name>` | Run all tests in a named suite | `composer test-suite -- "Framework Tests"` |
| `composer test-group -- <group>` | Run tests in a PHPUnit group | `composer test-group -- SomeGroup` |

All scripts pass `--no-progress` to PHPUnit by default (except `composer test`).

### Choosing the Right Scope

1. **Changed a single class?** → `composer test-file` with its test file.
2. **Changed a module?** → `composer test-filter -- ModuleName` to match by class name.
3. **Unsure which tests cover a change?** → `composer test-filter -- ClassName`.
4. **Full suite** → `composer test` (runs all tests).

---

## Base Test Cases

All tests extend `ApplicationTestCase`, which extends PHPUnit's `TestCase`. Specialized base classes provide domain-specific setup:

| Base Class | Namespace | Purpose |
|---|---|---|
| `ApplicationTestCase` | `AppFrameworkTestClasses` | Primary base — boots the application, provides common helpers, transaction management |
| `AjaxTestCase` | `AppFrameworkTestClasses` | AJAX method testing |
| `APITestCase` | `AppFrameworkTestClasses\API` | API method testing |
| `APIClientTestCase` | `AppFrameworkTestClasses\API` | API client testing |
| `CountriesTestCase` | `AppFrameworkTestClasses` | Country collection tests |
| `DBHelperTestCase` | `AppFrameworkTestClasses` | Database helper tests |
| `FormTestCase` | `AppFrameworkTestClasses` | Form building/validation tests |
| `LDAPTestCase` | `AppFrameworkTestClasses` | LDAP connectivity tests |
| `MediaTestCase` | `AppFrameworkTestClasses` | Media library tests |
| `NewsTestCase` | `AppFrameworkTestClasses` | News central tests |
| `RequestLogTestCase` | `AppFrameworkTestClasses` | Request logging tests |
| `RevisionableTestCase` | `AppFrameworkTestClasses` | Revisionable record tests |
| `TaggingTestCase` | `AppFrameworkTestClasses` | Tagging system tests |
| `UserTestCase` | `AppFrameworkTestClasses` | User management tests |

---

## Shared Test Traits

Reusable test behavior is implemented via trait + interface pairs in `tests/AppFrameworkTestClasses/Traits/`. A test class implements the interface and uses the trait:

| Trait | Interface | Purpose |
|---|---|---|
| `ConnectorTestTrait` | `ConnectorTestInterface` | Connector testing |
| `DBHelperTestTrait` | `DBHelperTestInterface` | DBHelper testing |
| `DataGridTestTrait` | — | DataGrid testing |
| `ImageMediaTestTrait` | `ImageMediaTestInterface` | Image media testing |
| `MythologyTestTrait` | `MythologyTestInterface` | Test application mythology testing |
| `OperationResultTestTrait` | `OperationResultTestInterface` | Operation result testing |
| `RevisionableTestTrait` | — | Revisionable record testing |

API-specific traits in `tests/AppFrameworkTestClasses/API/`:

| Trait | Interface | Purpose |
|---|---|---|
| `APIClientTestTrait` | `APIClientTestInterface` | API client testing |
| `APIMethodTestTrait` | `APIMethodTestInterface` | API method testing |

---

## Bootstrap Process

The test bootstrap (`tests/bootstrap.php`):

1. Defines `APP_ROOT` pointing to `tests/application/` (the framework test application).
2. Defines `TESTS_ROOT`, `APP_INSTALL_FOLDER`, `APP_VENDOR_PATH` constants.
3. Sets `APP_FRAMEWORK_TESTS = true` to signal the framework's own test suite.
4. Requires the framework bootstrap (`src/classes/Application/Bootstrap/Bootstrap.php`).
5. Initializes `Application_Bootstrap` and boots the `TestSuiteBootstrap` class.

This means the full application stack is available in tests (database, services, configuration).

---

## Framework Test Application

The framework includes a working test application in `tests/application/` that provides concrete implementations of framework abstractions. This application is used by the test suite to exercise framework features against real database tables and screens.

---

## Local Test Environment Setup

The test suite requires local configuration files that are **not committed** to the repository. Before running tests for the first time, copy the distribution templates and configure them for the local environment:

1. Copy `tests/application/config/test-db-config.dist.php` → `tests/application/config/test-db-config.php`
2. Copy `tests/application/config/test-ui-config.dist.php` → `tests/application/config/test-ui-config.php`
3. Copy `tests/application/config/test-cas-config.dist.php` → `tests/application/config/test-cas-config.php`

Edit `test-db-config.php` and set the correct database host, name, user, and password. The database name defaults to `app_framework_testsuite`; import `tests/sql/testsuite.sql` to initialise it.

Edit `test-ui-config.php` and adjust `TESTS_BASE_URL` to the URL at which `tests/application` is reachable on the local webserver.

`test-cas-config.php` is only required when `TESTS_SESSION_TYPE` is set to `CAS` in `test-ui-config.php`. For most local runs the default `NoAuth` session type is sufficient and the CAS config can be left as-is.

---

## Stubs

Located in `tests/AppFrameworkTestClasses/Stubs/`:

| Stub | Purpose |
|---|---|
| `ClientFormStub` | Client form stub |
| `HiddenVariablesStub` | Hidden variables stub |
| `IDTableCollectionStub` | ID table collection stub |
| `LegacyUIRenderableStub` | Legacy UI renderable stub |
| `PropertizableStub` | Propertizable stub |
| `StringableStub` | Stringable stub |
| `ValidatableStub` | Validatable stub |

Additional stubs exist in subdirectories: `Stubs/Admin/`, `Stubs/DBHelper/`, `Stubs/Revisionables/`, `Stubs/Session/`, `Stubs/UI/`.

---

## API Test Stub Placement

API method stubs that invoke `processReturn()` must be placed in the test application's source directory:

```
tests/application/assets/classes/TestDriver/API/
```

**Do not** place such stubs in `tests/AppFrameworkTestClasses/` — they will not be discovered by the method index.

### Why this matters

`APIMethodParameter` validates that the method name passed to `processReturn()` exists in the framework's API method index. This index is built by scanning the test application's source folders (`tests/application/assets/classes/`). Classes in `AppFrameworkTestClasses/` are invisible to this discovery process and will trigger a "method not found" validation error.

Simple stubs that do not invoke `processReturn()` (e.g., those only used via `createStub()` / `createMock()`) can still reside in `AppFrameworkTestClasses/`.

---

## PHPUnit Mock Conventions

PHPUnit 13 emits a **Notice** — "No expectations were configured for the mock object for X"
— when `createMock()` is used without any `expects()` call. Use `createStub()` instead when
the test only needs a double that returns values (no call-count verification):

```php
// Correct — test only needs the object to return a value; no expectation needed
$method = $this->createStub(APIMethodInterface::class);

// Avoid — triggers a PHPUnit Notice when no expects() are added
$method = $this->createMock(APIMethodInterface::class);
```

When writing helper methods that return a test double, annotate the return type with
`&\PHPUnit\Framework\MockObject\Stub` (not `MockObject`) to match `createStub()`'s
actual return type:

```php
/** @return APIMethodInterface&\PHPUnit\Framework\MockObject\Stub */
private function createMethodStub() : APIMethodInterface
{
    return $this->createStub(APIMethodInterface::class);
}
```

Use `createMock()` only when the test explicitly verifies interaction (e.g., `expects(once())`).

