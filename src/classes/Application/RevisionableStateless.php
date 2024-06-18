<?php
/**
 * File containing the {@link Application_RevisionableStateless} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_RevisionableStateless
 */

declare(strict_types=1);

use Application\Revisionable\Event\BeforeSaveEvent;
use Application\Revisionable\Event\RevisionAddedEvent;
use Application\Revisionable\Event\TransactionEndedEvent;
use Application\Revisionable\RevisionableChangelogTrait;
use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionableStatelessInterface;
use Application\Revisionable\RevisionableStorageException;
use Application\Revisionable\TransactionInfo;
use Application\Traits\RevisionDependentTrait;
use AppUtils\ConvertHelper;
use AppUtils\ConvertHelper_Exception;
use AppUtils\NamedClosure;

/**
 * Base class for stateless revisionable items: provides a
 * method skeleton with basic functionality for data types
 * that can be versioned, but which do not change state
 * between revisions.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_RevisionableStateless
    implements
    RevisionableStatelessInterface
{
    use Application_Traits_LockableWithManager;
    use Application_Traits_Disposable;
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;
    use Application_Traits_Simulatable;
    use RevisionableChangelogTrait;
    use RevisionDependentTrait;

    protected Application_RevisionStorage $revisions;
    protected bool $requiresNewRevision = false;
    protected ?int $transactionSourceRevision = null;
    protected ?int $transactionTargetRevision = null;
    protected static int $instanceCounter = 0;
    protected int $instanceID;
    protected bool $initialized = false;
    
    /**
     * Initializes the underlying objects like the revision
     * storage object and state handler. Make sure to call this
     * if you have your own constructor using parent::__construct().
     */
    public function __construct()
    {
        $this->revisions = $this->createRevisionStorage();
        
        self::$instanceCounter++;
        $this->instanceID = self::$instanceCounter;

        $this->initRevisionEvents();
        $this->initInternalStorageParts();
        $this->initStorageParts();

        $this->init();
        $this->initialized = true;
    }
    
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
    
   /**
    * Retrieves the revisionable's ID.
    * @return integer
    */
    abstract public function getID() : int;
    
    public function getInstanceID() : int
    {
        return $this->instanceID;
    }

    /**
     * Creates the revision storage instance that is used to
     * store the object data between revisions.
     *
     * @return Application_RevisionStorage
     */
    protected function createRevisionStorage() : Application_RevisionStorage
    {
        return new MemoryRevisionStorage($this);
    }

    public function getOwnerID() : int
    {
        return $this->revisions->getOwnerID();
    }

    public function getOwnerName() : string
    {
        return $this->revisions->getOwnerName();
    }

    public function countRevisions() : int
    {
        return $this->revisions->countRevisions();
    }

    public function getRevisionComments() : ?string
    {
        return $this->revisions->getComments();
    }

    public function getRevisions() : array
    {
        return $this->revisions->getRevisions();
    }
    
    public function getRevision() : ?int
    {
        return $this->revisions->getRevision();
    }

    public function requireRevision() : int
    {
        $revision = $this->getRevision();
        if($revision !== null) {
            return $revision;
        }

        throw new RevisionableException(
            'No revision selected',
            'No revision has been selected, but one is required for this operation.',
            RevisionableStatelessInterface::ERROR_NO_REVISION_SELECTED
        );
    }

    public function getRevisionable() : RevisionableStatelessInterface
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
        return $this->revisions->getFilterCriteria();
    }
    
    public function getPrettyRevision() : int
    {
        return $this->getRevision();
    }

    /**
     * @return $this
     */
    public function selectRevision(int $number) : self
    {
        $this->revisions->selectRevision($number);
        return $this;
    }

    /**
     * @return int
     * @throws RevisionableException
     */
    public function getLatestRevision() : int
    {
        return $this->revisions->getLatestRevision();
    }

    /**
    * Retrieves the date the revisionable was last modified.
    * @return DateTime
    */
    public function getLastModifiedDate() : DateTime
    {
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
        return $this->revisions->getTimestamp();
    }

    /**
     * Retrieves a DateTime object for the current revision's creation time.
     * @return DateTime
     */
    public function getRevisionDate() : DateTime
    {
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
        $this->revisions->rememberRevision();
        return $this;
    }

    /**
     * @return $this
     */
    public function restoreRevision() : self
    {
        $this->revisions->restoreRevision();
        return $this;
    }

    public function revisionExists(int $number) : bool
    {
        return $this->revisions->revisionExists($number);
    }

    // region: Transactions

    protected bool $transactionActive = false;

    /**
     * Starts a modification transaction: does all modifications
     * in a new revision, and only commits the changes if all
     * goes well and if a new revision is required.
     *
     * Throws an exception if a transaction has already been started.
     *
     * @see self::endTransaction()
     * @throws RevisionableException
     * @return $this
     */
    public function startTransaction(int $newOwnerID, string $newOwnerName, ?string $comments = null) : self
    {
        $this->log('Transaction | START | Starting new transaction.');

        $this->logRevisionData();

        if ($this->transactionActive) {
            throw new RevisionableException(
                'Cannot start new transaction',
                'A transaction has been run previously, changes have to be saved or discarded to start a new one.',
                RevisionableStatelessInterface::ERROR_CANNOT_START_TRANSACTION
            );
        }

        $this->clearChangelogQueue();

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

        return $this;
    }
    
   /**
    * Starts a new transaction with the currently authenticated
    * user as the owner of the transaction.
    * 
    * @param string|NULL $comments
    * @return $this
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
    public function endTransaction() : bool
    {
        $this->log('Transaction | END | Ending the transaction.');

        if(!$this->transactionActive) {
            throw new RevisionableException(
                'Cannot end transaction',
                'Cannot end a transaction, no transaction has been started.',
                RevisionableStatelessInterface::ERROR_CANNOT_END_TRANSACTION
            );
        }

        if(!$this->requiresNewRevision)
        {
            return $this->endTransactionWithoutChanges();
        }

        if($this->isSimulationEnabled())
        {
            return $this->endTransactionSimulation();
        }

        return $this->endTransactionWithChanges();
    }

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

    /**
     * Rolls back any new revision added by a transaction. Has no
     * effect if the transaction did not add a new revision.
     *
     * @return $this
     */
    public function rollBackTransaction() : self
    {
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

        return $this;
    }

    protected function logRevisionData() : void
    {
        $this->log('Revision | Author: [%s %s]', $this->getRevisionAuthorID(), $this->getRevisionAuthorName());
        $this->log('Revision | Pretty revision: [%s].', $this->getPrettyRevision());
        $this->log('Revision | Comments: [%s].', $this->getRevisionComments());
        $this->log('Revision | Date: [%s].', $this->getRevisionDate()->format('d.m.Y H:i:s'));
    }

    protected ?TransactionInfo $lastTransaction = null;

    public function hasLastTransactionAddedARevision() : bool
    {
        return $this->getLastAddedRevision() !== null;
    }

    public function getLastAddedRevision() : ?int
    {
        if($this->isTransactionStarted()) {
            throw new RevisionableException(
                'Cannot check for added revision',
                'Cannot check for an added revision while a transaction is still active.',
                RevisionableStatelessInterface::ERROR_CANNOT_GET_ADDED_REVISION_DURING_TRANSACTION
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
            RevisionableStatelessInterface::ERROR_OPERATION_REQUIRES_TRANSACTION
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
            RevisionableStatelessInterface::ERROR_TRANSACTION_CHANGELOG_NOT_EMPTY
        );
    }

    /**
     * Selects the most recent revision of the item.
     */
    public function selectLatestRevision() : self
    {
        return $this->selectRevision($this->getLatestRevision());
    }
    
    public function selectFirstRevision() : self
    {
        return $this->selectRevision($this->getFirstRevision());
    }
    
    public function getFirstRevision() : int
    {
        return $this->revisions->getFirstRevision();
    }
    
    public function lockRevision() : self
    {
        $this->revisions->lock();
        return $this;
    }

    /**
     * @return $this
     */
    public function unlockRevision() : self
    {
        $this->revisions->unlock();
        return $this;
    }
    
    public function isRevisionLocked() : bool
    {
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
        $prev = $this->getPreviousRevision();
        if(!$prev) {
            return false;
        }
        
        $this->selectRevision($prev);
        return true;
    }

    // region Data handling

    public const STORAGE_PART_CUSTOM_KEYS = 'customKeys';
    public const STORAGE_PART_DATA_KEYS = 'revdata';

    public const KEY_TYPE_DATA_KEYS = 'data_keys';
    public const KEY_TYPE_REGULAR = 'standard';

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
        return $this->requiresNewRevision;
    }

    /**
     * Resets the internal changes tracking, for example, after
     * a save operation.
     *
     * @see self::hasChanges()
     * @see self::changesMade()
     */
    protected function resetChanges() : void
    {
        $this->log('Resetting all internal changes.');

        $this->changedParts = array();
    }

    /**
     * Basic save implementation: checks whether any changes
     * were made, and if yes calls the {@link _save()} custom
     * implementation, commits the changelog and resets changes.
     *
     * If any parts have been marked as modified using the
     * {@link setPartChanged()} method, they are saved as well.
     *
     * @see RevisionableStatelessInterface::save()
     * @see _save()
     * @see saveParts()
     */
    public function save() : bool
    {
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
        $this->registerStoragePart(self::STORAGE_PART_DATA_KEYS, Closure::fromCallable(array($this, '_saveDataKeys')));
        $this->registerStoragePart(self::STORAGE_PART_CUSTOM_KEYS, Closure::fromCallable(array($this, '_saveCustomKeys')));
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
                RevisionableStatelessInterface::ERROR_STORAGE_PART_ALREADY_REGISTERED
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
            RevisionableStatelessInterface::ERROR_MISSING_PART_SAVE_METHOD
        );
    }

    /**
     * Object-specific save method: this is where your class
     * must implement its save mechanism.
     */
    abstract protected function _save() : void;

    /**
     * Saves all data keys that are stored in the revdata storage.
     * This is automated and does not need to be handled by the
     * revisionable implementation.
     *
     * Hint: this gets called, because setting a revdata key uses
     * the part named "revdata", and thus gets called by the saveParts
     * method.
     *
     * @see Application_RevisionableStateless::saveParts()
     * @see Application_RevisionableStateless::setDataKey()
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
    */
    public function handle_revisionLoaded(int $number) : void
    {
        
    }

    protected array $changedParts = array();

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
     * @param boolean $structural (Unused for stateless revisionables)
     * @return $this
     * @throws RevisionableException
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

        $this->log('Transaction | Part [%s] has changed.', $part);

        return $this;
    }

    /**
     * Checks whether the specified storage part has been modified.
     *
     * @param string $part
     * @return bool
     * @throws RevisionableException
     */
    public function isPartChanged(string $part) : bool
    {
        $this->requirePartExists($part);

        return isset($this->changedParts[$part]) && $this->changedParts[$part]===true;
    }

    public function getChangedParts() : array
    {
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
            RevisionableStatelessInterface::ERROR_UNKNOWN_STORAGE_PART
        );
    }

    // endregion

    protected ?string $revisionableTypeName = null;
    
    public function getRevisionableTypeName() : string
    {
        if(!isset($this->revisionableTypeName)) {
            $this->revisionableTypeName = getClassTypeName($this);
        }
        
        return $this->revisionableTypeName;
    }
    

    
    public function reload() : void
    {
        $this->revisions->reload();
    }
    
   /**
    * Checks whether a transaction has been started.
    * @return boolean
    */
    public function isTransactionStarted() : bool
    {
        return $this->transactionActive;
    }
    
    public function dispose() : void
    {
        $this->revisions->dispose();
    }

    public function isEditable() : bool
    {
        return !$this->isLocked();
    }

   /**
    * Retrieves the revisionable's first revision date.
    * @return DateTime
    */
    public function getCreationDate() : DateTime
    {
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
        $this->rememberRevision();
        $this->selectFirstRevision();
        $user = $this->getChangelogOwner();
        $this->restoreRevision();
        
        return $user;
    }

    public function getRevisionAuthor() : ?Application_User
    {
        $id = $this->getRevisionAuthorID();
        if($id > 0 && Application::userIDExists($id)) {
            return Application::createUser($id);
        }

        return null;
    }
    
    public function getLockPrimary() : string
    {
        return $this->getRevisionableTypeName().'-'.$this->getID();
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
    protected function setRevisionKey(string $name, $value, string $part, bool $structural, string $changelogID='', array $changelogData=array()): bool
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
    protected function setCustomKey(string $name, $value, bool $structural, string $changelogID='', array $changelogData=array()) : bool
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
     * @throws RevisionableException
     */
    protected function getRevisionKey(string $name)
    {
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
    protected function setDataKey(string $name, $value, bool $structural, string $changelogID='', array $changelogData=array()) : bool
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
    private function _setRevisionKey(string $type, string $name, $value, string $part, bool $structural, string $changelogID='', array $changelogData=array()) : bool
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
    public function getDataKey(string $name, $default=null)
    {
        return $this->revisions->getDataKey($name, $default);
    }

    // region: Event handling

    public const EVENT_TRANSACTION_ENDED = 'TransactionEnded';
    public const EVENT_BEFORE_SAVE = 'BeforeSave';
    public const EVENT_REVISION_ADDED = 'RevisionAdded';

    /**
     * @var array<string,true>
     */
    protected static array $revisionAgnosticEvents = array();

    private function initRevisionEvents() : void
    {
        self::registerRevisionAgnosticEvent(self::EVENT_BEFORE_SAVE);
        self::registerRevisionAgnosticEvent(self::EVENT_REVISION_ADDED);
        self::registerRevisionAgnosticEvent(self::EVENT_TRANSACTION_ENDED);

        $this->revisions->onRevisionAdded(NamedClosure::fromClosure(
            Closure::fromCallable(array($this, 'callback_revisionAdded')),
            array($this, 'callback_revisionAdded')
        ));

        $this->_registerEvents();
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
            return (string)$this->getRevision();
        }

        return null;
    }

    /**
     * Adds a callback to call before the revisionable is saved.
     *
     * This gets a single parameter:
     *
     * - The revisionable instance {@see RevisionableStatelessInterface}.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     */
    public function onBeforeSave(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_BEFORE_SAVE, $callback);
    }

    /**
     * Adds a callback for when a new revision is added to the revisionable.
     *
     * The callback gets the following parameters:
     *
     * 1) The revisionable instance {@see RevisionableStatelessInterface}.
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
     * 1) The revisionable instance {@see RevisionableStatelessInterface}.
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
