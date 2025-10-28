<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses;

use AppFrameworkTestClasses\Traits\DBHelperTestInterface;
use AppFrameworkTestClasses\Traits\ImageMediaTestInterface;
use AppFrameworkTestClasses\Traits\MythologyTestInterface;
use Application;
use Application\AppFactory;
use Application\Interfaces\ChangelogableInterface;
use Application_Countries_Country;
use Application\ConfigSettings\BaseConfigRegistry;
use Application_Formable_Generic;
use Application_RequestLog;
use Application_Session_Base;
use Application_User;
use AppLocalize\Localization\Locales\LocaleInterface;
use AppUtils\FileHelper\FolderInfo;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\TestOutputFile;
use PHPUnit\Framework\TestCase;
use AppLocalize\Localization;
use TestDriver\ClassFactory;
use TestDriver\TestDBRecords\TestDBRecord;
use UI;
use UI_Page;

abstract class ApplicationTestCase extends TestCase implements ApplicationTestCaseInterface
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

        if($this instanceof ImageMediaTestInterface) {
            $this->tearDownImageTestCase();
        }
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
        AppFactory::createLogger()->logModeEcho();

        Application_RequestLog::setActive(true);
    }

    protected function disableLogging(): void
    {
        AppFactory::createLogger()->logModeNone();

        Application_RequestLog::setActive(false);
    }

    protected function isRunViaApplication(): bool
    {
        return boot_defined(BaseConfigRegistry::FRAMEWORK_TESTS) !== true;
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

    // region: Create test records

    public function createTestUser() : Application_User
    {
        $number = $this->getTestCounter();

        $newUser = ClassFactory::createUsers()->createNewUser(
            'test-user-'.$number.'@mistralys.eu',
            'Test',
            'User '.$number,
            'foreign-user-id-'.$number
        );

        return Application::createUser($newUser->getID());
    }

    protected function createTestLocale(string $name = ''): LocaleInterface
    {
        if (empty($name)) {
            $names = DBHelper::createFetchMany('locales_application')
                ->fetchColumn('locale_name');

            $name = $names[array_rand($names)];
        }

        return Localization::getAppLocaleByName($name);
    }

    protected function createTestCountry(string $iso, string $label='') : Application_Countries_Country
    {
        $countries = AppFactory::createCountries();

        if($countries->isoExists($iso))
        {
            return $countries->getByISO($iso);
        }

        if(empty($label))
        {
            $label = 'Test country '.$this->getTestCounter();
        }

        return $countries->createNewCountry($iso, $label);
    }

    /**
     * Gets the path to the root folder of the bundled test application.
     * @return FolderInfo
     */
    protected function getTestAppFolder() : FolderInfo
    {
        return FolderInfo::factory(__DIR__.'/../application')->requireExists();
    }

    // endregion

    protected function setUp(): void
    {
        Localization::selectAppLocale(Localization::BUILTIN_LOCALE_NAME);
        AppFactory::createLogger()->reset();
        Application_Session_Base::setRedirectsEnabled(true);
        UI::selectDefaultInstance();

        $this->setUpTraits();

        // A script somewhere resets this to ~512MB in the middle of
        // the tests, so that the phpunit.xml setting is ignored.
        // Searches did not turn up anything meaningful, so this was
        // the best interim solution.
        Application::setMemoryLimit(900, 'Test');
    }

    private function setUpTraits() : void
    {
        if($this instanceof ImageMediaTestInterface) {
            $this->setUpImageTestCase();
        }

        if($this instanceof DBHelperTestInterface) {
            $this->setUpDBHelperTestTrait();
        }

        if($this instanceof MythologyTestInterface) {
            $this->setUpMythologyTestTrait();
        }
    }

    /**
     * @param string $content
     * @param string|null $extension If no extension is specified, uses {@see Application::DEFAULT_TEST_FILE_EXTENSION}.
     * @param string|null $name If no name is specified, generates a unique name.
     * @return TestOutputFile
     */
    public function saveTestFile(string $content, ?string $extension = null, ?string $name = null): TestOutputFile
    {
        return new TestOutputFile($content, $extension, $name);
    }

    // region Custom assertions

    /**
     * Asserts that the default locale is currently selected ({@see Localization::BUILTIN_LOCALE_NAME}).
     * @return void
     */
    public function assertDefaultLocaleSelected() : void
    {
        $this->assertSame(
            Localization::BUILTIN_LOCALE_NAME,
            Localization::getAppLocaleName(),
            sprintf(
                'The default locale must be selected, currently %s is selected.',
                Localization::getAppLocaleName()
            )
        );
    }

    /**
     * Checks if the changelogable has the specified changelog entry type in its queue.
     *
     * @param ChangelogableInterface $changelogable
     * @param string $changelogType
     * @return void
     */
    public function assertChangelogableHasTypeEnqueued(ChangelogableInterface $changelogable, string $changelogType) : void
    {
        $this->assertContains($changelogType, $changelogable->getChangelogQueueTypes());
    }

    /**
     * @param TestDBRecord[] $records
     * @param string[] $names
     * @return void
     */
    protected function assertTestRecordsContainNames(array $records, array $names) : void
    {
        $recordLabels = array();
        foreach($records as $record) {
            $recordLabels[] = $record->getLabel();
        }

        foreach($names as $name)
        {
            if(!in_array($name, $recordLabels)) {
                $this->fail(sprintf(
                    'Record %1$s not found in result set. Record labels: '.PHP_EOL.
                    '- %2$s',
                    $name,
                    implode(PHP_EOL.'- ', $recordLabels)
                ));
            }
        }
    }

    // endregion

    /**
     * Empties the given tables by deleting all existing records from them.
     * @param string[] $tables
     * @return void
     */
    protected function cleanUpTables(array $tables) : void
    {
        DBHelper::requireTransaction('Clean up database tables');

        foreach($tables as $table) {
            DBHelper::deleteRecords($table);
        }
    }
}
