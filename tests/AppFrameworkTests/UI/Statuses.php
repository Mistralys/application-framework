<?php

declare(strict_types=1);

use AppFrameworkTestClasses\ApplicationTestCase;

final class UI_StatusesTest extends ApplicationTestCase
{
    public function test_getAll() : void
    {
        $statuses = new TestStatuses();

        $items = $statuses->getAll();
        $this->assertCount(3, $items);
    }

    public function test_getByID() : void
    {
        $statuses = new TestStatuses();

        $status = $statuses->getByID('trivial');

        $this->assertInstanceOf(TestStatus::class, $status);
        $this->assertSame('trivial', $status->getID());
    }

    public function test_getIDs() : void
    {
        $statuses = new TestStatuses();

        // The IDs list is always sorted alphabetically.
        $expected = array(
            'default',
            'important',
            'trivial'
        );

        $this->assertEquals($expected, $ids = $statuses->getIDs());
    }

    public function test_criticalityDefault() : void
    {
        $statuses = new TestStatuses();

        $default = $statuses->getByID('default');

        $this->assertSame(UI_Statuses_Status::DEFAULT_CRITICALITY, $default->getCriticality());
    }

    public function test_isCriticality() : void
    {
        $statuses = new TestStatuses();

        $status = $statuses->getByID('important');

        $this->assertTrue($status->isWarning());
    }
}
