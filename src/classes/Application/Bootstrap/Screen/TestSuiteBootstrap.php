<?php

declare(strict_types=1);

namespace Application\Bootstrap\Screen;

use Application\Application;
use Application\AppFactory;
use Application\Bootstrap\BootException;
use Application\ConfigSettings\BaseConfigRegistry;
use Application_Bootstrap_Screen;
use AppUtils\FileHelper;
use DBHelper;

class TestSuiteBootstrap extends Application_Bootstrap_Screen
{
    // Thrown when system users are missing from the test database; run "composer seed-tests" to seed them.
    public const int ERROR_TEST_DB_NOT_SEEDED = 175001;
    // Thrown when Application::getSystemUserIDs() returns an empty array (no system users configured).
    public const int ERROR_NO_SYSTEM_USERS_CONFIGURED = 175002;
    // Thrown when the expected tests root folder does not exist on disk.
    public const int ERROR_TESTS_FOLDER_NOT_FOUND = 175003;
    // Thrown when one or more required APP_DB_TESTS_* constants are not defined in the test config.
    public const int ERROR_TEST_DB_CONSTANTS_MISSING = 175004;

    protected function _boot() : void
    {
        $this->disableAuthentication();
        $this->enableScriptMode();

        $this->createEnvironment();

        if (!defined('APP_TESTS_RUNNING')) {
            define('APP_TESTS_RUNNING', true);
        }

        $this->configureDatabase();
        $this->registerTransactionCleanupHandler();
        $this->configurePaths();
        $this->configureUsers();
    }

    private function configureDatabase(): void
    {
        $requiredConstants = array(
            'APP_DB_TESTS_NAME',
            'APP_DB_TESTS_USER',
            'APP_DB_TESTS_PASSWORD',
            'APP_DB_TESTS_HOST'
        );

        $missing = array();
        foreach($requiredConstants as $name)
        {
            if(!defined($name))
            {
                $missing[] = $name;
            }
        }

        if(!empty($missing))
        {
            throw new BootException(
                'Test database constants not configured.',
                sprintf(
                    'The following required constants are not defined: [%s]. ' .
                    'Ensure they are set in the test configuration file.',
                    implode(', ', $missing)
                ),
                self::ERROR_TEST_DB_CONSTANTS_MISSING
            );
        }

        $port = 0;

        if (defined('APP_DB_TESTS_PORT')) {
            $port = (int)APP_DB_TESTS_PORT;
        }

        DBHelper::registerDB(
            'tests',
            APP_DB_TESTS_NAME,
            APP_DB_TESTS_USER,
            APP_DB_TESTS_PASSWORD,
            APP_DB_TESTS_HOST,
            $port
        );

        DBHelper::selectDB('tests');
    }

    private function configureUsers(): void
    {
        $users = AppFactory::createUsers();
        $systemUserIDs = Application::getSystemUserIDs();

        if(empty($systemUserIDs))
        {
            throw new BootException(
                'No system users configured.',
                'Application::getSystemUserIDs() returned an empty array. ' .
                'At least one system user ID must be configured for the test environment.',
                self::ERROR_NO_SYSTEM_USERS_CONFIGURED
            );
        }

        $missingIDs = array();

        foreach($systemUserIDs as $id)
        {
            if(!$users->idExists($id))
            {
                $missingIDs[] = $id;
            }
        }

        if(!empty($missingIDs))
        {
            throw new BootException(
                'Test database not seeded: system user(s) missing.',
                sprintf(
                    'The following system user IDs are missing from the test database: [%s]. '.
                    'Run "composer seed-tests" to initialize the test environment.',
                    implode(', ', $missingIDs)
                ),
                self::ERROR_TEST_DB_NOT_SEEDED
            );
        }

        $this->log('System users verified.');
    }

    /**
     * Seeds system users into the test database within a transaction.
     *
     * Starts a database transaction, runs the {@see InitSystemUsers} installer
     * task, and commits on success. On any failure the transaction is rolled
     * back via {@see DBHelper::rollbackConditional()} and the original
     * throwable is re-thrown.
     *
     * Invoke this method via the Composer helper before running the test suite:
     *
     * <pre>composer seed-tests</pre>
     *
     * @return void
     * @throws \Application_Exception If the {@see InitSystemUsers} task ID is
     *                                not found in the installer task registry.
     * @throws \Throwable             Re-throws any exception or error raised
     *                                during task execution after rolling back
     *                                the transaction.
     */
    public static function seedSystemUsers(): void
    {
        DBHelper::startTransaction();

        try
        {
            Application::createInstaller()
                ->getTaskByID('InitSystemUsers')
                ->process();

            DBHelper::commitTransaction();
        }
        catch(\Throwable $e)
        {
            DBHelper::rollbackConditional();
            throw $e;
        }
    }

    /**
     * Registers a PHP shutdown handler to roll back any open transaction if
     * the process exits unexpectedly. Called before {@see configurePaths()} so
     * that even a fatal error during path setup cannot leave the database in a
     * partially-modified state.
     */
    private function registerTransactionCleanupHandler(): void
    {
        register_shutdown_function(static function(): void {
            DBHelper::rollbackConditional();
        });
    }

    private function configurePaths(): void
    {
        $testsRoot = APP_ROOT . '/tests';

        if (BaseConfigRegistry::areUnitTestsRunning()) {
            $testsRoot = APP_ROOT;
        }

        if (!is_dir($testsRoot)) {
            throw new BootException(
                'Cannot run tests: tests folder not found.',
                sprintf(
                    'The expected tests folder [%s] does not exist.',
                    $testsRoot
                ),
                self::ERROR_TESTS_FOLDER_NOT_FOUND
            );
        }

        define('TESTS_ASSETS_FOLDER', TESTS_ROOT . '/assets');
        define('TESTS_CLASSES_FOLDER', TESTS_ASSETS_FOLDER . '/classes');

        if (is_dir(TESTS_CLASSES_FOLDER)) {
            $names = FileHelper::createFileFinder(TESTS_CLASSES_FOLDER)
                ->getPHPClassNames();

            // load all classes that may be needed for the tests to run.
            foreach ($names as $name) {
                require_once TESTS_CLASSES_FOLDER . '/' . $name . '.php';
            }
        }
    }

    public function getDispatcher() : string
    {
        return 'bootstrap.php';
    }
}
