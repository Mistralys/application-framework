<?php

declare(strict_types=1);

namespace Application\FilterCriteria;

use Application\Collection\CollectionItemInterface;
use Application\Interfaces\FilterCriteriaInterface;
use Application_FilterCriteria_Database_Join;
use DBHelper_StatementBuilder;

interface FilterCriteriaDBInterface extends FilterCriteriaInterface
{
    /**
     * Stores placeholders to replace it with variables in the query.
     *
     * @param string|int|float $value
     * @param string $name
     * @return $this
     */
    public function addPlaceholder(string $name, string|int|float $value) : self;

    /**
     * Retrieves an associative array with placeholder => value pairs of
     * variables to use in the query.
     *
     * @return array<string,string>
     */
    public function getQueryVariables() : array;

    public function getWheres() : array;

    /**
     * @return $this
     */
    public function resetQueryVariables() : self;

    /**
     * Adds a column to the select statement, to include additional
     * data in the result sets.
     *
     * @param string|DBHelper_StatementBuilder $columnSelect
     * @return $this
     */
    public function addSelectColumn(string|DBHelper_StatementBuilder $columnSelect) : self;

    /**
     * @return string[]
     */
    public function getSelects() : array;

    /**
     * Sets this query as distinct: the SELECT statement will
     * automatically be changed, and other details also be
     * adjusted, like adding the order column to the selected
     * fields for compatibility reasons for example.
     *
     * @return $this
     */
    public function makeDistinct() : self;

    /**
     * Gets all queries that have been run so far.
     * @return string[]
     */
    public function getQueries() : array;

    /**
     * Adds a where statement (without the `WHERE`).
     *
     * @param string|DBHelper_StatementBuilder $statement
     * @return $this
     */
    public function addWhere(string|DBHelper_StatementBuilder $statement) : self;

    /**
     * @param string $template
     * @return $this
     */
    public function addWhereStatement(string $template) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param bool $null
     * @return $this
     */
    public function addWhereColumnISNULL(string|DBHelper_StatementBuilder $column, bool $null=true) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @return $this
     */
    public function addWhereColumnNOT_NULL(string|DBHelper_StatementBuilder $column) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string[] $values
     * @param bool $exclude
     * @return $this
     */
    public function addWhereColumnIN(string|DBHelper_StatementBuilder $column, array $values, bool $exclude=false) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string|string[] $value
     * @return $this
     */
    public function addWhereColumnLIKE(string|DBHelper_StatementBuilder $column, string|array $value) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string|string[] $value
     * @return $this
     */
    public function addWhereColumnNOT_LIKE(string|DBHelper_StatementBuilder $column, string|array $value) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string[] $values
     * @return $this
     */
    public function addWhereColumnNOT_IN(string|DBHelper_StatementBuilder $column, array $values) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string $value
     * @return $this
     */
    public function addWhereColumnEquals(string|DBHelper_StatementBuilder $column, string $value) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string $value
     * @return $this
     */
    public function addWhereColumnNOT_Equals(string|DBHelper_StatementBuilder $column, string $value) : self;

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @return $this
     */
    public function addWhereColumnNOT_Empty(string|DBHelper_StatementBuilder $column) : self;

    /**
     * Retrieves all join statements that are currently
     * in use in the criteria. Includes all dependencies
     * if a join requires another one, even if that has
     * only been registered so far.
     *
     * @return Application_FilterCriteria_Database_Join[]
     */
    public function getJoins(bool $includeRegistered=false) : array;

    /**
     * Retrieves all join statements currently used in
     * the criteria, ordered to ensure that joins that
     * depend on each other are added in the correct
     * sequence.
     *
     * @return Application_FilterCriteria_Database_Join[]
     */
    public function getJoinsOrdered(bool $includeRegistered=false) : array;

    /**
     * @param string|DBHelper_StatementBuilder $statement
     * @param string $joinID
     * @return Application_FilterCriteria_Database_Join
     */
    public function addJoin(string|DBHelper_StatementBuilder $statement, string $joinID='') : Application_FilterCriteria_Database_Join;

    /**
     * @param string $template
     * @param string $joinID
     * @return DBHelper_StatementBuilder
     */
    public function addJoinStatement(string $template, string $joinID='') : DBHelper_StatementBuilder;

    /**
     * Adds a `JOIN` statement that was previously registered
     * with {@see Application_FilterCriteria_Database::registerJoin()}.
     *
     * Use this to add joins to a query only when they are needed,
     * as alternative to {@see Application_FilterCriteria_Database::addJoin()},
     * which adds it regardless of whether it is actually used.
     *
     * > NOTE: If the join has already been added, this will be ignored.
     *
     * @param string $joinID
     * @return $this
     * @see FilterCriteriaException::ERROR_JOIN_ID_NOT_FOUND
     */
    public function requireJoin(string $joinID) : self;

    /**
     * Gets a join statement, either from those that have
     * been added, or who have been registered but not added
     * yet.
     *
     * @param string $joinID
     * @return Application_FilterCriteria_Database_Join
     */
    public function getJoinByID(string $joinID) : Application_FilterCriteria_Database_Join;

    /**
     * Registers a `JOIN` statement that can be referenced
     * by its ID, to allow columns to require joins only
     * when they are actually needed.
     *
     * @param string $joinID
     * @param string|DBHelper_StatementBuilder $statement
     * @return Application_FilterCriteria_Database_Join
     */
    public function registerJoin(string $joinID, string|DBHelper_StatementBuilder $statement) : Application_FilterCriteria_Database_Join;

    /**
     * Registers a `JOIN` statement using a statement builder
     * template. The join will not be used unless it is specifically
     * added using {@see Application_FilterCriteria_Database::requireJoin()},
     * or a custom column requires it.
     *
     * @param string $joinID
     * @param string $statementTemplate Statement template
     * @return Application_FilterCriteria_Database_Join
     */
    public function registerJoinStatement(string $joinID, string $statementTemplate) : Application_FilterCriteria_Database_Join;

    /**
     * @param string|DBHelper_StatementBuilder $statement
     * @return $this
     */
    public function addHaving(string|DBHelper_StatementBuilder $statement) : self;

    /**
     * Generates a placeholder name unique for the specified value:
     * will return the same placeholder name for every same value
     * within the same request.
     *
     * @param string|int|float|null $value Placeholder name with prepended `:`.
     * @return string
     */
    public function generatePlaceholder(string|int|float|null $value) : string;

    /**
     * @return $this
     */
    public function debug() : self;

    /**
     * @param string|DBHelper_StatementBuilder $groupBy
     * @return $this
     */
    public function addGroupBy(string|DBHelper_StatementBuilder $groupBy) : self;

    public function addGroupByStatement(string $template) : DBHelper_StatementBuilder;

    /**
     * Converts all entries to statements before adding them.
     *
     * @param string ...$args
     * @return $this
     */
    public function addGroupByStatements(...$args) : self;

    /**
     * @return string[]
     */
    public function getGroupBys() : array;

    public function renderQuery() : string;

    /**
     * Selects the date ranges specified by the date string, and
     * stores the corresponding SQL statements under the given type
     * (works like the {@link selectCriteriaValue() method).
     *
     * @param string $type
     * @param string $dateSearchString
     * @return $this
     */
    public function selectCriteriaDate(string $type, string $dateSearchString) : self;

    /**
     * Adds the required WHERE statements for a date search stored
     * in the specified type, for the given column.
     *
     * @param string $type
     * @param string $column
     * @return $this
     */
    public function addDateSearch(string $type, string $column) : self;

    /**
     * Adds an SQL statement from a statement builder
     * template. It uses the internal statement values
     * container for filling the placeholders.
     *
     * @param string $template
     * @return DBHelper_StatementBuilder
     */
    public function statement(string $template) : DBHelper_StatementBuilder;

    /**
     * Retrieves all matching record instances.
     * @return CollectionItemInterface[]
     */
    public function getItemsObjects() : array;

    /**
     * Retrieves a list of fields in the query that gets
     * built that can be used to search in. Must return
     * an indexed array with field names.
     *
     * Example:
     *
     * array(
     *     '`field_name`',
     *     'tablename.`field_name`'
     * )
     *
     * @return array<int,string|DBHelper_StatementBuilder>
     */
    public function getSearchFields() : array;
}
