<?php

declare(strict_types=1);

namespace TestDriver;

use Application\AppFactory;
use TestDriver\TestDBRecords\TestDBCollection;

class ClassFactory extends AppFactory
{
    public static function createTestDBCollection() : TestDBCollection
    {
        return self::createClassInstance(TestDBCollection::class);
    }
}
