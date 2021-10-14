<?php

declare(strict_types=1);

final class TestDriver_FilterCriteria_TestCriteria extends Application_FilterCriteria_DatabaseExtended
{
    const JOIN_FEEDBACK = 'feedback';
    const JOIN_OPTIONAL_TABLE = 'table_optional_join';

    /**
     * Enables the custom column, which automatically
     * adds it to the select statement, and ensures
     * joins get added as well.
     */
    public function withFeedbackText()
    {
        $this->withCustomColumn('text');
    }

    /**
     * Adds the custom column to the select statement,
     * without enabling the column. This means that the
     * required join will not be present, but the method
     * {@see Application_FilterCriteria_DatabaseExtended::buildQuery()}
     * handles this case.
     */
    public function addFeedbackManually()
    {
        $this->addSelectColumn($this->getColFeedbackText());
    }

    protected function getSelect()
    {
        return array(
            $this->getColEmail()
        );
    }

    protected function getSearchFields()
    {
        return array(
            $this->getColEmail()
        );
    }

    public function getColEmail() : string
    {
        return (string)$this->statement('{users}.{email}');
    }

    protected function getQuery()
    {
        return $this->statement(
            "SELECT 
                {WHAT} 
            FROM 
                {table_users} AS {users} 
            {JOINS} 
            {WHERE} 
            {GROUPBY} 
            {ORDERBY} 
            {LIMIT}"
        );
    }

    public function getColFeedbackText() : string
    {
        return $this->getCustomColumn('text')->getStatement();
    }

    protected function _initCustomColumns() : void
    {
        $this->registerCustomSelect('{feedback}.{feedback_text}', 'text')
           ->requireJoin(self::JOIN_FEEDBACK);
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container) : void
    {
        $container
            ->table('{table_users}', Application_Users::TABLE_NAME)
            ->table('{table_feedback}', Application_Feedback::TABLE_NAME)
            ->table('{table_optional}', self::JOIN_OPTIONAL_TABLE)

            ->alias('{users}', 'users')
            ->alias('{feedback}', 'feedback')

            ->field('{email}', Application_Users_User::COL_EMAIL)
            ->field('{users_primary}', Application_Users::PRIMARY_NAME)
            ->field('{feedback_primary}', Application_Feedback::PRIMARY_NAME)
            ->field('{feedback_text}', Application_Feedback_Report::COL_FEEDBACK);
    }

    protected function _registerJoins() : void
    {
        $this->registerJoinStatement(
            self::JOIN_FEEDBACK,
            "JOIN
                    {table_feedback} AS {feedback}
                ON
                    {feedback}.{users_primary}={users}.{users_primary}"
        );

        $this->registerJoinStatement(
            self::JOIN_OPTIONAL_TABLE,
            "JOIN {table_optional}"
        );
    }
}
