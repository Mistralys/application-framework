<?php

use PHPUnit\Framework\TestCase;
use AppLocalize\Localization_Locale;
use AppLocalize\Localization;

abstract class ApplicationTestCase extends TestCase
{
    /**
     * @var array<string,int>
     */
    private static $counter = array();

    protected function logHeader(string $testName) : void
    {
        Application::logHeader(get_class($this).' | '.$testName);
    }

    protected function createEntryID() : string
    {
        return 'entry'.$this->getTestCounter();
    }

    protected function getTestCounter(string $name='') : int
    {
        if(empty($name)) {
            $name = '__default';
        }

        if(!isset(self::$counter[$name])) {
            self::$counter[$name] = 0;
        }

        self::$counter[$name]++;

        return self::$counter[$name];
    }

    protected function tearDown() : void
    {
        $this->clearTransaction();
        $this->disableLogging();
    }

    protected function startTransaction()
    {
        DBHelper::startConditional();
    }

    protected function clearTransaction()
    {
        DBHelper::rollbackConditional();
        DBHelper::disableDebugging();
    }

    protected function startTest(string $name) : void
    {
        Application::logHeader(getClassTypeName(get_class($this)).' - '.$name);
    }

    protected function enableLogging() : void
    {
        Application::getLogger()->logModeEcho();
    }

    protected function disableLogging() : void
    {
        Application::getLogger()->logModeNone();
    }

    protected function createTestLocale(string $name='') : Localization_Locale
    {
        if(empty($name))
        {
            $names = DBHelper::createFetchMany('locales_application')
                ->fetchColumn('locale_name');

            $name = $names[array_rand($names)];
        }

        return Localization::getAppLocaleByName($name);
    }

    protected function isRunViaApplication() : bool
    {
        return boot_defined('APP_FRAMEWORK_TESTS') !== true;
    }

    protected function skipIfRunViaApplication() : bool
    {
        if ($this->isRunViaApplication())
        {
            $this->markTestSkipped();
        }

        return false;
    }

    protected function createTestFormable(array $defaultValues=array()) : Application_Formable_Generic
    {
        $formable = new Application_Formable_Generic();
        $formable->createFormableForm('formable-'.$this->getTestCounter(), $defaultValues);

        return $formable;
    }

    /**
     * @var UI|null
     */
    protected static $ui = null;

    protected function createUI() : UI
    {
        if(!isset(self::$ui))
        {
            self::$ui = UI::getInstance();

            if(!self::$ui->hasPage())
            {
                self::$ui->setPage(new UI_Page(self::$ui, 'unit-tests'));
            }
        }

        return self::$ui;
    }
}
