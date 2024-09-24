<?php

declare(strict_types=1);

namespace AppFrameworkTests\Driver;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;

final class VersionTests extends ApplicationTestCase
{
    public function test_createAutomatically() : void
    {
        $changelog = AppFactory::createDevChangelog();

        $this->assertTrue($changelog->changelogExists());
        $this->assertFalse($changelog->currentVersionExists());

        $version = $changelog->getCurrentVersion();

        $this->assertEquals('1.0.0', $version->getVersion());
        $this->assertTrue($changelog->currentVersionExists());
    }

    protected function setUp(): void
    {
        parent::setUp();

        AppFactory::createDevChangelog()->clearCurrentVersion();

        $this->assertFileDoesNotExist(APP_ROOT.'/version');
    }
}
