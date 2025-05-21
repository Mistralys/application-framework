<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application;
use AppUtils\FileHelper;

final class FolderTests extends ApplicationTestCase
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
}