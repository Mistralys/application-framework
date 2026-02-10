<?php

declare(strict_types=1);

namespace AppFrameworkTests\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Index\AdminScreenIndexer;
use Application\Admin\Index\ScreenDataInterface;
use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeysListAction;
use Application\AppFactory;

final class IndexerTests extends ApplicationTestCase
{
    public function test_buildIndex() : void
    {
        $data = new AdminScreenIndexer(AppFactory::createDriver())->serialize();

        $this->assertArrayHasKey(ScreenDataInterface::KEY_ROOT_URL_PATHS, $data);
        $this->assertArrayHasKey(ScreenDataInterface::KEY_ROOT_FLAT, $data);
        $this->assertArrayHasKey(ScreenDataInterface::KEY_ROOT_TREE, $data);

        $this->assertNotEmpty($data[ScreenDataInterface::KEY_ROOT_URL_PATHS]);
        $this->assertNotEmpty($data[ScreenDataInterface::KEY_ROOT_FLAT]);
        $this->assertNotEmpty($data[ScreenDataInterface::KEY_ROOT_TREE]);
    }

    public function test_accessIndex() : void
    {
        $index = AdminScreenIndex::getInstance();

        $this->assertSame(APIKeysListAction::class, $index->getClassByURLPath('api-clients.view.api_keys.list'));
    }
}
