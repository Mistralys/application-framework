<?php

declare(strict_types=1);

final class Collection_IntegerRecordTest extends ApplicationTestCase
{
    public function test_getKeyDate() : void
    {
        $record = new TestIntegerBaseRecord(1);

        $this->assertNull($record->getDate('date_not_string'));
        $this->assertNull($record->getDate('date_invalid'));
        $this->assertInstanceOf(DateTime::class, $record->getDate('date_string'));
        $this->assertInstanceOf(DateTime::class, $record->getDate('date_object'));
    }

    public function test_getKeyBool() : void
    {
        $record = new TestIntegerBaseRecord(1);

        $this->assertFalse($record->getBool('bool_null'));
        $this->assertTrue($record->getBool('bool_true'));
        $this->assertTrue($record->getBool('bool_string'));
    }

    public function test_setValues() : void
    {
        $record = new TestIntegerBaseRecord(1);

        $this->assertTrue($record->setKeyNotExists());
        $this->assertTrue($record->setKeyOverwriteValue());
        $this->assertFalse($record->setKeySameValue());
    }

    /**
     * Setting the value of the primary key in the data set
     * must not be possible (even if it does not exist in the
     * data set in the first place).
     */
    public function test_disallowSetPrimary() : void
    {
        $record = new TestIntegerBaseRecord(1);

        $this->assertFalse($record->setPrimary());
    }
}
