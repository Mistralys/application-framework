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

        $this->sourceFolder = TESTS_ROOT.'/assets/files/TypeHinter';
        $this->inputFile = $this->sourceFolder.'/TypeHinterTestClass.php';
        $this->outputFile = $this->sourceFolder.'/TypeHinterTestClass.php.output';
        $this->expectedFile = $this->sourceFolder.'/TypeHinterTestClass.php.expected';
    }

    public function test_findFiles() : void
    {
        $this->assertCount(1, (new TypeHinter($this->sourceFolder))->getFilesList());
    }

    public function test_replace() : void
    {
        (new TypeHinter($this->sourceFolder))
            ->setFileSuffix('output')
            ->addMethod('_handleActions', 'bool')
            ->addReplace($this->defActionSearch, $this->defActionReplace)
            ->process();

        $this->assertEquals(
            FileHelper::readContents($this->expectedFile),
            FileHelper::readContents($this->outputFile)
        );
    }

    private $defActionSearch = <<<EOT
function getDefaultAction() : string
    {
        return null;
    }
EOT;

    private $defActionReplace = <<<EOT
function getDefaultAction() : string
    {
        return '';
    }
EOT;

    private $defSubmodeSearch = <<<EOT
function getDefaultSubmode() : string
    {
        return null;
    }
EOT;

    private $defSubmodeReplace = <<<EOT
function getDefaultSubmode() : string
    {
        return '';
    }
EOT;

    public function test_replaceEverywhere() : void
    {
        $this->markTestSkipped();

        $hinter = new TypeHinter(APP_INSTALL_FOLDER);

        $hinter
            ->addMethod('_handleActions', 'bool')
            ->addMethod('_handleSubactions', 'void')
            ->addMethod('_handleSubnavigation', 'void')
            ->addMethod('_handleBreadcrumb', 'void')
            ->addMethod('_handleSidebar', 'void')
            ->addMethod('_handleBreadcrumb', 'void')
            ->addMethod('_handleHelp', 'void')
            ->addMethod('_initSteps', 'void')
            ->addMethod('getURLName', 'string')
            ->addMethod('getRecordMissingURL', 'string')
            ->addMethod('getDefaultSubmode', 'string')
            ->addMethod('getDefaultAction', 'string')
            ->addMethod('getNavigationTitle', 'string')
            ->addMethod('isUserAllowed', 'bool')
            ->addReplace($this->defActionSearch, $this->defActionReplace)
            ->addReplace($this->defSubmodeSearch, $this->defSubmodeReplace)
            ->process();

        $this->addToAssertionCount(1);
    }
}
