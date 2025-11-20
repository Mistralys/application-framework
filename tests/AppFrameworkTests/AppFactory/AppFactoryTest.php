<?php

declare(strict_types=1);

namespace AppFrameworkTests\AppFactory;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\AppFactory\ClassCacheHandler;
use AppUtils\FileHelper\FolderInfo;
use DBHelper_BaseFilterSettings;
use TestDriver\TestDBRecords\TestDBFilterSettings;

final class AppFactoryTest extends ApplicationTestCase
{
    // region: _Tests

    public function test_disabledForUnitTests() : void
    {
        self::assertFalse(ClassCacheHandler::isCacheEnabled());
    }

    public function test_forcingCacheEnabled() : void
    {
        ClassCacheHandler::setCacheEnabled(true);

        self::assertTrue(ClassCacheHandler::isCacheEnabled());
    }

    public function test_classListFilteredByInstanceOf() : void
    {
        $classes = AppFactory::findClassesInFolder(
            $this->getTestClassesFolder(),
            true,
            DBHelper_BaseFilterSettings::class
        );

        $this->assertCount(1, $classes);
        $this->assertContains(TestDBFilterSettings::class, $classes);
    }

    public function test_classListIsTheSameWithAndWithoutCache() : void
    {
        $this->assertFalse(ClassCacheHandler::isCacheEnabled());

        $withoutCache = AppFactory::findClassesInFolder($this->getTestClassesFolder());

        ClassCacheHandler::setCacheEnabled(true);

        $withCache = AppFactory::findClassesInFolder($this->getTestClassesFolder());

        sort($withoutCache);
        sort($withCache);

        $this->assertSame($withoutCache, $withCache);
    }

    // endregion

    // region: Support methods

    protected function getTestClassesFolder() : FolderInfo
    {
        return FolderInfo::factory($this->getTestAppFolder().'/assets/classes/TestDriver/TestDBRecords');
    }

    protected function setUp(): void
    {
        parent::setUp();

        ClassCacheHandler::clearClassCache();
        ClassCacheHandler::setCacheEnabled(null);
    }

    // endregion
}
