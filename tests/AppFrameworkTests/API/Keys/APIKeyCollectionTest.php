<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Keys;

use Application\AppFactory;
use Mistralys\AppFrameworkTests\TestClasses\APIClientTestCase;

final class APIKeyCollectionTest extends APIClientTestCase
{
    // region: _Tests

    public function test_createNewKey(): void
    {
        $client = AppFactory::createAPIClients()->createNewClient(
            'Test Client',
            'TESTCLIENT01'
        );

        $pseudoUser = $this->createTestUser();

        $key = $client->createNewAPIKey('Test key', $pseudoUser);

        $this->assertSame('Test key', $key->getLabel());
    }

    public function test_setLabel() : void
    {
        $key = $this->createTestAPIKey();
        $keyID = $key->getID();

        $this->assertSame(
            'Updated Label',
            $key
                ->setLabel('Updated Label')
                ->saveChained()
                ->getCollection()
                ->resetCollection() // Force DB reload
                ->getByID($keyID)
                ->getLabel()
        );
    }

    public function test_grantAllMethods() : void
    {
        $key = $this->createTestAPIKey();
        $methods = $key->getMethods();

        $this->assertFalse($key->areAllMethodsGranted());
        $this->assertFalse($methods->areAllGranted());
        $this->assertEmpty($methods->getMethodNames());

        $key->setGrantAll(true);

        $this->assertTrue($key->areAllMethodsGranted());
        $this->assertTrue($methods->areAllGranted());
        $this->assertNotEmpty($methods->getMethodNames());
    }

    // endregion
}
