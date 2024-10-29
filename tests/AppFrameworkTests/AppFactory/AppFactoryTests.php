<?php

declare(strict_types=1);

namespace AppFrameworkTests\AppFactory;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory\ClassCacheHandler;

final class AppFactoryTests extends ApplicationTestCase
{
    public function test_disabledForUnitTests() : void
    {
        self::assertFalse(ClassCacheHandler::isCacheEnabled());
    }

    public function test_forcingCacheEnabled() : void
    {
        ClassCacheHandler::setCacheEnabled(true);

        self::assertTrue(ClassCacheHandler::isCacheEnabled());
    }

    protected function setUp(): void
    {
        parent::setUp();

        ClassCacheHandler::clearClassCache();
        ClassCacheHandler::setCacheEnabled(null);
    }
}