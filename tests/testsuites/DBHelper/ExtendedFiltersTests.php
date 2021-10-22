<?php

declare(strict_types=1);

final class DBHelper_ExtendedFiltersTests extends ApplicationTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
    }

    /**
     * Enabling a column using {@see Application_FilterCriteria_DatabaseExtended::withCustomColumn()}
     * must automatically add it to the query's select statement.
     *
     * @throws Application_Exception
     */
    public function test_detectCustomColumnUsage_manualEnable() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $this->assertFalse($col->isEnabled());
        $this->assertCount(0, $criteria->getActiveCustomColumns());
        $this->assertCount(1, $criteria->getSelects());
        $this->assertFalse($col->getUsage()->isInSelect());

        $criteria->enableFeedbackText();

        $this->assertTrue($col->isEnabled());
        $this->assertCount(1, $criteria->getActiveCustomColumns());
        $this->assertTrue($col->getUsage()->isInSelect());
        $this->assertCount(2, $criteria->getSelects());

        $sql = $criteria->renderQuery();

        $this->assertStringContainsString($col->getSQLStatement(), $sql);
    }

    /**
     * Adding a column to the select statement without
     * explicitly enabling it must still work: The filters
     * must detect the presence and enable it automatically.
     *
     * @see Application_FilterCriteria_DatabaseExtended::buildQuery()
     */
    public function test_detectSelect() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $criteria->addFeedbackTextToSelect();

        // Adding the column to the select statement does
        // not yet enable it: This is done at the end, when
        // the query is built.
        $this->assertFalse($col->isEnabled());

        // The usage however, must already reflect that the
        // column is present in the select fields.
        $this->assertTrue($col->getUsage()->isInSelect());

        $query = $criteria->renderQuery();

        // After rendering, the column has been automatically
        // enabled, since it was present in a select statement.
        $this->assertTrue($col->isEnabled());
        $this->assertCount(1, $criteria->getActiveCustomColumns(), $query);
    }

    public function test_detectOrderBy() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $criteria->orderByFeedbackText();

        $this->assertFalse($col->isEnabled());
        $this->assertTrue($col->getUsage()->isInOrderBy());

        $query = $criteria->renderQuery();

        $this->assertTrue($col->isEnabled());
        $this->assertCount(1, $criteria->getActiveCustomColumns(), $query);
    }

    public function test_detectWhere() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addWhere($col->getValueStatement()." = 'value'");

        $this->assertTrue($col->getUsage()->isInWhere());
    }

    public function test_detectSelect_manual() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addSelectColumn($col->getSelect());

        $this->assertTrue($col->getUsage()->isInSelect());
    }

    public function test_canUseAlias_notInSelect() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addGroupBy($col->getOrderBy());

        $this->assertTrue($col->getUsage()->isInUse());
        $this->assertFalse($col->canUseAlias());
    }

    public function test_canUseAlias_inSelect() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addSelectColumn($col->getSelect());

        $this->assertTrue($col->getUsage()->isInUse());
        $this->assertTrue($col->canUseAlias());
    }

    public function test_canUseAlias_subquery() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColSubquery();

        $criteria->addSelectColumn($col->getSelect());

        $this->assertTrue($col->isSubQuery());
        $this->assertTrue($col->getUsage()->isInUse());
        $this->assertFalse($col->canUseAlias());
    }

    public function test_bypassColumnSelection() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $criteria->addJoin(sprintf(
            "JOIN `custom_join_table` WHERE %s='test'", $col->getValueStatement()
        ));

        $this->assertTrue($col->getUsage()->isInUse());
    }

    /**
     * Using automatic column enabling must add the column
     * to the select statement.
     */
    public function test_autoEnable_addToSelect() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $criteria->orderByFeedbackText();

        $sql = $criteria->renderQuery();

        $this->assertTrue($col->getUsage()->isInSelect());
        $this->assertStringContainsString('`feedback`.`feedback` AS `text`', $sql);
    }
}
