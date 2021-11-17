<?php

declare(strict_types=1);

use AppUtils\FileHelper;

final class TypeHinter_ReplaceTests extends ApplicationTestCase
{
    /**
     * @var string
     */
    private $inputFile;

    /**
     * @var string
     */
    private $outputFile;


    /**
     * @var string
     */
    private $expectedFile;
    /**
     * @var string
     */
    private $sourceFolder;

    protected function setUp() : void
    {
        parent::setUp();

        $this->sourceFolder = TESTS_ROOT.'/files/TypeHinter';

        $this->assertDirectoryExists($this->sourceFolder);

        $this->inputFile = $this->sourceFolder.'/TypeHinterTestClass.php';
        $this->outputFile = $this->sourceFolder.'/TypeHinterTestClass.php.output';
        $this->expectedFile = $this->sourceFolder.'/TypeHinterTestClass.php.expected';
    }

    public function test_findFiles() : void
    {
        $this->assertCount(1, (new TypeHinter($this->sourceFolder))->getFilesList());
    }

    public function test_updateV1_21() : void
    {
        $update = new TypeHinter_UpdateV1_21();

        (new TypeHinter($this->sourceFolder))
            ->setFileSuffix('output')
            ->addMethod('_handleActions', 'bool')
            ->addReplace($update->getActionSearch(), $update->getActionReplace())
            ->process();

        $this->assertEquals(
            FileHelper::readContents($this->expectedFile),
            FileHelper::readContents($this->outputFile)
        );
    }

    public function test_executeUpdateV1_21() : void
    {
        (new TypeHinter_UpdateV1_21())->create(APP_INSTALL_FOLDER)->process();

        $this->addToAssertionCount(1);

        FileHelper::saveFile(__DIR__.'/output.log', implode(PHP_EOL, Application::getLogger()->getLog()));
    }
}
