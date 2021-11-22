<?php

declare(strict_types=1);

abstract class DBHelperTestCase extends ApplicationTestCase
{
    public const TEST_RECORDS_PRIMARY = 'record_id';
    public const TEST_RECORDS_COL_LABEL = 'label';
    public const TEST_RECORDS_COL_ALIAS = 'alias';
    public const TEST_RECORDS_DATA_TABLE = 'test_records_data';
    public const TEST_RECORDS_TABLE = 'test_records';

    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
    }
}
