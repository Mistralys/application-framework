<?php

declare(strict_types=1);

namespace AppFrameworkTests\AppSets;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\Admin\Index\AdminScreenIndex;
use Application\Admin\Welcome\Screens\WelcomeArea;
use Application\AppFactory;
use Application\AppSets\AppSetsCollection;
use Application\NewsCentral\Admin\Screens\ManageNewsArea;
use Application\AppSets\AppSet;
use Application\Sets\AppSetsException;

final class AppSetsTest extends ApplicationTestCase
{
    public function test_createNew() : void
    {
        $set = AppSetsCollection::getInstance()->createNew(
            'test',
            'Label',
            AppFactory::createDriver()->createArea(ManageNewsArea::URL_NAME)
        );

        $this->assertSame('test', $set->getAlias());
        $this->assertSame('Label', $set->getLabel());
        $this->assertInstanceOf(ManageNewsArea::class, $set->getDefaultArea());
    }

    public function test_createNewAliasExists() : void
    {
        AppSetsCollection::getInstance()->createNew(
            'test',
            'Label',
            AppFactory::createDriver()->createArea(WelcomeArea::URL_NAME)
        );

        $this->expectException(AppSetsException::class);
        $this->expectExceptionCode(AppSetsException::ERROR_ALIAS_ALREADY_EXISTS);

        AppSetsCollection::getInstance()->createNew(
            'test',
            'Label',
            AppFactory::createDriver()->createArea(WelcomeArea::URL_NAME)
        );
    }

    public function test_defaultAppSetHasAllAreasEnabled() :void
    {
        $collection = AppFactory::createAppSets();
        $defaultSet = $collection->getDefaultSet();

        $driver = AppFactory::createDriver();
        $areas = $driver->getAdminAreaObjects();

        foreach($areas as $area) {
            $this->assertTrue($defaultSet->isAreaEnabled($area));
        }

        $this->assertSame(AdminScreenIndex::getInstance()->getAdminAreaURLNames(), $defaultSet->getEnabledURLNames());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        AppSetsCollection::getInstance()->resetCollection();
    }
}
