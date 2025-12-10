<?php

declare(strict_types=1);

namespace AppFrameworkTests\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Index\AdminScreenIndexer;
use Application\AppFactory;
use TestDriver\Area\APIClientsArea\ViewAPIClientMode\APIKeysSubmode\APIKeysListAction;

final class IndexerTests extends ApplicationTestCase
{
    public function test_buildIndex() : void
    {
        $data = new AdminScreenIndexer(AppFactory::createDriver())->serialize();

        $this->assertArrayHasKey(AdminScreenIndex::KEY_URL_PATHS, $data);
        $this->assertArrayHasKey(AdminScreenIndex::KEY_FLAT, $data);
        $this->assertArrayHasKey(AdminScreenIndex::KEY_TREE, $data);

        $this->assertNotEmpty($data[AdminScreenIndex::KEY_URL_PATHS]);
        $this->assertNotEmpty($data[AdminScreenIndex::KEY_FLAT]);
        $this->assertNotEmpty($data[AdminScreenIndex::KEY_TREE]);
    }

    public function test_accessIndex() : void
    {
        $index = AdminScreenIndex::getInstance();

        $this->assertSame(APIKeysListAction::class, $index->getClassByURLPath('api-clients.view.api_keys.list'));
    }
}
