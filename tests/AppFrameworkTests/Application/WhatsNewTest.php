<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace testsuites\Application;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\WhatsNew\AppVersion;
use Application\WhatsNew\AppVersion\CategoryItem;
use Application\WhatsNew\AppVersion\LanguageCategory;
use Application\WhatsNew\AppVersion\VersionLanguage;
use Application\WhatsNew\WhatsNew;
use Application\WhatsNew\XMLFileWriter;
use AppUtils\FileHelper;

/**
 * @package Application
 * @subpackage UnitTests
 */
class WhatsNewTest extends ApplicationTestCase
{
    // region: _Tests

    public function test_create() : void
    {
        $new = AppFactory::createWhatsNew();

        $versions = $new->getVersions();

        $this->assertCount(1, $versions);

        $version = $versions[0];
        $languages = $version->getLanguages();

        $this->assertSame('1.0.0', $version->getNumber());
        $this->assertCount(3, $languages);

        foreach($languages as $language)
        {
            $items = $language->getItems();
            $this->assertNotEmpty($items);

            foreach($items as $item)
            {
                $this->assertNotEmpty($item->getCategory()->getLabel());
                $this->assertNotEmpty($item->getText());
            }
        }
    }

    public function test_getCategories() : void
    {
        $whatsNew = AppFactory::createWhatsNew();
        $version = $whatsNew->getCurrentVersion();

        $this->assertNotNull($version);

        $language = $version->getLanguage('DEV');
        $categories = $language->getCategories();

        $this->assertCount(1, $categories);
        $this->assertSame('Framework', $categories[0]->getLabel());
    }

    public function test_getItems() : void
    {
        $whatsNew = AppFactory::createWhatsNew();
        $version = $whatsNew->getCurrentVersion();

        $this->assertNotNull($version);

        $language = $version->getLanguage('DEV');
        $items = $language->getItems();

        $this->assertCount(1, $items);
        $this->assertSame('Developer text', $items[0]->getText());
        $this->assertSame('ISSUE-123', $items[0]->getIssue());
        $this->assertSame('smordziol', $items[0]->getAuthor());
    }

    public function test_detectImages() : void
    {
        $items = AppFactory::createWhatsNew()
            ->getCurrentVersion()
            ->getLanguage('EN')
            ->getCategoryByLabel('Image')
            ->getItems();

        $this->assertCount(2, $items);

        $imagesA = $items[0]->detectImages();
        $this->assertCount(1, $imagesA);

        $this->assertSame('{image:manage-menu.png}', $imagesA[0]->getMatchedText());
        $this->assertSame('manage-menu.png', $imagesA[0]->getImageName());
        $this->assertNull($imagesA[0]->getWidth());

        $imagesB = $items[1]->detectImages();
        $this->assertCount(1, $imagesB);

        $this->assertSame('{image 120px:manage-menu.png}', $imagesB[0]->getMatchedText());
        $this->assertSame('manage-menu.png', $imagesB[0]->getImageName());
        $width = $imagesB[0]->getWidth();
        $this->assertNotNull($width);
        $this->assertSame('120px', $width->toCSS());
    }

    public function test_write() : void
    {
        $whatsNew = AppFactory::createWhatsNew();
        $whatsNew->addVersion('5.0.0');

        $writer = new XMLFileWriter($whatsNew);
        $writer->write($this->outputFile);

        $updated = new WhatsNew($this->outputFile);

        $this->assertCount(2, $updated->getVersions());
    }

    public function test_sortLanguageIDs() : void
    {
        $expected = array(
            'DE',
            'EN',
            'DEV'
        );

        $actual = VersionLanguage::getLanguageIDs();

        $this->assertSame(
            $expected,
            $actual,
            'The sorting order does not match.'.PHP_EOL.
            'Expected:'.PHP_EOL.
            print_r($expected, true).PHP_EOL.
            'Given:'.PHP_EOL.
            print_r($actual, true)
        );
    }

    // endregion

    // region: Support methods

    private string $outputFile;

    protected function setUp() : void
    {
        parent::setUp();

        $this->outputFile = __DIR__.'/../../files/whats-new-edited.xml';
    }

    protected function tearDown() : void
    {
        parent::tearDown();

        if(file_exists($this->outputFile))
        {
            FileHelper::deleteFile($this->outputFile);
        }
    }

    // endregion
}
