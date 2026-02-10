<?php

declare(strict_types=1);

namespace AppFrameworkTests\Driver;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\Application;
use Application\Driver\VersionInfo;

final class VersionTest extends ApplicationTestCase
{
    /**
     * The version info class will automatically create and
     * save the version file if it doesn't exist, independently
     * of the deployment task that creates it. This is used
     * as a fallback solution.
     */
    public function test_createAutomatically() : void
    {
        $changelog = AppFactory::createDevChangelog();
        $versionInfo = AppFactory::createVersionInfo();

        $this->assertTrue($changelog->changelogExists());
        $this->assertFalse($versionInfo->fileExists());

        $this->assertEquals('1.0.0', $changelog->getCurrentVersion()->getVersion());
        $this->assertEquals('1.0.0', $versionInfo->getFullVersion());

        $this->assertTrue($versionInfo->fileExists());

        $before = filemtime($versionInfo->getVersionFile()->getPath());

        usleep(500);

        $new = new VersionInfo();
        $new->getFullVersion();

        $after = filemtime($versionInfo->getVersionFile()->getPath());

        $this->assertEquals($before, $after);
    }

    /**
     * Ensure that the version file is only written once per request
     * if it does not exist.
     */
    public function test_onlyWrittenOncePerRequest() : void
    {
        $first = AppFactory::createVersionInfo();
        $first->getFullVersion();

        $before = filemtime($first->getVersionFile()->getPath());

        usleep(500);

        // Use a new instance to trigger the file creation
        $new = new VersionInfo();
        $new->getFullVersion();

        $after = filemtime($new->getVersionFile()->getPath());

        $this->assertEquals($before, $after);
    }

    protected function setUp(): void
    {
        parent::setUp();

        AppFactory::createVersionInfo()->clearVersion();
        AppFactory::createDevChangelog()->clearCurrentVersion();

        $this->assertFileDoesNotExist(Application::getCacheFolder().'/'.VersionInfo::FILE_NAME);
    }
}
