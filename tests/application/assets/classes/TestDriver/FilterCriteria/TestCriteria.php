<?php

declare(strict_types=1);

use Application\Feedback\FeedbackCollection;
use Application\Feedback\FeedbackRecord;
use Application\FilterCriteria\Items\GenericIntegerItem;

final class TestDriver_FilterCriteria_TestCriteria extends Application_FilterCriteria_DatabaseExtended
{
    public const string JOIN_FEEDBACK = 'feedback';
    public const string JOIN_OPTIONAL_TABLE = 'table_optional_join';
    public const string JOIN_LAST_USED_VERSION = 'join_last_used_version';

    public const string CUSTOM_COL_USER_FEEDBACK_AMOUNT = 'feedback_amount';
    public const string CUSTOM_COL_LAST_USED_VERSION = 'custom_last_used_version';
    public const string CUSTOM_COL_TEXT = 'text';
    public const string CUSTOM_COL_CASE_TEXT_EMPTY = 'custom_case_text_empty';

    /**
     * Enables the custom column, which automatically
     * adds it to the select statement, and ensures
     * joins get added as well.
     *
     * @return $this
     */
    public function enableFeedbackText() : self
    {
        return $this->withCustomColumn(self::CUSTOM_COL_TEXT);
    }

    /**
     * Adds the custom column to the select statement,
     * without enabling the column. This means that the
     * required join will not be present, but the method
     * {@see Application_FilterCriteria_DatabaseExtended::buildQuery()}
     * handles this case.
     *
     * @return $this
     */
    public function addFeedbackManually() : self
    {
        return $this->addSelectColumn($this->getColFeedbackText()->getPrimarySelectValue());
    }

    /**
     * @return $this
     */
    public function addFeedbackTextToSelect() : self
    {
        $this->addSelectStatement($this->getColFeedbackText()->getPrimarySelectValue(), false);
        return $this;
    }

    /**
     * @return $this
     */
    public function orderByFeedbackText() : self
    {
        return $this->setOrderBy($this->getColFeedbackText()->getOrderByValue());
    }

    protected function getSelect() : array
    {
        return array(
            $this->getColEmail(),
            $this->getColUserID()
        );
    }

    public function getIDKeyName(): string
    {
        return Application_Users::PRIMARY_NAME;
    }

    public function getSearchFields() : array
    {
        return array(
            $this->getColEmail()
        );
    }

    public function getColEmail() : string
    {
        return (string)$this->statement('{users}.{email}');
    }

    public function getColUserID() : string
    {
        return (string)$this->statement('{users}.{users_primary}');
    }

    protected function getQuery() : DBHelper_StatementBuilder
    {
        return $this->statement(
            /** @lang text */
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

    public function getColFeedbackText() : Application_FilterCriteria_Database_CustomColumn
    {
        return $this->getCustomColumn(self::CUSTOM_COL_TEXT);
    }

    public function getColUserFeedbackAmount() : Application_FilterCriteria_Database_CustomColumn
    {
        return $this->getCustomColumn(self::CUSTOM_COL_USER_FEEDBACK_AMOUNT);
    }

    public function getColLastUsedVersion() : Application_FilterCriteria_Database_CustomColumn
    {
        return $this->getCustomColumn(self::CUSTOM_COL_LAST_USED_VERSION);
    }

    public function getColCaseTextEmpty() : Application_FilterCriteria_Database_CustomColumn
    {
        return $this->getCustomColumn(self::CUSTOM_COL_CASE_TEXT_EMPTY);
    }

    protected function _initCustomColumns() : void
    {
        $this->registerCustomSelect(
            '{feedback}.{feedback_text}',
            self::CUSTOM_COL_TEXT
        )
           ->requireJoin(self::JOIN_FEEDBACK);

        $case = <<<'EOT'
(
    CASE %1$s
    WHEN '' THEN '(Empty)'
    ELSE %1$s
    END
)
EOT;

        $this->registerCustomSelect(
            sprintf(
                $case,
                $this->getColFeedbackText()->getSecondarySelectValue()
            ),
            self::CUSTOM_COL_CASE_TEXT_EMPTY
        );

        $this->registerCustomSelect(
            '{last_used_version}.{setting_value}',
            self::CUSTOM_COL_LAST_USED_VERSION
        )
            ->requireJoin(self::JOIN_LAST_USED_VERSION);

        $this->registerCustomSelect(
            "(
            SELECT 
                COUNT({feedback_primary}) AS {amount_feedbacks} 
            FROM 
                {table_feedback}
            WHERE
                {table_feedback}.{users_primary}={feedback}.{users_primary}
            )",
            self::CUSTOM_COL_USER_FEEDBACK_AMOUNT
        )
            ->requireJoin(self::JOIN_FEEDBACK);
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container) : void
    {
        $container
            ->table('{table_users}', Application_Users::TABLE_NAME)
            ->table('{table_feedback}', FeedbackCollection::TABLE_NAME)
            ->table('{table_optional}', self::JOIN_OPTIONAL_TABLE)
            ->table('{table_permanent_join}', self::JOIN_LAST_USED_VERSION)
            ->table('{table_settings}', Application_Users::TABLE_USER_SETTINGS)

            ->alias('{users}', 'users')
            ->alias('{feedback}', 'feedback')
            ->alias('{last_used_version}', 'last_used_version')

            ->field('{setting_name}', Application_User_Storage_DB::COL_SETTING_NAME)
            ->field('{setting_value}', Application_User_Storage_DB::COL_SETTING_VALUE)
            ->field('{amount_feedbacks}', self::CUSTOM_COL_USER_FEEDBACK_AMOUNT)
            ->field('{email}', Application_Users_User::COL_EMAIL)
            ->field('{users_primary}', Application_Users::PRIMARY_NAME)
            ->field('{feedback_primary}', FeedbackCollection::PRIMARY_NAME)
            ->field('{feedback_text}', FeedbackRecord::COL_FEEDBACK);
    }

    protected function _registerJoins() : void
    {
        $this->addJoinStatement(
            sprintf("
                RIGHT OUTER JOIN 
                    {table_settings} AS {last_used_version}
                ON
                    {last_used_version}.{users_primary} = {users}.{users_primary}
                AND
                    {last_used_version}.{setting_name} = %s",
                $this->generatePlaceholder(Application_Driver::SETTING_USER_LAST_USED_VERSION)
            ),
            self::JOIN_LAST_USED_VERSION
        );

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

    /**
     * @return GenericIntegerItem[]
     */
    public function getItemsObjects(): array
    {
        $result = array();

        foreach ($this->getItems() as $item) {
            $result[] = new GenericIntegerItem(
                (int)$item[Application_Users::PRIMARY_NAME],
                (string)$item[Application_Users_User::COL_EMAIL]
            );
        }

        return $result;
    }
}
