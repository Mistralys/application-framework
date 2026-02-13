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

    public function test_enableArea() : void
    {
        $driver = AppFactory::createDriver();
        $set = AppSetsCollection::getInstance()->createNew(
            'test-enable',
            'Test Enable',
            $driver->createArea(WelcomeArea::URL_NAME)
        );

        $newsArea = $driver->createArea(ManageNewsArea::URL_NAME);
        $set->enableArea($newsArea);

        $this->assertTrue($set->isAreaEnabled($newsArea), 'News area should be enabled');
    }

    public function test_enableMultipleAreas() : void
    {
        $driver = AppFactory::createDriver();
        $set = AppSetsCollection::getInstance()->createNew(
            'test-multi',
            'Test Multi',
            $driver->createArea(WelcomeArea::URL_NAME)
        );

        $newsArea = $driver->createArea(ManageNewsArea::URL_NAME);
        $areas = array($newsArea);

        $set->enableAreas($areas);

        $enabledURLNames = $set->getEnabledURLNames();
        $this->assertContains(ManageNewsArea::URL_NAME, $enabledURLNames);
    }

    public function test_coreAreasAlwaysEnabled() : void
    {
        $driver = AppFactory::createDriver();

        // Create a set with no explicitly enabled areas
        $set = AppSetsCollection::getInstance()->createNew(
            'test-core',
            'Test Core',
            $driver->createArea(WelcomeArea::URL_NAME)
        );

        // Get all core areas
        $coreAreas = array_filter(
            $driver->getAdminAreaObjects(),
            fn($area) => $area->isCore()
        );

        $this->assertNotEmpty($coreAreas, 'There should be core areas in the system');

        // Verify all core areas are enabled even though we didn't explicitly enable them
        foreach ($coreAreas as $coreArea) {
            $this->assertTrue(
                $set->isAreaEnabled($coreArea),
                sprintf('Core area "%s" should always be enabled', $coreArea->getURLName())
            );
        }
    }

    public function test_coreAreasIncludedWhenRequested() : void
    {
        $driver = AppFactory::createDriver();

        $set = AppSetsCollection::getInstance()->createNew(
            'test-with-core',
            'Test With Core',
            $driver->createArea(WelcomeArea::URL_NAME)
        );

        // Get enabled areas including core
        $enabledAreasWithCore = $set->getEnabledAreas(true);
        $this->assertNotEmpty($enabledAreasWithCore, 'Should have core areas when includeCore is true');

        // Get enabled areas excluding core
        $enabledAreasWithoutCore = $set->getEnabledAreas(false);

        // The set without core should have fewer (or equal) areas
        $this->assertLessThanOrEqual(
            count($enabledAreasWithCore),
            count($enabledAreasWithoutCore),
            'Areas excluding core should be <= areas including core'
        );
    }

    public function test_getEnabledURLNames() : void
    {
        $driver = AppFactory::createDriver();
        $newsArea = $driver->createArea(ManageNewsArea::URL_NAME);

        $set = AppSetsCollection::getInstance()->createNew(
            'test-url-names',
            'Test URL Names',
            $driver->createArea(WelcomeArea::URL_NAME),
            array($newsArea)
        );

        $urlNames = $set->getEnabledURLNames();

        $this->assertContains(ManageNewsArea::URL_NAME, $urlNames);
    }

    public function test_getEnabledAreaLabels() : void
    {
        $driver = AppFactory::createDriver();
        $newsArea = $driver->createArea(ManageNewsArea::URL_NAME);

        $set = AppSetsCollection::getInstance()->createNew(
            'test-labels',
            'Test Labels',
            $driver->createArea(WelcomeArea::URL_NAME),
            array($newsArea)
        );

        $labels = $set->getEnabledAreaLabels();

        $this->assertContains($newsArea->getTitle(), $labels);
    }

    public function test_getActiveSet() : void
    {
        $collection = AppSetsCollection::getInstance();
        $activeSet = $collection->getActive();

        $this->assertInstanceOf(AppSet::class, $activeSet);
        $this->assertTrue($activeSet->isActive());
    }

    public function test_makeSetActive() : void
    {
        $collection = AppSetsCollection::getInstance();
        $driver = AppFactory::createDriver();

        $set = $collection->createNew(
            'test-active',
            'Test Active',
            $driver->createArea(WelcomeArea::URL_NAME)
        );

        $collection->makeSetActive($set);

        $this->assertTrue($set->isActive());
        $this->assertSame($set->getID(), $collection->getActiveID());
    }

    public function test_areAllAreasEnabled() : void
    {
        $collection = AppSetsCollection::getInstance();
        $defaultSet = $collection->getDefaultSet();

        $this->assertTrue(
            $defaultSet->areAllAreasEnabled(),
            'Default set should have all areas enabled'
        );

        $driver = AppFactory::createDriver();
        $partialSet = $collection->createNew(
            'test-partial',
            'Test Partial',
            $driver->createArea(WelcomeArea::URL_NAME),
            array($driver->createArea(ManageNewsArea::URL_NAME))
        );

        $this->assertFalse(
            $partialSet->areAllAreasEnabled(),
            'Partial set should not have all areas enabled'
        );
    }

    public function test_defaultSetIsDefault() : void
    {
        $collection = AppSetsCollection::getInstance();
        $defaultSet = $collection->getDefaultSet();

        $this->assertTrue($defaultSet->isDefault());
    }

    public function test_regularSetIsNotDefault() : void
    {
        $driver = AppFactory::createDriver();
        $set = AppSetsCollection::getInstance()->createNew(
            'test-regular',
            'Test Regular',
            $driver->createArea(WelcomeArea::URL_NAME)
        );

        $this->assertFalse($set->isDefault());
    }

    public function test_defaultSetCannotBeSaved() : void
    {
        $defaultSet = AppSetsCollection::getInstance()->getDefaultSet();

        // Default set's save method should return true but do nothing
        $result = $defaultSet->save(true);
        $this->assertTrue($result);
    }

    public function test_getByIDWithDefaultID() : void
    {
        $collection = AppSetsCollection::getInstance();
        $set = $collection->getByID(AppSetsCollection::DEFAULT_ID);

        $this->assertTrue($set->isDefault());
    }

    public function test_idExistsForDefaultSet() : void
    {
        $collection = AppSetsCollection::getInstance();

        $this->assertTrue($collection->idExists(AppSetsCollection::DEFAULT_ID));
    }

    public function test_getDefaultArea() : void
    {
        $driver = AppFactory::createDriver();
        $defaultArea = $driver->createArea(ManageNewsArea::URL_NAME);

        $set = AppSetsCollection::getInstance()->createNew(
            'test-default-area',
            'Test Default Area',
            $defaultArea
        );

        $this->assertSame(ManageNewsArea::URL_NAME, $set->getDefaultArea()->getURLName());
    }

    public function test_invalidDefaultAreaFallsBackToWelcome() : void
    {
        $collection = AppSetsCollection::getInstance();

        // Create a set manually with an invalid default area
        $set = $collection->createNewRecord(array(
            AppSetsCollection::COL_ALIAS => 'test-invalid',
            AppSetsCollection::COL_LABEL => 'Test Invalid',
            AppSetsCollection::COL_DEFAULT_URL_NAME => 'non-existent-area',
            AppSetsCollection::COL_URL_NAMES => ''
        ));

        // Should fall back to WelcomeArea
        $this->assertSame(WelcomeArea::URL_NAME, $set->getDefaultArea()->getURLName());
    }

    public function test_enabledURLNamesFiltersOutNonExistentAreas() : void
    {
        $collection = AppSetsCollection::getInstance();

        // Create a set with some valid and some invalid area names
        $set = $collection->createNewRecord(array(
            AppSetsCollection::COL_ALIAS => 'test-filter',
            AppSetsCollection::COL_LABEL => 'Test Filter',
            AppSetsCollection::COL_DEFAULT_URL_NAME => WelcomeArea::URL_NAME,
            AppSetsCollection::COL_URL_NAMES => 'news,non-existent-area,another-fake-area'
        ));

        $urlNames = $set->getEnabledAreaURLNames();

        // Should only contain 'news' (if it exists and is not core)
        foreach ($urlNames as $urlName) {
            $this->assertTrue(
                AdminScreenIndex::getInstance()->areaExists($urlName),
                sprintf('URL name "%s" should be a valid area', $urlName)
            );
        }

        $this->assertNotContains('non-existent-area', $urlNames);
        $this->assertNotContains('another-fake-area', $urlNames);
    }

    public function test_aliasExists() : void
    {
        $collection = AppSetsCollection::getInstance();
        $driver = AppFactory::createDriver();

        $collection->createNew(
            'test-exists',
            'Test Exists',
            $driver->createArea(WelcomeArea::URL_NAME)
        );

        $this->assertTrue($collection->aliasExists('test-exists'));
        $this->assertFalse($collection->aliasExists('does-not-exist'));
    }

    public function test_getIDByAlias() : void
    {
        $collection = AppSetsCollection::getInstance();
        $driver = AppFactory::createDriver();

        $set = $collection->createNew(
            'test-get-by-alias',
            'Test Get By Alias',
            $driver->createArea(WelcomeArea::URL_NAME)
        );

        $id = $collection->getIDByAlias('test-get-by-alias');
        $this->assertSame($set->getID(), $id);
    }

    public function test_getIDByAliasReturnsNullForNonExistent() : void
    {
        $collection = AppSetsCollection::getInstance();

        $id = $collection->getIDByAlias('definitely-does-not-exist');
        $this->assertNull($id);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        AppSetsCollection::getInstance()->resetCollection();
    }
}
