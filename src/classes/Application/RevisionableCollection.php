<?php

declare(strict_types=1);

use Application\AppFactory;
use Application\Revisionable\RevisionableCollectionInterface;
use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableInterface;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper_Exception;

abstract class Application_RevisionableCollection
    implements
    RevisionableCollectionInterface
{
    use Application_Traits_Loggable;

    /**
     * @deprecated Use {@see Application_RevisionableCollection::STUB_OBJECT_ID} instead.
     */
    public const DUMMY_ID = self::STUB_OBJECT_ID;

    public const COL_REV_DATE = 'date';
    public const COL_REV_LABEL = 'label';
    public const COL_REV_STATE = 'state';
    public const COL_REV_COMMENTS = 'comments';
    public const COL_REV_AUTHOR = 'author';
    public const COL_REV_PRETTY_REVISION = 'pretty_revision';
    public const STUB_OBJECT_ID = -9999;


    /**
     * This is called right after the collection's constructor:
     * it is used to process any custom arguments that may have
     * been specified in the {@link create()} method call.
     *
     * Use this to enforce and/or validate specific arguments the
     * collection implementation may require.
     *
     * @param array<mixed> $arguments
     */
    abstract protected function initCustomArguments(array $arguments=array()) : void;

    protected string $recordTypeName;
    protected string $tableName;
    protected string $revisionsTableName;
    protected string $revisionKeyName;
    protected string $currentRevisionsTableName;
    protected string $primaryKeyName;
    protected string $changelogTableName;
    protected string $instanceID;
    
   /**
    * @param array<int,mixed> $customArguments
    */
    protected function __construct(array $customArguments=array())
    {
        $this->recordTypeName = $this->getRecordTypeName();
        $this->tableName = $this->getRecordTableName();
        $this->revisionsTableName = $this->getRevisionsTableName();
        $this->currentRevisionsTableName = $this->getCurrentRevisionsTableName();
        $this->primaryKeyName = $this->getPrimaryKeyName();
        $this->revisionKeyName = $this->getRevisionKeyName();
        $this->changelogTableName = $this->getRecordChangelogTableName();
        $this->instanceID = nextJSID();
        
        $this->initCustomArguments($customArguments);
        $this->init();
    }

    /**
     * Creates a new collection instance. Can be given arbitrary
     * arguments that are passed on to the constructor, and can
     * be used in the collection implementation's {@link init()} method.
     *
     * @param array<int,mixed> ...$args
     * @return Application_RevisionableCollection
     * @throws BaseClassHelperException
     */
    public static function create(...$args) : Application_RevisionableCollection
    {
        $className = static::class;
        
        return ClassHelper::requireObjectInstanceOf(
            Application_RevisionableCollection::class,
            new $className($args)
        );
    }
    
   /**
    * Initializer, called after the constructor to allow extended classes to do their stuff.
    */
    protected function init() : void
    {
        
    }

    public function getInstanceID() : string
    {
        return $this->instanceID;
    }

   /**
    * @return string[]
    */
    public function getRecordSearchableKeys() : array
    {
        $columns = $this->getRecordSearchableColumns();
        return array_keys($columns);
    }

   /**
    * @return Application_RevisionableCollection_FilterCriteria
    */
    public function getFilterCriteria() : Application_RevisionableCollection_FilterCriteria
    {
        $class = $this->getRecordFiltersClassName();
        return new $class($this);
    }
    
   /**
    * @return Application_RevisionableCollection_FilterSettings
    */
    public function getFilterSettings() : Application_RevisionableCollection_FilterSettings
    {
        $class = $this->getRecordFilterSettingsClassName();
        return new $class($this);
    }
    
    /**
     * Creates a stub revisionable object to access
     * information that only instances can provide, like
     * the available revisionable states.
     *
     * @return RevisionableInterface
     */
    public function createDummyRecord() : RevisionableInterface
    {
        return $this->getByID(self::STUB_OBJECT_ID);
    }
    
   /**
    * Creates a new revisionable record in the collection.
    * 
    * @param string $label
    * @param Application_User|NULL $author If empty, the current user is used.
    * @param array<string,mixed> $data 
    * @return RevisionableInterface
    */
    public function createNewRecord(string $label, ?Application_User $author=null, array $data=array()) : RevisionableInterface
    {
        DBHelper::requireTransaction(sprintf('Create a new %s record.', $this->getRecordReadableNameSingular()));
        
        $this->log(sprintf('Creating new record | [%s]', $label));
        
        // first off, we need an ID.
        $revisionable_id = (int)DBHelper::insert(sprintf(
            "INSERT INTO
                `%s`
            SET `%s` = DEFAULT",
            $this->tableName,
            $this->primaryKeyName
        ));
        
        if(!$author) 
        {
            $author = Application::getUser();
        }
        
        /* @var $storage BaseDBCollectionStorage */
        
        $this->log(sprintf('Creating new record | Inserted with ID [%s].', $revisionable_id));
        
        $dummy = $this->createDummyRecord();
        $storageClass = $this->getRevisionsStorageClass();
        $storage = new $storageClass($dummy);
        
        $initialState = $this->getInitialState();
        
        // now insert the revision
        $revision = $storage->createRevision(
            $revisionable_id,
            $label,
            $initialState,
            new DateTime(),
            $author,
            1,
            t('Created %1$s.', $this->getRecordReadableNameSingular()),
            $data
        );
        
        $this->log(sprintf(
            'Revisionable [%s] | Added revision [%s] with state [%s].',
            $revisionable_id,
            $revision,
            $initialState->getName()
        ));
        
        $this->setCurrentRevision($revisionable_id, $revision);
        
        return $this->getByID($revisionable_id);
    }
    
   /**
    * @return Application_StateHandler_State
    */
    public function getInitialState() : Application_StateHandler_State
    {
        return $this->createDummyRecord()->getInitialState();
    }
    
    public function idExists(int $record_id) : bool
    {
        return $this->getCurrentRevision($record_id) !== null;
    }
    
    public function getAll() : array
    {
        return $this->getFilterCriteria()->getItemsObjects();
    }
    
   /**
    * @var RevisionableInterface[]|NULL
    */
    protected ?array $cachedItems = null;
    
    public function getByID(int $record_id) : RevisionableInterface
    {
        if(!isset($this->cachedItems)) {
            $this->cachedItems = array();
        }
        
        if(!isset($this->cachedItems[$record_id])) {
            $this->cachedItems[$record_id] = $this->createRecordInstance($record_id);
        }
        
        return $this->cachedItems[$record_id];
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function unloadRecord(RevisionableInterface $revisionable) : self
    {
        $record_id = $revisionable->getID();

        if(isset($this->cachedItems[$record_id])) {
            unset($this->cachedItems[$record_id]);
        }

        $revisionable->dispose();

        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function resetRecordCache() : self
    {
        foreach($this->cachedItems as $revisionable) {
            $this->unloadRecord($revisionable);
        }

        return $this;
    }

    protected function createRecordInstance(int $record_id) : RevisionableInterface
    {
        $class = $this->getRecordClassName();

        return ClassHelper::requireObjectInstanceOf(
            RevisionableInterface::class,
            new $class($this, $record_id)
        );
    }

   /**
    * Retrieves a revisionable by its revision.
    *
    * @param integer $revision
    * @throws Application_Exception
    * @return RevisionableInterface
    */
    public function getByRevision(int $revision) : RevisionableInterface
    {
        $id = $this->revisionExists($revision);
        
        if($id) 
        {
            return $this->getByID($id);
        }
        
        throw new Application_Exception(
            'Revision does not exist',
            sprintf(
                'Cannot find %s by revision [%s]: it cannot be found in the [%s] table. Campaign keys used: [%s]',
                $this->getRecordReadableNameSingular(),
                $revision,
                $this->revisionsTableName,
                JSONConverter::var2json($this->getCampaignKeys())
            ),
            RevisionableCollectionInterface::ERROR_REVISION_DOES_NOT_EXIST
        );
    }
    
   /**
    * Attempts to retrieve a revisionable instance by looking
    * for a request parameter named like the primary key of
    * the revisionable.
    *
    * @return RevisionableInterface|NULL
    */
    public function getByRequest() : ?RevisionableInterface
    {
        $id = (int)Application_Request::getInstance()->registerParam($this->getPrimaryKeyName())->setInteger()->get();
        if(!empty($id) && $this->idExists($id)) {
            return $this->getByID($id);
        }
        
        return null;
    }
    
   /**
    * Checks if the specified revision exists.
    *
    * @param integer $revision
    * @return integer|boolean The record ID if found, false otherwise
    */
    public function revisionExists(int $revision) : bool
    {
        // since we're tied to the campaign keys, we
        // need to ensure that we look for the revision
        // in the correct place.
        $where = $this->getCampaignKeys();
        $where[$this->revisionKeyName] = $revision;
        
        $id = DBHelper::fetchKeyInt(
            $this->primaryKeyName,
            sprintf(
                "SELECT
                    `%s`
                FROM
                    `%s`
                WHERE
                    %s",
                $this->primaryKeyName,
                $this->revisionsTableName,
                DBHelper::buildWhereFieldsStatement($where)
            ),
            $where
        );
        
        return $id > 0;
    }
    
    public function getCurrentRevision(int $revisionableID) : ?int
    {
        $params = $this->getCampaignKeys();
        $params[$this->primaryKeyName] = $revisionableID;
        
        $query = sprintf(
            "SELECT
                `current_revision`
            FROM
                `%s`
            WHERE
                %s",
            $this->getCurrentRevisionsTableName(),
            DBHelper::buildWhereFieldsStatement($params)
        );
        
        $entry = DBHelper::fetch($query, $params);
        
        if(isset($entry['current_revision'])) 
        {
            return (int)$entry['current_revision'];
        }
        
        return null;
    }

    /**
     * Attempts to find the most recent revision number for the
     * target revisionable ID that matches the given state.
     *
     * @param int $revisionableID
     * @param Application_StateHandler_State $state
     * @return int|null
     */
    public function getLatestRevisionByState(int $revisionableID, Application_StateHandler_State $state) : ?int
    {
        $revision = DBHelper::createFetchOne($this->getRevisionsTableName())
            ->selectColumn('MAX(`'.$this->getRevisionKeyName().'`) as `rev`')
            ->whereValue($this->getPrimaryKeyName(), $revisionableID)
            ->whereValue(self::COL_REV_STATE, $state->getName())
            ->whereValues($this->getCampaignKeys())
            ->fetch();

        if (isset($revision['rev'])) {
            return (int)$revision['rev'];
        }

        return null;
    }
    
   /**
    * Checks whether the specified column value exists in the
    * record's revisions table.
    *
    * @param string $key The column name
    * @param string $value The value to search for
    * @return integer|boolean The ID of the matching record, false otherwise
    */
    public function keyValueExists($key, $value)
    {
        $primaryKey = $this->primaryKeyName;
        $revisionsTable = $this->revisionsTableName;
        $currentRevsTable = $this->currentRevisionsTableName;
        $revisionKey = $this->revisionKeyName;
        
        $query =
        "SELECT
        revs.`$primaryKey`,
        revs.`$revisionKey`
        FROM
        `$revisionsTable` AS revs
        LEFT JOIN
        `$currentRevsTable` AS current
        ON
        revs.`$primaryKey` = current.`$primaryKey`
        WHERE
        revs.`$revisionKey` = current.current_revision";
        
        $where = $this->getCampaignKeys();
        $where[$key] = $value;
        
        $keys = array_keys($where);
        foreach($keys as $whereKey) {
            $query .= " AND revs.`$whereKey` = :$whereKey";
        }

        $record = DBHelper::fetch($query, $where);
        if(!empty($record)) {
            return $record[$primaryKey];
        }
        
        return false;
    }
    
   /**
    * @var array<string,string>
    */
    protected array $campaignKeys = array();
    
   /**
    * Sets the collection to use a campaign key: this is used as a namespace
    * for all record revisions to keep them separate, so revisions with
    * a different campaign key value can live in parallel.
    *
    * The campaign column must be present in the revisions table as well as
    * the current revisions table.
    *
    * NOTE: Meant to be handled via a constructor of the collection class.
    *
    * @param string $keyName
    * @param string $keyValue
    */
    protected function setCampaignKey(string $keyName, string $keyValue) : void
    {
        $this->campaignKeys[$keyName] = $keyValue;
    }
    
   /**
    * @return array<string,string>
    */
    public function getCampaignKeys() : array
    {
        return $this->campaignKeys;
    }

    /**
     * @inheritDoc
     *
     * @throws DBHelper_Exception
     * @throws JsonException
     * @throws ConvertHelper_Exception
     */
    public function setCurrentRevision(int $revisionableID, int $revision) : void
    {
        $this->log(sprintf(
            'Revisionable [%s] | Setting current revision to [%s].',
            $revisionableID,
            $revision
        ));
        
        $foreignKeys = $this->getCampaignKeys();
        
        $data = $foreignKeys;
        $data[$this->primaryKeyName] = $revisionableID;
        $data['current_revision'] = $revision;
        
        // Primary keys are the campaign keys + the revisionable ID.
        // Without campaign keys, it's just the revisionable ID.
        $primaries = array_keys($foreignKeys);
        $primaries[] = $this->primaryKeyName;
        
        DBHelper::insertOrUpdate(
            $this->currentRevisionsTableName,
            $data,
            $primaries
        );
    }
    
    public function getLogIdentifier() : string
    {
        $id = sprintf(
            '%s Collection | ',
            ucfirst($this->getRecordTypeName())
        );
        
        if(!empty($this->campaignKeys)) 
        {
            $id .= http_build_query($this->campaignKeys, '', '; ').' | ';
        }
        
        return $id;
    }
    
   /**
    * @param array<string,string|number> $params
    * @return string
    */
    protected function getAdminURL(array $params=array()) : string
    {
        $params = array_merge($params, $this->getAdminURLParams());
        return AppFactory::createRequest()->buildURL($params);
    }

    public function destroy(RevisionableInterface $revisionable) : void
    {
        if(!$this->canRecordBeDestroyed($revisionable))
        {
            throw new RevisionableException(
                'Cannot destroy record',
                sprintf(
                    'The %s [%s] cannot be destroyed.',
                    $this->getRecordReadableNameSingular(),
                    $revisionable->getIdentification()
                ),
                RevisionableCollectionInterface::ERROR_CANNOT_DESTROY_RECORD
            );
        }

        DBHelper::requireTransaction('Destroy a revisionable');
        
        AppFactory::createMessageLog()->addInfo(
            t(
                'Destroyed the %1$s %2$s.',
                $this->getRecordReadableNameSingular(),
                $revisionable->getLabel()
            ),
            t('Revisionables')
        );
        
        DBHelper::delete(
            sprintf(
                "DELETE FROM
                    `%s`
                WHERE
                    `%s`=:revisionable_id",
                $this->tableName,
                $this->primaryKeyName
            ),
            array(
                'revisionable_id' => $revisionable->getID()
            )
        );

        $revisionable->dispose();

        $this->unloadRecord($revisionable);
    }
    
   /**
    * Creates a datagrid multi action handler: these are used to handle common
    * revisionable tasks like publishing, deleting etc. via the multi-action
    * datagrid feature.
    *
    * @param class-string $className The name of the multi action class to use. Must extend the base class.
    * @param Application_Admin_Skeleton $adminScreen The administration screen in which the grid is used.
    * @param UI_DataGrid $grid The grid to apply the action to.
    * @param string $label The label of the item.
    * @param string $redirectURL The URL to redirect to when this action completes.
    * @param boolean $confirm Whether this is an action that displays a confirmation message.
    * @return Application_RevisionableCollection_DataGridMultiAction
    */
    public function createListMultiAction(string $className, Application_Admin_Skeleton $adminScreen, UI_DataGrid $grid, $label, $redirectURL, bool $confirm=false): Application_RevisionableCollection_DataGridMultiAction
    {
        $obj = new $className($this, $adminScreen, $grid, $label, $redirectURL, $confirm);
        
        if(!$obj instanceof Application_RevisionableCollection_DataGridMultiAction) 
        {
            throw new Application_Exception(
                'Invalid multi action class',
                sprintf(
                    'The object [%s] does not extend the [%s] class.',
                    get_class($obj),
                    'Application_RevisionableCollection_DataGridMultiAction'
                ),
                RevisionableCollectionInterface::ERROR_INVALID_MULTI_ACTION_CLASS
            );
        }
        
        return $obj;
    }
}
