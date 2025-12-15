<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application;
use Application\Framework\AppFolder;
use AppUtils\FileHelper;

final class FolderTest extends ApplicationTestCase
{
    public function test_isInstalledAsDependency() : void
    {
        $this->assertFalse(Application::isInstalledAsDependency());
    }

    public function test_detectRootFolder() : void
    {
        $this->assertSame(
            FileHelper::normalizePath(dirname(__DIR__, 3)),
            Application::detectRootFolder()->getRealPath()
        );
    }

    public function test_appFolderIsFrameworkClasses() : void
    {
        $folder = AppFolder::create(__DIR__.'/../../../src/classes/Application.php');

        $this->assertTrue($folder->isValid());
        $this->assertTrue($folder->isFrameworkClasses());
        $this->assertTrue($folder->isFramework());
    }

    public function test_appFolderIsDriverClasses() : void
    {
        $folder = AppFolder::create(__DIR__.'/../../application/assets/classes/TestDriver.php');

        $this->assertTrue($folder->isValid());
        $this->assertTrue($folder->isDriverClasses());
        $this->assertTrue($folder->isDriver());
    }

    public function test_appFolderIsDriverRoot() : void
    {
        $folder = AppFolder::create(__DIR__.'/../../application/bootstrap.php');

        $this->assertTrue($folder->isValid());
        $this->assertTrue($folder->isDriverRoot());
        $this->assertTrue($folder->isDriver());
    }
}
