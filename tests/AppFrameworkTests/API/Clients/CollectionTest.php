<?php

declare(strict_types=1);

namespace AppFrameworkTests\API\Clients;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\API\Clients\APIClientsCollection;
use Application\AppFactory;

final class CollectionTest extends ApplicationTestCase
{
    // region: _Tests

    public function test_createNewClient(): void
    {
        $client = AppFactory::createAPIClients()->createNewClient(
            'Test Client',
            'TESTCLIENT01'
        );

        $this->assertSame('Test Client', $client->getLabel());
        $this->assertSame('TESTCLIENT01', $client->getForeignID());
        $this->assertEmpty($client->getComments());
        $this->assertNotNull($client->getRecordMicrotimeKey(APIClientsCollection::COL_DATE_CREATED));
        $this->assertTrue($client->isActive());
        $this->assertTrue($client->getCreatedByID() > 0);
    }

    // endregion

    // region: Support methods

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();
    }

    // endregion
}
