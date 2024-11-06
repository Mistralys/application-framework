<?php
/**
 * File containing the {@link DBHelper} class.
 * @package Helpers
 * @subpackage DBHelper
 * @see DBHelper
 */

use Application\ConfigSettings\AppConfig;
use Application\ConfigSettings\BaseConfigRegistry;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\Highlighter;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\Microtime;
use DBHelper\Exception\CLIErrorRenderer;
use DBHelper\Exception\HTMLErrorRenderer;
use function AppUtils\parseVariable;

/**
 * Simple database utility class used tu run queries against
 * the database using the main PDO object. Does not abstract
 * database access by design; merely simplifies the code
 * required to run a query.
 *
 * Queries themselves have to be created manually, making it
 * easier to maintain individual queries as opposed to a
 * know-it-all approach that is often quite hermetic.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper
{
    /**
     * Counter for the number of queries that were run during a request.
     * @var int
     */
    protected static int $queryCount = 0;

    /**
     * @var int
     */
    protected static int $queryCountRead = 0;

    /**
     * @var int
     */
    protected static int $queryCountWrite = 0;

    /**
     * The statement object for the last query that was run, if any.
     * @var PDOStatement|NULL
     */
    protected static ?PDOStatement $activeStatement = null;

    /**
     * @var array<string,PDO|NULL>
     * @see getDB()
     */
    protected static array $db = array(
        'main' => null,
        'admin' => null
    );

    public const ERROR_EXECUTING_QUERY = 33871001;
    public const ERROR_PREPARING_QUERY = 33871002;
    public const ERROR_INSERTING = 33871003;
    public const ERROR_FETCHING = 33871004;
    public const ERROR_CONNECTING = 33871005;
    public const ERROR_CANNOT_ROLL_BACK_TRANSACTION = 33871008;
    public const ERROR_CANNOT_COMMIT_TRANSACTION = 33871009;
    public const ERROR_CANNOT_START_TRANSACTION = 33871010;
    public const ERROR_NO_ACTIVE_STATEMENT = 33871011;
    public const ERROR_TRANSACTION_REQUIRED_FOR_OPERATION = 33871012;
    public const ERROR_CONNECTING_NO_DRIVER = 33871013;
    public const ERROR_NOT_A_DBHELPER_COLLECTION = 33871014;
    public const ERROR_NO_PARENT_RECORD_SPECIFIED = 33871015;
    public const ERROR_INVALID_PARENT_RECORD = 33871016;
    public const ERROR_INVALID_TABLE_NAME = 338701017;
    public const ERROR_INVALID_COLUMN_NAME = 338701018;
    public const ERROR_DB_NOT_REGISTERED = 338701019;
    public const ERROR_CANNOT_CONVERT_OBJECT = 338701021;
    public const ERROR_CANNOT_CONVERT_ARRAY = 338701022;
    public const ERROR_CANNOT_CONVERT_RESOURCE = 338701023;
    public const ERROR_EMPTY_WHERE = 338701024;
    
    protected static float $startTime;

    /**
     * @var array{0:string,1:array<string,mixed>}|null
     */
    protected static ?array $activeQuery = null;
    
    public static function isQueryTrackingEnabled() : bool
    {
        return AppConfig::isQueryTrackingEnabled();
    }

    /**
     * Executes a query string with the specified variables.
     *
     * Uses the <code>PDO->prepare()</code> method to prepare the query.
     * The result is returned by the <code>PDOStatement->execute()</code> method.
     *
     * If the query fails, the error information can be accessed via
     * {@see self::getErrorMessage()}.
     *
     * @param int $operationType
     * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
     * @param array<string,mixed> $variables Associative array with placeholders and values to replace in the query
     * @param bool $exceptionOnError
     * @return boolean
     * @throws DBHelper_Exception
     * @throws JsonException
     * @see getErrorCode()
     * @see getErrorMessage()
     */
    public static function execute(int $operationType, $statementOrBuilder, array $variables = array(), bool $exceptionOnError=true) : bool
    {
        if(self::isQueryTrackingEnabled()) {
            self::$startTime = microtime(true);
        }

        $statement = (string)$statementOrBuilder;

        self::$queryCount++;
        self::$activeQuery = array($statement, $variables);
        
        if(DBHelper_OperationTypes::isWriteOperation($operationType))
        {
            if(self::hasListener('BeforeDBWriteOperation'))
            {
                $event = self::triggerEvent('BeforeDBWriteOperation', array($operationType, $statement, $variables));
                if ($event !== null && $event->isCancelled())
                {
                    return true;
                }
            }

            self::$queryCountWrite++;
        }
        else
        {
            self::$queryCountRead++;
        }

        $filteredVariables = self::filterVariablesForDB($variables);

        try
        {
            $stmt = self::$activeDB->prepare($statement);

            if ($stmt === false)
            {
                throw self::createException(
                    self::ERROR_PREPARING_QUERY, 
                    'Could not prepare query'
                );
            }
        } 
        catch(PDOException $e)
        {
            throw self::createException(
                self::ERROR_PREPARING_QUERY, 
                'Could not prepare query', 
                null, 
                $e
            );
        }
        
        self::$activeStatement = $stmt;
        
        try
        {
            $result = self::$activeStatement->execute($filteredVariables);
            
            if (!$result && $exceptionOnError) 
            {
                throw self::createException(
                    self::ERROR_EXECUTING_QUERY,
                    'Query execution failed',
                );
            }
        } 
        catch(PDOException $e)
        {
            throw self::createException(
                self::ERROR_EXECUTING_QUERY, 
                'Query execution failed',
                null, 
                $e
            );
        }

        return $result;
    }

    /**
     * Converts all values in the variable collection to
     * database compatible values, converting them as
     * necessary.
     *
     * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables
     * @return array<string,string|NULL>
     * @throws DBHelper_Exception
     *
     * @see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
     * @see DBHelper::ERROR_CANNOT_CONVERT_OBJECT
     * @see DBHelper::ERROR_CANNOT_CONVERT_ARRAY
     */
    public static function filterVariablesForDB(array $variables) : array
    {
        $result = array();

        foreach($variables as $name => $value)
        {
            $result[$name] = self::filterValueForDB($value);
        }

        return $result;
    }

    /**
     * @param mixed $value
     * @return string|NULL
     * @throws DBHelper_Exception
     *
     * @see ConvertHelper::ERROR_INVALID_BOOLEAN_STRING
     * @see DBHelper::ERROR_CANNOT_CONVERT_OBJECT
     * @see DBHelper::ERROR_CANNOT_CONVERT_ARRAY
     */
    public static function filterValueForDB($value) : ?string
    {
        if($value === null)
        {
            return null;
        }

        if($value instanceof Microtime)
        {
            return $value->getMySQLDate();
        }

        if($value instanceof DateTime)
        {
            return $value->format('Y-m-d H:i:s');
        }

        if(is_bool($value))
        {
            return ConvertHelper::boolStrict2string($value);
        }

        if($value instanceof StringableInterface)
        {
            return (string)$value;
        }

        if(is_object($value))
        {
            throw new DBHelper_Exception(
                'Invalid database storage value',
                sprintf(
                    'An object of class [%s] cannot be converted to string. Only object that implement the [%s] interface may be used as values.',
                    parseVariable($value)->enableType()->toString(),
                    StringableInterface::class
                ),
                self::ERROR_CANNOT_CONVERT_OBJECT
            );
        }

        if(is_array($value))
        {
            throw new DBHelper_Exception(
                'Invalid database storage value',
                'Arrays cannot be used as database values. To store JSON or serialized strings, convert the value to string first.',
                self::ERROR_CANNOT_CONVERT_ARRAY
            );
        }

        if(is_resource($value))
        {
            throw new DBHelper_Exception(
                'Invalid database storage value',
                'Resources cannot be used as database values.',
                self::ERROR_CANNOT_CONVERT_RESOURCE
            );
        }

        return (string)$value;
    }

    static protected bool $queryLogging = false;

   /**
    * Runs an insert query and returns the insert ID if applicable.
    * Note that this method requires a full INSERT query, it does
    * not automate anything. The only difference to the {@link execute()}
    * method is that it returns the insert ID.
    *
    * For tables that have no autoincrement fields, this will return
    * a null value. As it triggers an exception in all cases where
    * something could go wrong, there is no need to check the return
    * value of this method.
    *
    * @param string|DBHelper_StatementBuilder $statementOrBuilder
    * @param array<string,mixed> $variables
    * @return string
    * @throws DBHelper_Exception|ConvertHelper_Exception|JsonException
    */
    public static function insert($statementOrBuilder, array $variables = array()) : string
    {
        if (!self::executeAndRegister(DBHelper_OperationTypes::TYPE_INSERT, $statementOrBuilder, $variables, false))
        {
            throw self::createException(
                self::ERROR_INSERTING,
                'Failed inserting a record'
            );
        }
        
        return self::getDB()->lastInsertId();
    }

    /**
     * Like `insert`, but converts the result to an integer.
     *
     * @param string|DBHelper_StatementBuilder $statementOrBuilder
     * @param array $variables
     * @return int
     *
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    public static function insertInt($statementOrBuilder, array $variables=array()) : int
    {
        return (int)self::insert($statementOrBuilder, $variables);
    }

    /**
     * Registers a query by adding it to the internal queries cache.
     * They can be retrieved using the {@link getQueries()} method,
     * and the last query that was run can be retrieved using the
     * {@link getSQL()} and {@link getSQLHighlighted()} methods.
     *
     * @param int $operationType
     * @param string|DBHelper_StatementBuilder $statementOrBuilder
     * @param array $variables
     * @param bool $result
     */
    protected static function registerQuery(int $operationType, $statementOrBuilder, array $variables, bool $result) : void
    {
        if (self::$queryLogging === true) {
            self::log(self::getSQL());
        }
        
        if(self::$debugging) {
            self::debugQuery($result);
        }

        if(self::isQueryTrackingEnabled()) {
            $time = microtime(true)-self::$startTime;
            self::$queries[] = array((string)$statementOrBuilder, $variables, $time, $operationType);
        } 
    }

    /**
     * Returns the error message from the last query that was run, if any.
     * @return string
     * @see getErrorCode()
     */
    public static function getErrorMessage() : string
    {
        if (!isset(self::$activeStatement)) {
            return '';
        }

        $errorInfo = self::$activeStatement->errorInfo();
        if (isset($errorInfo[2])) {
            return $errorInfo[2];
        }

        $error = error_get_last();
        if ($error) {
            return $error['message'] . ' in ' . $error['file'] . ':' . $error['line'];
        }

        return '';
    }

    /**
     * Returns the error code from the last query that was run, if any.
     * @return string
     * @see getErrorMessage()
     */
    public static function getErrorCode() : string
    {
        if (!isset(self::$activeStatement))
        {
            return '';
        }

        $errorInfo = self::$activeStatement->errorInfo();
        if (isset($errorInfo[0]) && $errorInfo[0] !== '00000')
        {
            return (string)$errorInfo[0];
        }

        $error = error_get_last();
        if($error) {
            return (string)$error['type'];
        }

        return '';
    }

    /**
     * Retrieves the raw SQL query string from the last query, if any.
     * @return string
     */
    public static function getSQL() : string
    {
        if (!isset(self::$activeQuery))
        {
            return '';
        }

        return ConvertHelper::normalizeTabs(self::formatQuery(self::$activeQuery[0], self::$activeQuery[1]), true);
    }

    /**
     * @return array{0:string,1:array<string,mixed>}|null
     */
    public static function getActiveQuery() : ?array
    {
        return self::$activeQuery;
    }

    /**
     * @param string $query
     * @param array<string,mixed> $variables
     * @return string
     */
    public static function formatQuery(string $query, array $variables) : string
    {
        if(empty($variables))
        {
            return $query;
        }
        
        $replaces = array();
        foreach ($variables as $placeholder => $value) {
            $placeholder = ':'.ltrim($placeholder, ':');
            $replaces[$placeholder] = "'".$value."'";
        }
        
        return str_replace(array_keys($replaces), array_values($replaces), $query);
    }

    public static function getSQLHighlighted() : string
    {
        $sql = self::getSQL();
        if(!empty($sql)) {
            return Highlighter::sql($sql);
        }
        
        return '';
    }

    /**
     * Runs an update query. This is an alias for the {@link execute()}
     * method, which exists for semantic purposes and the possibility
     * to add specific functionality at a later time. It is recommended
     * to use this method if you run UPDATE queries.
     *
     * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
     * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables Associative array with placeholders and values to replace in the query
     * @return boolean
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     */
    public static function update($statementOrBuilder, array $variables = array()) : bool
    {
        return self::executeAndRegister(DBHelper_OperationTypes::TYPE_UPDATE, $statementOrBuilder, $variables);
    }

    /**
     * Executes the query and registers it internally.
     *
     * @param int $operationType
     * @param string|DBHelper_StatementBuilder $statementOrBuilder
     * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables
     * @param bool $exceptionOnError
     * @return boolean
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    protected static function executeAndRegister(int $operationType, $statementOrBuilder, array $variables=array(), bool $exceptionOnError=true) : bool
    {
        $result = self::execute($operationType, $statementOrBuilder, $variables, $exceptionOnError);
        self::registerQuery($operationType, $statementOrBuilder, $variables, $result);
        return $result;
    }

    /**
     * Runs a "DELETE" query. This is an alias for the {@link execute()}
     * method, which exists for semantic purposes and the possibility
     * to add specific functionality at a later time. It is recommended
     * to use this method if you run DELETE queries.
     *
     * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
     * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables Associative array with placeholders and values to replace in the query
     * @return boolean
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    public static function delete($statementOrBuilder, array $variables = array()) : bool
    {
        return self::executeAndRegister(DBHelper_OperationTypes::TYPE_DELETE, $statementOrBuilder, $variables);
    }

    /**
     * Fetches a single entry as an associative array from a SELECT query.
     * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
     * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables Associative array with placeholders and values to replace in the query
     * @return array<mixed>|NULL
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    public static function fetch($statementOrBuilder, array $variables = array()) : ?array
    {
        self::executeAndRegister(DBHelper_OperationTypes::TYPE_SELECT, $statementOrBuilder, $variables);
        
        $fetch = self::$activeStatement->fetch(PDO::FETCH_ASSOC);
        
        if ($fetch === false) {
            $fetch = null;
        }
        
        return $fetch;
    }

    /**
     * Like {@link fetch()}, but builds the query dynamically to
     * fetch data from a single table.
     *
     * @param string $table The table name
     * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $where Any "WHERE" column values required
     * @param string[] $columnNames The columns to fetch. Defaults to all columns if empty.
     * @return NULL|array
     * @throws DBHelper_Exception
     */
    public static function fetchData(string $table, array $where=array(), array $columnNames=array()) : ?array
    {
        $select = '*';
        if(!empty($columnNames))
        {
            $entries = array();
            foreach($columnNames as $name) {
                $entries[] = self::escapeName($name);
            } 
            
            $select = implode(', ', $entries);
        }
        
        $whereString = self::buildWhereFieldsStatement($where);
        
        $query = sprintf(
            "SELECT
                %s
            FROM
                `%s`
            WHERE
                %s",
            $select,
            $table,
            $whereString
        );
        
        return self::fetch($query, $where);
    }

    /**
     * Fetches all entries matching a SELECT query, as an indexed array
     * with associative arrays for each record.
     *
     * @param string|DBHelper_StatementBuilder $statementOrBuilder The full SQL query to run with placeholders for variables
     * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $variables Associative array with placeholders and values to replace in the query
     * @return array<int,array<string,string>>
     * @throws DBHelper_Exception|ConvertHelper_Exception
     */
    public static function fetchAll($statementOrBuilder, array $variables = array()) : array
    {
        self::executeAndRegister(DBHelper_OperationTypes::TYPE_SELECT, $statementOrBuilder, $variables);
        
        $result = self::$activeStatement->fetchAll(PDO::FETCH_ASSOC);
        
        if($result===false) 
        {
            throw self::createException(
                self::ERROR_FETCHING,
                'Failed fetching a record'
            );
        }
        
        return $result;
    }

    /**
     * @param int $code
     * @param string|null $title
     * @param string|null $errorMessage
     * @param PDOException|null $e
     * @return DBHelper_Exception
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    private static function createException(int $code, ?string $title=null, ?string $errorMessage=null, ?PDOException $e=null) : DBHelper_Exception
    {
        if(empty($title)) {
            $title = 'Query failed';
        }
        
        throw new DBHelper_Exception(
            $title,
            self::renderErrorMessage($e, $errorMessage),
            $code,
            $e
        );
    }

    private static function renderErrorMessage(?PDOException $e=null, ?string $errorMessage=null) : string
    {
        if(!isCLI()) {
            return (string)(new HTMLErrorRenderer($e, $errorMessage));
        }

        return (string)(new CLIErrorRenderer($e, $errorMessage));
    }


    /**
     * Retrieves the current query count. This has to be
     * done at the end of the request to be accurate for the total
     * number of queries in a request.
     *
     * @return int
     */
    public static function getQueryCount() : int
    {
        return self::$queryCount;
    }

    public static function getLimitSQL(int $limit = 0, int $offset = 0) : string
    {
        if ($limit < 1 && $offset < 1) {
            return '';
        }

        $limitSQL = 'LIMIT ';
        if ($offset > 0) {
            $limitSQL .= $offset . ',';
        }
        $limitSQL .= $limit;

        return $limitSQL . PHP_EOL;
    }

    /**
     * @var array<int,array<int,mixed>>
     */
    protected static array $queries = array();

   /**
    * Retrieves all queries executed so far, optionally restricted
    * to only the specified types.
    * 
    * @param int[] $types
    * @return array
    */
    public static function getQueries(array $types=array()) : array
    {
        if(empty($types)) {
            return self::$queries;
        }
        
        $queries = array();
        $total = count(self::$queries);
        for($i=0; $i<$total; $i++) {
            if(in_array(self::$queries[$i][3], $types)) {
                $queries[] = self::$queries[$i];
            }
        }
        
        return $queries;
    }
    
   /**
    * Retrieves information about all queries made up to this point,
    * but only write operations.
    * 
    * @return array
    */
    public static function getWriteQueries() : array
    {
        return self::getQueries(DBHelper_OperationTypes::getWriteTypes());
    }
    
    public static function getSelectQueries() : array
    {
        return self::getQueries(array(DBHelper_OperationTypes::TYPE_SELECT));
    }
    
    public static function countSelectQueries() : int
    {
        return self::$queryCountRead;
    }
    
    public static function countWriteQueries() : int
    {
        return self::$queryCountWrite;
    }

    /**
     * @return int
     */
    public static function countQueries() : int
    {
        return self::$queryCount;
    }

    /**
     * Retrieves a list of all tables present in the
     * database; Only shows the tables that the user
     * has access to. Returns an indexed array with
     * table names.
     *
     * @throws DBHelper_Exception
     * @return string[]
     */
    public static function getTablesList() : array
    {
        $entries = self::fetchAll('SHOW TABLES');
        
        $list = array();
        foreach ($entries as $entry) {
            $list[] = $entry[key($entry)];
        }

        return $list;
    }

    /**
     * Truncates the specified table (deletes all rows if any).
     * Returns true on success, false on failure.
     *
     * @param string $tableName
     * @throws DBHelper_Exception
     * @return boolean
     */
    public static function truncate(string $tableName) : bool
    {
        $query = 'TRUNCATE TABLE ' . self::escapeName($tableName);
        return self::executeAndRegister(DBHelper_OperationTypes::TYPE_TRUNCATE, $query);
    }

    /**
     * Used to keep track of transactions.
     * @var boolean
     */
    protected static bool $transactionStarted = false;

    /**
     * Starts a new transaction.
     *
     * NOTE: The transaction must be committed or rolled
     * back once all statements have been run.
     *
     * @return boolean
     * @throws DBHelper_Exception
     * @see commitTransaction()
     * @see rollbackTransaction()
     */
    public static function startTransaction() : bool
    {
        self::log('Starting a new transaction.');

        if (self::$transactionStarted) {
            throw new DBHelper_Exception(
                'Cannot start another transaction',
                'You cannot start a new transaction within a transaction; Commit or rollback the current transaction before starting a new one.',
                self::ERROR_CANNOT_START_TRANSACTION
            );
        }

        self::$transactionStarted = true;

        return self::executeAndRegister(DBHelper_OperationTypes::TYPE_TRANSACTION, 'START TRANSACTION');
    }

    public static function startConditional() : void
    {
        if(!self::isTransactionStarted())
        {
            self::startTransaction();
        }
    }

    public static function commitConditional() : void
    {
        if(self::isTransactionStarted())
        {
            self::commitTransaction();
        }
    }

    /**
     * Checks whether a transaction has been started.
     * @return boolean
     */
    public static function isTransactionStarted() : bool
    {
        return self::$transactionStarted;
    }

    /**
     * Commits a previously started transaction by applying
     * all changes made permanently.
     *
     * @return boolean
     * @throws DBHelper_Exception
     * @see rollbackTransaction()
     * @see startTransaction()
     */
    public static function commitTransaction() : bool
    {
        self::log('Committing the transaction.');

        if (!self::$transactionStarted) {
            throw new DBHelper_Exception(
                'Cannot commit transaction',
                'No transaction was started to commit.',
                self::ERROR_CANNOT_COMMIT_TRANSACTION
            );
        }

        self::$transactionStarted = false;

        return self::executeAndRegister(DBHelper_OperationTypes::TYPE_TRANSACTION, 'COMMIT');
    }

    /**
     * Rolls back a previously started transaction by cancelling
     * all changes.
     *
     * @throws DBHelper_Exception
     * @return boolean
     * @see commitTransaction()
     * @see startTransaction()
     */
    public static function rollbackTransaction() : bool
    {
        self::log('Rolling back the transaction.');

        if (!self::$transactionStarted) {
            throw new DBHelper_Exception(
                'Cannot roll back transaction',
                'No transaction was started to roll back.',
                self::ERROR_CANNOT_ROLL_BACK_TRANSACTION
            );
        }

        self::$transactionStarted = false;

        return self::executeAndRegister(DBHelper_OperationTypes::TYPE_TRANSACTION, 'ROLLBACK');
    }

    public static function rollbackConditional() : void
    {
        if(self::isTransactionStarted())
        {
            self::rollbackTransaction();
        }
    }

    /**
     * @var string
     */
    protected static string $selectedDB = 'main';

    /**
     * @var PDO|NULL
     */
    protected static ?PDO $activeDB = null;

    public static function init() : void
    {
        DBHelper_OperationTypes::init();
        
        $port = 0;
        
        if(defined('APP_DB_PORT')) 
        {
            $port = (int)APP_DB_PORT;
        }
        
        self::registerDB(
            'main', 
            APP_DB_NAME, 
            APP_DB_USER, 
            APP_DB_PASSWORD, 
            APP_DB_HOST,
            $port
        );
        
        self::$activeDB = self::getDB();

        self::triggerEvent('Init');
    }

    /**
     * @var array<string,array{name:string,username:string,password:string,host:string,port:int}>
     */
    protected static array $databases = array();
    
    public static function registerDB(string $id, string $name, string $username, string $password, string $host, int $port=0) : void
    {
        if($port <= 0) {
            $port = 3306;
        }
        
        self::$databases[$id] = array(
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'host' => $host,
            'port' => $port
        );
    }
    
    public static function selectDB(string $id) : void
    {
        self::$selectedDB = $id;
        self::$activeDB = self::getDB();
    }
    
   /**
    * Triggers an event and returns the event handler instance.
    * 
    * @param string $eventName
    * @param array $args
    * @return DBHelper_Event|NULL
    */
    protected static function triggerEvent(string $eventName, array $args=array()) : ?DBHelper_Event
    {
        $handler = new DBHelper_Event($eventName, $args);
        
        if(!self::hasListener($eventName)) {
            return null;
        }
        
        foreach(self::$eventHandlers[$eventName] as $listener) {
            $data = $listener['data'];

            array_unshift($data, $handler);
            
            call_user_func_array($listener['callback'], $data);
            
            if($handler->isCancelled()) {
                break;
            }
        }
        
        return $handler;
    }
    
    public static function hasListener(string $eventName) : bool
    {
        return isset(self::$eventHandlers[$eventName]) && !empty(self::$eventHandlers[$eventName]);
    }

    protected static int $eventCounter = 0;

    /**
     * @var array<string,array<int,array{id:int,callback:callable,data:mixed|NULL}>>
     */
    protected static array $eventHandlers = array();

    /**
     * @param callable $handler
     * @param mixed|NULL $data
     * @return int
     */
    public static function onInit(callable $handler, $data=null) : int
    {
        return self::addListener('Init', $handler, $data);
    }

    /**
     * @param string $eventName
     * @param callable $handler
     * @param mixed|NULL $data
     * @return int
     */
    protected static function addListener(string $eventName, callable $handler, $data=null) : int
    {
        self::$eventCounter++;
        
        if(!isset(self::$eventHandlers[$eventName])) {
            self::$eventHandlers[$eventName] = array();
        }
        
        if(empty($data)) {
            $data = array();
        }
        
        if(!is_array($data)) {
            $data = array($data);
        }
        
        $id = count(self::$eventHandlers[$eventName]) + 1;
        self::$eventHandlers[$eventName][$id] = array(
            'id' => $id,
            'callback' => $handler,
            'data' => $data
        );
        
        return $id;
    }
    
   /**
    * Removes all listeners to the specified event, if any.
    * @param string $eventName
    */
    public static function removeListeners(string $eventName) : void
    {
        if(isset(self::$eventHandlers[$eventName])) {
            unset(self::$eventHandlers[$eventName]);
        }
    }
    
   /**
    * Removes a specific event listener by its ID if it exists.
    * @param int $listenerID
    */
    public static function removeListener(int $listenerID) : void
    {
        foreach(self::$eventHandlers as $name => $listeners) {
            foreach($listeners as $id => $listener) {
                if($id === $listenerID) {
                    unset(self::$eventHandlers[$name][$id]);
                    return;
                }
            }
        }
    }

    /**
     * @return array{name:string,username:string,password:string,host:string,port:int}
     * @throws DBHelper_Exception
     */
    public static function getSelectedDB() : array
    {
        if(isset(self::$databases[self::$selectedDB]))
        {
            return self::$databases[self::$selectedDB];
        }

        throw new DBHelper_Exception(
            'Cannot select unregistered database.',
            sprintf(
                'The database [%s] has not been registered.',
                self::$selectedDB
            ),
            self::ERROR_DB_NOT_REGISTERED
        );
    }

    /**
     * Retrieves the PDO database connection object for
     * the currently selected DB account.
     *
     * @throws DBHelper_Exception
     * @return PDO
     */
    public static function getDB() : PDO
    {
        if(isset(self::$db[self::$selectedDB])) {
            return self::$db[self::$selectedDB];
        }

        $def = self::getSelectedDB();
        
        try
        {
            $db = new PDO(
                self::getDBUri(),
                $def['username'],
                $def['password'],
                array(
                    PDO::ATTR_PERSISTENT => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES latin1"
                )
            );
        } 
        catch (PDOException $e) 
        {
            if(stripos($e->getMessage(), 'driver') !== false)
            {
                throw self::createException(
                    self::ERROR_CONNECTING_NO_DRIVER,
                    'PDO is not installed',
                    'The PDO MYSQL driver is missing, cannot connect to the database.', 
                    $e
                );
            }
            
            throw self::createException(
                self::ERROR_CONNECTING,
                sprintf(
                    'Could not connect to the database at %s. The database said: %s',
                    self::getDBUri(),
                    $e->getMessage()
                ),
                null,
                $e
            );
        }

        self::$db[self::$selectedDB] = $db;

        return self::$db[self::$selectedDB];
    }

    /**
     * Retrieves the active database's URI, without authentication information.
     *
     * @return string
     * @throws DBHelper_Exception
     */
    public static function getDBUri() : string
    {
        $def = self::getSelectedDB();

        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s',
            $def['host'],
            $def['port'],
            $def['name']
        );
    }

    /**
     * Simple helper method for retrieving the counter from a
     * count query: the result of the query merely has to
     * contain a "count" field which is then returned. In all
     * other cases, this will return 0.
     *
     * @param string|DBHelper_StatementBuilder $statementOrBuilder
     * @param array<string,mixed> $variables
     * @param string $columName
     * @return int
     *
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    public static function fetchCount($statementOrBuilder, array $variables = array(), string $columName='count') : int
    {
        $entry = self::fetch($statementOrBuilder, $variables);
        if ($entry !== null && isset($entry[$columName])) {
            return $entry[$columName];
        }

        return 0;
    }

    /**
     * Builds a limit statement to append to a query string
     * using the specified limit and offset values. Returns
     * an empty string if both are set to 0.
     *
     * @param int $limit
     * @param int $offset
     * @return string
     */
    public static function buildLimitStatement(int $limit = 0, int $offset = 0) : string
    {
        return self::getLimitSQL($limit, $offset);
    }

    public const INSERTORUPDATE_UPDATE = 'upd';

    /**
     * Utility method that either inserts or updates an existing record.
     *
     * @param string $table
     * @param array<string,mixed> $data
     * @param string[] $primaryFieldNames
     * @return string The insert ID in case of an insert operation, or the update status code.
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     * @throws JsonException
     * @see DBHelper::INSERTORUPDATE_UPDATE
     */
    public static function insertOrUpdate(string $table, array $data, array $primaryFieldNames) : string
    {
        $where = array();

        foreach ($primaryFieldNames as $fieldName)
        {
            if (!array_key_exists($fieldName, $data)) 
            {
                throw new DBHelper_Exception(
                    'Database configuration error',
                    sprintf(
                        'Cannot insert or update a record, the value for primary field %1$s is not included in the data set. Data set contains the following keys: %2$s.',
                        $fieldName,
                        implode(', ', array_keys($data))
                    ),
                    self::ERROR_INSERTING
                );
            }

            $where[$fieldName] = $data[$fieldName];
        }

        $col = current($primaryFieldNames);

        $key = self::createFetchKey($col, $table)
            ->whereValues($where)
            ->fetchString();

        if ($key !== '') {
            self::updateDynamic($table, $data, $primaryFieldNames);
            return self::INSERTORUPDATE_UPDATE;
        }

        return self::insertDynamic($table, $data);
    }
    
   /**
    * Updates a table by building the query dynamically.
    * 
    * @param string $table
    * @param array<string,mixed> $data
    * @param string[] $primaryFields
    * @throws DBHelper_Exception
    * @return boolean
    */
    public static function updateDynamic(string $table, array $data, array $primaryFields) : bool
    {
        $where = array();
        foreach ($primaryFields as $fieldName) {
            if (!array_key_exists($fieldName, $data)) {
                throw new DBHelper_Exception(
                    'Database configuration error',
                    sprintf(
                        'Cannot insert or update a record, the value for primary field %1$s is not included in the data set. Data set contains the following keys: %2$s.',
                        $fieldName,
                        implode(', ', array_keys($data))
                    ),
                    self::ERROR_INSERTING
                );
            }
            $where[$fieldName] = $data[$fieldName];
        }

        return self::update(
            sprintf(
                "UPDATE
                    `%s`
                SET
                    %s
                WHERE
                    %s",
                $table,
                self::buildSetStatement($data),
                self::buildWhereFieldsStatement($where)
            ),
            $data
        );
    }
    
   /**
    * Builds a list of columns to set values for, with value placeholders
    * named after the column names.
    * 
    * @param array<string,string|number|NULL> $data
    * @return string
    */
    public static function buildSetStatement(array $data) : string
    {
        $tokens = array();
        $columns = array_keys($data);
        foreach($columns as $name) {
            $tokens[] = sprintf(
                "`%s`=:%s", 
                $name, 
                $name
            );
        }
        
        return implode(', ', $tokens);
    }

    protected static bool $debugging = false;

    /**
     * Enables debugging, which will output all queries as they are run.
     */
    public static function enableDebugging() : void
    {
        self::$debugging = true;
    }

    /**
     * Disables the debug mode.
     */
    public static function disableDebugging() : void
    {
        self::$debugging = false;
    }

    /**
     * @param bool $result
     */
    protected static function debugQuery(bool $result) : void
    {
        if (!self::$debugging) {
            return;
        }

        $replaces = array(
            "\t" => '',
            '    ' => '',
            ',' => ','.PHP_EOL
        );
        
        $sql = trim(self::getSQL());
        $sql = str_replace(array_keys($replaces), array_values($replaces), $sql);

        if(isCLI())
        {
            echo PHP_EOL.
            '----------------------------------------------'.PHP_EOL.
            $sql.PHP_EOL;
        }
        else
        {
            echo 
            '<pre>' . 
                $sql . PHP_EOL . 
                'Result: '. ConvertHelper::boolStrict2string($result) .
            '</pre>';
        }
    }

    /**
     * @return string[]
     * @throws DBHelper_Exception
     * @deprecated Use {@see DBHelper::getTablesList()} instead.
     * @see DBHelper::getTablesList()
     */
    public static function fetchTableNames() : array
    {
        return self::getTablesList();
    }

    public static function dropTables() : bool
    {
        $query = self::getDropTablesQuery();

        return self::executeAndRegister(DBHelper_OperationTypes::TYPE_DROP, $query);
    }

    public static function getDropTablesQuery() : string
    {
        $tables = self::getTablesList();
        $tableQueries = array();
        foreach ($tables as $table) {
            $tableQueries[] = sprintf('DROP TABLE `%s`;', $table);
        }

        return sprintf(
            "SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;" . PHP_EOL .
            "SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;" . PHP_EOL .
            "SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';" . PHP_EOL .
            "%s" . PHP_EOL .
            "SET SQL_MODE=@OLD_SQL_MODE;" . PHP_EOL .
            "SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;" . PHP_EOL .
            "SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;",
            implode(PHP_EOL, $tableQueries)
        );
    }

    protected static function log(string $message) : void
    {
        if(isset(self::$logCallback)) {
            call_user_func(self::$logCallback, sprintf(
                'DBHelper | %1$s',
                $message
            ));
        }
    }

    /**
     * @var callable|NULL
     */
    protected static $logCallback = null;
    
    public static function setLogCallback(callable $callback) : void
    {
        self::$logCallback = $callback;
    }

    public static function countAffectedRows() : int
    {
        if (!isset(self::$activeStatement)) {
            throw new DBHelper_Exception(
                'No active statement present',
                'Cannot retrieve affected rows if no active statement is present.',
                self::ERROR_NO_ACTIVE_STATEMENT
            );
        }

        return self::$activeStatement->rowCount();
    }

    /**
     * @var array<string,bool>
     */
    protected static array $cachedColumnExist = array();

    /**
     * Checks whether the specified column exists in the target table.
     *
     * @param string $tableName
     * @param string $columnName
     * @return boolean
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     */
    public static function columnExists(string $tableName, string $columnName) : bool
    {
        $key = $tableName.'.'.$columnName;
        if(isset(self::$cachedColumnExist[$key])) {
            return self::$cachedColumnExist[$key];
        }
        
        self::$cachedColumnExist[$key] = false;
        
    	$info = self::fetch(
    		"SELECT
				`COLUMN_NAME`
			FROM
				`INFORMATION_SCHEMA`.`COLUMNS`
			WHERE
				`TABLE_SCHEMA` = :dbname
			AND
				`TABLE_NAME` = :tablename
			AND
				`COLUMN_NAME` = :columnname",
    		array(
    			'dbname' => APP_DB_NAME,
    			'tablename' => $tableName,
    			'columnname' => $columnName
    		)
    	);
    	
    	if(is_array($info) && isset($info['COLUMN_NAME'])) {
    		self::$cachedColumnExist[$key] = true;
    	}
    	
    	return self::$cachedColumnExist[$key];
    }

    /**
     * Fetches a single row using the {@link fetch()} method, and returns
     * the specified key from the result set. Returns null if the key is
     * not found.
     *
     * @param string $key
     * @param string|DBHelper_StatementBuilder $statementOrBuilder
     * @param array<string,mixed> $variables
     * @return string|NULL
     *
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    public static function fetchKey(string $key, $statementOrBuilder, array $variables=array()) : ?string
    {
        $data = self::fetch($statementOrBuilder, $variables);
        if($data !== null && isset($data[$key])) {
            return $data[$key];
        }
        
        return null;
    }
    
    public static function createFetchKey(string $key, string $table) : DBHelper_FetchKey
    {
        return new DBHelper_FetchKey($key, $table);
    }
    
    public static function createFetchOne(string $table) : DBHelper_FetchOne
    {
        return new DBHelper_FetchOne($table);
    }
    
    public static function createFetchMany(string $table) : DBHelper_FetchMany
    {
        return new DBHelper_FetchMany($table);
    }

    /**
     * Fetches a key, and returns an integer value.
     *
     * @param string $key
     * @param string|DBHelper_StatementBuilder $statementOrBuilder
     * @param array<string,mixed> $variables
     * @return integer
     *
     * @throws DBHelper_Exception
     * @throws JsonException
     */
    public static function fetchKeyInt(string $key, $statementOrBuilder, array $variables=array()) : int
    {
        $value = self::fetchKey($key, $statementOrBuilder, $variables);
        
        if($value === null) {
            return 0;
        }
        
        return (int)$value;
    }

    /**
     * Fetches all matching rows using the {@link fetchAll()} method, and
     * returns an indexed array with all available values for the specified
     * key in the result set. Returns an empty array if the key is not found,
     * or if no records match.
     *
     * @param string $key
     * @param string|DBHelper_StatementBuilder $statementOrBuilder
     * @param array<string,mixed> $variables
     * @return string[]
     *
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     */
    public static function fetchAllKey(string $key, $statementOrBuilder, array $variables=array()) : array
    {
        $result = array();
        $entries = self::fetchAll($statementOrBuilder, $variables);

        foreach ($entries as $record)
        {
           if(isset($record[$key])) {
               $result[] = $record[$key];
           } 
        }

        return $result;
    }

    /**
     * Like <code>fetchAllKey</code>, but enforces int
     * values for all values that were found.
     *
     * @param string $key
     * @param string|DBHelper_StatementBuilder $statementOrBuilder
     * @param array<string,mixed> $variables
     * @return int[]
     *
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     */
    public static function fetchAllKeyInt(string $key, $statementOrBuilder, array $variables=array()) : array
    {
        $items = self::fetchAllKey($key, $statementOrBuilder, $variables);
        $result = array();

        foreach ($items as $value)
        {
            $result[] = (int)$value;
        }

        return $result;
    }
    
   /**
    * Allows only column names with lowercase letters,
    * no numbers, and underscores. Must begin with a
    * letter, and end with a letter.
    *
    * @param string $column
    * @throws DBHelper_Exception
    */
    public static function validateColumnName(string $column) : void
    {
        if(preg_match('/\A[a-z][a-z_]+[a-z]\z/', $column)) {
            return;
        }
        
        throw new DBHelper_Exception(
            'Invalid column name.',
            sprintf(
                'The column name [%s] is not allowed.',
                $column
            ),
            self::ERROR_INVALID_COLUMN_NAME
        ); 
    }
    
   /**
    * Allows only table names with lowercase letters,
    * no numbers, and underscores. Must begin with a
    * letter, and end with a letter.
    * 
    * @param string $name
    * @throws DBHelper_Exception
    */
    public static function validateTableName(string $name) : void
    {
        if(preg_match('/\A[a-z][a-z_]+[a-z]\z/', $name)) {
            return;
        }
        
        throw new DBHelper_Exception(
            'Invalid table name.',
            sprintf(
                'The table name [%s] is not allowed.',
                $name
            ),
            self::ERROR_INVALID_TABLE_NAME
        ); 
    }
    
   /**
    * Deletes all records from the target table matching the 
    * specified column values, if any. Otherwise, all records
    * are deleted.
    * 
    * @param string $table The target table name.
    * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $where Associative array with column > value pairs for the where statement.
    * @return boolean
    */
    public static function deleteRecords(string $table, array $where=array()) : bool
    {
        self::validateTableName($table);
        
        $query = 
        "DELETE FROM
            `".$table."`";

        $vars = array();
        
        if(!empty($where)) {
            $tokens = array();
            foreach($where as $name => $value) {
                $tokens[] = sprintf(
                    "`%s` = :%s",
                    $name,
                    $name
                );
                $vars[':'.$name] = $value;
            }
            
            $query .= " WHERE ".implode(" AND ", $tokens);
        }
        
        return self::delete($query, $vars);
    }

    /**
     * Checks whether the specified key exists in the target table.
     *
     * Example:
     *
     * <pre>
     * // with a single key value
     * keyExists('tablename', array('primary_key' => 2));
     *
     * // with a complex key value
     * keyExists(
     *     'tablename',
     *     array(
     *         'first_key' => 5,
     *         'second_key' => 'text',
     *         'third_key' => 'yes'
     *     )
     * );
     * </pre>
     *
     * @param string $table
     * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $where Associative array with key => value pairs to check
     * @return boolean
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     */
    public static function keyExists(string $table, array $where) : bool
    {
        self::requireNonEmptyData($where);

        reset($where);

        $checkField = key($where); // used in the query to check if a record exists
        
        $entry = self::fetch(
            sprintf(
            "SELECT
                `%s`
            FROM
                `$table`
            WHERE
                %s",
                $checkField,
                self::buildWhereFieldsStatement($where)
            ),
            $where
        );

        return is_array($entry) && isset($entry[$checkField]);
    }

    /**
     * @param array $data
     * @throws DBHelper_Exception
     * @see DBHelper::ERROR_EMPTY_WHERE
     */
    private static function requireNonEmptyData(array $data) : void
    {
        if(!empty($data))
        {
            return;
        }

        throw new DBHelper_Exception(
            'No where query conditions set.',
            'Provided an empty array as where data.',
            self::ERROR_EMPTY_WHERE
        );
    }
    
   /**
    * Checks whether the specified table name exists.
    * Warning: Case sensitive!
    * 
    * @param string $tableName
    * @return boolean
    */
    public static function tableExists(string $tableName) : bool
    {
        $names = self::getTablesList();
        return in_array($tableName, $names);
    }
    
   /**
    * Inserts a record by building the SQL statement dynamically from
    * the provided data set. Note that no validation happens here: you
    * must ensure that all required columns have a value.
    * 
    * Note: columns with a null value are assumed to be nullable and will
    * be set to NULL accordingly.
    * 
    * @param string $tableName
    * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $data
    * @return string The insert ID, if any
    */
    public static function insertDynamic(string $tableName, array $data=array()) : string
    {
        if(empty($data)) {
            return self::insert(sprintf("INSERT INTO `%s` VALUES(NULL)", $tableName));
        }

        $setTokens = array();
        foreach($data as $column => $value) {
            // special case for null values, which need a different syntax.
            if($value===NULL) {
                $setTokens[] = "`".$column."`=NULL";
                unset($data[$column]);
            } 
            else 
            {
                $setTokens[] = "`".$column."`=:".$column;
            }
        }
        
        return self::insert(
            sprintf(
                "INSERT INTO
                    `%s`
                SET
                    %s",
                $tableName,
                implode(', ', $setTokens)
            ),
            $data
        );
    }

    /**
     * Checks whether the specified table column is an auto
     * increment column.
     *
     * @param string $tableName
     * @param string $columnName
     * @return boolean
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     */
    public static function isAutoIncrementColumn(string $tableName, string $columnName) : bool
    {
        $data = self::fetch(
            "SELECT 
               `EXTRA`
            FROM 
                INFORMATION_SCHEMA.COLUMNS
            WHERE 
                `TABLE_NAME`=:table
            AND
                `COLUMN_NAME`=:column
            AND 
                `EXTRA` like '%auto_increment%'",
            array(
                'table' => $tableName,
                'column' => $columnName
            )
        );

        return is_array($data) && isset($data['EXTRA']);
    }
    
   /**
    * Utility method that can be used to enforce an active
    * DB transaction. Throws an exception if a DB transaction
    * is not present.
    * 
    * @param string $operationLabel A short description for the operation that requires the transaction, added to the exception details 
    * @throws DBHelper_Exception
    */
    public static function requireTransaction(string $operationLabel) : void
    {
        if(self::isTransactionStarted()) {
           return; 
        }
        
        throw new DBHelper_Exception(
            'A transaction is required',
            sprintf(
                'A transaction is required for the following operation: %s.',
                $operationLabel
            ),
            self::ERROR_TRANSACTION_REQUIRED_FOR_OPERATION
        );
    }
    
   /**
    * Checks whether a record matching the specified 
    * fields exists in the table.
    *  
    * @param string $table
    * @param array $where
    * @return boolean
    */
    public static function recordExists(string $table, array $where) : bool
    {
        $query = sprintf( 
            "SELECT
                COUNT(*) AS `count`
            FROM
                `%s`
            WHERE
                %s",
            $table,
            self::buildWhereFieldsStatement($where)
        );

        return self::fetchCount($query, $where) > 0;
    }
    
   /**
    * Builds the condition statement for the selected fields
    * from a list of fields and values, connected by <code>AND</code>.
    * The bound variable names match the field names.
    *  
    * @param array<string,string|number|StringableInterface|Microtime|DateTime|bool|NULL> $params
    * @return string
    */
    public static function buildWhereFieldsStatement(array $params) : string
    {
        $tokens = array();
        foreach($params as $key => $value) 
        {
            $tokens[] = sprintf(
                "`%s`=:%s",
                $key,
                $key
            );
        }
        
        return implode(' AND ', $tokens);
    }
    
   /**
    * Adds an event listener that is called before a database write operation.
    * @param callable $eventCallback
    * @param mixed|NULL $data
    * @return int
    */
    public static function onBeforeWriteOperation(callable $eventCallback, $data=null) : int
    {
        return self::addListener('BeforeDBWriteOperation', $eventCallback, $data);
    }

    /**
     * @var array<string,DBHelper_BaseCollection>
     */
    protected static array $collections = array();

    /**
     * Clears/resets the internal collection instances cache,
     * which will force them to be created anew if requested.
     *
     * @return void
     */
    public static function clearCollections() : void
    {
        self::$collections = array();
    }

    /**
     * @param string $class
     * @param DBHelper_BaseRecord|NULL $parentRecord
     * @param bool $newInstance
     * @return DBHelper_BaseCollection
     * @throws DBHelper_Exception
     */
    public static function createCollection(string $class, ?DBHelper_BaseRecord $parentRecord=null, bool $newInstance=false) : ?DBHelper_BaseCollection
    {
        $key = $class;
        if($parentRecord) {
            $key .= '-'.$parentRecord->getID();
        }

        if(!$newInstance && isset(self::$collections[$key])) {
            return self::$collections[$key];
        }
    
        $instance = ClassHelper::requireObjectInstanceOf(
            DBHelper_BaseCollection::class,
            new $class(),
            self::ERROR_NOT_A_DBHELPER_COLLECTION
        );

        if($instance->hasParentCollection())
        {
            if(!$parentRecord) {
                throw new DBHelper_Exception(
                    'No parent record specified',
                    sprintf(
                        'The DBHelper collection class [%s] requires a parent record to be specified when calling createCollection.',
                        $class
                    ),
                    self::ERROR_NO_PARENT_RECORD_SPECIFIED
                );
            }
    
            $parentClass = get_class($parentRecord->getCollection());
            if($parentClass !== $instance->getParentCollectionClass()) {
                throw new DBHelper_Exception(
                    'Invalid parent record',
                    sprintf(
                        'The DBHelper collection class [%s] requires a parent record of the collection [%s], provided was a record of type [%s].',
                        $class,
                        $instance->getParentCollectionClass(),
                        get_class($parentRecord->getCollection())
                    ),
                    self::ERROR_INVALID_PARENT_RECORD
                );
            }
    
            $instance->bindParentRecord($parentRecord);
        }
        
        $instance->setupComplete();

        if(!$newInstance)
        {
            self::$collections[$key] = $instance;
        }

        return $instance;
    }

    /**
     * @var array<string,array<int,array{tablename:string,columname:string}>>
     */
    private static array $fieldRelations = array();

    /**
     * Fetches all fields from other tables in the database that
     * have relations to the specified field.
     *
     * NOTE: Results are cached within the request.
     *
     * @param string $tableName
     * @param string $fieldName
     * @return array<int,array{tablename:string,columname:string}>
     * @throws ConvertHelper_Exception
     * @throws DBHelper_Exception
     * @see https://stackoverflow.com/a/31972009/2298192
     */
    public static function getRelationsForField(string $tableName, string $fieldName) : array
    {
        $cacheKey = $tableName.'.'.$fieldName;

        if(isset(self::$fieldRelations[$cacheKey])) {
            return self::$fieldRelations[$cacheKey];
        }

        $query = <<<'SQL'
SELECT 
    c.table_name tablename,
    CONCAT(' ',GROUP_CONCAT(c.column_name ORDER BY ordinal_position SEPARATOR ', ')) columnname
FROM
    information_schema.columns c 
RIGHT JOIN
    (
        SELECT column_name , column_type FROM information_schema.columns WHERE 
        table_schema = DATABASE() AND table_name = '%1$s'
     ) 
    AS p
    USING (column_name,column_type)
WHERE
    c.table_schema = DATABASE()
AND 
    c.table_name != '%1$s'
GROUP BY 
    tablename
HAVING 
    (locate(' %2$s',columnname) > 0)  
ORDER BY
    columnname
SQL;

        $result = self::fetchAll(
            sprintf(
                $query,
                $tableName,
                $fieldName
            )
        );

        self::$fieldRelations[$cacheKey] = $result;

        return $result;
    }

    public static function escapeName(string $name) : string
    {
        if(strpos($name, '`') !== false) {
            return $name;
        }

        return '`'.$name.'`';
    }

    public static function escapeTableColumn(string $table, string $name) : string
    {
        return self::escapeName($table).'.'.self::escapeName($name);
    }
}
