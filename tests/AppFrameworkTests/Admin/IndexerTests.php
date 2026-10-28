<?php

declare(strict_types=1);

namespace AppFrameworkTests\Admin;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Index\AdminScreenIndexer;
use Application\Admin\Index\ScreenDataInterface;
use Application\API\Admin\Screens\Mode\View\APIKeys\APIKeysListAction;
use Application\AppFactory;
use AppLocalize\Localization;
use AppLocalize\Localization\Locale\de_DE;
use RuntimeException;

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

    /**
     * The indexer temporarily switches to the `en_GB` locale while
     * building the index, since screen labels must be collected in
     * a single reference locale. The previously active locale must
     * be restored afterward, on both success and failure.
     */
    public function test_indexRestoresAppLocale() : void
    {
        Localization::selectAppLocale(de_DE::LOCALE_NAME);

        new AdminScreenIndexer(AppFactory::createDriver())->index();

        $this->assertSame(
            de_DE::LOCALE_NAME,
            Localization::getAppLocaleName(),
            'The locale must be restored after a successful indexing run.'
        );

        Localization::selectAppLocale(de_DE::LOCALE_NAME);

        $indexer = new class(AppFactory::createDriver()) extends AdminScreenIndexer {
            public function serialize() : array
            {
                throw new RuntimeException('Simulated indexing failure.');
            }
        };

        try {
            $indexer->index();
            $this->fail('Expected exception was not thrown.');
        } catch(RuntimeException $e) {
            // Expected: the simulated failure must still propagate.
        }

        $this->assertSame(
            de_DE::LOCALE_NAME,
            Localization::getAppLocaleName(),
            'The locale must be restored even when indexing throws.'
        );
    }

    /**
     * After a regeneration, the {@see AdminScreenIndex} singleton must
     * serve the rebuilt data from the same instance, so existing
     * references are not left pointing at stale data.
     */
    public function test_reloadRefreshesSameInstance() : void
    {
        $instance = AdminScreenIndex::getInstance();

        new AdminScreenIndexer(AppFactory::createDriver())->index();

        $reloaded = $instance->reload();

        $this->assertSame($instance, $reloaded);
        $this->assertSame($instance, AdminScreenIndex::getInstance());
        $this->assertNotEmpty($instance->getAdminAreas());
    }
}
