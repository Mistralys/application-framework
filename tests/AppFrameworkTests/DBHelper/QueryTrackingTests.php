<?php

declare(strict_types=1);

namespace AppFrameworkTests\DBHelper;

use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;
use TestDriver\TestDBRecords\TestDBCollection;

final class QueryTrackingTests extends DBHelperTestCase
{
    public function test_trackSelectQuery() : void
    {
        DBHelper::fetchCount('SELECT * FROM '.TestDBCollection::TABLE_NAME);

        $queries = DBHelper::getQueries();

        $this->assertCount(1, $queries);

        $query = $queries[0];

        $this->assertTrue($query->isSelect());
    }

    public function test_getOriginator() : void
    {
        DBHelper::fetchCount('SELECT * FROM '.TestDBCollection::TABLE_NAME);

        $queries = DBHelper::getQueries();
        $this->assertNotEmpty($queries);
        $query = $queries[0];

        $originator = $query->getOriginator();

        $this->assertNotNull($originator);
        $this->assertSame('test_getOriginator', $originator->getFunction());
    }
}
