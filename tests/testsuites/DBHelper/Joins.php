<?php

declare(strict_types=1);

final class DBHelper_JoinsTests extends ApplicationTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $this->startTransaction();
    }

    /**
     * When only accessing a column that requires a join, but not actually
     * using the column, the join must not be added.
     */
    public function test_getColumnAddsNoJoin() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $this->assertEquals('`users`.`email`', $criteria->getColEmail());
        $this->assertEquals('`feedback`.`feedback`', $criteria->getColFeedbackText());

        $query = $criteria->renderQuery();

        $this->assertStringNotContainsString("`feedback`.`feedback`", $query);
        $this->assertStringNotContainsString('JOIN', $query);
        $this->assertStringNotContainsString(Application_Feedback::TABLE_NAME, $query);
    }

    /**
     * When using the {@see Application_FilterCriteria_DatabaseExtended::withCustomColumn()}
     * method, the column is enabled as a result, and added to the
     * list of selected columns.
     */
    public function test_selectColumnAddsJoin() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $criteria->withFeedbackText();

        $query = $criteria->renderQuery();

        $this->assertStringContainsString("`feedback`.`feedback`", $query);
        $this->assertStringContainsString('JOIN', $query);
        $this->assertStringContainsString(Application_Feedback::TABLE_NAME, $query);
    }

    /**
     * When a custom column is used in the query without
     * enabling it, it must be automatically detected anyway,
     * and any joins be added.
     */
    public function test_columnUsageAddsJoin() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $criteria->addFeedbackManually();

        $query = $criteria->renderQuery();

        $this->assertStringContainsString("`feedback`.`feedback`", $query);
        $this->assertStringContainsString('JOIN', $query);
        $this->assertStringContainsString(Application_Feedback::TABLE_NAME, $query);
    }

    /**
     * When adding a JOIN statement, adding custom
     * placeholder values must be possible alongside
     * the existing values.
     */
    public function test_joinStatementCustomVars() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $criteria->addJoinStatement(
            "JOIN
                {table_custom} AS {custom}
            ON
                {custom}.{feedback_primary}={feedback}.{feedback_primary}"
        )
            ->table('{table_custom}', 'custom_table')
            ->alias('{custom}', 'custom_alias');

        $query = $criteria->renderQuery();

        $this->assertStringContainsString('custom_table', $query);
    }

    public function test_joinRequiresOtherJoin() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $criteria->addJoin(
            "JOIN `table_fantasy_join`",
        )
            ->requireJoin(TestDriver_FilterCriteria_TestCriteria::JOIN_OPTIONAL_TABLE);

        $query = $criteria->renderQuery();

        $this->assertStringContainsString('table_fantasy_join', $query);
        $this->assertStringContainsString(TestDriver_FilterCriteria_TestCriteria::JOIN_OPTIONAL_TABLE, $query);
    }
}
