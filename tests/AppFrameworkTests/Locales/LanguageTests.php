<?php

declare(strict_types=1);

namespace AppFrameworkTests\Locales;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;

final class LanguageTests extends ApplicationTestCase
{
    public function test_collectionIsNotEmpty() : void
    {
        $this->assertNotEmpty(AppFactory::createLanguages()->getAll());
    }
}
