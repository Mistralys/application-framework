<?php

declare(strict_types=1);

final class DBHelper_ExtendedFiltersTests extends ApplicationTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
    }

    public function test_detectCustomColumnUsage_manualEnable() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $this->assertCount(0, $criteria->detectCustomColumnUsage());

        $criteria->enableFeedbackText();

        $this->assertCount(1, $criteria->detectCustomColumnUsage());
    }

    public function test_detectCustomColumnUsage_autoEnable() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $criteria->addFeedbackTextToSelect();

        $query = $criteria->renderQuery();

        $this->assertCount(1, $criteria->detectCustomColumnUsage(), $query);
    }

    public function test_detectCustomColumnUsage_orderBy() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $criteria->orderByFeedbackText();

        $query = $criteria->renderQuery();

        $this->assertCount(1, $criteria->detectCustomColumnUsage(), $query);
    }

    public function test_isInSelect() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $this->assertFalse($col->isInSelect());

        $criteria->addFeedbackTextToSelect();

        $this->assertTrue($col->isInSelect());
    }

    public function test_isInSelect_manual() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addSelectColumn($col->getSelect());

        $this->assertTrue($col->isInSelect());

        echo $criteria->renderQuery();
    }
}
