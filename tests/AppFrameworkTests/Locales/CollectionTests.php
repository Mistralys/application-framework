<?php

declare(strict_types=1);

namespace AppFrameworkTests\Locales;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;

final class CollectionTests extends ApplicationTestCase
{
    public function test_createCollection() : void
    {
        $this->assertNotEmpty(AppFactory::createLocales()->getAll());
    }
}
