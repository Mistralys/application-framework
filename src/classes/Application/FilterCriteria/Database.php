<?php
/**
 * File containing the class {@see Application_FilterCriteria_Database}.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @see Application_FilterCriteria_Database
 */

use AppUtils\ConvertHelper;

/**
 * Database-specific filter criteria base class: allows
 * selecting data from tables in the database, with database-
 * specific methods for handling JOIN statements and the like.
 *
 * NOTE: For new projects, it is recommended to use the
 * {@see Application_FilterCriteria_DatabaseExtended} class,
 * which automates more tasks.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_FilterCriteria_Database extends Application_FilterCriteria
{
    const ERROR_INVALID_WHERE_STATEMENT = 710001;
    const ERROR_EMPTY_SELECT_FIELDS_LIST = 710002;
    const ERROR_MISSING_SELECT_KEYWORD = 710004;
    const ERROR_CUSTOM_COLUMN_NOT_REGISTERED = 710005;
    const ERROR_JOIN_ID_NOT_FOUND = 710006;
    const ERROR_JOIN_ALREADY_REGISTERED = 710007;
    const ERROR_JOIN_ALREADY_ADDED = 710008;

    const DEFAULT_SELECT = 'SELECT {WHAT} FROM tablename {JOINS} {WHERE} {GROUPBY} {ORDERBY} {LIMIT}';

    /**
     * @var string
     */
    protected $placeholderPrefix = 'PH';

    /**
     * @var array<array<string,mixed>>
     */
    protected $queries = array();

    /**
     * @var array<string,string|DBHelper_StatementBuilder>
     */
    protected $columnSelects = array();

    /**
     * @var bool
     */
    protected $distinct = false;

    /**
     * @var int
     */
    protected $placeholderCounter = 0;

    /**
     * @var array<string,array<mixed>>
     */
    protected $placeholderHashes = array();

    /**
     * @var string[]
     */
    protected $havings = array();

    /**
     * @var array<string,string|DBHelper_StatementBuilder>
     */
    protected $groupBy = array();

    /**
     * @var string
     */
    protected $selectAlias;

    /**
     * @var DBHelper_StatementBuilder_ValuesContainer|NULL
     */
    protected $statementValues;

    /**
     * @var array<string,DBHelper_StatementBuilder>
     */
    protected $selectStatements = array();

    /**
     * @var string[]
     */
    protected $where = array();

    /**
     * Counts the amount of matching records,
     * according to the current filter criteria.
     *
     * NOTE: use the <code>countUnfiltered()</code>
     * method to count all records, without matching
     * the current criteria.
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
            $count = $count + $items[$i]['count'];
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
        $distinct = '';
        if($this->distinct) {
            $distinct = 'DISTINCT';
        }

        return sprintf(
            'COUNT(%s %s) AS `count`',
            $distinct,
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
     * @see Application_FilterCriteria_Database::ERROR_MISSING_SELECT_KEYWORD
     */
    protected function addDistinctKeyword($query) : string
    {
        $query = strval($query);

        if($this->isCount || !$this->distinct) {
            return $query;
        }

        // there may be more than one select keyword in the query
        // because of subqueries, and it may already have a distinct
        // keyword. We assume that the first one we find is the
        // one we want to modify.
        $result = array();
        preg_match_all('/SELECT[ ]*DISTINCT|SELECT/sU', $query, $result, PREG_PATTERN_ORDER);

        if(empty($result) || !isset($result[0][0])) {
            throw new Application_Exception(
                'SELECT keyword missing in the query.',
                'The query does not seem to have any SELECT keyword: '.$query,
                self::ERROR_MISSING_SELECT_KEYWORD
            );
        }

        // the distinct keyword has already been added
        $keyword = $result[0][0];
        if(stristr($keyword, 'distinct')) {
            return $query;
        }

        // add the keyword safely, by splitting the query at the position
        // of the first select instance.
        $startPos = strpos($query, $keyword);
        $endPos = $startPos + strlen($keyword);
        $start = substr($query, 0, $endPos);
        $end = substr($query, $endPos);
        $query = $start.' DISTINCT '.$end;

        return $query;
    }

    /**
     * Retrieves the select statement for the query, which
     * is used in the <code>{WHAT}</code> variable in the query.
     * When fetching the amount of records, this is automatically
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
    abstract protected function getSelect();

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
    abstract protected function getSearchFields();

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
    abstract protected function getQuery();

    /**
     * @var array<string,string>
     */
    protected $placeholders = array();

    /**
     * Stores placeholders to replace with variables in the query.
     * For placeholders that should not be reset with each query,
     * set the persistent parameter to true.
     *
     * @param string|int|float $value
     * @param string $name
     * @return $this
     */
    public function addPlaceholder(string $name, $value)
    {
        if (!substr($name, 0) == ':')
        {
            $name = ':' . $name;
        }

        $this->placeholders[$name] = strval($value);

        return $this;
    }

    /**
     * Retrieves an associative array with placeholder => value pairs of
     * variables to use in the query.
     *
     * @return array<string,string>
     */
    protected function getQueryVariables()
    {
        return $this->placeholders;
    }

    /**
     * @return $this
     */
    protected function resetQueryVariables()
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
    public function addSelectColumn($columnSelect)
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
     * @return string[]
     * @throws Application_Exception
     */
    public function getSelects() : array
    {
        $selects = $this->getSelect();

        if(empty($selects))
        {
            throw new Application_Exception(
                'Select fields list cannot be empty',
                'The method call [getSelect] returned an empty value.',
                self::ERROR_EMPTY_SELECT_FIELDS_LIST
            );
        }

        if(!is_array($selects))
        {
            $selects = ConvertHelper::explodeTrim(',', $selects);
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
    public static function getUniqueKey($statement) : string
    {
        if($statement instanceof DBHelper_StatementBuilder)
        {
            return $statement->getTemplate();
        }

        return $statement;
    }

    /**
     * Sets this query as distinct: the SELECT statement will
     * automatically be changed, and other details also be
     * adjusted, like adding the order column to the selected
     * fields for compatibility reasons for example.
     *
     * @return $this
     */
    public function makeDistinct()
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * Retrieves all matching items as an indexed array containing
     * associative array entries with the item data.
     *
     * @return array<int,array<string,string>>
     * @throws Application_Exception|DBHelper_Exception
     * @see getItem()
     */
    public function getItems() : array
    {
        return $this->fetchResults(false);
    }

    /**
     * @return string[]
     */
    public function getQueries() : array
    {
        $queries = array();
        foreach ($this->queries as $def) {
            $sql = $def['sql'];
            foreach ($def['vars'] as $name => $value) {
                $sql = str_replace(':'.$name, json_encode($value), $sql);
            }

            $queries[] = $sql;
        }

        return $queries;
    }

    /**
     * Adds a where statement (without the `WHERE`).
     *
     * @param string|DBHelper_StatementBuilder $statement
     * @return $this
     *
     * @throws Application_Exception
     */
    public function addWhere($statement)
    {
        $statement = strval($statement);

        if(empty($statement) || $statement == '()') {
            throw new Application_Exception(
                'Invalid where statement',
                sprintf(
                    'Where statements may not be empty, and must be valid SQL conditions in statement: %s',
                    $statement
                ),
                self::ERROR_INVALID_WHERE_STATEMENT
            );
        }

        if(!in_array($statement, $this->where))
        {
            $this->where[] = $statement;
            $this->handleCriteriaChanged();
        }

        return $this;
    }

    /**
     * @param string $template
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereStatement(string $template)
    {
        return $this->addWhere($this->statement($template));
    }

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param bool $null
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnISNULL($column, bool $null=true)
    {
        $token = '';
        if($null===true) {
            $token = 'NOT ';
        }

        return $this->addWhere(sprintf(
            "%s IS %sNULL",
            (string)$column,
            $token
        ));
    }

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnNOT_NULL($column)
    {
        return $this->addWhereColumnISNULL($column, false);
    }

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string[] $values
     * @param bool $exclude
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnIN($column, array $values, bool $exclude=false)
    {
        $column = strval($column);

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
     * @param string|DBHelper_StatementBuilder $column
     * @param string|string[] $value
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnLIKE($column, $value)
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
     * @param string|DBHelper_StatementBuilder $column
     * @param string[] $values
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnNOT_IN($column, array $values)
    {
        return $this->addWhereColumnIN($column, $values, true);
    }

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string $value
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnEquals($column, string $value)
    {
        $placeholder = $this->generatePlaceholder($value);

        return $this->addWhere(sprintf(
            "%s = %s",
            (string)$column,
            $placeholder
        ));
    }

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @param string $value
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnNOT_Equals($column, string $value)
    {
        $placeholder = $this->generatePlaceholder($value);

        return $this->addWhere(sprintf(
            "%s != %s",
            (string)$column,
            $placeholder
        ));
    }

    /**
     * @param string|DBHelper_StatementBuilder $column
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnNOT_Empty($column)
    {
        return $this->addWhere(sprintf(
            "%s != ''",
            (string)$column
        ));
    }

    // region: JOIN statements handling

    /**
     * @var array<string,Application_FilterCriteria_Database_Join>
     */
    protected $joins = array();

    /**
     * @var DBHelper_StatementBuilder[]
     */
    protected $joinStatements = array();

    /**
     * @var array<string,Application_FilterCriteria_Database_Join>
     */
    private $registeredJoins = array();

    private $joinsRegistered = false;

    abstract protected function _registerJoins() : void;

    protected final function registerJoins() : void
    {
        if($this->joinsRegistered === true)
        {
            return;
        }

        $this->joinsRegistered = true;

        $this->_registerJoins();
    }

    /**
     * Retrieves all join statements that are currently
     * in use in the criteria. Includes all dependencies
     * if a join requires another one, even if that has
     * only been registered so far.
     *
     * @return Application_FilterCriteria_Database_Join[]
     * @throws DBHelper_Exception
     */
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

    /**
     * Retrieves all join statements currently used in
     * the criteria, ordered to ensure that joins that
     * depend on each other are added in the correct
     * sequence.
     *
     * @return Application_FilterCriteria_Database_Join[]
     * @throws DBHelper_Exception
     */
    public function getJoinsOrdered(bool $includeRegistered=false) : array
    {
        $joins = $this->getJoins($includeRegistered);

        // Sort the joins so that all joins that depend
        // on another are above their dependent joins.
        // All other, independent joins get appended and
        // sorted by their ID at the end of the list.
        usort($joins, function(Application_FilterCriteria_Database_Join $a, Application_FilterCriteria_Database_Join $b)
        {
            if($a->dependsOn($b))
            {
                return 1;
            }
            else if($b->dependsOn($a))
            {
                return -1;
            }

            $aJoins = $a->hasJoins() || $a->hasDependentJoins();
            $bJoins = $b->hasJoins() || $b->hasDependentJoins();

            if($aJoins && !$bJoins)
            {
                return -1;
            }
            else if(!$aJoins && $bJoins)
            {
                return 1;
            }

            if(!$bJoins && !$aJoins)
            {
                return strnatcasecmp($a->getID(), $b->getID());
            }

            return 0;
        });

        return $joins;
    }

    /**
     * @param string|DBHelper_StatementBuilder $statement
     * @param string $joinID
     * @return Application_FilterCriteria_Database_Join
     */
    public function addJoin($statement, string $joinID='') : Application_FilterCriteria_Database_Join
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

    /**
     * @param string $template
     * @param string $joinID
     * @return DBHelper_StatementBuilder
     */
    public function addJoinStatement(string $template, string $joinID='') : DBHelper_StatementBuilder
    {
        $statement = $this->statement($template);

        $this->addJoin($statement, $joinID);

        return $statement;
    }

    /**
     * Adds a `JOIN` statement that was previously registered
     * with {@see Application_FilterCriteria_Database::registerJoin()}.
     *
     * Use this to add joins to a query only when they are needed,
     * as alternative to {@see Application_FilterCriteria_Database::addJoin()},
     * which adds it regardless of whether it is actually used.
     *
     * NOTE: If the join has already been added, this will be ignored.
     *
     * @param string $joinID
     * @return $this
     * @throws DBHelper_Exception
     * @see Application_FilterCriteria_Database::ERROR_JOIN_ID_NOT_FOUND
     */
    public function requireJoin(string $joinID)
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

    /**
     * Gets a join statement, either from those that have
     * been added, or who have been registered but not added
     * yet.
     *
     * @param string $joinID
     * @return Application_FilterCriteria_Database_Join
     * @throws DBHelper_Exception
     * @see Application_FilterCriteria_Database::ERROR_JOIN_ID_NOT_FOUND
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

        throw new DBHelper_Exception(
            'Missing JOIN statement.',
            sprintf(
                'JOIN not found by ID [%s].',
                $joinID
            ),
            self::ERROR_JOIN_ID_NOT_FOUND
        );
    }

    /**
     * Registers a `JOIN` statement that can be referenced
     * by its ID, to allow columns to require joins only
     * when they are actually needed.
     *
     * @param string $joinID
     * @param string|DBHelper_StatementBuilder $statement
     * @return Application_FilterCriteria_Database_Join
     *
     * @throws DBHelper_Exception
     * @see Application_FilterCriteria_Database::ERROR_JOIN_ALREADY_ADDED
     * @see Application_FilterCriteria_Database::ERROR_JOIN_ALREADY_REGISTERED
     */
    public function registerJoin(string $joinID, $statement) : Application_FilterCriteria_Database_Join
    {
        $join = new Application_FilterCriteria_Database_Join($this, $statement, $joinID);
        $id = $join->getID();

        if(isset($this->joins[$id]))
        {
            throw new DBHelper_Exception(
                'JOIN statement already added.',
                sprintf(
                    'Cannot register JOIN statement [%s], it has already been added.',
                    $joinID
                ),
                self::ERROR_JOIN_ALREADY_ADDED
            );
        }

        if(!isset($this->registeredJoins[$id]))
        {
            $this->registeredJoins[$id] = $join;
            $this->handleCriteriaChanged();

            return $join;
        }

        throw new DBHelper_Exception(
            'JOIN statement already registered.',
            sprintf(
                'The statement has already been registered: 
                %s',
                $statement
            ),
            self::ERROR_JOIN_ALREADY_REGISTERED
        );
    }

    /**
     * Registers a `JOIN` statement using a statement builder
     * template. The join will not be used unless it is specifically
     * added using {@see Application_FilterCriteria_Database::requireJoin()},
     * or a custom column requires it.
     *
     * @param string $joinID
     * @param string $statementTemplate Statement template
     * @return Application_FilterCriteria_Database_Join
     * @throws DBHelper_Exception
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
    public function addHaving($statement)
    {
        if(!in_array($statement, $this->havings))
        {
            $this->havings[] = (string)$statement;
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
    protected final function createStatementValues() : DBHelper_StatementBuilder_ValuesContainer
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
    protected function quoteColumnName($name) : string
    {
        if($name instanceof DBHelper_StatementBuilder)
        {
            return (string)$name;
        }

        if(substr($name, 0, 1) == '`' || substr($name, 0, 2) == Application_FilterCriteria_Database_CustomColumn::MARKER_SUFFIX)
        {
            return $name;
        }

        return '`'.$name.'`';
    }

    /**
     * Generates a placeholder name unique for the specified value:
     * will return the same placeholder name for every same value
     * within the same request.
     *
     * Returns the placeholder name.
     *
     * @param string|int|float|null $value
     * @return string
     */
    public function generatePlaceholder($value) : string
    {
        $value = strval($value);
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


    /**
     * @return $this
     */
    public function debug()
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
    public function addGroupBy($groupBy)
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
     * @param string|DBHelper_StatementBuilder ...$args
     */
    public function addGroupBys(...$args)
    {
        foreach($args as $groupBy) {
            $this->addGroupBy($groupBy);
        }
    }

    /**
     * Converts all entries to statements before adding them.
     *
     * @param string ...$args
     * @return $this
     */
    public function addGroupByStatements(...$args)
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
            '{WHAT}' => null,
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
        $cnt = 0;

        for($i=0; $i<$totalTerms; $i++)
        {
            $term = $searchTerms[$i];

            // escape the underscore characters, which have special
            // meaning in SQL and will not give the expected results
            $term = str_replace('_', '\_', $term);

            if($term=='NOT' || $term==t('NOT')) {
                $like = false;
                continue;
            }

            $connector = $this->getConnector($term);
            if ($connector) {
                // search terms may not start with a connector
                if($i==0) {
                    $this->addWarning('The search terms may not start with a logical operator.');
                    continue;
                }

                if($i==($totalTerms-1)) {
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

            $cnt++;
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
            !strstr($field, '.')
                &&
            !strstr($field, '\\')
                &&
            !strstr($field, Application_FilterCriteria_Database_CustomColumn::MARKER_SUFFIX);
    }

    protected function buildOrderby() : string
    {
        // no ordering in count queries
        if ($this->isCount || !isset($this->orderField)) {
            return '';
        }

        $field = $this->orderField;

        // leave the name be if it has a dot (meaning it has a table or alias specified)
        if($this->canAddTableAlias($field))
        {
            $field = $this->quoteColumnName($this->orderField);

            // add the table alias if it is present
            if(isset($this->selectAlias) && substr($field, 0, 1) != '\\') {
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
    protected function setSelectAlias(string $alias)
    {
        if($this->selectAlias !== $alias)
        {
            $this->selectAlias = $alias;
            $this->handleCriteriaChanged();
        }

        return $this;
    }

    /**
     * Selects the date ranges specified by the date string, and
     * stores the corresponding SQL statements under the given type
     * (works like the {@link selectCriteriaValue() method).
     *
     * @param string $type
     * @param string $dateSearchString
     * @return $this
     * @throws Application_Exception
     */
    public function selectCriteriaDate(string $type, string $dateSearchString)
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
    public function addDateSearch(string $type, string $column)
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
     * @throws Application_Exception
     */
    public function setOrderBy($fieldName, string $orderDir = self::ORDER_DIR_ASCENDING)
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
