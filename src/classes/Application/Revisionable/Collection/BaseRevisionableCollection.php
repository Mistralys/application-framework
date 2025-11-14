<?php

declare(strict_types=1);

namespace Application\Revisionable\Collection;

use Application;
use Application\AppFactory;
use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\Storage\BaseDBCollectionStorage;
use Application_Admin_Skeleton;
use Application_EventHandler_EventableListener;
use Application_StateHandler_State;
use Application\Disposables\DisposableTrait;
use Application_Traits_Eventable;
use Application_Traits_Loggable;
use Application_User;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper\JSONConverter;
use AppUtils\ConvertHelper\JSONConverter\JSONConverterException;
use AppUtils\ConvertHelper_Exception;
use AppUtils\Interfaces\StringableInterface;
use DateTime;
use DBHelper;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseCollection;
use DBHelper_Exception;
use JsonException;
use UI\AdminURLs\AdminURLInterface;
use UI_DataGrid;

abstract class BaseRevisionableCollection
    implements
    RevisionableCollectionInterface
{
    use Application_Traits_Loggable;
    use Application_Traits_Eventable;
    use DisposableTrait;
    use DBHelper\Traits\BeforeCreateEventTrait;
    use DBHelper\Traits\AfterRecordCreatedEventTrait;

    /**
     * This is called right after the collection's constructor:
     * it is used to process any custom arguments that may have
     * been specified in the {@link create()} method call.
     *
     * Use this to enforce and/or validate specific arguments the
     * collection implementation may require.
     *
     * @param array<int|mixed> $arguments
     */
    abstract protected function initCustomArguments(array $arguments = array()): void;

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
    protected function __construct(array $customArguments = array())
    {
        $this->recordTypeName = $this->getRecordTypeName();
        $this->tableName = $this->getRecordTableName();
        $this->revisionsTableName = $this->getRevisionsTableName();
        $this->currentRevisionsTableName = $this->getCurrentRevisionsTableName();
        $this->primaryKeyName = $this->getRecordPrimaryName();
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
     * @return RevisionableCollectionInterface
     * @throws BaseClassHelperException
     */
    public static function create(...$args): RevisionableCollectionInterface
    {
        $className = static::class;

        return ClassHelper::requireObjectInstanceOf(
            RevisionableCollectionInterface::class,
            new $className($args)
        );
    }

    /**
     * Initializer, called after the constructor to allow extended classes to do their stuff.
     */
    protected function init(): void
    {

    }

    public function getInstanceID(): string
    {
        return $this->instanceID;
    }

    /**
     * @return string[]
     */
    final public function getRecordSearchableKeys(): array
    {
        return array_keys($this->getRecordSearchableColumns());
    }

    /**
     * @return RevisionableFilterCriteriaInterface
     * @throws BaseClassHelperException
     */
    public function getFilterCriteria(): RevisionableFilterCriteriaInterface
    {
        $class = $this->getRecordFiltersClassName();

        return ClassHelper::requireObjectInstanceOf(
            RevisionableFilterCriteriaInterface::class,
            new $class($this)
        );
    }

    public function getFilterSettings(): RevisionableFilterSettingsInterface
    {
        $class = $this->getRecordFilterSettingsClassName();
        return new $class($this);
    }

    public function createStubRecord(): RevisionableInterface
    {
        return $this->getByID(RevisionableCollectionInterface::STUB_OBJECT_ID);
    }

    /**
     * Creates a new revisionable record in the collection.
     *
     * @param string $label
     * @param Application_User|NULL $author If empty, the current user is used.
     * @param array<string,mixed> $data
     * @return RevisionableInterface
     */
    public function createNewRevisionable(string $label, ?Application_User $author = null, array $data = array()): RevisionableInterface
    {
        DBHelper::requireTransaction(sprintf('Create a new %s record.', $this->getRecordReadableNameSingular()));

        $this->log(sprintf('Creating new record | [%s]', $label));

        $this->log('Creating a new record.');

        $this->handleOnBeforeCreateRecord($data);

        // first off, we need an ID.
        $revisionable_id = (int)DBHelper::insert(sprintf(
            "INSERT INTO
                `%s`
            SET `%s` = DEFAULT",
            $this->tableName,
            $this->primaryKeyName
        ));

        if (!$author) {
            $author = Application::getUser();
        }

        /* @var $storage BaseDBCollectionStorage */

        $this->log(sprintf('Creating new record | Inserted with ID [%s].', $revisionable_id));

        $dummy = $this->createStubRecord();
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

        $revisionable = $this->getByID($revisionable_id);

        $this->handleAfterRecordCreated($revisionable, false, array());

        return $revisionable;
    }

    /**
     * @return Application_StateHandler_State
     */
    final public function getInitialState(): Application_StateHandler_State
    {
        return $this->createStubRecord()->getInitialState();
    }

    final public function idExists(int $record_id): bool
    {
        return $this->getCurrentRevision($record_id) !== null;
    }

    /**
     * @return RevisionableInterface[]
     * @throws BaseClassHelperException
     */
    public function getAll(): array
    {
        return $this->getFilterCriteria()->getItemsObjects();
    }

    /**
     * @var array<int,RevisionableInterface>|NULL
     */
    private ?array $cachedItems = null;

    public function getByID($record_id): RevisionableInterface
    {
        $record_id = (int)$record_id;

        if (!isset($this->cachedItems)) {
            $this->cachedItems = array();
        }

        if (!isset($this->cachedItems[$record_id])) {
            $this->cachedItems[$record_id] = $this->createRecordInstance($record_id);
        }

        return $this->cachedItems[$record_id];
    }

    /**
     * @inheritDoc
     * @return $this
     */
    final public function unloadRecord(RevisionableInterface $revisionable): self
    {
        $record_id = $revisionable->getID();

        if (isset($this->cachedItems[$record_id])) {
            unset($this->cachedItems[$record_id]);
        }

        $revisionable->dispose();

        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    final public function resetCollection(): self
    {
        if (isset($this->cachedItems)) {
            foreach ($this->cachedItems as $revisionable) {
                $this->unloadRecord($revisionable);
            }
        }

        return $this;
    }

    protected function createRecordInstance(int $record_id): RevisionableInterface
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
     * @return RevisionableInterface
     *
     * @throws JSONConverterException
     * @throws RevisionableException
     */
    public function getByRevision(int $revision): RevisionableInterface
    {
        $id = $this->getIDByRevision($revision);

        if ($id !== null) {
            return $this->getByID($id);
        }

        throw new RevisionableException(
            'Revision does not exist',
            sprintf(
                'Cannot find %s by revision [%s]: it cannot be found in the [%s] table. Campaign keys used: [%s]',
                $this->getRecordReadableNameSingular(),
                $revision,
                $this->revisionsTableName,
                JSONConverter::var2json($this->getCampaignKeys())
            ),
            RevisionableException::ERROR_REVISION_DOES_NOT_EXIST
        );
    }

    private bool $paramRegistered = false;

    /**
     * Attempts to retrieve a revisionable instance by looking
     * for a request parameter named like the primary key of
     * the revisionable.
     *
     * @return RevisionableInterface|NULL
     *
     * @see self::getRecordRequestPrimaryName()
     */
    public function getByRequest(): ?RevisionableInterface
    {
        $request = AppFactory::createRequest();

        if ($this->paramRegistered === false) {
            $this->paramRegistered = true;
            $request->registerParam($this->getRecordRequestPrimaryName())->setInteger();
            $request->registerParam($this->getRecordPrimaryName())->setInteger();
        }

        $id = (int)$request->getParam($this->getRecordRequestPrimaryName());
        if (!empty($id) && $this->idExists($id)) {
            return $this->getByID($id);
        }

        $id = (int)$request->getParam($this->getRecordPrimaryName());
        if (!empty($id) && $this->idExists($id)) {
            return $this->getByID($id);
        }

        return null;
    }

    final public function getIDByRevision(int $revision): ?int
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

        if ($id > 0) {
            return $id;
        }

        return null;
    }

    /**
     * Checks if the specified revision exists.
     *
     * @param integer $revision
     * @return boolean
     */
    final public function revisionExists(int $revision): bool
    {
        return $this->getIDByRevision($revision) !== null;
    }

    final public function getCurrentRevision(int $revisionableID): ?int
    {
        $params = $this->getCampaignKeys();
        $params[$this->primaryKeyName] = $revisionableID;

        $query = sprintf(
            "SELECT
                `%s`
            FROM
                `%s`
            WHERE
                %s",
            RevisionableCollectionInterface::COL_CURRENT_REVISION,
            $this->getCurrentRevisionsTableName(),
            DBHelper::buildWhereFieldsStatement($params)
        );

        $entry = DBHelper::fetch($query, $params);

        if (isset($entry[RevisionableCollectionInterface::COL_CURRENT_REVISION])) {
            return (int)$entry[RevisionableCollectionInterface::COL_CURRENT_REVISION];
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
    final public function getLatestRevisionByState(int $revisionableID, Application_StateHandler_State $state): ?int
    {
        $revision = DBHelper::createFetchOne($this->getRevisionsTableName())
            ->selectColumn('MAX(`' . $this->getRevisionKeyName() . '`) as `rev`')
            ->whereValue($this->getRecordPrimaryName(), $revisionableID)
            ->whereValue(RevisionableCollectionInterface::COL_REV_STATE, $state->getName())
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
     * @return integer|null The ID of the matching record, null otherwise
     */
    final public function findIDByKey(string $key, string $value) : ?int
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
        revs.`$revisionKey` = current." . RevisionableCollectionInterface::COL_CURRENT_REVISION;

        $where = $this->getCampaignKeys();
        $where[$key] = $value;

        $keys = array_keys($where);
        foreach ($keys as $whereKey) {
            $query .= " AND revs.`$whereKey` = :$whereKey";
        }

        $record = DBHelper::fetch($query, $where);
        if (!empty($record)) {
            return $record[$primaryKey];
        }

        return null;
    }

    /**
     * @var array<string,string>
     */
    private array $campaignKeys = array();

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
    final protected function setCampaignKey(string $keyName, string $keyValue): void
    {
        $this->campaignKeys[$keyName] = $keyValue;
    }

    /**
     * @return array<string,string>
     */
    final public function getCampaignKeys(): array
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
    final public function setCurrentRevision(int $revisionableID, int $revision): void
    {
        $this->log(sprintf(
            'Revisionable [%s] | Setting current revision to [%s].',
            $revisionableID,
            $revision
        ));

        $foreignKeys = $this->getCampaignKeys();

        $data = $foreignKeys;
        $data[$this->primaryKeyName] = $revisionableID;
        $data[RevisionableCollectionInterface::COL_CURRENT_REVISION] = $revision;

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

    protected function _getIdentification(): string
    {
        return sprintf(
            '%s Collection | ',
            $this->getRecordTypeName()
        );
    }

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    protected function getAdminURL(array $params = array()): string
    {
        $params = array_merge($params, $this->getAdminURLParams());
        return AppFactory::createRequest()->buildURL($params);
    }

    final public function destroy(RevisionableInterface $revisionable): void
    {
        if (!$this->canRecordBeDestroyed($revisionable)) {
            throw new RevisionableException(
                'Cannot destroy record',
                sprintf(
                    'The %s [%s] cannot be destroyed.',
                    $this->getRecordReadableNameSingular(),
                    $revisionable->getIdentification()
                ),
                RevisionableException::ERROR_CANNOT_DESTROY_RECORD
            );
        }

        DBHelper::requireTransaction('Destroy a revisionable');

        AppFactory::createMessageLog()->addInfo(
            t(
                'Destroyed the %1$s %2$s (%3$s).',
                $this->getRecordReadableNameSingular(),
                $revisionable->getLabel(),
                t('Identification:', $revisionable->getIdentification())
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
     * @param class-string<BaseRevisionableDataGridMultiAction> $className The name of the multi action class to use. Must extend the base class.
     * @param Application_Admin_Skeleton $adminScreen The administration screen in which the grid is used.
     * @param UI_DataGrid $grid The grid to apply the action to.
     * @param string|int|float|StringableInterface|null $label The label of the item.
     * @param string|AdminURLInterface $redirectURL The URL to redirect to when this action completes.
     * @param boolean $confirm Whether this is an action that displays a confirmation message.
     * @return BaseRevisionableDataGridMultiAction
     */
    public function createListMultiAction(string $className, Application_Admin_Skeleton $adminScreen, UI_DataGrid $grid, string|int|float|StringableInterface|null $label, string|AdminURLInterface $redirectURL, bool $confirm = false): BaseRevisionableDataGridMultiAction
    {
        $obj = new $className($this, $adminScreen, $grid, $label, $redirectURL, $confirm);

        if (!$obj instanceof BaseRevisionableDataGridMultiAction) {
            throw new RevisionableException(
                'Invalid multi action class',
                sprintf(
                    'The object [%s] does not extend the [%s] class.',
                    get_class($obj),
                    BaseRevisionableDataGridMultiAction::class
                ),
                RevisionableException::ERROR_INVALID_MULTI_ACTION_CLASS
            );
        }

        return $obj;
    }

    // region: DBHelper methods

    final public function getRecordSearchableLabels(): array
    {
        return array_keys($this->getRecordSearchableColumns());
    }

    public function getRecordDefaultSortKey(): string
    {
        return RevisionableCollectionInterface::COL_REV_LABEL;
    }

    public function registerRequestParams(): void
    {

    }

    /**
     * @inheritDoc
     * @param string $key
     * @param string $value
     * @return RevisionableInterface|null
     */
    final public function getByKey(string $key, string $value): ?RevisionableInterface
    {
        $id = $this->findIDByKey($key, $value);
        if ($id !== null) {
            return $this->getByID($id);
        }

        return null;
    }

    public function getRecordLabel(): string
    {
        return $this->getRecordReadableNameSingular();
    }

    public function getCollectionLabel() : string
    {
        return $this->getRecordReadableNamePlural();
    }

    final public function countRecords(): int
    {
        return $this->getFilterCriteria()->countItems();
    }

    public function getForeignKeys(): array
    {
        // Typically revisionables do not have foreign keys.
        return array();
    }

    public function createNewRecord(array $data = array(), bool $silent = false, array $options = array()): RevisionableInterface
    {
        if(!isset($data[RevisionableCollectionInterface::COL_REV_LABEL])) {
            throw new RevisionableException(
                'Revision label is required to create a new revisionable record.',
                sprintf(
                    'When creating a new revisionable record using [%s()], the label must be specified in the data array using the key [%s].',
                    __METHOD__,
                    RevisionableCollectionInterface::COL_REV_LABEL
                ),
                RevisionableException::ERROR_INVALID_CREATE_ARGUMENTS
            );
        }

        unset($data[RevisionableCollectionInterface::COL_REV_LABEL]);

        $author = null;
        if(isset($data[RevisionableCollectionInterface::COL_REV_AUTHOR])) {
            $author = Application::createUser((int)$data[RevisionableCollectionInterface::COL_REV_AUTHOR]);
            unset($data[RevisionableCollectionInterface::COL_REV_AUTHOR]);
        }

        return $this->createNewRevisionable(
            $data[RevisionableCollectionInterface::COL_REV_LABEL],
            $author,
            $data
        );
    }

    final public function hasRecordIDTable(): bool
    {
        return false;
    }

    final public function onBeforeCreateRecord(callable $callback): Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            DBHelperCollectionInterface::EVENT_BEFORE_CREATE_RECORD,
            $callback
        );
    }

    final public function onAfterCreateRecord(callable $callback): Application_EventHandler_EventableListener
    {
        return $this->addEventListener(
            DBHelperCollectionInterface::EVENT_AFTER_CREATE_RECORD,
            $callback
        );
    }

    /**
     * NOT AVAILABLE FOR REVISIONABLES, will throw an exception.
     * @inheritDoc
     * @throws RevisionableException {@see RevisionableException::ERROR_CANNOT_DELETE_RECORD_DIRECTLY}
     */
    final public function onAfterDeleteRecord(callable $callback): Application_EventHandler_EventableListener
    {
        throw new RevisionableException(
            'Revisionables cannot be deleted directly',
            'Revisionable records must be deleted by setting an equivalent state.',
            RevisionableException::ERROR_CANNOT_DELETE_RECORD_DIRECTLY
        );
    }

    /**
     * @inheritDoc
     * @throws RevisionableException {@see RevisionableException::ERROR_CANNOT_DELETE_RECORD_DIRECTLY}
     */
    final public function deleteRecord(DBHelperRecordInterface $record, bool $silent = false): void
    {
        throw new RevisionableException(
            'Revisionables cannot be deleted directly',
            'Revisionable records must be deleted by setting an equivalent state.',
            RevisionableException::ERROR_CANNOT_DELETE_RECORD_DIRECTLY
        );
    }

    final public function isRecordLoaded(int $recordID): bool
    {
        return isset($this->cachedItems[$recordID]);
    }

    public function getRecordDefaultSortDir(): string
    {
        return DBHelperCollectionInterface::SORT_DIR_ASC;
    }

    final public function setupComplete(): void
    {
        // Nothing to do here.
    }

    /**
     * Revisionables do not have parent records.
     * @return null
     */
    final public function getParentRecord(): null
    {
        return null;
    }

    final public function getDataGridName(): string
    {
        return $this->getRecordTypeName() . '-grid';
    }

    final public function refreshRecordsData(): void
    {
        foreach($this->cachedItems as $item) {
            $item->refreshData();
        }
    }

    // endregion
}
