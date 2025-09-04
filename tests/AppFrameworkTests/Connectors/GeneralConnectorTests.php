<?php

declare(strict_types=1);

namespace AppFrameworkTests\Connectors;

use AppFrameworkTestClasses\ApplicationTestCase;
use Connectors;
use TestDriver\Connectors\InternalAjaxConnector;

final class GeneralConnectorTests extends ApplicationTestCase
{
    public function test_connectorExists() : void
    {
        $this->assertTrue(Connectors::connectorExists(InternalAjaxConnector::class));
    }

    public function test_connectorNotExists() : void
    {
        $this->assertFalse(Connectors::connectorExists('UnknownConnector'));
    }

    public function test_connectorExistsWithLegacyNaming() : void
    {
        $this->assertTrue(Connectors::connectorExists('LegacyNamingConnector'));
    }
}