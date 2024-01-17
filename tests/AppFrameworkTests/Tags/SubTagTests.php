<?php

declare(strict_types=1);

namespace BMStorageTests\TestSuites\Tags;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\Tags\TagCollection;
use DBHelper;

class SubTagTests extends ApplicationTestCase
{
    public function test_createTag(): void
    {
        $collection = AppFactory::createTags();

        $rootTag = $collection->createNewTag('Root tag');

        $this->assertNull($rootTag->getParentTag());
        $this->assertCount(1, $collection->getFilterCriteria()->getItems());
    }

    public function test_subTagsNotIncludedInRootCollection(): void
    {
        $collection = AppFactory::createTags();

        $rootTag = $collection->createNewTag('Root tag');

        $this->assertSame(1, $collection->countRecords());

        $rootTag->addSubTag('Sub tag 1');

        $this->assertSame(2, $collection->countRecords());
        $this->assertCount(1, $rootTag->getSubTags());
    }

    public function test_subTagsAreSelfContained() : void
    {
        $collection = AppFactory::createTags();

        $rootTag = $collection->createNewTag('Root tag');

        $this->assertSame(1, $collection->countRecords());

        $rootTag->addSubTag('Sub tag 1');
        $rootTag->addSubTag('Sub tag 2');

        $this->assertSame(2, $rootTag->getSubTagCriteria()->countItems());
    }

    public function test_getIDChain() : void
    {
        $collection = AppFactory::createTags();

        $tag1 = $collection->createNewTag('Tag 1');
        $tag2 = $tag1->addSubTag('Tag 2');
        $tag3 = $tag2->addSubTag('Tag 3');

        $result = TagCollection::getTagIDChain($tag3->getID());

        $this->assertSame(
            array(
                $tag3->getID(),
                $tag2->getID(),
                $tag1->getID(),
            ),
            $result
        );
    }

    public function test_getAnyTag() : void
    {
        $collection = AppFactory::createTags();

        $tag1 = $collection->createNewTag('Tag 1');
        $tag2 = $tag1->addSubTag('Tag 2');
        $tag3 = $tag2->addSubTag('Tag 3');

        $this->assertSame($tag3->getParentTagID(), $tag2->getID());
        $this->assertSame($tag3->getParentTag(), $tag2);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();

        DBHelper::deleteRecords(TagCollection::TABLE_NAME);
    }
}
