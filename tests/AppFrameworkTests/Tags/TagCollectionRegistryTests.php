<?php

declare(strict_types=1);

namespace AppFrameworkTests\Tags;

use AppFrameworkTestClasses\Traits\DBHelperTestInterface;
use AppFrameworkTestClasses\Traits\DBHelperTestTrait;
use AppFrameworkTestClasses\Traits\ImageMediaTestInterface;
use AppFrameworkTestClasses\Traits\ImageMediaTestTrait;
use Application\AppFactory;
use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;
use TestDriver\TestDBRecords\TestDBCollection;

final class TagCollectionRegistryTests
    extends TaggingTestCase
    implements
    ImageMediaTestInterface,
    DBHelperTestInterface
{
    use ImageMediaTestTrait;
    use DBHelperTestTrait;

    public function test_registerCollections() : void
    {
        $registry = $this->tagsCollection->createCollectionRegistry();

        $collections = $registry->getAll();

        $this->assertCount(3, $collections);
        $this->assertContains(AppFactory::createMedia(), $collections);
        $this->assertContains(AppFactory::createMediaCollection(), $collections);
        $this->assertContains(TestDBCollection::getInstance(), $collections);
    }

    public function test_getMediaByUniqueID() : void
    {
        $image = $this->createTestImage();
        $uniqueID = $image->getTagManager()->getUniqueID();

        $found = $this->tagsCollection->getTaggableByUniqueID($uniqueID);

        $this->assertSame($uniqueID, $found->getTagManager()->getUniqueID());
        $this->assertTrue($this->tagsCollection->uniqueIDExists($uniqueID));
        $this->assertSame($image, $this->tagsCollection->getTaggableByUniqueID($uniqueID));
    }

    public function test_getDBTestByUniqueID() : void
    {
        $record = $this->createTestDBRecord();
        $uniqueID = $record->getTagManager()->getUniqueID();

        $found = $this->tagsCollection->getTaggableByUniqueID($uniqueID);

        $this->assertSame($uniqueID, $found->getTagManager()->getUniqueID());
        $this->assertTrue($this->tagsCollection->uniqueIDExists($uniqueID));
        $this->assertSame($record, $this->tagsCollection->getTaggableByUniqueID($uniqueID));
    }
}
