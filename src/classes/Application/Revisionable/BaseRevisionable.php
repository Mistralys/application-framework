<?php
/**
 * @package Application
 * @subpackage Revisionable
 */

declare(strict_types=1);

use Application\Disposables\Attributes\DisposedAware;
use Application\Disposables\DisposableDisposedException;
use Application\Disposables\DisposableTrait;
use Application\Revisionable\Changelog\BaseRevisionableChangelogHandler;
use Application\Revisionable\Changelog\RevisionableChangelogHandlerInterface;
use Application\Revisionable\Changelog\RevisionableChangelogTrait;
use Application\Revisionable\Collection\BaseRevisionableCollection;
use Application\Revisionable\Collection\RevisionableCollectionInterface;
use Application\Revisionable\Event\BeforeSaveEvent;
use Application\Revisionable\Event\RevisionAddedEvent;
use Application\Revisionable\Event\TransactionEndedEvent;
use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\Storage\BaseDBCollectionStorage;
use Application\Revisionable\Storage\BaseRevisionStorage;
use Application\Revisionable\Storage\Event\Application_RevisionStorage_Event_RevisionAdded;
use Application\Revisionable\Storage\Event\RevisionSelectedEvent;
use Application\Revisionable\Storage\RevisionableStorageException;
use Application\Revisionable\Storage\RevisionStorageException;
use Application\Revisionable\Storage\StubDBRevisionStorage;
use Application\Revisionable\TransactionInfo;
use Application\StateHandler\StateHandlerException;
use Application\Traits\RevisionDependentTrait;
use AppUtils\BaseException;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use DBHelper\Traits\RecordKeyHandlersTrait;

/**
 * Base class for data types that are revisionable and have states.
 * Provides a skeleton and common functionality for all revisionable
 * items, along with their states.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseRevisionable implements RevisionableInterface
{
    use Application_Traits_LockableWithManager;
    use DisposableTrait;
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;
    use Application_Traits_Simulatable;
    use RevisionableChangelogTrait;
    use RevisionDependentTrait;
    use RecordKeyHandlersTrait;

    public const int ERROR_NO_CURRENT_REVISION_FOUND = 14701;
    public const int ERROR_LAST_TRANSACTION_NOT_AVAILABLE = 14702;

    protected BaseDBCollectionStorage $revisions;
    protected bool $requiresNewRevision = false;
    protected ?int $transactionSourceRevision = null;
    protected ?int $transactionTargetRevision = null;
    protected static int $instanceCounter = 0;
    protected string $instanceID;
    protected bool $initialized = false;
    private ?int $selectedRevision = null;
    protected Application_StateHandler $stateHandler;

    protected RevisionableCollectionInterface $collection;
    protected int $id;

    public function __construct(RevisionableCollectionInterface $collection, int $id)
    {
        $this->collection = $collection;
        $this->id = $id;

        $this->revisions = $this->createRevisionStorage();

        self::$instanceCounter++;
        $this->instanceID = 'RV'.self::$instanceCounter;

        $this->initRevisionEvents();
        $this->initInternalStorageParts();
        $this->initStorageParts();

        $this->init();
        $this->initialized = true;

        $this->initStateHandler();
        $this->selectCurrentRevision();
    }

    /**
     * Selects the revisionable's current revision.
     * @return $this
     *
     * @throws DisposableDisposedException
     * @throws RevisionableException
     * @throws RevisionStorageException
     */
    public function selectCurrentRevision(): self
    {
        $current = $this->getCurrentRevision();

        if ($current !== null) {
            return $this->selectRevision($current);
        }

        throw new RevisionableException(
            'Error selecting current revision',
            sprintf(
                'Could not load %s [%s] from database, no current revision found.',
                $this->getRecordTypeName(),
                $this->id
            ),
            self::ERROR_NO_CURRENT_REVISION_FOUND
        );
    }

    /**
     * Retrieves the revisionable's collection instance.
     * @return RevisionableCollectionInterface
     */
    public function getCollection(): RevisionableCollectionInterface
    {
        return $this->collection;
    }

    /**
     * @throws RevisionableException
     * @see BaseDBCollectionStorage
     */
    protected function createRevisionStorage(): BaseDBCollectionStorage
    {
        if ($this->isStub()) {
            return new StubDBRevisionStorage($this);
        }

        try {
            $className = $this->collection->getRevisionsStorageClass();

            return ClassHelper::requireObjectInstanceOf(
                BaseDBCollectionStorage::class,
                new $className($this)
            );
        } catch (BaseClassHelperException $e) {
            throw new RevisionableException(
                'Invalid revision storage',
                sprintf(
                    'The revision storage for [%s] must extend the base [%s] class.',
                    get_class($this),
                    BaseDBCollectionStorage::class
                ),
                RevisionableInterface::ERROR_INVALID_REVISION_STORAGE,
                $e
            );
        }
    }

    final public function getID(): int
    {
        return $this->id;
    }

    /**
     * The item's custom save implementation that is called when the item
     * has changes that also changed the item's state, which usually means
     * the new revision has to be added permanently.
     *
     * @see self::save()
     */
    protected function _saveWithStateChange(): void
    {
        $this->revisions->writeRevisionKeys(array(
            RevisionableCollectionInterface::COL_REV_STATE => $this->getStateName()
        ));
    }

    protected function _saveWithoutStateChange(): void
    {

    }

    /**
     * Retrieves the base URL parameters collection used to
     * administrate this revisionable. Presupposes that an
     * administration interface exists for it.
     *
     * @return array
     */
    public function getAdminURLParams(): array
    {
        $params = $this->collection->getAdminURLParams();
        $params[$this->collection->getRecordPrimaryName()] = $this->getID();
        return $params;
    }

    protected function getAdminURL(array $params = array()): string
    {
        $params = array_merge($params, $this->getAdminURLParams());
        return Application_Driver::getInstance()->getRequest()->buildURL($params);
    }

    protected bool $handleDBTransaction = false;

    /**
     * Rolls back any new revision added by a transaction. Has no
     * effect if the transaction did not add a new revision.
     *
     * @return $this
     * @throws Application_Exception
     * @throws DisposableDisposedException
     * @throws DBHelper_Exception
     */
    #[DisposedAware]
    public function rollBackTransaction(): self
    {
        $this->requireNotDisposed('Rolling back transaction');

        if (!$this->transactionActive) {
            return $this;
        }

        $this->log('Transaction | ROLLBACK | Rolling back the transaction.');

        $this->revisions->removeRevision((int)$this->transactionTargetRevision);
        $this->revisions->selectRevision((int)$this->transactionSourceRevision);

        $this->lastTransaction = new TransactionInfo(
            $this,
            TransactionInfo::TRANSACTION_ROLLED_BACK,
            $this->isSimulationEnabled(),
            (int)$this->transactionSourceRevision,
            null
        );

        $this->resetTransactionData();

        $this->triggerTransactionEnded($this->lastTransaction);

        if ($this->handleDBTransaction) {
            DBHelper::rollbackTransaction();
        }

        return $this;
    }

    public function getChangelogTable(): string
    {
        return $this->collection->getRecordChangelogTableName();
    }

    public function getChangelogItemPrimary(): array
    {
        return array(
            $this->collection->getRecordPrimaryName() => $this->getID()
        );
    }

    public function getChangelogItemInsertColumns(): array
    {
        return array(
            $this->collection->getRecordPrimaryName() => $this->getID(),
            $this->collection->getRevisionKeyName() => $this->getRevision()
        );
    }

    final public function selectLastRevisionByState(Application_StateHandler_State $state): int|false
    {
        $revision = $this->getLastRevisionByState($state);
        if ($revision) {
            $this->selectRevision($revision);
            return $revision;
        }

        return false;
    }

    final public function getLastRevisionByState(Application_StateHandler_State $state): int|false
    {
        $revisionKey = $this->collection->getRevisionKeyName();
        $primaryKey = $this->collection->getRecordPrimaryName();

        $where = $this->collection->getCampaignKeys();
        $where[$primaryKey] = $this->getID();
        $where[RevisionableCollectionInterface::COL_REV_STATE] = $state->getName();

        $query = sprintf(
            "SELECT
                `%s`
            FROM
                `%s`
            WHERE
                %s
            ORDER BY
                `date` DESC
            LIMIT 0,1",
            $revisionKey,
            $this->collection->getRevisionsTableName(),
            DBHelper::buildWhereFieldsStatement($where)
        );

        $revision = DBHelper::fetchKeyInt($revisionKey, $query, $where);

        if (!empty($revision)) {
            return $revision;
        }

        return false;
    }

    final public function getCurrentRevision(): ?int
    {
        if ($this->isStub()) {
            return StubDBRevisionStorage::STUB_REVISION_NUMBER;
        }

        return $this->collection->getCurrentRevision($this->getID());
    }

    public function getPrettyRevision(): int
    {
        return (int)$this->revisions->getKey(RevisionableCollectionInterface::COL_REV_PRETTY_REVISION);
    }

    public function getLabel(): string
    {
        return (string)$this->getRevisionKey(RevisionableCollectionInterface::COL_REV_LABEL);
    }

    public function setLabel(string $label): self
    {
        $this->setCustomKey(
            RevisionableCollectionInterface::COL_REV_LABEL,
            $label,
            false,
            RevisionableChangelogHandlerInterface::CHANGELOG_SET_LABEL
        );

        return $this;
    }

    /**
     * Retrieves the item's state handler.
     * @return Application_StateHandler
     * @throws DisposableDisposedException
     */
    public function getStateHandler() : Application_StateHandler
    {
        $this->requireNotDisposed();

        return $this->stateHandler;
    }

   /**
    * @var Application_StateHandler[]
    */
    protected static array $masterStateHandlers = array();
    
    /**
     * Initializes the state handler by retrieving the item-specific
     * state definitions, and configuring the state handler with this
     * information.
     */
    protected function initStateHandler() : void
    {
        $class = get_class($this);
        
        // to avoid having to go through the setup of the state definitions
        // for each single revisionable of the same class, we clone the
        // existing state handler and replace the revisionable instance 
        // with this one.
        if(isset(self::$masterStateHandlers[$class])) {
            $handler = clone self::$masterStateHandlers[$class];
            $handler->setRevisionable($this);
            $this->stateHandler = $handler;
            return;
        }

        $this->stateHandler = new Application_StateHandler($this);
        
        self::$masterStateHandlers[$class] = $this->stateHandler;
        
        $defs = $this->buildStateDefs();

        $states = array_keys($defs);
        $total = count($states);

        /* @var $collection array<string,Application_StateHandler_State> */
        $collection = array(); // keep the collection locally for performance reasons
        for($i=0; $i<$total; $i++) {
            $stateName = $states[$i];
            $def = $defs[$stateName];
            
            if(!isset($def['initial'])) {
                $def['initial'] = false;
            }
            
            if(!isset($def['increasesPrettyRevision'])) {
                $def['increasesPrettyRevision'] = false;
            }
            
            $collection[$stateName] = $this->stateHandler->registerState(
                $stateName,
                $def['label'],
                $def['uiType'],
                $def['changesAllowed'],
                $def['initial']
            );
        }

        // now that all states are known, add the dependencies
        for($i=0; $i<$total; $i++) {
            $stateName = $states[$i];
            $state = $collection[$stateName];
            $dependencies = $defs[$stateName]['dependencies'];
            foreach ($dependencies as $dependentName) {
                $state->addDependency($collection[$dependentName]);
            }
        }

        // and now we can manage additional settings
        for($i=0; $i<$total; $i++) {
            $stateName = $states[$i];
            $def = $defs[$stateName];
            $state = $collection[$stateName];
            if (isset($def['timedChangeTo'])) {
                $state->setTimedChange(
                    $collection[$def['timedChangeTo']],
                    $def['timerDelay']
                );
            }

            if (isset($def['onStructuralChange'])) {
                $changeTo = $collection[$def['onStructuralChange']];
                $state->setOnStructuralChange($changeTo);
            }
        }
    }

    /**
     * Retrieves the label for the current state the object is in,
     * or null if no state has been set yet.
     * @return string|NULL
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function getCurrentStateLabel() : ?string
    {
        return $this->getState()?->getLabel();
    }

    /**
     * @return string|null
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function getCurrentPrettyStateLabel() : ?string
    {
        return $this->getState()?->getPrettyLabel();
    }

    /**
     * Returns the human-readable label for the specified state,
     * in the current application locale. If the state name is not
     * specified, the current item's state will be used.
     *
     * @param string|NULL $stateName If no state name is specified, uses the current state.
     * @return string
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function getStateLabel(?string $stateName = null) : string
    {
        $this->requireNotDisposed();

        if (empty($stateName)) {
            $stateName = $this->getStateName();
        }

        return $this->stateHandler->getStateByName($stateName)->getLabel();
    }

    /**
     * Retrieves the pretty human-readable state label.
     * Contains HTML.
     *
     * @param string|NULL $stateName If no state name is specified, uses the current state.
     * @return string
     * @throws RevisionableException
     */
    public function getPrettyStateLabel(?string $stateName = null) : string
    {
        if (empty($stateName)) {
            $stateName = $this->getStateName();
        }

        return $this->stateHandler->getStateByName($stateName)->getPrettyLabel();
    }

    /**
     * The state according to the current revision. Note that
     * this can be null if it has not been set.
     *
     * @return Application_StateHandler_State|NULL
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function getState() : ?Application_StateHandler_State
    {
        $this->requireNotDisposed();

        $state = $this->revisions->getKey(RevisionableCollectionInterface::COL_REV_STATE);

        if($state instanceof Application_StateHandler_State) {
            return $state;
        }

        return null;
    }

    /**
     * Like {@see self::getState()}, but the method does not
     * return <code>NULL</code>. An exception is thrown instead
     * if no state is available.
     *
     * @return Application_StateHandler_State
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function requireState() : Application_StateHandler_State
    {
        $state = $this->getState();

        if($state !== null) {
            return $state;
        }

        throw new RevisionableException(
            'No revisionable state available',
            sprintf(
                'No state available in the revisionable [%s].',
                $this->getIdentification()
            ),
            RevisionableInterface::ERROR_NO_STATE_AVAILABLE
        );
    }

    /**
     * Retrieves the name of the current state.
     *
     * @return string
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function getStateName() : string
    {
        return $this->requireState()->getName();
    }

    /**
     * A list of all available states for the item, as an indexed
     * array containing state objects.
     *
     * @return Application_StateHandler_State[]
     * @throws DisposableDisposedException
     */
    public function getStates() : array
    {
        $this->requireNotDisposed();

        return $this->stateHandler->getStates();
    }

    protected bool $stateChanged = false;

    /**
     * Sets the state of the item, making sure the new
     * state is allowed to be set according to the
     * dependencies of the current state.
     *
     * @param Application_StateHandler_State $newState
     * @return $this
     * @throws RevisionableException
     * @throws StateHandlerException
     * @throws DisposableDisposedException
     */
    public function setState(Application_StateHandler_State $newState) : self
    {
        $this->requireNotDisposed();
        $this->requireTransaction();

        if($newState->getName() === $this->getStateName()) {
            return $this;
        }

        $state = $this->getState();
        if (!is_null($state) && !$state->hasDependency($newState)) {
            throw new StateHandlerException(
                'Invalid state change.',
                sprintf(
                'Cannot set state to [%s], it is not allowed after the current [%s] state.',
                    $newState,
                    $state
                ),
                RevisionableInterface::ERROR_INVALID_STATE_CHANGE
            );
        }

        $this->log('Setting state to [%1$s].', $newState->getName());

        $this->revisions->setKey(RevisionableCollectionInterface::COL_REV_STATE, $newState);
        
        $this->structureChanged('State has changed');
        $this->stateChanged = true;

        $this->triggerEvent('StateChanged', array($newState));
        
        $this->log('State changed successfully.');

        $this->enqueueChangelog(
            RevisionableChangelogHandlerInterface::CHANGELOG_SET_STATE,
            BaseRevisionableChangelogHandler::resolveSetStateData($state, $newState)
        );

        return $this;
    }
    
   /**
    * Collection of validation messages when validating
    * a state change.
    *  
    * @var string[]
    * @see validateStateChange()
    */
    protected array $stateValidationMessages = array();
    
   /**
    * Checks if the revisionable can be safely changed to
    * the specified state.
    * 
    * @param Application_StateHandler_State $state
    * @return boolean
    * @see getStateChangeMessages()
    * @throws DisposableDisposedException
    */
    public function validateStateChange(Application_StateHandler_State $state) : bool
    {
        $this->requireNotDisposed();

        $this->stateValidationMessages = array();
        
        $method = '_validateStateChange_'.$state->getName();
        if(method_exists($this, $method)) {
            $this->$method();
        }
        
        if(empty($this->stateValidationMessages)) {
            return true;
        }
        
        return false;
    }

    /**
     * @param string $message
     * @return $this
     */
    protected function addValidateStateMessage(string $message) : self
    {
        $this->stateValidationMessages[] = $message;
        return $this;
    }
    
   /**
    * Retrieves all messages added during the last
    * call to the {@link validateStateChange()} method.
    * @return string[]
    * @throws DisposableDisposedException
    */
    public function getStateChangeMessages() : array
    {
        $this->requireNotDisposed();

        return $this->stateValidationMessages;
    }

    /**
     * Implement this in your class, and return an array that
     * looks like this:
     *
     * <pre>
     * array(
     *     'state_name_1' => array(
     *         'label' => 'State number one',
     *         'dependencies' => array(
     *             'state_name_2',
     *             'state_name_6',
     *             [...]
     *         )
     *     ),
     *     [...]
     * )
     * </pre>
     *
     * Each entry defines the name of the state as well as
     * a human-readable label and a list of dependencies.
     * The dependencies determine which states can be set
     * after the state.
     *
     * @return array
     */
    abstract protected function buildStateDefs() : array;

    /**
     * Checks whether the currently selected state has the
     * specified state name/object as dependency.
     *
     * @param string|Application_StateHandler_State $state_object_or_name
     * @return boolean
     * @throws DisposableDisposedException
     */
    public function stateHasDependency($state_object_or_name) : bool
    {
        $this->requireNotDisposed();

        $state = $this->stateHandler->getStateByName($state_object_or_name);

        return $this->requireState()->hasDependency($state);
    }

    /**
     * Retrieves the specified state object by its name.
     *
     * @param string|Application_StateHandler_State $nameOrInstance
     * @return Application_StateHandler_State
     * @throws DisposableDisposedException
     */
    public function getStateByName($nameOrInstance) : Application_StateHandler_State
    {
        $this->requireNotDisposed();

        return $this->stateHandler->getStateByName($nameOrInstance);
    }

    /**
     * Checks whether the object is in the specified state.
     * @param string|Application_StateHandler_State $nameOrInstance
     * @return boolean
     * @throws DisposableDisposedException
     */
    public function isState($nameOrInstance) : bool
    {
        $this->requireNotDisposed();

        if ($nameOrInstance instanceof Application_StateHandler_State) {
            $stateName = $nameOrInstance->getName();
        } else {
            $stateName = $nameOrInstance;
        }

        return $this->getStateName() === $stateName;
    }

    /**
     * @var boolean
     */
    protected bool $structuralChanges = false;

    /**
     * Resets the tracking of structural changes before a
     * transaction.
     */
    private function resetStructuralChanges() : void
    {
        $this->structuralChanges = false;
    }

    /**
     * Resets the internal changes tracking, for example, after
     * a save operation.
     *
     * @see self::hasChanges()
     * @see self::changesMade()
     */
    protected function resetChanges(): void
    {
        $this->log('Resetting all internal changes.');

        $this->changedParts = array();

        $this->resetStructuralChanges();
    }

    /**
     * Sets that structural changes have been made, which
     * will require a change of state.
     *
     * @param string $reason
     * @return $this
     */
    protected function structureChanged(string $reason) : self
    {
        if ($this->hasStructuralChanges() === true) {
            return $this;
        }

        if ($this->requiresNewRevision === false) {
            $this->changesMade('Structural change: ['.$reason.'].');
        }

        $this->structuralChanges = true;

        $this->log('Transaction | Structural change: [%s]', $reason);

        return $this;
    }

    /**
     * Checks if the item has any changes that modify its structure,
     * which would mean that its state has to change.
     *
     * @return boolean
     */
    public function hasStructuralChanges() : bool
    {
        return $this->structuralChanges;
    }

    /**
     * Sets that the specified part of the revisionable item has
     * been modified, to keep track of granular changes. Sets the
     * global change flag as well.
     *
     * Example:
     *
     * <pre>
     * setPartChanged('properties');
     * </pre>
     *
     * Then, when saving, it is possible to check whether properties
     * have been modified:
     *
     * <pre>
     * if($this->hasPartChanged('properties')) {
     *     // save properties
     * }
     * </pre>
     *
     * <b>WARNING:</b> The revisionable has to implement the _savePart_xxxx
     * method if you use this (where xxxx is the part name), to save
     * the data related to the part when the save() method is called.
     *
     * @param string $part
     * @param boolean $structural
     * @return BaseRevisionable
     * @throws Application_Exception
     */
    protected function setPartChanged(string $part, bool $structural=false) : self
    {
        $this->requirePartExists($part);

        $this->requireTransaction(sprintf(
            '%s [%s v%s] instance [%s]: Tried to set part [%s] as modified without starting a transaction.',
            get_class($this),
            $this->getID(),
            $this->getRevision(),
            $this->getInstanceID(),
            $part
        ));

        if($this->isPartChanged($part)) {
            return $this;
        }

        $this->changesMade('Part ['.$part.'] modified.');

        $this->changedParts[$part] = true;

        if($structural) {
            $this->structureChanged(sprintf('Structural part [%s] changed.', $part));
        } else {
            $this->log('Transaction | Part [%s] has changed.', $part);
        }

        return $this;
    }

    protected function _save(): void
    {
        $state = $this->requireState();

        // automatically change the state if any structural changes were made
        if ($this->hasStructuralChanges())
        {
            $newState = $state->getStructuralChangeState();

            if (!$this->stateChanged && $newState !== null) {
                $this->log(sprintf('Saving | Structural changes detected, automatically changing the state to [%s].', $newState->getName()));
                $this->setState($newState);
            }
        }

        if ($this->stateChanged) {
            $this->log('Saving | Calling the item\'s save implementation (with state change).');
            $this->_saveWithStateChange();
        } else {
            $this->log('Saving | Calling the item\'s save implementation (without state change).');
            $this->_saveWithoutStateChange();
        }
    }

    /**
     * @inheritDoc
     * @throws DisposableDisposedException
     */
    public function makeState(Application_StateHandler_State $state, ?string $comments=null) : bool
    {
        $this->requireNotDisposed();

        if($state->getName() === $this->getStateName()) {
            return false;
        }
        
        $transaction = false;
        if(!$this->isTransactionStarted()) {
            $this->startCurrentUserTransaction($comments);
            $transaction = true;
        }
        
        $this->setState($state);
        
        if($transaction) {
            $this->endTransaction();
        }

        return true;
    }

    /**
     * @param int $newOwnerID
     * @param string $newOwnerName
     * @param string|null $comments
     * @return $this
     * @throws Application_Exception
     * @throws DisposableDisposedException
     */
    public function startTransaction(int $newOwnerID, string $newOwnerName, ?string $comments = null) : self
    {
        $this->requireNotDisposed();
        $this->requireNotStub('Start a revision transaction');

        // to allow this transaction to be wrapped in an
        // existing transaction, we check if we have to
        // start one automatically or not.
        $this->handleDBTransaction = false;

        if (!DBHelper::isTransactionStarted()) {
            $this->handleDBTransaction = true;
            DBHelper::startTransaction();
        }

        $this->requireNotDisposed('Starting a transaction');

        $this->log('Transaction | START | Starting new transaction.');

        $this->logRevisionData();

        if ($this->transactionActive) {
            throw new RevisionableException(
                'Cannot start new transaction',
                'A transaction has been run previously, changes have to be saved or discarded to start a new one.',
                RevisionableInterface::ERROR_CANNOT_START_TRANSACTION
            );
        }

        $this->lastTransaction = null;
        $this->transactionActive = true;
        $this->requiresNewRevision = false;
        $this->transactionSourceRevision = $this->getRevision();

        $this->log('Transaction | START | Copying revision [%s] to new revision.', $this->transactionSourceRevision);

        $this->transactionTargetRevision = $this->revisions->addByCopy(
            $this->transactionSourceRevision,
            $newOwnerID,
            $newOwnerName,
            $comments
        );

        $this->revisions->selectRevision($this->transactionTargetRevision);

        $this->log('Transaction | START | Transaction initialized.');

        $this->log('Transaction | Current state is [%1$s].', $this->getStateName());

        $this->stateChanged = false;

        return $this;
    }

    /**
     * @return bool
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function endTransaction() : bool
    {
        $this->requireNotDisposed();
        $this->requireNotStub('End a revision transaction');

        if ($this->stateChanged) {
            $this->setRequiresNewRevision('State has changed');
        }

        // we need to do this, because we want to trigger it later
        $this->ignoreEvent(self::EVENT_TRANSACTION_ENDED);

        $this->requireNotDisposed('Ending a transaction');

        $this->log('Transaction | END | Ending the transaction.');

        if(!$this->transactionActive) {
            throw new RevisionableException(
                'Cannot end transaction',
                'Cannot end a transaction, no transaction has been started.',
                RevisionableInterface::ERROR_CANNOT_END_TRANSACTION
            );
        }

        if(!$this->requiresNewRevision)
        {
            $result = $this->endTransactionWithoutChanges();
        }
        else if($this->isSimulationEnabled())
        {
            $result = $this->endTransactionSimulation();
        }
        else
        {
            $result = $this->endTransactionWithChanges();
        }

        if ($result) {
            $this->collection->setCurrentRevision($this->id, $this->getRevision());
        }

        // do we handle the DB transaction here?
        if ($this->handleDBTransaction) {
            if ($this->isSimulationEnabled()) {
                $this->log('Transaction | END | Simulation mode, transaction will not be committed.');
                DBHelper::rollbackTransaction();
            } else {
                $this->log('Transaction | END | Committing transaction.');
                DBHelper::commitTransaction();
            }
        }

        // now that everything's through, we can trigger the event.
        $this->unIgnoreEvent(self::EVENT_TRANSACTION_ENDED);

        if (isset($this->lastTransaction)) {
            $this->triggerTransactionEnded($this->lastTransaction);
            return $result;
        }

        throw new RevisionableException(
            'No last transaction data stored.',
            'The last transaction data is not available, and the transaction cannot be completed.',
            self::ERROR_LAST_TRANSACTION_NOT_AVAILABLE
        );
    }

    final protected function requireNotStub(string $operation) : void
    {
        if(!$this->isStub()) {
            return;
        }

        throw new RevisionableException(
            'Operation not allowed on stub objects.',
            sprintf(
                'Target operation [%s] is not allowed on stub objects.',
                $operation
            ),
            RevisionableInterface::ERROR_OPERATION_NOT_ALLOWED_ON_STUB
        );
    }

    /**
     * Renders a page title for this item using a template: this will
     * automatically add the current state of the item within the title,
     * including developer-specific information for developer users.
     *
     * @param string|NULL $title
     * @return UI_Page_RevisionableTitle
     * @throws DisposableDisposedException
     */
    public function renderTitle(?string $title=null) : UI_Page_RevisionableTitle
    {
        $this->requireNotDisposed();

        return UI::getInstance()->getPage()->createRevisionableTitle($this)->setLabel($title);
    }

    /**
     * @inheritDoc
     * @throws DisposableDisposedException
     */
    final public function hasState(string $stateName) : bool
    {
        $this->requireNotDisposed();

        return $this->stateHandler->isStateKnown($stateName);
    }
    
   /**
    * Whether changes may be made to the revisionable in its current state.
    * @return boolean
    * @throws DisposableDisposedException
    */
    public function isChangingAllowed() : bool
    {
        return $this->requireState()->isChangingAllowed();
    }

    /**
     * @return bool
     * @throws DisposableDisposedException
     */
    #[DisposedAware]
    public function isEditable() : bool
    {
        $this->requireNotDisposed();

        return !$this->isLocked() && $this->isChangingAllowed();
    }

    /**
     * @return Application_StateHandler_State
     * @throws BaseException
     * @throws DisposableDisposedException
     * @throws StateHandlerException
     */
    #[DisposedAware]
    final public function getInitialState() : Application_StateHandler_State
    {
        $this->requireNotDisposed();

        return $this->stateHandler->getInitialState();
    }

    final public function isStub() : bool
    {
        return $this->getID() === RevisionableCollectionInterface::STUB_OBJECT_ID;
    }

    final public function getLatestRevisionByState(Application_StateHandler_State $state) : ?int
    {
        return $this->getCollection()->getLatestRevisionByState($this->getID(), $state);
    }

    /**
     * Deletes the latest revision of the revisionable, and
     * sets the previous one as active.
     *
     * @throws RevisionableException {@see RevisionableInterface::ERROR_CANNOT_UNDO_REVISION}
     * @throws DisposableDisposedException
     * @return int
     */
    final public function undoRevision() : int
    {
        $this->requireNotDisposed();

        // get the last two revisions
        $filters = $this->getRevisionsFilterCriteria();
        $filters->setOrderBy(RevisionableCollectionInterface::COL_REV_DATE, 'DESC');
        $filters->setLimit(2, 0);
        $items = $filters->getItems();

        if(count($items) !== 2) {
            throw new RevisionableException(
                'Cannot undo revision, no revisions left to undo',
                '',
                RevisionableInterface::ERROR_CANNOT_UNDO_REVISION
            );
        }

        $collection = $this->getCollection();
        $revColumn = $collection->getRevisionKeyName();

        $deleteRev = (int)$items[0][$revColumn];
        $replaceRev = (int)$items[1][$revColumn];

        $this->revisions->removeRevision($deleteRev);

        $collection->setCurrentRevision($this->getID(), $replaceRev);

        $this->selectRevision($replaceRev);

        return $replaceRev;
    }

    // region: DBHelper methods

    final public function getRecordKey(string $name, mixed $default = null): mixed
    {
        return $this->getDataKey($name, $default);
    }

    final public function getRecordData(): array
    {
        return $this->revisions->getDataKeys();
    }

    #[DisposedAware]
    final public function refreshData(): void
    {
        $this->revisions->reload();
    }

    final public function getRecordTable(): string
    {
        return $this->collection->getRecordTableName();
    }

    final public function getRecordPrimaryName(): string
    {
        return $this->collection->getRecordPrimaryName();
    }

    final public function getRecordTypeName(): string
    {
        return $this->collection->getRecordTypeName();
    }

    final public function recordKeyExists(string $name): bool
    {
        return $this->revisions->hasKey($name);
    }

    /**
     * @throws RevisionableException
     */
    final public function setRecordKey(string $name, mixed $value): never
    {
        throw new RevisionableException(
            'Cannot set record key on revisionable objects.',
            sprintf(
                'Setting record keys directly on revisionable objects is not allowed. Use [%1$s()] instead.',
                array($this, 'setDataKey')[1]
            ),
            RevisionableException::ERROR_CANNOT_USE_SET_RECORD_KEY
        );
    }

    public function requireRecordKeyExists(string $name): bool
    {
        return true;
    }

    final public function isModified(?string $key = null): bool
    {
        return $this->hasChanges();
    }

    final public function getModifiedKeys(): array
    {
        if($this->isModified()) {
            return array_keys($this->revisions->getDataKeys());
        }

        return array();
    }

    final public function saveChained(bool $silent = false): self
    {
        $this->save($silent);
        return $this;
    }

    /**
     * Revisionables do not have parent records.
     * @return null
     */
    public function getParentRecord(): null
    {
        return null;
    }

    public function getFormValues(): array
    {
        return $this->revisions->getDataKeys();
    }

    /**
     * **NOTE**: This is not supported by revisionables. It will always
     * be ignored.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onKeyModified(callable $callback): Application_EventHandler_EventableListener
    {
        return $this->addEventListener('void', $callback);
    }

    public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context): void
    {

    }

    public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void
    {
    }

    public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void
    {
    }

    // endregion






    protected function init() : void
    {

    }

    protected function setRequiresNewRevision(string $reason) : self
    {
        if ($this->requiresNewRevision === true) {
            return $this;
        }

        $this->log('Transaction | New revision required: %s', $reason);
        $this->requiresNewRevision = true;
        return $this;
    }

    public function getInstanceID() : string
    {
        return $this->instanceID;
    }

    public function getOwnerID() : int
    {
        $this->requireNotDisposed('Getting owner ID');

        return $this->revisions->getOwnerID();
    }

    public function getOwnerName() : string
    {
        $this->requireNotDisposed('Getting owner name');

        return $this->revisions->getOwnerName();
    }

    public function countRevisions() : int
    {
        $this->requireNotDisposed('Counting revisions');

        return $this->revisions->countRevisions();
    }

    public function getRevisionComments() : ?string
    {
        $this->requireNotDisposed('Getting revision comments');

        return $this->revisions->getComments();
    }

    public function getRevisions() : array
    {
        $this->requireNotDisposed('Getting revisions');

        return $this->revisions->getRevisions();
    }

    public function getRevision() : ?int
    {
        return $this->selectedRevision;
    }

    public function requireRevision() : int
    {
        $this->requireNotDisposed('Requiring revision');

        $revision = $this->getRevision();
        if($revision !== null) {
            return $revision;
        }

        throw new RevisionableException(
            'No revision selected',
            'No revision has been selected, but one is required for this operation.',
            RevisionableInterface::ERROR_NO_REVISION_SELECTED
        );
    }

    public function getRevisionable() : RevisionableInterface
    {
        return $this;
    }

    /**
     * Creates a filter criteria instance for accessing the
     * revisionable's available revisions list.
     *
     * @return Application_FilterCriteria_RevisionableRevisions
     */
    public function getRevisionsFilterCriteria() : Application_FilterCriteria_RevisionableRevisions
    {
        $this->requireNotDisposed('Getting revisions filter criteria');

        return $this->revisions->getFilterCriteria();
    }

    /**
     * @param int $number
     * @return $this
     *
     * @throws DisposableDisposedException
     * @throws RevisionStorageException
     */
    public function selectRevision(int $number) : self
    {
        $this->requireNotDisposed('Selecting revision');

        $this->revisions->selectRevision($number);
        return $this;
    }

    /**
     * @return int
     * @throws DisposableDisposedException
     * @throws RevisionableException
     */
    public function getLatestRevision() : int
    {
        $this->requireNotDisposed('Getting latest revision');

        return $this->revisions->getLatestRevision();
    }

    /**
     * Retrieves the date the revisionable was last modified.
     * @return DateTime
     */
    public function getLastModifiedDate() : DateTime
    {
        $this->requireNotDisposed('Getting last modified date');

        $this->rememberRevision();
        $this->selectLatestRevision();
        $date = $this->getRevisionDate();
        $this->restoreRevision();

        return $date;
    }

    /**
     * Retrieves the previous revision number to the currently selected
     * revision, if any. Returns the number or null if there is none.
     *
     * @return integer|NULL
     */
    public function getPreviousRevision() : ?int
    {
        $this->requireNotDisposed('Getting previous revision');

        $current = $this->getRevision();
        $revisions = $this->getRevisions();

        sort($revisions); // make sure they are in ascending order

        $total = count($revisions);
        for ($i = 0; $i < $total; $i++) {
            if ($revisions[$i] === $current) {
                $prevIdx = $i - 1;
                if (isset($revisions[$prevIdx])) {
                    return $revisions[$prevIdx];
                }
                break;
            }
        }

        return null;
    }

    public function getRevisionTimestamp() : ?int
    {
        $this->requireNotDisposed('Getting revision timestamp');

        return $this->revisions->getTimestamp();
    }

    /**
     * Retrieves a DateTime object for the current revision's creation time.
     * @return DateTime
     */
    public function getRevisionDate() : DateTime
    {
        $this->requireNotDisposed('Getting revision date');

        if ($this->revisions->hasKey('__date')) {
            return $this->revisions->getKey('__date');
        }

        $stamp = $this->revisions->getTimestamp();

        // Replaced this with an alternative, since the @ notation
        // introduced some hard to explain time differences as compared
        // to using the timestamp in a date() statement.
        // $date = new DateTime('@' . $stamp);

        $date = new DateTime(date('c', $stamp));
        $this->revisions->setKey('__date', $date);

        return $date;
    }

    /**
     * @return $this
     */
    public function rememberRevision() : self
    {
        $this->requireNotDisposed('Remembering revision');

        $this->revisions->rememberRevision();
        return $this;
    }

    /**
     * @return $this
     */
    public function restoreRevision() : self
    {
        $this->requireNotDisposed('Restoring revision');

        $this->revisions->restoreRevision();
        return $this;
    }

    public function revisionExists(int $number) : bool
    {
        $this->requireNotDisposed('Checking if revision exists');

        return $this->revisions->revisionExists($number);
    }

    // region: Transactions

    protected bool $transactionActive = false;



    /**
     * Starts a new transaction with the currently authenticated
     * user as the owner of the transaction.
     *
     * @param string|NULL $comments
     * @return $this
     * @throws DisposableDisposedException
     * @throws RevisionableException
     * @see self::startTransaction()
     */
    public function startCurrentUserTransaction(?string $comments = null) : self
    {
        $user = Application::getUser();

        return $this->startTransaction($user->getID(), $user->getName(), $comments);
    }

    protected ?int $lastTransactionAddedRevision = null;

    /**
     * Ends a transaction by either keeping the new revision
     * if it is required, or by dismissing the new revision but
     * keeping any minor changes.
     *
     * To set whether to add a new revision, the property
     * {@see self::requiresNewRevision} is used. You can use the
     * utility method {@see self::changesMade()} in your class to
     * have this set for you.
     *
     * Returns a boolean value indicating whether a new revision
     * has been added in the transaction.
     *
     * @see self::startTransaction()
     * @return boolean
     */

    private function endTransactionWithChanges() : bool
    {
        $this->log('Transaction | END | Saving data.');
        $this->save();

        $this->log('Transaction | END | Committing changelog');
        $this->commitChangelog();

        $this->log('Transaction | END | Done.');

        $this->logRevisionData();

        $this->lastTransaction = new TransactionInfo(
            $this,
            TransactionInfo::TRANSACTION_CHANGED,
            $this->isSimulationEnabled(),
            (int)$this->transactionSourceRevision,
            $this->transactionTargetRevision
        );

        $this->resetTransactionData();

        $this->triggerTransactionEnded($this->lastTransaction);

        return true;
    }

    private function endTransactionWithoutChanges() : bool
    {
        $this->log('Transaction | END | No changes made, ignoring.');

        $this->requireEmptyChangelogQueue();

        $this->revisions->removeRevision($this->transactionTargetRevision);
        $this->selectRevision($this->transactionSourceRevision);

        $this->lastTransaction = new TransactionInfo(
            $this,
            TransactionInfo::TRANSACTION_UNCHANGED,
            $this->isSimulationEnabled(),
            $this->transactionSourceRevision,
            null
        );

        $this->resetTransactionData();

        $this->triggerTransactionEnded($this->lastTransaction);
        return false;
    }

    private function endTransactionSimulation() : bool
    {
        $this->log('Transaction | END | Simulation enabled, rolling back.');

        $this->rollBackTransaction();

        return false;
    }

    protected function logRevisionData() : void
    {
        $this->log('Revision | Author: [%s %s]', $this->getRevisionAuthorID(), $this->getRevisionAuthorName());
        $this->log('Revision | Pretty revision: [%s].', $this->getPrettyRevision());
        $this->log('Revision | Comments: [%s].', $this->getRevisionComments());
        $this->log('Revision | Date: [%s].', $this->getRevisionDate()->format('d.m.Y H:i:s'));
    }

    protected ?TransactionInfo $lastTransaction = null;

    /**
     * @return bool
     * @throws DisposableDisposedException
     * @throws RevisionableException
     */
    public function hasLastTransactionAddedARevision() : bool
    {
        return $this->getLastAddedRevision() !== null;
    }

    /**
     * @return int|null
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function getLastAddedRevision() : ?int
    {
        $this->requireNotDisposed('Checking for added revision');

        if($this->isTransactionStarted()) {
            throw new RevisionableException(
                'Cannot check for added revision',
                'Cannot check for an added revision while a transaction is still active.',
                RevisionableInterface::ERROR_CANNOT_GET_ADDED_REVISION_DURING_TRANSACTION
            );
        }

        if(isset($this->lastTransaction)) {
            return $this->lastTransaction->getNewRevision();
        }

        return null;
    }

    protected function resetTransactionData() : void
    {
        $this->requiresNewRevision = false;
        $this->transactionSourceRevision = null;
        $this->transactionTargetRevision = null;
        $this->transactionActive = false;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function requireTransaction(string $developerDetails='') : self
    {
        if($this->transactionActive) {
            return $this;
        }

        throw new RevisionableException(
            'No transaction active',
            'The current operation requires a transaction to be started.',
            RevisionableInterface::ERROR_OPERATION_REQUIRES_TRANSACTION
        );
    }

    // endregion

    protected function requireEmptyChangelogQueue() : void
    {
        if(empty($this->changelogQueue)) {
            return;
        }

        throw new RevisionableException(
            'Transaction changelog is not empty.',
            sprintf(
                'The transaction is ending without requiring a new transaction, but the changelog queue is not empty. '.
                'This can point to a problem with the transaction handling, where the revisionable is not made aware of some changes. '.
                'The changelog queue contains the following change types: '.
                '- %s',
                implode('- '.PHP_EOL, $this->getChangelogQueueTypes())
            ),
            RevisionableInterface::ERROR_TRANSACTION_CHANGELOG_NOT_EMPTY
        );
    }

    /**
     * Selects the most recent revision of the item.
     */
    public function selectLatestRevision() : self
    {
        $this->requireNotDisposed('Selecting latest revision');

        return $this->selectRevision($this->getLatestRevision());
    }

    public function selectFirstRevision() : self
    {
        $this->requireNotDisposed('Selecting first revision');

        return $this->selectRevision($this->getFirstRevision());
    }

    public function getFirstRevision() : int
    {
        $this->requireNotDisposed('Getting first revision');

        return $this->revisions->getFirstRevision();
    }

    public function lockRevision() : self
    {
        $this->requireNotDisposed('Locking revision');

        $this->revisions->lock();
        return $this;
    }

    /**
     * @return $this
     */
    public function unlockRevision() : self
    {
        $this->requireNotDisposed('Unlocking revision');

        $this->revisions->unlock();
        return $this;
    }

    public function isRevisionLocked() : bool
    {
        $this->requireNotDisposed('Checking if revision is locked');

        return $this->revisions->isLocked();
    }

    /**
     * Selects the revision prior to the currently selected revision
     * if any exists.
     *
     * @return boolean Whether a previous revision existed and was selected
     */
    public function selectPreviousRevision() : bool
    {
        $this->requireNotDisposed('Selecting previous revision');

        $prev = $this->getPreviousRevision();
        if(!$prev) {
            return false;
        }

        $this->selectRevision($prev);
        return true;
    }

    // region Data handling

    public const string STORAGE_PART_CUSTOM_KEYS = 'customKeys';
    public const string STORAGE_PART_DATA_KEYS = 'revdata';

    public const string KEY_TYPE_DATA_KEYS = 'data_keys';
    public const string KEY_TYPE_REGULAR = 'standard';

    /**
     * Utility method to keep track of internal changes when
     * using transactions. Sets the {@see self::$requiresNewRevision}
     * property to true to trigger a new revision when ending
     * the current transaction.
     *
     * Call this method every time you have made changes for
     * which a new revision should be triggered.
     *
     * @see self::hasChanges()
     * @see self::resetChanges()
     */
    protected function changesMade(string $reason ='') : void
    {
        if(empty($reason)) {
            $reason = 'n/a';
        }

        $this->setRequiresNewRevision('A change was made. Reason: ['.$reason.'].');
    }

    /**
     * Checks whether this item has structural changes.
     * @return boolean
     * @see self::resetChanges()
     * @see self::changesMade()
     */
    public function hasChanges() : bool
    {
        $this->requireNotDisposed('Checking for changes');

        return $this->requiresNewRevision;
    }

    /**
     * Basic save implementation: checks whether any changes
     * were made, and if yes calls the {@link _save()} custom
     * implementation, commits the changelog and resets changes.
     *
     * If any parts have been marked as modified using the
     * {@link setPartChanged()} method, they are saved as well.
     *
     * @see RevisionableInterface::save()
     * @see _save()
     * @see saveParts()
     */
    public function save(bool $silent=false) : bool
    {
        $this->requireNotDisposed('Saving');
        $this->requireTransaction('Cannot save without starting a transaction.');

        $this->triggerEvent(
            self::EVENT_BEFORE_SAVE,
            array($this),
            BeforeSaveEvent::class
        );

        $this->log(
            'Saving | Has changes: [%s]',
            ConvertHelper::bool2string($this->hasChanges()),
        );

        if (!$this->hasChanges()) {
            $this->log('Saving | No changes were made, skipping save.');
            return false;
        }

        $this->_save();
        $this->saveParts();

        $this->log('Saving | Done.');

        $this->resetChanges();

        return true;
    }

    protected function initInternalStorageParts() : void
    {
        $this->registerStoragePart(self::STORAGE_PART_DATA_KEYS, $this->_saveDataKeys(...));
        $this->registerStoragePart(self::STORAGE_PART_CUSTOM_KEYS, $this->_saveCustomKeys(...));
    }

    /**
     * Used to register all data sets (parts) that must be
     * saved during transactions. The record must handle
     * applying any changes itself in these methods.
     *
     * Use the {@see self::registerStoragePart()} method to
     * register callbacks for each of these parts.
     *
     * Changes made to any custom revision fields of the
     * record must be saved this way. Typically, this part
     * is called <code>settings</code>.
     *
     * @return void
     */
    abstract protected function initStorageParts() : void;

    /**
     * @var array<string, callable>
     */
    private array $storageParts = array();

    /**
     * Registers a data storage part that must be saved
     * whenever a transaction is active.
     *
     * @param string $name
     * @param callable $callback
     * @return void
     * @throws RevisionableStorageException
     */
    protected function registerStoragePart(string $name, callable $callback) : void
    {
        if(isset($this->storageParts[$name])) {
            throw new RevisionableStorageException(
                'Cannot overwrite existing storage part',
                sprintf(
                    'The storage part [%s] has already been registered, and may not be overwritten.',
                    $name
                ),
                RevisionableInterface::ERROR_STORAGE_PART_ALREADY_REGISTERED
            );
        }

        $this->storageParts[$name] = $callback;
    }

    /**
     * Saves all individual parts of the revisionable item
     * that have been marked as changed using the {@see self::setPartChanged()}
     * method. Called automatically when the revisionable
     * is saved.
     *
     * @throws RevisionableException
     */
    protected function saveParts() : void
    {
        $this->log('StorageParts | Saving parts that have been set as changed.');

        foreach($this->changedParts as $part => $changed)
        {
            if($changed) {
                $this->log('StorageParts | [%s] | Has changes, saving...', $part);
                $this->savePart($part);
            } else {
                $this->log('StorageParts | [%s] | No changes, ignoring.', $part);
            }
        }

        $this->log('StorageParts | Done.');
    }

    private function savePart(string $name) : void
    {
        if(isset($this->storageParts[$name])) {
            $callback = $this->storageParts[$name];
            $callback();
            return;
        }

        throw new RevisionableException(
            'Unknown revisionable storage part',
            sprintf(
                'Tried saving part [%s], but no callback has been registered for it.'.PHP_EOL.
                'Parts can be registered with the [%s] method.',
                $name,
                array($this, 'registerStoragePart')[1].'()'
            ),
            RevisionableInterface::ERROR_MISSING_PART_SAVE_METHOD
        );
    }

    /**
     * Saves all data keys that are stored in the revdata storage.
     * This is automated and does not need to be handled by the
     * revisionable implementation.
     *
     * Hint: this gets called, because setting a revdata key uses
     * the part named "revdata", and thus gets called by the saveParts
     * method.
     *
     * @see self::saveParts()
     * @see self::setDataKey()
     */
    protected function _saveDataKeys() : void
    {
        // contrary to other revisionable data, this is
        // standardized and can be saved directly by the
        // revision storage itself: this is because the key names
        // can be used directly without mapping them internally
        // to something like a database column.

        $this->revisions->writeDataKeys();
    }

    protected function _saveCustomKeys() : void
    {
        $this->revisions->writeCustomKeys();
    }

    /**
     * This is called by the revisionable storage when a new
     * revision has been loaded. Can be extended to add any
     * relevant custom implementations.
     *
     * @deprecated Use the {@see self::onRevisionSelected()} event handling.
     */
    public function handle_revisionLoaded(int $number) : void
    {

    }

    protected array $changedParts = array();

    /**
     * Checks whether the specified storage part has been modified.
     *
     * @param string $part
     * @return bool
     * @throws RevisionableException
     */
    public function isPartChanged(string $part) : bool
    {
        $this->requireNotDisposed('Checking for part changes');
        $this->requirePartExists($part);

        return isset($this->changedParts[$part]) && $this->changedParts[$part]===true;
    }

    public function getChangedParts() : array
    {
        $this->requireNotDisposed('Getting changed parts');

        $result = array();
        foreach($this->changedParts as $part => $state) {
            if($state === true) {
                $result[] = $part;
            }
        }

        return $result;
    }

    private function requirePartExists(string $part) : void
    {
        if(isset($this->storageParts[$part])) {
            return;
        }

        throw new RevisionableException(
            'Unknown revisionable storage part.',
            sprintf(
                'The revisionable part [%s] is not known.',
                $part
            ),
            RevisionableInterface::ERROR_UNKNOWN_STORAGE_PART
        );
    }

    // endregion

    /**
     * @return RevisionableInterface
     */
    public function reload() : RevisionableInterface
    {
        if($this->isDisposed()) {
            return $this->getCollection()->getByID($this->getID());
        }

        return $this;
    }

    /**
     * Checks whether a transaction has been started.
     * @return boolean
     */
    public function isTransactionStarted() : bool
    {
        $this->requireNotDisposed('Checking if transaction is started');

        return $this->transactionActive;
    }

    public function getChildDisposables(): array
    {
        $disposables = $this->_getChildDisposables();
        $disposables[] = $this->revisions;

        return $disposables;
    }

    protected function _dispose(): void
    {
        $this->changedParts = array();
        $this->storageParts = array();
        $this->lastTransaction = null;
        $this->lastTransactionAddedRevision = null;
        $this->transactionSourceRevision = null;
        $this->transactionTargetRevision = null;

        $this->_disposeRevisionable();
    }

    abstract protected function _disposeRevisionable() : void;

    abstract protected function _getChildDisposables() : array;

    /**
     * Retrieves the revisionable's first revision date.
     * @return DateTime
     */
    public function getCreationDate() : DateTime
    {
        $this->requireNotDisposed('Getting creation date');

        $this->rememberRevision();
        $this->selectFirstRevision();
        $date = $this->getRevisionDate();
        $this->restoreRevision();

        return $date;
    }

    /**
     * Retrieves the user instance for the user that created this item.
     * @return Application_User
     */
    public function getCreator() : Application_User
    {
        $this->requireNotDisposed('Getting creator');

        $this->rememberRevision();
        $this->selectFirstRevision();
        $user = $this->getRevisionAuthor();
        $this->restoreRevision();

        return $user;
    }

    public function getRevisionAuthorID(): int
    {
        $this->requireNotDisposed('Getting revision author ID');

        return $this->revisions->getOwnerID();
    }

    public function getRevisionAuthorName(): string
    {
        $this->requireNotDisposed('Getting revision author name');

        return $this->revisions->getOwnerName();
    }

    public function getRevisionAuthor() : ?Application_User
    {
        $this->requireNotDisposed('Getting revision author');

        $id = $this->getRevisionAuthorID();
        if($id > 0 && Application::userIDExists($id)) {
            return Application::createUser($id);
        }

        return null;
    }

    public function getLockPrimary() : string
    {
        $this->requireNotDisposed('Getting lock primary');

        return $this->getRecordTypeName().'-'.$this->getID();
    }

    /**
     * Sets a key value in the main revision storage of the record.
     *
     * NOTE: The key name must be known. To set custom keys, use
     * {@see self::setDataKey()} instead.
     *
     * @param string $name The key name
     * @param mixed $value The key value
     * @param string $part The part that the key is a member of. Will be set as changed if the value is different.
     * @param bool $structural Whether the key is structural and requires a state change.
     * @param string $changelogID The changelog ID to use for adding a standardized changelog entry
     * @param array<string,string|number|bool|NULL> $changelogData Any data that should be stored alongside the changelog entry.
     * @return boolean Whether the value has changed, and a save will be needed.
     */
    protected function setRevisionKey(string $name, mixed $value, string $part, bool $structural, string $changelogID='', array $changelogData=array()): bool
    {
        return $this->_setRevisionKey(self::KEY_TYPE_REGULAR, $name, $value, $part, $structural, $changelogID, $changelogData);
    }

    /**
     * Sets a custom revision key value: These are stored together with the
     * regular revision keys like the author and comments, but are custom
     * for the revisionable.
     *
     * @param string $name
     * @param mixed $value
     * @param bool $structural
     * @param string $changelogID
     * @param array<string,string|number|bool|NULL> $changelogData
     * @return bool
     */
    protected function setCustomKey(string $name, mixed $value, bool $structural, string $changelogID='', array $changelogData=array()) : bool
    {
        return $this->setRevisionKey(
            $name,
            $value,
            self::STORAGE_PART_CUSTOM_KEYS,
            $structural,
            $changelogID,
            $changelogData
        );
    }

    /**
     * @param string $name
     * @return mixed|null
     * @throws DisposableDisposedException
     * @throws RevisionableException
     */
    protected function getRevisionKey(string $name) : mixed
    {
        $this->requireNotDisposed('Getting revision key');

        return $this->revisions->getKey($name);
    }

    /**
     * Sets a key value in the revision data, which allows
     * storing custom key values that are not part of the main
     * revision storage.
     *
     * This storage handles key/value pairs where the key name
     * is the unique key within a revision.
     *
     * @param string $name The key name
     * @param mixed $value The key value
     * @param bool $structural Whether the key is structural and requires a state change.
     * @param string $changelogID The changelog ID to use for adding a standardized changelog entry
     * @param array $changelogData Any data that should be stored alongside the changelog entry.
     * @return boolean Whether the value has changed, and a save will be needed.
     */
    protected function setDataKey(string $name, mixed $value, bool $structural, string $changelogID='', array $changelogData=array()) : bool
    {
        return $this->_setRevisionKey(self::KEY_TYPE_DATA_KEYS, $name, $value, self::STORAGE_PART_DATA_KEYS, $structural, $changelogID, $changelogData);
    }

    /**
     * Sets a revision key, either via the regular revision data, or the revdata storage.
     *
     * @param string $type The storage type: {@see self::KEY_TYPE_REGULAR} or {@see self::KEY_TYPE_DATA_KEYS}.
     * @param string $name The key name
     * @param mixed $value The key value
     * @param string $part The part that the key is a member of. Will be set as changed if the value is different.
     * @param bool $structural Whether the key is structural and requires a state change.
     * @param string $changelogID The changelog ID to use for adding a standardized changelog entry.
     * @param array<string,string|number|bool|NULL> $changelogData Any data that should be stored alongside the changelog entry.
     * @return boolean Whether the value has changed, and a save will be needed.
     * @throws RevisionableException
     * @throws ConvertHelper_Exception
     */
    private function _setRevisionKey(string $type, string $name, mixed $value, string $part, bool $structural, string $changelogID='', array $changelogData=array()) : bool
    {
        $this->requireTransaction();

        $isDataKey = $type === self::KEY_TYPE_DATA_KEYS;

        if($isDataKey) {
            $old = $this->revisions->getDataKey($name);
        } else {
            $old = $this->revisions->getKey($name);
        }

        if(ConvertHelper::areVariablesEqual($old, $value)) {
            return false;
        }

        $this->log('Transaction | Key [%s] has changed.', $name);

        if($value === '') { $value = null; }
        if($old === '') { $old = null; }

        $this->setPartChanged($part, $structural);

        if($isDataKey) {
            $this->revisions->setDataKey($name, $value);
        } else {
            $this->revisions->setKey($name, $value);
        }

        if(empty($changelogID)) {
            return true;
        }

        if(!isset($changelogData['old'])) {
            $changelogData['old'] = $old;
        }

        if(!isset($changelogData['new'])) {
            $changelogData['new'] = $value;
        }

        $this->enqueueChangelog($changelogID, $changelogData);

        return true;
    }

    /**
     * Retrieves a previously set revision key value from
     * the revision data storage.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws RevisionableException
     */
    #[DisposedAware]
    public function getDataKey(string $name, mixed $default=null) : mixed
    {
        $this->requireNotDisposed('Getting data key');

        return $this->revisions->getDataKey($name, $default);
    }

    // region: Event handling

    public const string EVENT_TRANSACTION_ENDED = 'TransactionEnded';
    public const string EVENT_BEFORE_SAVE = 'BeforeSave';
    public const string EVENT_REVISION_ADDED = 'RevisionAdded';

    /**
     * @var array<string,true>
     */
    protected static array $revisionAgnosticEvents = array();

    private function initRevisionEvents() : void
    {
        self::registerRevisionAgnosticEvent(self::EVENT_BEFORE_SAVE);
        self::registerRevisionAgnosticEvent(self::EVENT_REVISION_ADDED);
        self::registerRevisionAgnosticEvent(self::EVENT_TRANSACTION_ENDED);

        $this->revisions->onRevisionAdded($this->callback_revisionAdded(...));
        $this->revisions->onRevisionSelected($this->callback_revisionSelected(...));

        $this->_registerEvents();
    }

    private function callback_revisionSelected(RevisionSelectedEvent $event) : void
    {
        $this->selectedRevision = $event->getRevision();

        $this->triggerEvent(
            \Application\Revisionable\Event\RevisionSelectedEvent::EVENT_NAME,
            array($this, $event->getRevision()),
            \Application\Revisionable\Event\RevisionSelectedEvent::class
        );
    }

    private function callback_revisionAdded(Application_RevisionStorage_Event_RevisionAdded $event) : void
    {
        $this->triggerEvent(
            self::EVENT_REVISION_ADDED,
            array($this, $event),
            RevisionAddedEvent::class
        );
    }

    abstract protected function _registerEvents() : void;

    /**
     * Registers the name of an event that is not revision-specific,
     * and can be triggered regardless of the currently selected revision.
     *
     * @param string $name
     */
    protected static function registerRevisionAgnosticEvent(string $name) : void
    {
        self::$revisionAgnosticEvents[$name] = true;
    }

    /**
     * Checks if the specified event name is not revision-specific.
     *
     * @param string $name
     * @return bool
     */
    public function isEventRevisionAgnostic(string $name) : bool
    {
        return isset(self::$revisionAgnosticEvents[$name]);
    }

    protected function triggerTransactionEnded(TransactionInfo $info) : void
    {
        $this->triggerEvent(
            self::EVENT_TRANSACTION_ENDED,
            array($info),
            TransactionEndedEvent::class
        );
    }

    public function getEventNamespace(string $eventName) : ?string
    {
        if(!$this->isEventRevisionAgnostic($eventName)) {
            return (string)$this->selectedRevision;
        }

        return null;
    }

    /**
     * Adds a callback to call before the revisionable is saved.
     *
     * This gets a single parameter:
     *
     * - The event object {@see BeforeSaveEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onBeforeSave(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_BEFORE_SAVE, $callback);
    }

    /**
     * Adds a callback to whenever a different revisionable revision
     * has been selected.
     *
     * This gets a single parameter:
     *
     * - The event object {@see \Application\Revisionable\Event\RevisionSelectedEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onRevisionSelected(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(\Application\Revisionable\Event\RevisionSelectedEvent::EVENT_NAME, $callback);
    }

    /**
     * Adds a callback for when a new revision is added to the revisionable.
     *
     * The callback gets the following parameters:
     *
     * 1) The revisionable instance {@see RevisionableInterface}.
     * 2) The event instance {@see RevisionAddedEvent}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     * @see RevisionAddedEvent
     */
    public function onRevisionAdded(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_REVISION_ADDED, $callback);
    }

    /**
     * Adds a callback for when a revisionable change transaction has ended.
     *
     * The callback gets the following parameters:
     *
     * 1) The revisionable instance {@see RevisionableInterface}.
     * 2) The event instance {@see Application_Revisionable_Event_TransactionEnded}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onTransactionEnded(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_TRANSACTION_ENDED, $callback);
    }

    // endregion
}
