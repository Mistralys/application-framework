<?php
/**
 * File containing the class {@see Application_FilterCriteria_DatabaseExtended}.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @see Application_FilterCriteria_DatabaseExtended
 */

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\NamedClosure;

/**
 * Formalizes the use of custom columns in the filter criteria,
 * by defining abstract methods to set up the custom columns to
 * use.
 *
 * **What are custom columns?**
 *
 * Any columns not directly present in the target tables, or
 * columns that are the result of more complex selections like
 * sub-queries or CASE statements.
 *
 * **Custom column features**
 *
 * - Specify JOIN statements that the column needs
 * - Add custom placeholder values only used when enabled
 * - Automatically detect if the column is used in the query
 * - Recursively detect and add all dependencies
 * - Object-Oriented interface to handle the columns
 * - Automatic support for sub-queries in `GROUP BY` and `ORDER BY`
 *
 * **How the technical side works**
 *
 * In a first step, custom columns only add a placeholder string
 * to the query, called "markers". When the final query is built,
 * these markers are replaced with the actual SQL statements.
 *
 * This makes it possible to easily detect which custom columns
 * are actually used in the query string, and to enable them
 * as needed if they have not been enabled manually. Example:
 * enabling a custom column if it is used in the `ORDER BY`
 * statement.
 *
 * @package Application
 * @subpackage FilterCriteria
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_FilterCriteria_DatabaseExtended extends Application_FilterCriteria_Database
{
    public const ERROR_CANNOT_REGISTER_COLUMN_AGAIN = 90501;
    public const ERROR_MAX_BUILD_ITERATIONS_REACHED = 90502;
    public const ERROR_MAX_REPLACE_ITERATIONS_REACHED = 90503;

    const MAX_BUILD_ITERATIONS = 20;
    const MAX_REPLACE_ITERATIONS = 4;

    /**
     * @var bool
     */
    private $customColumnsInitialized = false;

    /**
     * @var array<string,Application_FilterCriteria_Database_CustomColumn>
     */
    protected $customColumns = array();

    /**
     * @var int
     */
    private $buildIteration = 0;

    /**
     * @var string
     */
    private $buildInitialQuery = '';

    /**
     * @var array<string,Application_FilterCriteria_Database_ColumnUsage>
     */
    private $columnUsage = array();

    /**
     * Actually boolean keys in the array, but since
     * booleans are converted to int, documenting it
     * as int here.
     *
     * @var array<int,string>
     */
    private $buildCache;

    abstract protected function _initCustomColumns() : void;

    private function initCustomColumns() : void
    {
        if($this->customColumnsInitialized)
        {
            return;
        }

        $this->customColumnsInitialized = true;

        $this->_initCustomColumns();
    }

    /**
     * @return string[]
     * @throws Application_Exception
     */
    public function getSelects() : array
    {
        $selects = array_merge(parent::getSelects(), $this->getCustomSelects());

        return array_map('strval', $selects);
    }

    /**
     * Fetches all select statements for custom columns.
     *
     * @return string[]
     */
    public function getCustomSelects() : array
    {
        $this->initCustomColumns();

        $result = array();

        foreach ($this->customColumns as $column)
        {
            if(!$column->isEnabled())
            {
                continue;
            }

            $result[] = $column->getPrimarySelectMarker();
        }
        
        return $result;
    }

    /**
     * Fetches the custom column's select statement from the target method.
     *
     * @param string $columnID
     * @return string
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database::ERROR_CUSTOM_COLUMN_NOT_REGISTERED
     */
    protected function getCustomSelect(string $columnID) : string
    {
        $this->initCustomColumns();

        if(isset($this->customColumns[$columnID]))
        {
            return $this->customColumns[$columnID]->getPrimarySelectValue();
        }

        throw $this->createMissingColumnException($columnID);
    }

    /**
     * Select a custom column that is not directly available in the
     * target table. This requires the column to have been registered
     * first, using the {@see Application_FilterCriteria_Database::registerCustomColumn()}
     * method.
     *
     * To use this feature, it is recommended to use the extended
     * filter class {@see Application_FilterCriteria_DatabaseExtended}.
     * It formalizes the use of custom columns.
     *
     * @param string $columnID The name of the custom column
     * @param bool $enable Whether to enable this column. Allows turning it on and off at will.
     * @return $this
     *
     * @see Application_FilterCriteria_Database::registerCustomColumn()
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria_Database::ERROR_CUSTOM_COLUMN_NOT_REGISTERED
     */
    protected function withCustomColumn(string $columnID, bool $enable=true)
    {
        $this->initCustomColumns();

        if(isset($this->customColumns[$columnID]))
        {
            $this->customColumns[$columnID]->setEnabled($enable);
            return $this;
        }

        throw $this->createMissingColumnException($columnID);
    }

    protected function createMissingColumnException(string $columnID) : Application_Exception
    {
        return new Application_Exception(
            'Custom column not registered',
            sprintf(
                'The custom column [%s] is not available, it has not been registered in the filters class [%s]. Registered columns are: [%s].',
                $columnID,
                get_class($this),
                implode(', ', array_keys($this->customColumns))
            ),
            self::ERROR_CUSTOM_COLUMN_NOT_REGISTERED
        );
    }

    /**
     * Check if the specified custom column has been added,
     * and is enabled. Returns false if it has been added,
     * but is not enabled.
     *
     * @param string $columnID
     * @return bool
     */
    public function hasCustomColumn(string $columnID) : bool
    {
        $this->initCustomColumns();

        return isset($this->customColumns[$columnID]) && $this->customColumns[$columnID]->isEnabled();
    }

    protected function registerCustomSelect(string $template, string $columnID='') : Application_FilterCriteria_Database_CustomColumn
    {
        if(empty($columnID))
        {
            $columnID = $template;
        }

        return $this->registerCustomColumn($columnID, $this->statement($template));
    }

    /**
     * @param string|DBHelper_StatementBuilder $countryIDColumn
     * @param string $columnID
     * @return Application_FilterCriteria_Database_CustomColumn
     */
    protected function registerCountryLabelSelect($countryIDColumn, string $columnID) : Application_FilterCriteria_Database_CustomColumn
    {
        return $this->registerCustomSelect(
            $this->renderCountriesCase($countryIDColumn),
            $columnID
        );
    }

    /**
     * Adds a custom column to select a username, given
     * a user ID column value, ideal for sorting purposes
     * for example.
     *
     * @param string|DBHelper_StatementBuilder $userIDColumn
     * @param string $columnID
     * @return Application_FilterCriteria_Database_CustomColumn
     */
    protected function registerUserNameSelect($userIDColumn, string $columnID) : Application_FilterCriteria_Database_CustomColumn
    {
        $query = statementBuilder(
        "
            (
                SELECT
                    CONCAT({table_users}.{first_name}, ' ', {table_users}.{last_name})
                FROM
                    {table_users}
                WHERE
                    {table_users}.{users_primary}=%s
            )"
        )
            ->table('{table_users}', Application_Users::TABLE_NAME)
            ->field('{first_name}', Application_Users_User::COL_FIRST_NAME)
            ->field('{last_name}', Application_Users_User::COL_LAST_NAME)
            ->field('{users_primary}', Application_Users::PRIMARY_NAME);

        return $this->registerCustomSelect(
            sprintf((string)$query, (string)$userIDColumn),
            $columnID
        );
    }

    /**
     * Adds a column to select translated country labels
     * given a country ID column value, ideal for sorting
     * purposes for example.
     *
     * @param string|DBHelper_StatementBuilder $countryIDColumn
     * @return string
     */
    private function renderCountriesCase($countryIDColumn) : string
    {
        $countries = Application_Countries::getInstance();
        $all = $countries->getAll(false);
        $case = DBHelper_CaseStatement::create($countryIDColumn);

        foreach($all as $country)
        {
            $case->addIntString($country->getID(), $country->getLocalizedLabel());
        }

        return $case->render();
    }

    /**
     * Registers a new custom column that is used to add
     * additional, dynamic select statements to the query.
     *
     * NOTE: When using a closure, the callback method gets
     * the custom column instance as argument.
     *
     * @param string $columnID
     * @param NamedClosure|DBHelper_StatementBuilder $source
     * @return Application_FilterCriteria_Database_CustomColumn
     *
     * @throws Application_Exception
     * @see Application_FilterCriteria_DatabaseExtended::ERROR_CANNOT_REGISTER_COLUMN_AGAIN
     */
    protected function registerCustomColumn(string $columnID, $source) : Application_FilterCriteria_Database_CustomColumn
    {
        if(!isset($this->customColumns[$columnID]))
        {
            $column = new Application_FilterCriteria_Database_CustomColumn($this, $columnID, $source);
            $this->customColumns[$columnID] = $column;
            return $column;
        }

        throw new Application_Exception(
            'Can register custom columns only once',
            sprintf(
                'The column [%s] has already been registered.',
                $columnID
            ),
            self::ERROR_CANNOT_REGISTER_COLUMN_AGAIN
        );
    }

    /**
     * Fetches a custom column instance.
     *
     * @param string $columnID
     * @return Application_FilterCriteria_Database_CustomColumn
     * @throws Application_Exception
     */
    public function getCustomColumn(string $columnID) : Application_FilterCriteria_Database_CustomColumn
    {
        $this->initCustomColumns();

        if(isset($this->customColumns[$columnID]))
        {
            return $this->customColumns[$columnID];
        }

        throw $this->createMissingColumnException($columnID);
    }

    /**
     * @param bool $debug
     * @return $this
     */
    public function debugBuild(bool $debug=true)
    {
        $this->debugBuild = $debug;
        return $this;
    }

    private $debugBuild = false;

    private function logBuild(string $message) : void
    {
        if($this->debugBuild === true)
        {
            $this->log($message);
        }
    }

    private function logQuery(string $query) : void
    {
        if($this->debugBuild === true)
        {
            echo $query.PHP_EOL;
        }
    }

    protected function buildQuery(bool $isCount=false) : string
    {
        $this->log(
            'Building query: Iteration [%s], count: [%s].',
            $this->buildIteration,
            ConvertHelper::boolStrict2string($isCount)
        );

        if(isset($this->buildCache[$isCount]))
        {
            $this->log('Using existing cached query.');
            return $this->buildCache[$isCount];
        }

        $query = parent::buildQuery($isCount);

        $this->logBuild('Current query:');
        $this->logQuery($query);

        if($this->dumpQuery && $this->buildIteration === 0)
        {
            $this->buildInitialQuery = $query;
        }

        // Automatically detect which custom columns
        // are used in the query, and enable them as
        // needed.
        $this->autoEnableColumns($query);

        // Re-build the query, since newly added columns
        // may have dependencies (like JOINs) that require
        // additional columns.
        $adjusted = parent::buildQuery($isCount);

        // If the query does not change, it means all columns
        // have all dependencies ready.
        if($adjusted !== $query)
        {
            $this->logBuild('Adjusted query is not the same, rebuilding.');

            if($this->buildIteration > self::MAX_BUILD_ITERATIONS)
            {
                throw new DBHelper_Exception(
                    'Query max build iteration exceeded',
                    sprintf(
                        'Reached the maximum of [%s] query build iterations.'.
                        PHP_EOL.
                        '--------------------------------'.PHP_EOL.
                        'Current query SQL:'.PHP_EOL.
                        $query.
                        PHP_EOL.
                        '--------------------------------'.PHP_EOL.
                        'Adjusted query SQL:'.PHP_EOL.
                        $adjusted,
                        self::MAX_BUILD_ITERATIONS
                    ),
                    self::ERROR_MAX_BUILD_ITERATIONS_REACHED
                );
            }

            $this->buildIteration++; // Signify that we are in a recursive build

            return $this->buildQuery($isCount);
        }

        // Now we replace all custom column's markers with the
        // actual SQL statements.
        //
        $query = $this->replaceMarkers($query);

        // Now that all markers have been replaced, there is
        // one issue: The SQL statements may have revealed
        // new, nested markers of columns that have not been
        // enabled yet.
        //
        // This is handled with replacement iterations: Each
        // iteration replaces newly revealed markers, and auto-
        // enables any new columns that are detected.
        //
        // Example with markers nested 2 times:
        //
        // 1) Replace markers 1 time -> new markers revealed
        // 2) Auto-Enable new columns
        // 3) Re-build the query *recursion*
        // 4) Replace markers 2 times -> new markers revealed
        // 5) Auto-Enable new columns
        // 6) Re-build the query *recursion*
        // 7) Replace markers 3 times -> no new markers
        // 8) Query fully built

        // Are we in an additional marker replacement iteration?
        // Then do one replacement run for each iteration.
        if($this->replaceIteration > 0)
        {
            $this->logBuild('Replacing markers for ['.$this->replaceIteration.'] total iterations.');

            for ($i = 1; $i <= $this->replaceIteration; $i++)
            {
                $query = $this->replaceMarkers($query);
            }
        }

        $this->logBuild('Query after replacing markers:');
        $this->logQuery($query);

        // The query is complete when there are no further
        // column markers left to replace.
        if(strstr($query, Application_FilterCriteria_Database_CustomColumn::MARKER_SUFFIX) === false)
        {
            $this->logBuild('OK: No placeholders left in the query.');

            if($this->dumpQuery)
            {
                $different = $query !== $this->buildInitialQuery;

                echo '<pre style="color:#444;font-family:monospace;font-size:14px;background:#f0f0f0;border-radius:5px;border:solid 1px #333;padding:16px;margin:12px 0;">';
                if($different) {echo '----------------';}
                echo 'Query built by the filters';
                echo PHP_EOL;
                echo PHP_EOL;
                echo print_r($this->buildInitialQuery, true);

                if($different)
                {
                    echo PHP_EOL;
                    echo PHP_EOL;
                    echo '----------------';
                    echo 'Query after adding required custom columns';
                    echo PHP_EOL;
                    echo PHP_EOL;
                    echo print_r($query, true);
                }

                echo '</pre>';
            }

            // Reset the recursion properties
            $this->buildInitialQuery = '';
            $this->buildIteration = 0;
            $this->replaceIteration = 0;

            $this->buildCache[$isCount] = $query;
            return $query;
        }

        // Column markers have been found in the query, which means
        // that we need to re-check the new markers and enable any
        // columns accordingly.

        $this->logBuild('Markers found, processing replace iteration ['.$this->replaceIteration.'].');

        // Failsafe: Nesting columns further than this is not allowed.
        if($this->replaceIteration > self::MAX_REPLACE_ITERATIONS)
        {
            throw new Application_Exception(
                'Column markers are nested too deep.',
                sprintf(
                    'Reached replace iteration [%s]. Query: %s',
                    $this->replaceIteration,
                    $query
                ),
                self::ERROR_MAX_REPLACE_ITERATIONS_REACHED
            );
        }

        $this->autoEnableColumns($query, true);

        $this->replaceIteration++;
        $this->buildIteration++;

        return $this->buildQuery($isCount);
    }

    private $replaceIteration = 0;

    protected function replaceMarkers(string $query) : string
    {
        // Let all the custom columns replace their
        // placeholders with the actual SQL statements.
        foreach($this->customColumns as $column)
        {
            $query = $column->replaceMarkers($query);
        }

        return $query;
    }

    protected function handleCriteriaChanged() : void
    {
        parent::handleCriteriaChanged();

        // Reset the column usage cache
        $this->columnUsage = array();

        $this->buildCache = array();
    }

    public function checkColumnUsage(Application_FilterCriteria_Database_CustomColumn $column) : Application_FilterCriteria_Database_ColumnUsage
    {
        $name = $column->getName();

        if(!isset($this->columnUsage[$name]))
        {
            $this->columnUsage[$name] = new Application_FilterCriteria_Database_ColumnUsage($this, $column);
        }

        return $this->columnUsage[$name];
    }

    public function handleColumnModified(Application_FilterCriteria_Database_CustomColumn $column) : void
    {
        $this->handleCriteriaChanged();
    }

    protected function _applyFilters() : void
    {
        parent::_applyFilters();

        $this->initCustomColumns();
        $this->initJoins();
    }

    protected function initJoins() : void
    {
        foreach ($this->customColumns as $column)
        {
            if(!$column->isEnabled() || !$column->hasRequiredJoins())
            {
                continue;
            }

            $IDs = $column->getRequiredJoinIDs();

            foreach($IDs as $joinID)
            {
                $this->requireJoin($joinID);
            }
        }
    }

    protected function autoEnableColumns(string $query, bool $iteration=false) : void
    {
        foreach($this->customColumns as $column)
        {
            if(!$column->isEnabled() && $column->isFoundInString($query))
            {
                $this->log('Auto-enabling column [%s].', $column->getName());
                $column->setEnabled(true);
            }
        }
    }

    public function getGroupBys() : array
    {
        $groupBys = parent::getGroupBys();

        foreach($this->customColumns as $column)
        {
            if(!$column->isEnabled())
            {
                continue;
            }

            $statement = $column->getGroupByValue();

            if(!in_array($statement, $groupBys))
            {
                $groupBys[] = $statement;
            }
        }

        return $groupBys;
    }

    /**
     * Detects all custom columns that are used in the
     * query, from the select statements to the order by
     * columns.
     *
     * @return Application_FilterCriteria_Database_CustomColumn[]
     */
    public function getActiveCustomColumns() : array
    {
        $this->initCustomColumns();

        $columns = array();

        foreach ($this->customColumns as $column)
        {
            if($column->isEnabled() || $this->checkColumnUsage($column)->isInUse())
            {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    public function getQueryVariables() : array
    {
        $vars = parent::getQueryVariables();

        foreach ($this->customColumns as $column)
        {
            if(!$column->isEnabled() || !$column->hasPlaceholders())
            {
                continue;
            }

            $vars = array_merge($vars, $column->getPlaceholders());
        }

        return $vars;
    }
}
