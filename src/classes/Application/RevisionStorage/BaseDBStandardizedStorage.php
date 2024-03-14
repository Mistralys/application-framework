<?php
/**
 * File containing the {@link BaseDBStandardizedStorage} class.
 * 
 * @package Application
 * @subpackage Revisionable
 * @see BaseDBStandardizedStorage
 */

declare(strict_types=1);

use Application\RevisionStorage\BaseDBRevisionStorage;
use Application\RevisionStorage\RevisionStorageException;
use TestDriver\Revisionables\RevisionableCollection;

/**
 * Standardized implementation for database revision storage: generic
 * implementation using the common revision storage setup. Handles
 * loading and modifying revisions automatically given information 
 * about the revision storage tables.
 * 
 * This assumes the following things to work:
 * 
 * - A single ID column for the revisionable
 * - An autoincrement column for the revision ID
 * - A revision data table with the usual keys:
 *      - {@see Application_RevisionableCollection::COL_REV_LABEL}
 *      - {@see Application_RevisionableCollection::COL_REV_STATE}
 *      - {@see Application_RevisionableCollection::COL_REV_DATE}
 *      - {@see Application_RevisionableCollection::COL_REV_AUTHOR}
 *      - {@see Application_RevisionableCollection::COL_REV_COMMENTS}
 *      - {@see Application_RevisionableCollection::COL_REV_PRETTY_REVISION}
 *      - Any additional custom columns
 *
 * If these conditions are met, to use this class only the abstract
 * methods need to be implemented.
 * 
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseDBStandardizedStorage extends BaseDBRevisionStorage
{
    public const ERROR_LOADING_REVISION = 534001;
    public const ERROR_LOADING_REVISION_USER = 534002;

    public const FREEFORM_KEY_PREFIX = 'freeform_';
    public const COL_DATA_KEY = 'data_key';
    public const COL_DATA_VALUE = 'data_value';

    protected int $revisionableID;
    protected string $revisionTable;
    protected string $idColumn;
    protected string $revisionColumn;
    protected string $revDataTable;
    
    public function __construct(Application_RevisionableStateless $revisionable)
    {
        parent::__construct($revisionable);
        
        $this->revisionableID = $this->getRevisionableID();
        $this->revisionTable = $this->getRevisionsTable();
        $this->revDataTable = $this->revisionTable.'_data';
        $this->idColumn = $this->getIDColumn();
        $this->revisionColumn = $this->getRevisionColumn();
    }
    
   /**
    * Retrieves the value for the revisionable ID column.
    * @return integer
    */
    abstract public function getRevisionableID() : int;
    
   /**
    * Retrieves the name of the table storing the revisionable's revision data.
    * @return string
    */
    abstract public function getRevisionsTable() : string;
    
   /**
    * Retrieves the name of the column in which the revisionable ID is stored.
    * @return string
    */
    abstract public function getIDColumn() : string;
    
   /**
    * Retrieves the name of the column in which the revision ID is stored.
    * @return string
    */
    abstract public function getRevisionColumn() : string;

    /**
     * @param int $number
     * @throws RevisionStorageException
     * @throws DBHelper_Exception
     */
    protected function _loadRevision(int $number) : void
    {
        $data = DBHelper::fetch(
            sprintf(
                "SELECT
                    *
                FROM
                    `%s`
                WHERE
                    `%s`=:revisionable_id
                AND
                    `%s`=:revision",
                $this->revisionTable,
                $this->idColumn,
                $this->revisionColumn
            ),
            array(
                'revisionable_id' => $this->revisionableID,
                'revision' => $number
            )
        );

        if (!is_array($data) || !isset($data[$this->idColumn])) {
             throw new RevisionStorageException(
                'Could not load revision',
                sprintf(
                    'Tried loading revision [%1$s] for revisionable [%2$s].',
                    $number,
                    $this->revisionableID
                ),
                self::ERROR_LOADING_REVISION
            );
        }

        $userID = (int)$data[Application_RevisionableCollection::COL_REV_AUTHOR];

        if (!Application::userIDExists($userID))
        {
            throw new RevisionStorageException(
                'Revisioning error',
                sprintf(
                    'Author [%1$s] for revision [%2$s] of revisionable [%3$s] does not exist.',
                    $data[Application_RevisionableCollection::COL_REV_AUTHOR],
                    $number,
                    $this->revisionableID
                ),
                self::ERROR_LOADING_REVISION_USER
            );
        }

        $author = Application::createUser($userID);

        $this->addRevision(
            $number,
            $author->getID(),
            $author->getName(),
            strtotime($data[Application_RevisionableCollection::COL_REV_DATE]),
            $data[Application_RevisionableCollection::COL_REV_COMMENTS]
        );

        $this->setKeys($data);

        if($this->revisionable instanceof Application_Revisionable) {
            $this->setKey(
                Application_RevisionableCollection::COL_REV_STATE,
                $this->revisionable->getStateHandler()->getStateByName($data[Application_RevisionableCollection::COL_REV_STATE])
            );
        }
    }

    protected ?int $cacheRevisionCount = null;

    /**
     * @return int
     */
    public function countRevisions() : int
    {
        if ($this->cacheRevisionCount !== null) {
            return $this->cacheRevisionCount;
        }

        $count = DBHelper::fetchCount(
            sprintf(
                "SELECT
                    COUNT(`%s`) AS `count`
                FROM
                    `%s`
                WHERE
                    `%s`=:revisionable_id",
                $this->revisionColumn,
                $this->revisionTable,
                $this->idColumn
            ),
            array(
                'revisionable_id' => $this->revisionableID
            )
        );

        $this->cacheRevisionCount = $count;

        return $count;
    }
    
    protected array $cacheKnownRevisions = array();

    public function revisionExists(int $number, bool $forceLiveCheck=false) : bool
    {
        if (!$forceLiveCheck && isset($this->cacheKnownRevisions[$number])) {
            return $this->cacheKnownRevisions[$number];
        }

        $entry = DBHelper::fetch(
            sprintf(
                "SELECT
                    `%s`
                FROM
                    `%s`
                WHERE
                    `%s`=:revision",
                $this->revisionColumn,
                $this->revisionTable,
                $this->revisionColumn
            ),
            array(
                'revision' => $number
            )
        );

        $this->cacheKnownRevisions[$number] = false;
        if (isset($entry[$this->revisionColumn])) {
            $this->cacheKnownRevisions[$number] = true;
        }

        return $this->cacheKnownRevisions[$number];
    }

    /**
     * @return $this
     */
    protected function resetCache() : self
    {
        $this->cacheKnownRevisions = array();
        $this->cacheRevisionsList = null;
        $this->cacheRevisionCount = null;

        $this->log('Internal cache reset.');

        return $this;
    }

    /**
     * Removes a revision. Note that only the latest
     * revision may be removed. If you wish to remove
     * an earlier revision, you will need to remove all
     * revisions that came after it.
     * 
     * @param int $number
     */
    protected function _removeRevision(int $number) : self
    {
        DBHelper::delete(
            sprintf(
                "DELETE FROM
                    `%s`
                WHERE
                    `%s`=:revision",
                $this->revisionTable,
                $this->revisionColumn
            ),
            array(
                'revision' => $number
            )
        );

        unset($this->data[$number]);

        $this->log('Revision removed.');

        return $this->resetCache();
    }

   /**
    * Retrieves the data for all revision columns to create
    * the next revision, using the current revisionable data.
    * This needs only contain the non-standard column values,
    * excluding those like state, author, date or comments as
    * well as the IDs.
    * 
    * @return array<string,string|number|DateTime|bool|NULL>
    */
    abstract public function getNextRevisionData() : array;
    
    public function nextRevision() : int
    {
        $this->log('Creating the next revision.');
        
        foreach($this->staticColumns as $column => $value) {
            $this->log(sprintf('Using static column [%s] with value [%s].', $column, $value));
        }
        
        $user = Application::getUser();

        $customFields = $this->getNextRevisionData();

        if(!empty($customFields)) {
            $this->log('Using custom revision fields: [%s].', implode(', ', array_keys($customFields)));
        }

        // retrieves revisionable-specific data in addition
        // to any required static column values.
        $data = $this->getColumns($customFields);

        $data[Application_RevisionableCollection::COL_REV_LABEL] = $this->revisionable->getLabel();
        $data[Application_RevisionableCollection::COL_REV_AUTHOR] = $user->getID();
        $data[Application_RevisionableCollection::COL_REV_DATE] = date('Y-m-d H:i:s');
        $data[Application_RevisionableCollection::COL_REV_STATE] = '';
        $data[Application_RevisionableCollection::COL_REV_COMMENTS] = '';
        $data[Application_RevisionableCollection::COL_REV_PRETTY_REVISION] = $this->nextPrettyRevision();
        
        if($this->revisionable instanceof Application_Revisionable)
        {
            $data[Application_RevisionableCollection::COL_REV_STATE] = $this->revisionable->getStateName();
        }
        
        $number = (int)DBHelper::insertDynamic($this->revisionTable, $data);
        
        $this->cacheKnownRevisions[$number] = true;
        $this->cacheRevisionsList[] = $number;
        $this->cacheRevisionCount++;

        $this->log(sprintf(
            'Next revision [%1$s] inserted, with pretty revision [%2$s].',
            $number,
            $data[Application_RevisionableCollection::COL_REV_PRETTY_REVISION]
        ));

        return $number;
    }

    /**
     * @var int[]|null
     */
    protected ?array $cacheRevisionsList = null;

    public function getRevisions() : array
    {
        if ($this->cacheRevisionsList) {
            return $this->cacheRevisionsList;
        }

        $this->log('Retrieving the revisions list.');

        $query = sprintf(
            "SELECT
                `%s`
            FROM
                `%s`
            WHERE
                %s
            ORDER BY
                `date` ASC",
            $this->revisionColumn,
            $this->revisionTable,
            $this->buildColumnsWhere()
        );
        
        $this->cacheRevisionsList = DBHelper::fetchAllKeyInt($this->revisionColumn, $query, $this->getColumns());

        $this->log(sprintf(
            'Revisions list loaded, found %1$s revisions.',
            count($this->cacheRevisionsList)
        ));

        return $this->cacheRevisionsList;
    }
    
    public function getFilterCriteria() : Application_FilterCriteria_RevisionableRevisions
    {
        return new Application_FilterCriteria_RevisionableRevisions($this);
    }
    
    public function getLogIdentifier() : string
    {
        if(!isset($this->logFormat[$this->revision])) {
            $this->logFormat[$this->revision] = $this->logName.' ['.$this->revisionableID.'] | RevisionStorage ['.$this->revision.'] | ';
        }
        
        return parent::getLogIdentifier();
    }

   /**
    * Retrieves the next pretty revision for the revisionable. 
    * Checks for existing revisions and returns the next 
    * highest increment.
    * 
    * @return integer
    */
    public function nextPrettyRevision() : int
    {
        $query = sprintf(
            'SELECT
                MAX(`%1$s`) + 1 AS `%1$s`
            FROM
                `%2$s`
            WHERE
                %3$s',
            Application_RevisionableCollection::COL_REV_PRETTY_REVISION,
            $this->revisionTable,
            $this->buildColumnsWhere()
        );
        
        $rev = DBHelper::fetchKeyInt(Application_RevisionableCollection::COL_REV_PRETTY_REVISION, $query, $this->getColumns());
        if($rev > 0) {
            return $rev;
        }
        
        return 1;
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function getColumns(array $data=array()) : array
    {
        foreach($this->staticColumns as $column => $value) {
            $data[$column] = $value;
        }
        
        $data[$this->idColumn] = $this->revisionableID;
        
        return $data;
    }
    
    protected function buildColumnsWhere() : string
    {
        $reqs = $this->getColumns();
        
        // build the list of columns for the where statement
        $tokens = array();
        $cols = array_keys($reqs);
        foreach($cols as $column) {
            $tokens[] = "`".$column."`=:".$column;
        }
        
        return implode(" AND ", $tokens);
    }
    
    public function getRevdataTable() : string
    {
        return $this->revDataTable;
    }
    
    protected function _hasDataKeys() : bool
    {
        return DBHelper::tableExists($this->revDataTable);
    }
    
    protected function _loadDataKey(string $name) : string
    {
        return (string)DBHelper::fetchKey(
            self::COL_DATA_VALUE,
            sprintf(
                "SELECT
                    `".self::COL_DATA_VALUE."`
                FROM
                    `%s`
                WHERE
                    `%s`=:%s
                AND
                    `".self::COL_DATA_KEY."`=:data_key",
                $this->revDataTable,
                $this->revisionColumn,
                $this->revisionColumn
            ),
            array(
                $this->revisionColumn => $this->revision,
                self::COL_DATA_KEY => $name
            )
        );
    }
    
    protected function _writeDataKey(string $key, $value) : void
    {
        $this->log('Write data key: '.$key);
        
        DBHelper::insertOrUpdate(
            $this->revDataTable,
            array(
                $this->revisionColumn => $this->revision,
                $this->idColumn => $this->getRevisionableID(),
                self::COL_DATA_KEY => $key,
                self::COL_DATA_VALUE => $value
            ),
            array(
                $this->revisionColumn,
                self::COL_DATA_KEY
            )
        );
    }

    protected function _writeRevisionKeys(array $data) : void
    {
        $data[$this->revisionColumn] = $this->getRevision();

        DBHelper::insertOrUpdate(
            $this->getRevisionsTable(),
            $data,
            array($this->revisionColumn)
        );
    }

    /**
     * Generates the internal data key name used for the
     * freeform keys, using the base data key name. These
     * names use a prefix, so they do not conflict with
     * other keys if they have the same name.
     *
     * @param string $name
     * @return string
     */
    public static function getFreeformKeyName(string $name) : string
    {
        return self::FREEFORM_KEY_PREFIX .$name;
    }
    
    public function setFreeformKey(string $name, string $value) : void
    {
        $this->_writeDataKey(self::getFreeformKeyName($name), $value);
    }
    
    public function getFreeformKey(string $name) : string
    {
        return $this->_loadDataKey(self::getFreeformKeyName($name));
    }
}
