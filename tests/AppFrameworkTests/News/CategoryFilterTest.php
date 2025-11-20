<?php

declare(strict_types=1);


namespace AppFrameworkTests\News;

use AppFrameworkTestClasses\NewsTestCase;
use Application\AppFactory;

final class CategoryFilterTest extends NewsTestCase
{
    public function test_selectID() : void
    {
        $collection = AppFactory::createNews()->createCategories();

        $fooID = $collection->createNewCategory('Foo')->getID();
        $collection->createNewCategory('Bar');

        $this->assertCount(2, $collection->getFilterCriteria()->getItems());

        $this->assertCount(1, $collection->getFilterCriteria()->selectCategoryID($fooID)->getItems());
    }
}
