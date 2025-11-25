<?php
/**
 * @package Application
 * @subpackage FilterCriteria
 */

declare(strict_types=1);

use Application\FilterCriteria\FilterCriteriaDBInterface;
use Application\FilterCriteria\FilterCriteriaException;
use Application\Interfaces\FilterCriteriaInterface;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper\JSONConverter;

/**
 * Database-specific filter criteria base class: allows
 * selecting data from tables in the database, with
 * database-specific methods for handling JOIN statements
 * and the like.
 *
 * > NOTE: For new projects, it is recommended to use the
 * > {@see Application_FilterCriteria_DatabaseExtended} class,
 * > which automates more tasks.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_FilterCriteria_Database extends Application_FilterCriteria implements FilterCriteriaDBInterface
{
    protected string $placeholderPrefix = 'PH';

    /**
     * @var array<array<string,mixed>>
     */
    protected array $queries = array();

    /**
     * @var array<string,string|DBHelper_StatementBuilder>
     */
    protected array $columnSelects = array();

    protected bool $distinct = false;
    protected int $placeholderCounter = 0;

    /**
     * @var array<string,array<int|string,mixed>>
     */
    protected array $placeholderHashes = array();

    /**
     * @var string[]
     */
    protected array $havings = array();

    /**
     * @var array<string,string|DBHelper_StatementBuilder>
     */
    protected array $groupBy = array();

    protected ?string $selectAlias = null;

    protected ?DBHelper_StatementBuilder_ValuesContainer $statementValues = null;

    /**
     * @var array<string,DBHelper_StatementBuilder>
     */
    protected array $selectStatements = array();

    /**
     * @var string[]
     */
    protected array $where = array();

    /**
     * Counts the number of matching records,
     * according to the current filter criteria.
     *
     * > NOTE: use the {@see self::countUnfiltered()}
     * > method to count all records, without matching
     * > the current criteria.
     *
     * @return int
     * @throws Application_Exception
     * @throws DBHelper_Exception
     * @see Application_FilterCriteria::countUnfiltered()
     */
    public function countItems() : int
    {
        $items = $this->fetchResults(true);

        $count = 0;
        $total = count($items);
        for($i=0; $i < $total; $i++) {
            $count += $items[$i]['count'];
        }

        return $count;
    }

    /**
     * @param bool $isCount
     * @return array<int,array<string,string>>
     * @throws Application_Exception
     * @throws DBHelper_Exception
     */
    private function fetchResults(bool $isCount) : array
    {
        $sql = $this->buildQuery($isCount);
        $vars = $this->getQueryVariables();

        $this->queries[] = array(
            'sql' => $sql,
            'vars' => $vars
        );

        if($this->dumpQuery) {
            DBHelper::enableDebugging();
        }

        $items = DBHelper::fetchAll($sql, $vars);

        if($this->dumpQuery) {
            DBHelper::disableDebugging();
        }

        return $items;
    }

    protected function getCountSelect() : string
    {
        if($this->distinct)
        {
            $column = $this->getCountColumn();

            if($column === '*') {
                throw new FilterCriteriaException(
                    'Cannot use DISTINCT with wildcard column',
                    'When using a distinct query, the count column name must be more specific than a wildcard.',
                    FilterCriteriaException::ERROR_CANNOT_USE_WILDCARD_AND_DISTINCT
                );
            }

            return sprintf(
                'COUNT(DISTINCT(%s)) AS `count`',
                $column
            );
        }

        return sprintf(
            'COUNT(%s) AS `count`',
            $this->getCountColumn()
        );
    }

    protected function getCountColumn() : string
    {
        return '*';
    }

    // region: Finalizing the query filters

    protected function _applyFilters() : void
    {
        $this->createStatementValues();
        $this->finalizeWhere();
        $this->registerJoins();
    }

    protected function finalizeWhere() : void
    {
        if (!isset($this->search))
        {
            return;
        }

        $searchTokens = $this->buildSearchTokens();

        if(!empty($searchTokens))
        {
            $this->addWhere($searchTokens);
        }
    }

    // endregion

    public function getWheres() : array
    {
        return $this->where;
    }

    /**
     * Parses the SQL and adds the DISTINCT keyword as needed to
     * the SELECT statement if the filter has been set to distinct.
     * This is done intelligently, only adding it if it has not
     * already been added manually.
     *
     * @param string|DBHelper_StatementBuilder $query
     * @return string
     *
     * @throws Application_Exception
     * @see FilterCriteriaException::ERROR_MISSING_SELECT_KEYWORD
     */
    protected function addDistinctKeyword(string|DBHelper_StatementBuilder $query) : string
    {
        $query = (string)$query;

        if($this->isCount || !$this->distinct) {
            return $query;
        }

        // there may be more than one select keyword in the query
        // because of subqueries, and it may already have a distinct
        // keyword. We assume that the first one we find is the
        // one we want to modify.
        $result = array();
        preg_match_all('/SELECT[ ]*DISTINCT|SELECT/U', $query, $result, PREG_PATTERN_ORDER);

        if(empty($result[0][0])) {
            throw new FilterCriteriaException(
                'SELECT keyword missing in the query.',
                'The query does not seem to have any SELECT keyword: '.$query,
                FilterCriteriaException::ERROR_MISSING_SELECT_KEYWORD
            );
        }

        // the distinct keyword has already been added
        $keyword = $result[0][0];
        if(stripos($keyword, 'distinct') !== false) {
            return $query;
        }

        // add the keyword safely, by splitting the query at the position
        // of the first select instance.
        $startPos = strpos($query, $keyword);
        $endPos = $startPos + strlen($keyword);
        $start = substr($query, 0, $endPos);
        $end = substr($query, $endPos);

        return $start.' DISTINCT '.$end;
    }

    /**
     * Retrieves the select statement for the query, which
     * is used in the <code>{WHAT}</code> variable in the query.
     * When fetching the number of records, this is automatically
     * replaced with a count statement.
     *
     * <b>Examples</b>
     *
     * Either an array with column names:
     * <pre>
     * array(
     *     `tablename`.`fieldname`,
     *     `tablename`.`other_fieldname`
     * )
     * </pre>
     *
     * Or a string:
     * <pre>
     * tablename.`fieldname`,
     * tablename.`another_fieldname`
     * </pre>
     *
     * The array is preferred for performance reasons as well as
     * stability (the string is split by commas).
     *
     * @return string|array<int,string|DBHelper_StatementBuilder>
     */
    abstract protected function getSelect() : string|array;

    /**
     * Retrieves the query to run, which is very simple
     * as it is mostly made of variables. Its purpose is
     * to set which tables the contents will be selected
     * from.
     *
     * Example:
     *
     * <pre>
     * SELECT {WHAT} FROM tablename {JOINS} {WHERE} {GROUPBY} {ORDERBY} {LIMIT}
     * </pre>
     *
     * @return string|DBHelper_StatementBuilder
     */
    abstract protected function getQuery() : string|DBHelper_StatementBuilder;

    /**
     * @var array<string,string>
     */
    protected array $placeholders = array();

    public function addPlaceholder(string $name, string|int|float $value) : self
    {
        $name = ':' . ltrim($name, ':');

        $this->placeholders[$name] = (string)$value;

        return $this;
    }

    public function getQueryVariables() : array
    {
        return $this->placeholders;
    }

    /**
     * @return $this
     */
    public function resetQueryVariables() : self
    {
        $this->placeholders = array();
        return $this;
    }

    /**
     * Adds a column to the select statement, to include additional
     * data in the result sets.
     *
     * @param string|DBHelper_StatementBuilder $columnSelect
     * @return $this
     */
    public function addSelectColumn(string|DBHelper_StatementBuilder $columnSelect) : self
    {
        $key = self::getUniqueKey($columnSelect);

        if(!isset($this->columnSelects[$key]))
        {
            $this->columnSelects[$key] = $columnSelect;
            $this->handleCriteriaChanged();
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function getSelects() : array
    {
        $select = $this->getSelect();

        if(empty($select))
        {
            throw new FilterCriteriaException(
                'Select fields list cannot be empty',
                'The method call [getSelect] returned an empty value.',
                FilterCriteriaException::ERROR_EMPTY_SELECT_FIELDS_LIST
            );
        }

        if(is_array($select))
        {
           $selects = $select;
        }
        else
        {
            $selects = ConvertHelper::explodeTrim(',', $select);
        }

        if(!empty($this->columnSelects))
        {
            $selects = array_merge($selects, $this->columnSelects);
        }

        // distinct queries require the ordering field to be part of the select
        if($this->distinct && !empty($this->orderField)) {
            $selects[] = $this->orderField;
        }

        $selects = array_map('strval', $selects);

        return array_unique($selects);
    }

    /**
     * @param string|DBHelper_StatementBuilder $statement
     * @return string
     */
    public static function getUniqueKey(string|DBHelper_StatementBuilder $statement) : string
    {
        if($statement instanceof DBHelper_StatementBuilder)
        {
            return $statement->getTemplate();
        }

        return $statement;
    }

    public function makeDistinct() : self
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Retrieves all matching items as an indexed array containing
     * associative array entries with the item data.
     *
     * @return array<int,array<string,string|int|float|bool|null>>
     * @throws Application_Exception|DBHelper_Exception
     * @see getItem()
     */
    public function getItems() : array
    {
        return $this->fetchResults(false);
    }

    public function getQueries() : array
    {
        $queries = array();

        foreach ($this->queries as $def)
        {
            $sql = $def['sql'];

            foreach ($def['vars'] as $name => $value) {
                $sql = str_replace(
                    ':'.ltrim($name, ':'),
                    JSONConverter::var2json($value),
                    $sql
                );
            }

            $queries[] = $sql;
        }

        return $queries;
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhere(string|DBHelper_StatementBuilder $statement) : self
    {
        $statement = (string)$statement;

        if(empty($statement) || $statement === '()') {
            throw new FilterCriteriaException(
                'Invalid where statement',
                sprintf(
                    'Where statements may not be empty, and must be valid SQL conditions in statement: %s',
                    $statement
                ),
                FilterCriteriaException::ERROR_INVALID_WHERE_STATEMENT
            );
        }

        if(!in_array($statement, $this->where, true))
        {
            $this->where[] = $statement;
            $this->handleCriteriaChanged();
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhereStatement(string $template) : self
    {
        return $this->addWhere($this->statement($template));
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhereColumnISNULL(string|DBHelper_StatementBuilder $column, bool $null=true) : self
    {
        $token = '';
        if($null===false) {
            $token = 'NOT ';
        }

        return $this->addWhere(sprintf(
            "%s IS %sNULL",
            $column,
            $token
        ));
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhereColumnNOT_NULL(string|DBHelper_StatementBuilder $column) : self
    {
        return $this->addWhereColumnISNULL($column, false);
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhereColumnIN(string|DBHelper_StatementBuilder $column, array $values, bool $exclude=false) : self
    {
        $column = (string)$column;

        if(empty($values)) {
            return $this;
        }

        $tokens = array();
        foreach($values as $value) {
            $tokens[] = $this->generatePlaceholder($value);
        }

        $connector = 'IN';
        if($exclude) {
            $connector = 'NOT IN';
        }

        return $this->addWhere(sprintf(
            "%s %s(%s)",
            $column,
            $connector,
            implode(',', $tokens)
        ));
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhereColumnLIKE(string|DBHelper_StatementBuilder $column, string|array $value) : self
    {
        $column = $this->quoteColumnName($column);

        if(is_array($value))
        {
            foreach($value as $entry)
            {
                $this->addWhereColumnLike($column, $entry);
            }

            return $this;
        }

        return $this->addWhere($column." LIKE ".$this->generatePlaceholder('%'.$value.'%'));
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhereColumnNOT_IN(string|DBHelper_StatementBuilder $column, array $values) : self
    {
        return $this->addWhereColumnIN($column, $values, true);
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhereColumnEquals(string|DBHelper_StatementBuilder $column, string $value) : self
    {
        $placeholder = $this->generatePlaceholder($value);

        return $this->addWhere(sprintf(
            "%s = %s",
            $column,
            $placeholder
        ));
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function addWhereColumnNOT_Equals(string|DBHelper_StatementBuilder $column, string $value) : self
    {
        $placeholder = $this->generatePlaceholder($value);

        return $this->addWhere(sprintf(
            "%s != %s",
            $column,
            $placeholder
        ));
    }

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @return $this
     * @throws FilterCriteriaException
     */
    public function addWhereColumnNOT_Empty(string|DBHelper_StatementBuilder $column) : self
    {
        return $this->addWhere(sprintf(
            "%s != ''",
            $column
        ));
    }

    // region: JOIN statements handling

    /**
     * @var array<string,Application_FilterCriteria_Database_Join>
     */
    protected array $joins = array();

    /**
     * @var array<string,Application_FilterCriteria_Database_Join>
     */
    private array $registeredJoins = array();

    private bool $joinsRegistered = false;

    abstract protected function _registerJoins() : void;

    final protected function registerJoins() : void
    {
        if($this->joinsRegistered === true)
        {
            return;
        }

        $this->joinsRegistered = true;

        $this->_registerJoins();
    }

    public function getJoins(bool $includeRegistered=false) : array
    {
        $this->registerJoins();

        $result = array();

        foreach($this->joins as $join)
        {
            $id = $join->getID();
            $result[$id] = $join;

            if(!$join->hasJoins())
            {
                continue;
            }

            $joinIDs = $join->getRequiredJoinIDs();

            foreach($joinIDs as $joinID)
            {
                $result[$joinID] = $this->getJoinByID($joinID);
            }
        }

        if($includeRegistered)
        {
            $result = array_merge($result, $this->registeredJoins);
        }

        return array_values($result);
    }

    public function getJoinsOrdered(bool $includeRegistered=false) : array
    {
        $joins = $this->getJoins($includeRegistered);

        // Sort the joins so that all joins that depend
        // on another are above their dependent joins.
        // All other, independent joins get appended and
        // sorted by their ID at the end of the list.
        usort($joins, static function(Application_FilterCriteria_Database_Join $a, Application_FilterCriteria_Database_Join $b) : int
        {
            if($a->dependsOn($b))
            {
                return 1;
            }

            if($b->dependsOn($a))
            {
                return -1;
            }

            $aJoins = $a->hasJoins() || $a->hasDependentJoins();
            $bJoins = $b->hasJoins() || $b->hasDependentJoins();

            if($aJoins && !$bJoins)
            {
                return -1;
            }

            if(!$aJoins && $bJoins)
            {
                return 1;
            }

            return strnatcasecmp($a->getID(), $b->getID());
        });

        return $joins;
    }

    /**
     * @param string|DBHelper_StatementBuilder $statement
     * @param string $joinID
     * @return Application_FilterCriteria_Database_Join
     */
    public function addJoin(string|DBHelper_StatementBuilder $statement, string $joinID='') : Application_FilterCriteria_Database_Join
    {
        $join = new Application_FilterCriteria_Database_Join($this, $statement, $joinID);
        $id = $join->getID();

        if(isset($this->joins[$id]))
        {
            return $this->joins[$id];
        }

        // Used the registered version of the join,
        // if one is available.
        if(isset($this->registeredJoins[$id]))
        {
            $join = $this->registeredJoins[$id];
        }

        $this->joins[$id] = $join;

        $this->handleCriteriaChanged();

        return $join;
    }

    public function addJoinStatement(string $template, string $joinID='') : DBHelper_StatementBuilder
    {
        $statement = $this->statement($template);

        $this->addJoin($statement, $joinID);

        return $statement;
    }

    public function requireJoin(string $joinID) : self
    {
        if($this->hasJoin($joinID))
        {
            return $this;
        }

        $join = $this->getJoinByID($joinID);

        $this->joins[$joinID] = $join;

        return $this;
    }

    protected function hasJoin($joinID) : bool
    {
        $this->registerJoins();

        return isset($this->joins[$joinID]);
    }

    protected function isJoinRegistered($joinID) : bool
    {
        return isset($this->registeredJoins[$joinID]);
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     * @see FilterCriteriaException::ERROR_JOIN_ID_NOT_FOUND
     */
    public function getJoinByID(string $joinID) : Application_FilterCriteria_Database_Join
    {
        $this->registerJoins();

        if(isset($this->joins[$joinID]))
        {
            return $this->joins[$joinID];
        }

        if(isset($this->registeredJoins[$joinID]))
        {
            return $this->registeredJoins[$joinID];
        }

        throw new FilterCriteriaException(
            'Missing JOIN statement.',
            sprintf(
                'JOIN not found by ID [%s].',
                $joinID
            ),
            FilterCriteriaException::ERROR_JOIN_ID_NOT_FOUND
        );
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     * @see FilterCriteriaException::ERROR_JOIN_ALREADY_ADDED
     * @see FilterCriteriaException::ERROR_JOIN_ALREADY_REGISTERED
     */
    public function registerJoin(string $joinID, string|DBHelper_StatementBuilder $statement) : Application_FilterCriteria_Database_Join
    {
        $join = new Application_FilterCriteria_Database_Join($this, $statement, $joinID);
        $id = $join->getID();

        if(isset($this->joins[$id]))
        {
            throw new FilterCriteriaException(
                'JOIN statement already added.',
                sprintf(
                    'Cannot register JOIN statement [%s], it has already been added.',
                    $joinID
                ),
                FilterCriteriaException::ERROR_JOIN_ALREADY_ADDED
            );
        }

        if(!isset($this->registeredJoins[$id]))
        {
            $this->registeredJoins[$id] = $join;
            $this->handleCriteriaChanged();

            return $join;
        }

        throw new FilterCriteriaException(
            'JOIN statement already registered.',
            sprintf(
                'The statement has already been registered: 
                %s',
                $statement
            ),
            FilterCriteriaException::ERROR_JOIN_ALREADY_REGISTERED
        );
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function registerJoinStatement(string $joinID, string $statementTemplate) : Application_FilterCriteria_Database_Join
    {
        return $this->registerJoin($joinID, $this->statement($statementTemplate));
    }

    // endregion

    /**
     * @param string|DBHelper_StatementBuilder $statement
     * @return $this
     */
    public function addHaving(string|DBHelper_StatementBuilder $statement) : self
    {
        $statement = (string)$statement;

        if(!in_array($statement, $this->havings, true))
        {
            $this->havings[] = $statement;
            $this->handleCriteriaChanged();
        }

        return $this;
    }

    // region: Utility methods

    /**
     * Returns the internal SQL statement values container
     * that is used to store all placeholder values for
     * statements managed by the filter criteria class methods.
     *
     * @return DBHelper_StatementBuilder_ValuesContainer
     */
    final protected function createStatementValues() : DBHelper_StatementBuilder_ValuesContainer
    {
        if(!isset($this->statementValues))
        {
            $this->statementValues = statementValues();
            $this->_registerStatementValues($this->statementValues);
        }

        return $this->statementValues;
    }

    abstract protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container) : void;

    /**
     * @param string|DBHelper_StatementBuilder $name
     * @return string
     */
    protected function quoteColumnName(string|DBHelper_StatementBuilder $name) : string
    {
        if($name instanceof DBHelper_StatementBuilder)
        {
            return (string)$name;
        }

        if($name === '') {
            return '';
        }

        if($name[0] === '`' || str_starts_with($name, Application_FilterCriteria_Database_CustomColumn::MARKER_SUFFIX)) {
            return $name;
        }

        return '`'.$name.'`';
    }

    /**
     * Generates a placeholder name unique for the specified value:
     * will return the same placeholder name for every same value
     * within the same request.
     *
     * @param string|int|float|null $value Placeholder name with prepended `:`.
     * @return string
     */
    public function generatePlaceholder(string|int|float|null $value) : string
    {
        $value = (string)$value;
        $hash = md5($value);

        if(isset($this->placeholderHashes[$hash]))
        {
            $pl = $this->placeholderHashes[$hash];

            // ensure that the placeholder value still exists
            if(!isset($this->placeholders[$pl[0]])) {
                $this->addPlaceholder($pl[0], $pl[1]);
            }

            return $pl[0];
        }

        $this->placeholderCounter++;

        // to avoid a placeholder like PH1 interfering with a
        // longer placeholder like PH11, we add zero padding.
        $name = ':'.$this->placeholderPrefix.sprintf('%04d', $this->placeholderCounter);

        $this->placeholderHashes[$hash] = array($name, $value);

        $this->addPlaceholder($name, $value);

        return $name;
    }

    // endregion


    public function debug() : self
    {
        $queries = $this->getQueries();
        foreach($queries as $query) {
            echo '<pre>'.print_r($query ,true).'</pre>';
        }

        echo '<pre>Limit:'.print_r($this->limit ,true).'</pre>';
        echo '<pre>Offset:'.print_r($this->offset ,true).'</pre>';

        return $this;
    }

    /**
     * @param string|DBHelper_StatementBuilder $groupBy
     * @return $this
     */
    public function addGroupBy(string|DBHelper_StatementBuilder $groupBy) : self
    {
        if($groupBy instanceof DBHelper_StatementBuilder)
        {
            $key = $groupBy->getTemplate();
        }
        else
        {
            $key = $groupBy;
        }

        if(!isset($this->groupBy[$key]))
        {
            $this->groupBy[$key] = $groupBy;
            $this->handleCriteriaChanged();
        }

        return $this;
    }

    public function addGroupByStatement(string $template) : DBHelper_StatementBuilder
    {
        $statement = $this->statement($template);

        $this->addGroupBy($statement);

        return $statement;
    }

    /**
     * Converts all entries to statements before adding them.
     *
     * @param string ...$args
     * @return $this
     */
    public function addGroupByStatements(...$args) : self
    {
        foreach($args as $groupBy)
        {
            $this->addGroupBy($this->statement($groupBy));
        }

        return $this;
    }

    public function getGroupBys() : array
    {
        $items = array_values($this->groupBy);

        return array_map('strval', $items);
    }

    // region: Building and rendering the query

    /**
     * @param bool $isCount
     * @return string
     * @throws Application_Exception
     */
    protected function buildQuery(bool $isCount=false) : string
    {
        $query = $this->getQuery();

        $this->applyFilters();

        $this->isCount = $isCount;

        $query = $this->addDistinctKeyword($query);

        $replaces = array(
            '{WHAT}' => null, // So it is at the beginning of the array.
            '{WHERE}' => $this->buildWhere(),
            '{GROUPBY}' => $this->buildGroupBy(),
            '{ORDERBY}' => $this->buildOrderby(),
            '{JOINS}' => $this->buildJoins(),
            '{LIMIT}' => $this->buildLimit()
        );

        // do this at the end, so selects can be added dynamically
        $replaces['{WHAT}'] = $this->buildSelect();

        return str_replace(array_keys($replaces), array_values($replaces), $query);
    }

    public function renderQuery() : string
    {
        return $this->buildQuery();
    }

    /**
     * Builds the list of column names to select in
     * the query. This automatically adds columns as
     * needed according to the settings, like whether
     * this is a distinct query.
     *
     * @throws Application_Exception
     * @return string The list of columns to select, without the SELECT keyword.
     * @see getSelect()
     * @see addSelectColumn()
     */
    protected function buildSelect() : string
    {
        if($this->isCount)
        {
            return $this->getCountSelect();
        }

        $selects = array_unique($this->getSelects());

        $selects = array_map('trim', $selects);

        // in distinct queries, it is safer to add all select fields to the group by
        if($this->distinct) {
            foreach($selects as $field) {
                $this->addGroupBy($field);
            }
        }

        return implode(','.PHP_EOL.'    ', $selects);
    }

    protected function buildSearchTokens() : string
    {
        $searchFields = $this->getSearchFields();
        if (empty($searchFields)) {
            return '';
        }

        $searchTerms = $this->getSearchTerms();
        if(empty($searchTerms)) {
            return '';
        }

        $totalTerms = count($searchTerms);
        $totalFields = count($searchFields);

        $parts = array();
        $connectorAdded = false;
        $like = true;

        for($i=0; $i<$totalTerms; $i++)
        {
            $term = $searchTerms[$i];

            // escape the underscore characters, which have special
            // meaning in SQL and will not give the expected results
            $term = str_replace('_', '\_', $term);

            if($term === 'NOT' || $term === t('NOT')) {
                $like = false;
                continue;
            }

            $connector = $this->getConnector($term);
            if ($connector) {
                // search terms may not start with a connector
                if($i===0) {
                    $this->addWarning('The search terms may not start with a logical operator.');
                    continue;
                }

                if($i===($totalTerms-1)) {
                    $this->addWarning('The search terms may not end with a logical operator.');
                    continue;
                }

                $parts[] = $connector;
                $connectorAdded = true;
                continue;
            } else {
                if ($i > 0 && $i < $totalTerms) {
                    if (!$connectorAdded && !empty($parts)) {
                        $parts[] = ' AND ';
                    } else {
                        $connectorAdded = false;
                    }
                }
            }

            $concatenator = 'OR';
            $likeToken = 'LIKE';
            if(!$like) {
                $likeToken = 'NOT LIKE';
                $concatenator = 'AND';
                $like = true;
            }

            $fieldTokens = array();
            for($j=0; $j<$totalFields; $j++) {
                $fieldName = (string)$searchFields[$j];
                $placeholder = $this->generatePlaceholder('%'.$term.'%');
                $fieldTokens[] = $fieldName.' '.$likeToken.' '.$placeholder;
            }


            $parts[] = '('.implode(' '.$concatenator.' ', $fieldTokens).')';
        }

        return '('.implode(' ', $parts).')';
    }

    protected function buildJoins() : string
    {
        $joins = $this->getJoinsOrdered();

        $result = array();

        // Second pass: collect the actual statements.
        foreach($joins as $join)
        {
            $result[] = $join->getStatement();
        }

        return implode(PHP_EOL, $result);
    }

    protected function canAddTableAlias(string $field) : bool
    {
        return
            !str_contains($field, '.')
                &&
            !str_contains($field, '\\')
                &&
            !str_contains($field, Application_FilterCriteria_Database_CustomColumn::MARKER_SUFFIX);
    }

    protected function buildOrderby() : string
    {
        // no ordering in count queries
        if ($this->isCount || !isset($this->orderField)) {
            return '';
        }

        $field = (string)$this->orderField;

        if($field === '') {
            return '';
        }

        // leave the name be if it has a dot (meaning it has a table or alias specified)
        if($this->canAddTableAlias($field))
        {
            $field = $this->quoteColumnName($this->orderField);

            // add the table alias if it is present
            if(isset($this->selectAlias) && $field[0] !== '\\') {
                $field = $this->selectAlias.'.'.$field;
            }
        }

        $field = str_replace('\\', '', $field);

        return
            PHP_EOL .
            'ORDER BY' . PHP_EOL .
            "     ".$field." ".$this->orderDir;
    }

    protected function buildLimit() : string
    {
        if(!$this->isCount && ($this->limit > 0 || $this->offset > 0)) {
            return sprintf(" LIMIT %s,%s", $this->limit, $this->offset);
        }

        return '';
    }

    protected function buildWhere() : string
    {
        $wheres = $this->getWheres();

        if (empty($wheres)) {
            return '';
        }

        return
            PHP_EOL .
            'WHERE' . PHP_EOL .
            implode(PHP_EOL . 'AND' . PHP_EOL, $wheres);
    }

    protected function buildGroupBy() : string
    {
        $groupBy = $this->getGroupBys();

        if(empty($groupBy)) {
            return '';
        }

        return " GROUP BY ".implode(', ', $groupBy).$this->buildHavings();
    }

    protected function buildHavings() : string
    {
        if(!empty($this->havings)) {
            return " HAVING ".implode(' AND ', $this->havings);
        }

        return '';
    }

    // endregion

    /**
     * @param string $alias
     * @return $this
     */
    protected function setSelectAlias(string $alias) : self
    {
        if($this->selectAlias !== $alias)
        {
            $this->selectAlias = $alias;
            $this->handleCriteriaChanged();
        }

        return $this;
    }

    /**
     * @inheritDoc
     * @throws FilterCriteriaException
     */
    public function selectCriteriaDate(string $type, string $dateSearchString) : self
    {
        if(empty($dateSearchString)) {
            return $this;
        }

        $parse = new Application_FilterSettings_DateParser($dateSearchString);
        if(!$parse->isValid()) {
            $this->addWarning($parse->getErrorMessage());
            return $this;
        }

        $result = $parse->getDates();
        foreach($result as $entry) {
            $this->selectCriteriaValue($type, $entry['sql']);
        }

        return $this;
    }

    /**
     * Adds the required WHERE statements for a date search stored
     * in the specified type, for the given column.
     *
     * @param string $type
     * @param string $column
     * @return $this
     * @throws Application_Exception
     */
    public function addDateSearch(string $type, string $column) : self
    {
        $values = $this->getCriteriaValues($type);
        if(!empty($values)) {
            foreach($values as $sql) {
                $this->addWhere($column.' '.$sql);
            }
        }

        return $this;
    }

    /**
     * Adds an SQL statement from a statement builder
     * template. It uses the internal statement values
     * container for filling the placeholders.
     *
     * @param string $template
     * @return DBHelper_StatementBuilder
     */
    public function statement(string $template) : DBHelper_StatementBuilder
    {
        return $this->createStatementValues()->statement($template);
    }

    /**
     * @param string|DBHelper_StatementBuilder $fieldName
     * @param string $orderDir
     * @return $this
     */
    public function setOrderBy($fieldName, string $orderDir = FilterCriteriaInterface::ORDER_DIR_ASCENDING) : self
    {
        return parent::setOrderBy((string)$fieldName, $orderDir);
    }

    /**
     * Adds a select column from an SQL statement template,
     * which will be added to the `SELECT` part of the query.
     *
     * NOTE: Each template is only added once.
     *
     * @param string $template
     * @param bool $groupBy Whether to add the column to the "GROUP BY" statement.
     * @return DBHelper_StatementBuilder
     */
    protected function addSelectStatement(string $template, bool $groupBy=true) : DBHelper_StatementBuilder
    {
        if(!isset($this->selectStatements[$template]))
        {
            $this->selectStatements[$template] = $this->statement($template);
            $this->addSelectColumn($this->selectStatements[$template]);
        }

        if($groupBy)
        {
            $this->addGroupBy($this->selectStatements[$template]);
        }

        return $this->selectStatements[$template];
    }
}
