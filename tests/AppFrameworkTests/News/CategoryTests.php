<?php

declare(strict_types=1);

namespace AppFrameworkTests\News;

use AppFrameworkTestClasses\NewsTestCase;
use Application\AppFactory;

final class CategoryTests extends NewsTestCase
{
    public function test_createCategory() : void
    {
        $collection = AppFactory::createNews()->createCategories();

        $category = $collection->createNewCategory('Test category');

        $this->assertSame('Test category', $category->getLabel());
    }
}
