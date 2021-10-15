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
        $this->assertEquals('`feedback`.`feedback`', $criteria->getColFeedbackText()->getStatement());

        $query = $criteria->renderQuery();

        $this->assertStringNotContainsString("`feedback`.`feedback`", $query);
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
        $criteria->enableFeedbackText();

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

    /**
     * When a join requires another join, both must be
     * present in the rendered query.
     */
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

    /**
     * The order of Joins is important when they depend on
     * each other. The sorting mechanism must adjust the
     * order as needed, regardless of the order in which they
     * were added/registered.
     */
    public function test_joinOrder() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $criteria->registerJoin('3_third_one', 'JOIN `third_one')
            ->requireJoin('2_second_one');

        $criteria->registerJoin('2_second_one', 'JOIN `second_one`')
            ->requireJoin('1_first_one');

        $criteria->registerJoin('1_first_one', 'JOIN `first_one`');

        // Without any specific dependencies
        $criteria->registerJoin('z_last_one', 'JOIN `independent`');

        // This must also require the second and first automatically.
        $criteria->requireJoin('3_third_one');

        // Also require the independent one
        $criteria->requireJoin('z_last_one');

        $criteria->applyFilters();

        $joins = $criteria->getJoinsOrdered(false);

        $ids = array();
        $cnt = 1;
        foreach($joins as $join)
        {
            $ids[] = '  '.$cnt.' - '.$join->getID();
            $cnt++;
        }

        $label = 'Order:'.PHP_EOL.implode(PHP_EOL, $ids);

        $position = 0;

        $this->assertEquals('1_first_one', $joins[$position]->getID(), $label); $position++;
        $this->assertEquals('2_second_one', $joins[$position]->getID(), $label); $position++;
        $this->assertEquals('3_third_one', $joins[$position]->getID(), $label); $position++;
        $this->assertEquals(TestDriver_FilterCriteria_TestCriteria::JOIN_PERMANENT_JOIN, $joins[$position]->getID(), $label); $position++;
        $this->assertEquals('z_last_one', $joins[$position]->getID(), $label);
    }

    public function test_getParentJoins() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $third = $criteria->addJoin('JOIN `third_one', 'third_one')
            ->requireJoin('second_one');

        $second = $criteria->addJoin('JOIN `second_one`', 'second_one')
            ->requireJoin('first_one');

        $first = $criteria->addJoin('JOIN `first_one`', 'first_one');

        $criteria->applyFilters();

        $this->assertFalse($first->hasJoins());
        $this->assertTrue($second->hasJoins());
        $this->assertCount(1, $second->getParentJoins());
        $this->assertCount(2, $third->getParentJoins());
        $this->assertTrue($second->dependsOn($first));
        $this->assertTrue($third->dependsOn($second));
        $this->assertTrue($third->dependsOn($first));
    }

    public function test_getJoins() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $this->assertCount(1, $criteria->getJoins());
        $this->assertCount(3, $criteria->getJoins(true));

        $criteria->addJoinStatement('JOIN `sometable`');

        $this->assertCount(2, $criteria->getJoins());

        $criteria->registerJoinStatement('anotherid', 'JOIN `anothertable`');

        $this->assertCount(5, $criteria->getJoins(true));
    }

    /**
     * Ensure that individual joins correctly detect how
     * many joins depend on them (require them).
     */
    public function test_getDependentJoins() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $third = $criteria->addJoin('JOIN `third_one', 'third_one')
            ->requireJoin('second_one');

        $second = $criteria->addJoin('JOIN `second_one`', 'second_one')
            ->requireJoin('first_one');

        $first = $criteria->addJoin('JOIN `first_one`', 'first_one');

        $criteria->applyFilters();

        $this->assertCount(2, $first->getDependentJoins());
        $this->assertCount(1, $second->getDependentJoins());
        $this->assertCount(0, $third->getDependentJoins());
    }
}
