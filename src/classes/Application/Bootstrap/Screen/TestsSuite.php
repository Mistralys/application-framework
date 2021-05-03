<?php

class Application_Bootstrap_Screen_TestsSuite extends Application_Bootstrap_Screen
{
    protected function _boot()
    {
        $this->disableAuthentication();
        $this->enableScriptMode();

        $this->createEnvironment();

        define('APP_TESTS_RUNNING', true);

        $this->configureDatabase();
        $this->configurePaths();
        $this->configureUsers();
    }

    private function configureDatabase() : void
    {
        $port = 0;

        if (defined('APP_DB_TESTS_PORT')) {
            $port = intval(APP_DB_TESTS_PORT);
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

    private function configureUsers() : void
    {
        DBHelper::startTransaction();
        Application::createInstaller()->getTaskByID('InitSystemUsers')->process();
        DBHelper::commitTransaction();
    }

    private function configurePaths() : void
    {
        $testsRoot = APP_ROOT.'/tests';

        if(boot_constant('APP_FRAMEWORK_TESTS') === true)
        {
            $testsRoot = APP_ROOT;
        }

        if(!is_dir($testsRoot))
        {
            die('Cannot run tests: Could not find the application\'s [tests] folder.');
        }

        define('TESTS_ROOT', $testsRoot);
        define('TESTS_ASSETS_FOLDER', TESTS_ROOT.'/assets');
        define('TESTS_CLASSES_FOLDER', TESTS_ASSETS_FOLDER.'/classes');

        if(is_dir(TESTS_CLASSES_FOLDER))
        {
            $names = \AppUtils\FileHelper::createFileFinder(TESTS_CLASSES_FOLDER)
                ->getPHPClassNames();

            // load all classes that may be needed for the tests to run.
            foreach($names as $name)
            {
                require_once TESTS_CLASSES_FOLDER.'/'.$name.'.php';
            }
        }
    }

    public function getDispatcher()
    {
        return 'bootstrap.php';
    }
}
