<?php

declare(strict_types=1);

namespace AppFrameworkTests\UI;

use PHPUnit\Framework\TestCase;
use UI;
use UI_ResourceManager;

final class LoadKeyTest extends TestCase
{
    protected ?UI $ui = null;

    protected function setUp(): void
    {
        if (!isset($this->ui)) {
            $this->ui = UI::getInstance();
        }

        $this->ui->getResourceManager()->clearLoadkeys();
    }

    public function test_loadScript(): void
    {
        $this->assertEmpty($this->ui->getResourceManager()->getLoadedResourceKeys());
    }

    public function test_notAvoidable(): void
    {
        $this->assertFalse($this->ui->addJavascript('application.js')->isAvoidable());
    }

    public function test_avoidable(): void
    {
        $resourceManager = $this->ui->getResourceManager();

        $loadkey = $this->ui->addJavascript('application.js')->getKey();

        $resourceManager->clearLoadkeys();

        $_REQUEST[UI_ResourceManager::LOADKEYS_REQUEST_VARIABLE] = $loadkey;

        $this->assertNotEmpty($resourceManager->getLoadedResourceKeys());
        $this->assertTrue($this->ui->addJavascript('application.js')->isAvoidable());
    }
}
