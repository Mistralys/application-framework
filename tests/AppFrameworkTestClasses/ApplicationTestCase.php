<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses;

use Application;
use Application\AppFactory;
use Application\Tags\TagCollection;
use Application_Countries_Country;
use Application\ConfigSettings\BaseConfigRegistry;
use Application_Formable_Generic;
use Application_Media_Document;
use Application_Media_Document_Image;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use DBHelper;
use PHPUnit\Framework\TestCase;
use AppLocalize\Localization_Locale;
use AppLocalize\Localization;
use TestDriver\TestDBRecords\TestDBRecord;
use UI;
use UI_Page;

abstract class ApplicationTestCase extends TestCase
{
    /**
     * @var array<string,int>
     */
    private static array $counter = array();

    /**
     * @var Application_Media_Document[]
     */
    protected array $testMedia = array();

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
        $this->clearTestMedia();
    }

    protected function clearTestMedia(): void
    {
        foreach($this->testMedia as $media)
        {
            FileHelper::deleteFile($media->getPath());
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
    }

    protected function disableLogging(): void
    {
        AppFactory::createLogger()->logModeNone();
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

    protected function setUp(): void
    {
        Localization::selectAppLocale('en_UK');

        $this->testMedia = array();
    }

    protected function getMediaStoragePath() : string
    {
        return __DIR__.'/../files/Media';
    }

    protected function getExampleImagePath() : string
    {
        $file = $this->getMediaStoragePath() . '/example-image.png';

        $this->assertFileExists($file);

        return $file;
    }

    public function createTestImage(string $name='example-image') : Application_Media_Document_Image
    {
        $file = $this->getExampleImagePath();

        $document = AppFactory::createMedia()->createImageFromFile($name, FileInfo::factory($file));
        $documentPath = $document->getPath();

        $this->assertFileExists($documentPath);

        $this->testMedia[] = $document;

        return $document;
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
