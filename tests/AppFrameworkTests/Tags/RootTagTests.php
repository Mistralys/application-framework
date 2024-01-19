<?php

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\Tags\TagCollection;
use Application\Tags\TagRecord;

class RootTagTests extends ApplicationTestCase
{
    public function test_createTag() : void
    {
        $collection = AppFactory::createTags();

        // Not using `createNewTag` to test the instance of the returned object.
        $tag = $collection->createNewRecord(array(TagCollection::COL_LABEL => 'Foo bar'));

        $this->assertInstanceOf(TagRecord::class, $tag);
        $this->assertSame('Foo bar', $tag->getLabel());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->startTransaction();
    }
}
