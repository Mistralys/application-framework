<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses;

use Application\AppFactory;
use Application\NewsCentral\Categories\CategoriesCollection;
use Application\NewsCentral\NewsCollection;
use AppUtils\Microtime;
use DBHelper;
use NewsCentral\Entries\NewsArticle;
use NewsCentral\Entries\NewsEntry;

abstract class NewsTestCase extends ApplicationTestCase
{
    protected NewsCollection $newsCollection;
    protected CategoriesCollection $categoriesCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        $this->newsCollection = AppFactory::createNews();
        $this->categoriesCollection = $this->newsCollection->createCategories();

        DBHelper::deleteRecords(NewsCollection::TABLE_NAME);
        DBHelper::deleteRecords(NewsCollection::TABLE_NAME_ENTRY_CATEGORIES);
        DBHelper::deleteRecords(CategoriesCollection::TABLE_NAME);
    }

    protected function assertDatesHaveBeenSet(NewsEntry $entry) : void
    {
        $checkDateFormat = 'Y-m-d H:i';
        $date = Microtime::createNow()->format($checkDateFormat);

        $this->assertSame($date, $entry->getDateCreated()->format($checkDateFormat));
        $this->assertSame($date, $entry->getDateModified()->format($checkDateFormat));
    }

    protected function createTestNewsArticle() : NewsArticle
    {
        return AppFactory::createNews()->createNewArticle(
            'Foo',
            'en_UK',
            'Synopsis',
            'Article'
        );
    }
}
