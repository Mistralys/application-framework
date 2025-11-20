<?php

declare(strict_types=1);

namespace AppFrameworkTests\News;

use AppFrameworkTestClasses\NewsTestCase;

final class CategoryTest extends NewsTestCase
{
    public function test_createCategory() : void
    {
        $category = $this->categoriesCollection->createNewCategory('Test category');

        $this->assertSame('Test category', $category->getLabel());
    }

    public function test_entryCategoriesDefault() : void
    {
        $entry = $this->createTestNewsArticle();
        $manager = $entry->getCategoriesManager();

        $this->assertSame(0, $manager->countCategories());
        $this->assertFalse($manager->hasCategories());
        $this->assertEmpty($manager->getCategoryIDs());
        $this->assertEmpty($manager->getCategories());
    }

    public function test_entryAddCategory() : void
    {
        $entry = $this->createTestNewsArticle();
        $manager = $entry->getCategoriesManager();
        $foo = $this->categoriesCollection->createNewCategory('Foo');

        $this->assertTrue($manager->addCategory($foo));
        $this->assertTrue($manager->hasCategory($foo));
        $this->assertCount(1, $manager->getCategoryIDs());
        $this->assertCount(1, $manager->getCategories());
    }

    public function test_entryRemoveCategory() : void
    {
        $entry = $this->createTestNewsArticle();
        $manager = $entry->getCategoriesManager();
        $foo = $this->categoriesCollection->createNewCategory('Foo');

        $this->assertFalse($manager->removeCategory($foo));

        $manager->addCategory($foo);

        $this->assertTrue($manager->removeCategory($foo));
    }
}
