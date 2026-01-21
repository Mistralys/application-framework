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
            $this->assertTrue(
                $defaultSet->isAreaEnabled($area),
                sprintf('Area "%s" should be enabled but is not', $area->getURLName())
            );
        }

        $this->assertSame(AdminScreenIndex::getInstance()->getAdminAreaURLNames(), $defaultSet->getEnabledURLNames());
    }

    public function test_defaultAppSetURLNamesFormat() : void
    {
        $collection = AppFactory::createAppSets();
        $defaultSet = $collection->getDefaultSet();

        // Check what getEnabledAreaURLNames returns
        $urlNames = $defaultSet->getEnabledAreaURLNames();
        $this->assertIsArray($urlNames, 'getEnabledAreaURLNames should return an array');

        // Check the raw data from loadData
        $reflection = new \ReflectionClass($defaultSet);
        $method = $reflection->getMethod('loadData');
        $method->setAccessible(true);
        $data = $method->invoke($defaultSet);

        $this->assertIsString(
            $data[AppSetsCollection::COL_URL_NAMES],
            'COL_URL_NAMES in loadData should be a string (comma-separated)'
        );
    }

    public function test_defaultAppSetGetEnabledAreas() : void
    {
        $collection = AppFactory::createAppSets();
        $defaultSet = $collection->getDefaultSet();

        $enabledAreas = $defaultSet->getEnabledAreas();
        $allAreas = AppFactory::createDriver()->getAdminAreaObjects();

        $this->assertCount(
            count($allAreas),
            $enabledAreas,
            'Default set should have all areas enabled'
        );
    }

    public function test_defaultAppSetInitializesAreasFromURLNames() : void
    {
        $collection = AppFactory::createAppSets();
        $defaultSet = $collection->getDefaultSet();

        // Get the URL names that should be enabled
        $expectedURLNames = $defaultSet->getEnabledAreaURLNames();
        $this->assertNotEmpty($expectedURLNames, 'Default set should have enabled URL names');

        // Verify each URL name corresponds to an enabled area
        $enabledAreas = $defaultSet->getEnabledAreas(false); // Exclude core areas
        $enabledURLNames = array_map(function($area) {
            return $area->getURLName();
        }, $enabledAreas);

        sort($expectedURLNames);
        sort($enabledURLNames);

        $this->assertSame(
            $expectedURLNames,
            $enabledURLNames,
            'Enabled areas should match the URL names from getEnabledAreaURLNames()'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        AppSetsCollection::getInstance()->resetCollection();
    }
}
