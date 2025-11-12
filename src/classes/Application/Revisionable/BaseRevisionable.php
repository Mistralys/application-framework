<?php
/**
 * @package Application
 * @subpackage Revisionable
 */

declare(strict_types=1);

use Application\Disposables\DisposableDisposedException;
use Application\Revisionable\Changelog\BaseRevisionableChangelogHandler;
use Application\Revisionable\Changelog\RevisionableChangelogHandlerInterface;
use Application\Revisionable\Collection\BaseRevisionableCollection;
use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\RevisionableStatelessInterface;
use Application\Revisionable\Storage\BaseDBCollectionStorage;
use Application\Revisionable\Storage\RevisionStorageException;
use Application\Revisionable\Storage\StubDBRevisionStorage;
use Application\StateHandler\StateHandlerException;
use AppUtils\BaseException;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
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
abstract class BaseRevisionable
    extends Application_RevisionableStateless
    implements RevisionableInterface
{
    use RecordKeyHandlersTrait;

    public const int ERROR_NO_CURRENT_REVISION_FOUND = 14701;
    public const int ERROR_LAST_TRANSACTION_NOT_AVAILABLE = 14702;

    protected Application_StateHandler $stateHandler;

    protected BaseRevisionableCollection $collection;
    protected int $id;
    public function __construct(BaseRevisionableCollection $collection, int $id)
    {
        $this->collection = $collection;
        $this->id = $id;

        parent::__construct();

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
     * @return BaseRevisionableCollection
     */
    public function getCollection(): BaseRevisionableCollection
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
                    'Application\Revisionable\Storage\BaseDBCollectionStorage'
                ),
                RevisionableStatelessInterface::ERROR_INVALID_REVISION_STORAGE,
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
            BaseRevisionableCollection::COL_REV_STATE => $this->getStateName()
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
     * @return $this
     * @throws DBHelper_Exception
     */
    public function rollBackTransaction(): self
    {
        parent::rollBackTransaction();

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
        $where[BaseRevisionableCollection::COL_REV_STATE] = $state->getName();

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
        return (int)$this->revisions->getKey(BaseRevisionableCollection::COL_REV_PRETTY_REVISION);
    }

    public function getLabel(): string
    {
        return (string)$this->getRevisionKey(BaseRevisionableCollection::COL_REV_LABEL);
    }

    public function setLabel(string $label): self
    {
        $this->setCustomKey(
            BaseRevisionableCollection::COL_REV_LABEL,
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
        $state = $this->getState();

        if ($state !== null) {
            return $state->getLabel();
        }

        return null;
    }

    /**
     * @return string|null
     * @throws RevisionableException
     * @throws DisposableDisposedException
     */
    public function getCurrentPrettyStateLabel() : ?string
    {
        $state = $this->getState();

        if ($state !== null) {
            return $state->getPrettyLabel();
        }

        return null;
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

        $state = $this->revisions->getKey(BaseRevisionableCollection::COL_REV_STATE);

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
     * @throws \Application\Disposables\DisposableDisposedException
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

        $this->revisions->setKey(BaseRevisionableCollection::COL_REV_STATE, $newState);
        
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
    * @throws \Application\Disposables\DisposableDisposedException
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
     * @throws \Application\Disposables\DisposableDisposedException
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

    protected function resetChanges(): void
    {
        parent::resetChanges();

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
     * Sets that the specified revisionable part has changed,
     * with the option to specify whether the change was structural
     * and will require a new revision.
     *
     * @param string $part
     * @param boolean $structural
     * @return BaseRevisionable
     * @throws Application_Exception
     * @see Application_RevisionableStateless::setPartChanged()
     */
    protected function setPartChanged(string $part, bool $structural=false) : self
    {
        parent::setPartChanged($part, $structural);

        if($structural) {
            $this->structureChanged(sprintf('Structural part [%s] changed.', $part));
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

        parent::startTransaction($newOwnerID, $newOwnerName, $comments);

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

        $result = parent::endTransaction();

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
     * @throws \Application\Disposables\DisposableDisposedException
     */
    public function renderTitle(?string $title=null) : UI_Page_RevisionableTitle
    {
        $this->requireNotDisposed();

        return UI::getInstance()->getPage()->createRevisionableTitle($this)->setLabel($title);
    }

    /**
     * @inheritDoc
     * @throws \Application\Disposables\DisposableDisposedException
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
     * @throws \Application\Disposables\DisposableDisposedException
     */
    public function isEditable() : bool
    {
        $this->requireNotDisposed();

        if(!parent::isEditable()) {
            return false;
        }

        return $this->isChangingAllowed();
    }

    /**
     * @return Application_StateHandler_State
     * @throws BaseException
     * @throws DisposableDisposedException
     * @throws StateHandlerException
     */
    final public function getInitialState() : Application_StateHandler_State
    {
        $this->requireNotDisposed();

        return $this->stateHandler->getInitialState();
    }

    final public function isStub() : bool
    {
        return $this->getID() === BaseRevisionableCollection::STUB_OBJECT_ID;
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
        $filters->setOrderBy(BaseRevisionableCollection::COL_REV_DATE, 'DESC');
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

    #[Attribute]
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
        // TODO: Implement requireRecordKeyExists() method.
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
        // TODO: Implement getFormValues() method.
    }

    public function onKeyModified(callable $callback): Application_EventHandler_EventableListener
    {
        // TODO: Implement onKeyModified() method.
    }

    public function onCreated(DBHelper_BaseCollection_OperationContext_Create $context): void
    {
        // TODO: Implement onCreated() method.
    }

    public function onDeleted(DBHelper_BaseCollection_OperationContext_Delete $context): void
    {
        // TODO: Implement onDeleted() method.
    }

    public function onBeforeDelete(DBHelper_BaseCollection_OperationContext_Delete $context): void
    {
        // TODO: Implement onBeforeDelete() method.
    }

    // endregion
}
