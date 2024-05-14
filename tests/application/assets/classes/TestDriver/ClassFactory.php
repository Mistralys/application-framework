<?php

declare(strict_types=1);

namespace TestDriver;

use Application\AppFactory;
use TestDriver\Revisionables\RevisionableCollection;
use TestDriver\TestDBRecords\TestDBCollection;

class ClassFactory extends AppFactory
{
    public static function createTestDBCollection() : TestDBCollection
    {
        return self::createClassInstance(TestDBCollection::class);
    }

    public static function createRevisionableCollection() : RevisionableCollection
    {
        return RevisionableCollection::getInstance();
    }
}
