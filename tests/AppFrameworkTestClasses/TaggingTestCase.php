<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\Tags\TagCollection;
use DBHelper;
use TestDriver\TestDBRecords\TestDBCollection;

abstract class TaggingTestCase extends ApplicationTestCase
{
    protected TagCollection $tagsCollection;
    protected TestDBCollection $recordCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        DBHelper::deleteRecords(TagCollection::TABLE_NAME);
        DBHelper::deleteRecords(TestDBCollection::TABLE_NAME);

        $this->tagsCollection = AppFactory::createTags();
        $this->recordCollection = TestDBCollection::getInstance();
    }
}
