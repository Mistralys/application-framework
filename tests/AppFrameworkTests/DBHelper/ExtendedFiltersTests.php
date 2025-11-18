<?php

declare(strict_types=1);

use Application\Feedback\FeedbackCollection;
use Application\Feedback\FeedbackRecord;
use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;

final class DBHelper_ExtendedFiltersTests extends DBHelperTestCase
{
    public function test_getCustomSelects() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $this->assertCount(0, $criteria->getCustomSelects());

        $col->setEnabled(true);

        $this->assertCount(1, $criteria->getCustomSelects());
        $this->assertContains($col->getPrimarySelectMarker(), $criteria->getCustomSelects());

        $this->verifyFetchingWorks($criteria);
    }

    public function test_getSelects() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $this->assertCount(1, $criteria->getSelects());

        $col->setEnabled(true);

        $this->assertCount(2, $criteria->getSelects());
        $this->assertContains($col->getPrimarySelectMarker(), $criteria->getCustomSelects());
    }

    /**
     * Ensure that the foundInString methods actually finds all
     * relevant instances of the available markers.
     */
    public function test_foundInString() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $this->assertFalse($col->isFoundInString('Some text'));
        $this->assertTrue($col->isFoundInString($col->getPrimarySelectMarker()));
        $this->assertTrue($col->isFoundInString($col->getSecondarySelectMarker()));
        $this->assertTrue($col->isFoundInString($col->getWhereMarker()));
        $this->assertTrue($col->isFoundInString($col->getGroupByMarker()));
        $this->assertTrue($col->isFoundInString($col->getOrderByMarker()));
        $this->assertTrue($col->isFoundInString($col->getJoinMarker()));
    }

    /**
     * The filters must be aware of changes to the column
     * configuration, in order to update the internal cache
     * of the column usage.
     */
    public function test_usageCacheRenewed() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $usage = $col->getUsage();

        $col->setEnabled(true);

        $newUsage = $col->getUsage();

        $this->assertNotSame($usage, $newUsage);
    }

    public function test_getUsage() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $usage = $col->getUsage();
        $this->assertFalse($usage->isInUse());

        $col->setEnabled(true);

        $usage = $col->getUsage();
        $this->assertTrue($usage->isInUse());
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

        $this->verifyFetchingWorks($criteria);
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

        $this->verifyFetchingWorks($criteria);
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

        $this->verifyFetchingWorks($criteria);
    }

    public function test_detectWhere() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addWhere($col->getWhereValue()." = 'value'");

        $this->assertTrue($col->getUsage()->isInWhere());
    }

    public function test_detectSelect_manual() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addSelectColumn($col->getPrimarySelectValue());

        $this->assertTrue($col->getUsage()->isInSelect());
    }

    public function test_canUseAlias_notInSelect() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addGroupBy($col->getOrderByValue());

        $this->assertTrue($col->getUsage()->isInUse());
        $this->assertFalse($col->canUseAlias());
    }

    public function test_canUseAlias_inSelect() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColFeedbackText();

        $criteria->addSelectColumn($col->getPrimarySelectValue());

        $this->assertTrue($col->getUsage()->isInUse());
        $this->assertTrue($col->canUseAlias());
    }

    /**
     * When working with sub-queries, the columns must
     * know that they cannot use the alias in group by,
     * where or even order fields.
     */
    public function test_canUseAlias_subquery() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();

        $col = $criteria->getColUserFeedbackAmount();

        $criteria->addSelectColumn($col->getPrimarySelectValue());

        $this->assertTrue($col->isSubQuery());
        $this->assertTrue($col->getUsage()->isInUse());
        $this->assertTrue($col->getUsage()->isInSelect());
        $this->assertFalse($col->canUseAlias());

        $query = $criteria->renderQuery();

        // The rendered query must not contain any placeholders anymore
        $this->assertStringNotContainsString(Application_FilterCriteria_Database_CustomColumn::MARKER_SUFFIX, $query);

        // The column must automatically be added to the group
        // by statement, because it is a sub-query.
        $this->assertTrue($col->getUsage()->isInGroupBy());

        // Fetch results: this must not trigger an exception.
        $criteria->getItems();
    }

    public function test_bypassColumnSelection() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $col = $criteria->getColFeedbackText();

        $criteria->addJoin(sprintf(
            "JOIN `custom_join_table` WHERE %s='test'", $col->getJoinValue()
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

    public function test_referenceInSelect_caseStatement() : void
    {
        $criteria = new TestDriver_FilterCriteria_TestCriteria();
        $colText = $criteria->getColFeedbackText();
        $colEmpty = $criteria->getColCaseTextEmpty();

        $colEmpty->setEnabled(true);

        $query = $criteria->renderQuery();

        $this->assertTrue($colText->getUsage()->isInSelect());

        // The column must automatically be added to the group
        // by statement, because it is a sub-query.
        $this->assertTrue($colEmpty->getUsage()->isInGroupBy());

        // The rendered query must not contain any placeholders anymore
        $this->assertStringNotContainsString(Application_FilterCriteria_Database_CustomColumn::MARKER_SUFFIX, $query);

        $this->verifyFetchingWorks($criteria);
    }

    // region: Support methods

    private static ?FeedbackRecord $systemFeedback = null;
    private static Application_User $systemUser;

    private function createTestRecords() : void
    {
        if(isset(self::$systemFeedback))
        {
            return;
        }

        DBHelper::deleteRecords(FeedbackCollection::TABLE_NAME);

        self::$systemUser = Application::createSystemUser();

        $feedback = Application::createFeedback();

        self::$systemFeedback = $feedback->addImprovement(
            FeedbackCollection::SCOPE_APPLICATION,
            'Needs some improvement',
            '',
            self::$systemUser
        );
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->createTestRecords();
    }

    /**
     * Verifies that the filter criteria's query works by fetching
     * and counting records.
     *
     * @param TestDriver_FilterCriteria_TestCriteria $criteria
     */
    private function verifyFetchingWorks(TestDriver_FilterCriteria_TestCriteria $criteria) : void
    {
        $criteria->getItems();
        $this->addToAssertionCount(1);

        $criteria->countItems();
        $this->addToAssertionCount(1);
    }

    // endregion
}
