<?php

declare(strict_types=1);

namespace Application\DeploymentRegistry;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\DeploymentRegistry;
use TestDriver\ClassFactory;

final class HistoryTests extends ApplicationTestCase
{
    public function test_emptyRegistryByDefault() : void
    {
        $this->assertEmpty($this->registry->getHistory());
        $this->assertNull($this->registry->getLastDeployment());
    }

    public function test_registerVersion() : void
    {
        $this->registry->registerDeployment();

        $this->assertTrue($this->registry->versionExists('1.0.0'));
    }

    public function test_getLastDeployment() : void
    {
        $this->registry->registerDeployment();

        $last = $this->registry->getLastDeployment();

        $this->assertNotNull($last);
        $this->assertEquals('1.0.0', $last->getVersion());
    }

    public function test_getByVersion() : void
    {
        $this->registry->registerDeployment();

        $deployment = $this->registry->getByVersion('1.0.0');

        $this->assertSame('1.0.0', $deployment->getVersion());
    }

    private DeploymentRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = ClassFactory::createDeploymentRegistry();

        $this->registry->clearHistory();
    }
}