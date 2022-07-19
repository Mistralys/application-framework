<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use Application;
use Application_Formable_Generic;
use DBHelper;
use PHPUnit\Framework\TestCase;
use AppLocalize\Localization_Locale;
use AppLocalize\Localization;
use UI;
use UI_Page;

abstract class ApplicationTestCase extends TestCase
{
    /**
     * @var array<string,int>
     */
    private static array $counter = array();

    protected function logHeader(string $testName): void
    {
        Application::logHeader(get_class($this) . ' | ' . $testName);
    }

    protected function createEntryID(): string
    {
        return 'entry' . $this->getTestCounter();
    }

    protected function getTestCounter(string $name = ''): int
    {
        if (empty($name)) {
            $name = '__default';
        }

        if (!isset(self::$counter[$name])) {
            self::$counter[$name] = 0;
        }

        self::$counter[$name]++;

        return self::$counter[$name];
    }

    protected function tearDown(): void
    {
        $this->clearTransaction();
        $this->disableLogging();
    }

    protected function startTransaction() : void
    {
        DBHelper::startConditional();
    }

    protected function clearTransaction() : void
    {
        DBHelper::rollbackConditional();
        DBHelper::disableDebugging();
    }

    protected function startTest(string $name): void
    {
        Application::logHeader(getClassTypeName(get_class($this)) . ' - ' . $name);
    }

    protected function enableLogging(): void
    {
        Application::getLogger()->logModeEcho();
    }

    protected function disableLogging(): void
    {
        Application::getLogger()->logModeNone();
    }

    protected function createTestLocale(string $name = ''): Localization_Locale
    {
        if (empty($name)) {
            $names = DBHelper::createFetchMany('locales_application')
                ->fetchColumn('locale_name');

            $name = $names[array_rand($names)];
        }

        return Localization::getAppLocaleByName($name);
    }

    protected function isRunViaApplication(): bool
    {
        return boot_defined('APP_FRAMEWORK_TESTS') !== true;
    }

    protected function skipIfRunViaApplication(): bool
    {
        if ($this->isRunViaApplication()) {
            $this->markTestSkipped();
        }

        return false;
    }

    protected function createTestFormable(array $defaultValues = array()): Application_Formable_Generic
    {
        $formable = new Application_Formable_Generic();
        $formable->createFormableForm('formable-' . $this->getTestCounter(), $defaultValues);

        return $formable;
    }

    protected static ?UI $ui = null;

    protected function createUI(): UI
    {
        if (!isset(self::$ui)) {
            self::$ui = UI::getInstance();

            if (!self::$ui->hasPage()) {
                self::$ui->setPage(new UI_Page(self::$ui, 'unit-tests'));
            }
        }

        return self::$ui;
    }

    public function html2string(string $html): string
    {
        $replaces = array(
            '</p>' => PHP_EOL,
            '</ul>' => PHP_EOL,
            '</li>' => PHP_EOL,
            '<br>' => PHP_EOL,
            '</br>' => PHP_EOL,
            '</div>' => PHP_EOL,
            '</table>' => PHP_EOL
        );

        return strip_tags(str_replace(
            array_keys($replaces),
            array_values($replaces),
            $html
        ));
    }
}
