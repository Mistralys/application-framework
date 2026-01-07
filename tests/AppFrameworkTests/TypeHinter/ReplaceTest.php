<?php

declare(strict_types=1);

namespace testsuites\TypeHinter;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use AppUtils\FileHelper;
use TypeHinter\TypeHintRunner;
use TypeHinter_UpdateV1_21;

final class ReplaceTest extends ApplicationTestCase
{
    /**
     * @var string
     */
    private string $outputFile;

    /**
     * @var string
     */
    private string $expectedFile;
    /**
     * @var string
     */
    private string $sourceFolder;

    protected function setUp() : void
    {
        parent::setUp();

        $this->sourceFolder = TESTS_ROOT.'/files/TypeHinter\TypeHinter';

        $this->assertDirectoryExists($this->sourceFolder);

        $this->outputFile = $this->sourceFolder.'/TypeHinterTestClass.php.output';
        $this->expectedFile = $this->sourceFolder.'/TypeHinterTestClass.php.expected';

        FileHelper::deleteFile($this->outputFile);
    }

    public function test_findFiles() : void
    {
        $this->assertCount(1, (new TypeHintRunner($this->sourceFolder))->getFilesList());
    }

    public function test_updateV1_21() : void
    {
        $update = new TypeHinter_UpdateV1_21();

        $this->enableLogging();

        $hinter = (new TypeHintRunner($this->sourceFolder))
            ->setFileSuffix('output')
            ->addMethod('_handleActions', 'bool')
            ->addReplace($update->getActionSearch(), $update->getActionReplace());

        $this->assertCount(1, $hinter->getFilesList());

        $hinter->process();

        $this->assertEquals(
            FileHelper::readContents($this->expectedFile),
            FileHelper::readContents($this->outputFile)
        );
    }

    public function _test_executeUpdateV1_21() : void
    {
        (new TypeHinter_UpdateV1_21())->create(APP_INSTALL_FOLDER)->process();

        $this->addToAssertionCount(1);

        FileHelper::saveFile(
            __DIR__.'/output.log',
            implode(PHP_EOL, AppFactory::createLogger()->getLog())
        );
    }
}
