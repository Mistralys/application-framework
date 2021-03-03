<?php

abstract class Application_FilterCriteria_Database extends Application_FilterCriteria
{
    const ERROR_INVALID_WHERE_STATEMENT = 710001;
    const ERROR_EMPTY_SELECT_FIELDS_LIST = 710002;
    const ERROR_MISSING_SELECT_KEYWORD = 710004;

    const DEFAULT_SELECT = 'SELECT {WHAT} FROM tablename {JOINS} {WHERE} {GROUPBY} {ORDERBY} {LIMIT}';

    /**
     * @var string
     */
    protected $placeholderPrefix = 'PH';

    /**
     * @var string[]
     */
    protected $joins = array();

    /**
     * @var array<string,mixed>
     */
    protected $queries = array();

    /**
     * @var string[]
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
     * @var string[]
     */
    protected $groupBy = array();

    /**
     * @var string
     */
    protected $selectAlias;

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
        $sql = $this->buildQuery(true);
        if (!$sql) {
            return 0;
        }

        $vars = $this->getQueryVariables();

        $items = DBHelper::fetchAll($sql, $vars);
        $this->queries[] = array(
            'sql' => $sql,
            'vars' => $vars
        );

        $count = 0;
        $total = count($items);
        for($i=0; $i < $total; $i++) {
            $count = $count + $items[$i]['count'];
        }

        return $count;
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

    /**
     * @param false $isCount
     * @return string
     * @throws Application_Exception
     */
    protected function buildQuery(bool $isCount=false) : string
    {
        $this->isCount = $isCount;

        $query = $this->addDistinctKeyword($this->getQuery());

        if (isset($this->search)) {
            $searchTokens = $this->resolveSearchTokens();
            if(!empty($searchTokens)) {
                $this->addWhere($searchTokens);
            }
        }

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

    /**
     * Parses the SQL and adds the DISTINCT keyword as needed to
     * the SELECT statement if the filter has been set to distinct.
     * This is done intelligently, only adding it if it has not
     * already been added manually.
     *
     * @param string $query
     * @throws Application_Exception
     * @return string
     */
    protected function addDistinctKeyword($query)
    {
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
     * @return array|string
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
     * @return string[]
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
     */
    abstract protected function getQuery();

    protected function buildLimit() : string
    {
        if(!$this->isCount && ($this->limit > 0 || $this->offset > 0)) {
            return sprintf(" LIMIT %s,%s", $this->limit, $this->offset);
        }

        return '';
    }

    protected $where = array();

    protected function buildWhere() : string
    {
        if (empty($this->where)) {
            return '';
        }

        return
            PHP_EOL .
            'WHERE' . PHP_EOL .
            implode(PHP_EOL . 'AND' . PHP_EOL, $this->where);
    }

    protected function buildJoins() : string
    {
        return implode(PHP_EOL, $this->joins);
    }

    protected function buildOrderby() : string
    {
        // no ordering in count queries
        if ($this->isCount || !isset($this->orderField)) {
            return '';
        }

        $field = $this->orderField;

        // leave the name be if it has a dot (meaning it has a table or alias specified)
        if(!strstr($field, '.') && !strstr($field, '\\'))
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

    protected function quoteColumnName(string $name) : string
    {
        if(substr($name, 0, 1) == '`') {
            return $name;
        }

        return '`'.$name.'`';
    }

    protected function resolveSearchTokens() : string
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
        for($i=0; $i<$totalTerms; $i++) {
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
                $fieldName = $searchFields[$j];
                $placeholder = $this->generatePlaceholder('%'.$term.'%');
                $fieldTokens[] = $fieldName.' '.$likeToken.' '.$placeholder;
            }


            $parts[] = '('.implode(' '.$concatenator.' ', $fieldTokens).')';
        }

        return '('.implode(' ', $parts).')';
    }

    /**
     * @var array<string,string>
     */
    protected $placeholders = array();

    /**
     * Stores placeholders to replace with variables in the query.
     * For placeholders that should not be reset with each query,
     * set the persistent parameter to true.
     *
     * @var string|int|float $value
     * @var string $name
     * @return $this
     */
    public function addPlaceholder(string $name, $value)
    {
        if (!substr($name, 0) == ':') {
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
     * @param string $columnSelect
     * @return $this
     */
    public function addSelectColumn(string $columnSelect)
    {
        if(!in_array($columnSelect, $this->columnSelects)) {
            $this->columnSelects[] = $columnSelect;
        }

        return $this;
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
    protected function buildSelect()
    {
        if($this->isCount) {
            return $this->getCountSelect();
        }

        $selects = $this->getSelect();
        if(empty($selects)) {
            throw new Application_Exception(
                'Select fields list cannot be empty',
                'The method call [getSelect] returned an empty value.',
                self::ERROR_EMPTY_SELECT_FIELDS_LIST
            );
        }

        if(!is_array($selects)) {
            $selects = explode(',', $selects);
        }

        if(!empty($this->columnSelects)) {
            $selects = array_merge($selects, $this->columnSelects);
        }

        // distinct queries require the ordering field to be part of the select
        if($this->distinct && !empty($this->orderField)) {
            $selects[] = $this->orderField;
        }

        $selects = array_unique($selects);

        // in distinct queries, it is safer to add all select fields to the group by
        if($this->distinct) {
            foreach($selects as $field) {
                $this->addGroupBy($field);
            }
        }

        return implode(',', $selects);
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
     * @return mixed[]
     * @throws Application_Exception|DBHelper_Exception
     * @see getItem()
     */
    public function getItems() : array
    {
        $query = $this->buildQuery(false);
        if (!$query) {
            return array();
        }

        $vars = $this->getQueryVariables();
        $this->resetQueryVariables();

        $this->queries[] = array(
            'sql' => $query,
            'vars' => $vars
        );

        if($this->dumpQuery) {
            DBHelper::enableDebugging();
        }

        $result = DBHelper::fetchAll($query, $vars);

        if($this->dumpQuery) {
            DBHelper::disableDebugging();
        }

        return $result;
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
     * @param string $statement
     * @throws Application_Exception
     * @return $this
     */
    public function addWhere(string $statement)
    {
        if(empty($statement) || in_array($statement, array('()'))) {
            throw new Application_Exception(
                'Invalid where statement',
                sprintf(
                    'Where statements may not be empty, and must be valid SQL conditions in statement: %s',
                    $statement
                ),
                self::ERROR_INVALID_WHERE_STATEMENT
            );
        }

        if(!in_array($statement, $this->where)) {
            $this->where[] = $statement;
        }

        return $this;
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
    protected function generatePlaceholder($value) : string
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

    /**
     * @param string $column
     * @param bool $null
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnISNULL(string $column, bool $null=true)
    {
        $token = '';
        if($null===true) {
            $token = 'NOT ';
        }

        return $this->addWhere(sprintf(
            "%s IS %sNULL",
            $column,
            $token
        ));
    }

    /**
     * @param string $column
     * @param string[] $values
     * @param bool $exclude
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnIN(string $column, array $values, bool $exclude=false)
    {
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
     * @param string $column
     * @param string|string[] $value
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnLIKE(string $column, string $value)
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

        return $this->addWhere($column." LIKE ".$this->generatePlaceholder('%'.(string)$value.'%'));
    }

    /**
     * @param string $column
     * @param string[] $values
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnNOT_IN(string $column, array $values)
    {
        return $this->addWhereColumnIN($column, $values, true);
    }

    /**
     * @param string $column
     * @param string $value
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnEquals(string $column, string $value)
    {
        $placeholder = $this->generatePlaceholder($value);

        return $this->addWhere(sprintf(
            "%s = %s",
            $column,
            $placeholder
        ));
    }

    /**
     * @param string $column
     * @param string $value
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnNOT_Equals(string $column, string $value)
    {
        $placeholder = $this->generatePlaceholder($value);

        return $this->addWhere(sprintf(
            "%s != %s",
            $column,
            $placeholder
        ));
    }

    /**
     * @param string $column
     * @return $this
     * @throws Application_Exception
     */
    public function addWhereColumnNOT_Empty(string $column)
    {
        return $this->addWhere("%s != ''");
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function addJoin(string $statement)
    {
        if(!in_array($statement, $this->joins)) {
            $this->joins[] = $statement;
        }

        return $this;
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function addHaving(string $statement)
    {
        if(!in_array($statement, $this->havings)) {
            $this->havings[] = $statement;
        }

        return $this;
    }

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
     * @param string $groupBy
     * @return $this
     */
    public function addGroupBy(string $groupBy)
    {
        if(!in_array($groupBy, $this->groupBy)) {
            $this->groupBy[] = $groupBy;
        }

        return $this;
    }

    public function addGroupBys()
    {
        $args = func_get_args();
        foreach($args as $groupBy) {
            $this->addGroupBy($groupBy);
        }
    }

    protected function buildGroupBy() : string
    {
        if(empty($this->groupBy)) {
            return '';
        }

        return " GROUP BY ".implode(', ', $this->groupBy).$this->buildHavings();
    }

    protected function buildHavings() : string
    {
        if(!empty($this->havings)) {
            return " HAVING ".implode(' AND ', $this->havings);
        }

        return '';
    }

    /**
     * @param string $alias
     * @return $this
     */
    protected function setSelectAlias(string $alias)
    {
        $this->selectAlias = $alias;
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
}
