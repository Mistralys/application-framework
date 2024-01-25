<?php
/**
 * File containing the {@link Application_RevisionableStateless} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_RevisionableStateless
 */

use AppUtils\ConvertHelper;
use AppUtils\NamedClosure;

/**
 * Base class for stateless revisionable items: provides a
 * method skeleton with basic functionality for data types
 * that can be revisioned, but which do not change state
 * between revisions.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_RevisionableStateless
    implements
    Application_Revisionable_Interface,
    Application_Changelogable_Interface,
    Application_CollectionItemInterface
{
    public const ERROR_CANNOT_START_TRANSACTION = 68437001;
    public const ERROR_INVALID_REVISION_STORAGE = 68437002;
    public const ERROR_CANNOT_END_TRANSACTION =  68437003;
    public const ERROR_OPERATION_REQUIRES_TRANSACTION = 68437004;
    public const ERROR_MISSING_PART_SAVE_METHOD = 68437005;
    public const ERROR_CHANGELOG_FEATURE_NOT_IMPLEMENTED = 68437006;

    use Application_Traits_LockableWithManager;
    use Application_Traits_Disposable;
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

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

        $this->init();
        $this->initialized = true;
    }
    
    protected function init()
    {
        
    }
    
   /**
    * Retrieves the revisionable's ID.
    * @return integer
    */
    abstract public function getID() : int;
    
    public function getInstanceID()
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
        return new Application_RevisionStorage_Memory($this);
    }

    /**
     * Returns the ID of the owner of the currently selected revision.
     * @return int
     * @see getOwnerName()
     */
    public function getOwnerID() : int
    {
        return $this->revisions->getOwnerID();
    }

    /**
     * Returns the name of the owner of the currently selected revision.
     * @return string
     * @see getOwnerID()
     */
    public function getOwnerName()
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
    
    public function getRevision() : int
    {
        return $this->revisions->getRevision();
    }
    
   /**
    * Creates a filter criteria instance for accessing the
    * revisionable's available revisions list.
    * 
    * @return Application_FilterCriteria_RevisionableRevisions
    */
    public function getRevisionsFilterCriteria()
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
     * @throws Application_Exception
     */
    public function getLatestRevision() : int
    {
        return $this->revisions->getLatestRevision();
    }

    /**
    * Retrieves the date the revisionable was last modified.
    * @return DateTime
    */
    public function getLastModifiedDate()
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
    public function getPreviousRevision()
    {
        $current = $this->getRevision();
        $revisions = $this->getRevisions();

        sort($revisions); // make sure they are in ascending order

        $total = count($revisions);
        for ($i = 0; $i < $total; $i++) {
            if ($revisions[$i] == $current) {
                $prevIdx = $i - 1;
                if (isset($revisions[$prevIdx])) {
                    return $revisions[$prevIdx];
                }
                break;
            }
        }

        return null;
    }

    /**
     * (non-PHPdoc)
     * @see Application_Revisionable_Interface::getRevisionTimestamp()
     */
    public function getRevisionTimestamp()
    {
        return $this->revisions->getTimestamp();
    }

    /**
     * Retrieves a DateTime object for the current revision's creation time.
     * @return DateTime
     */
    public function getRevisionDate()
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
     * (non-PHPdoc)
     * @see Application_Revisionable_Interface::rememberRevision()
     */
    public function rememberRevision()
    {
        $this->revisions->rememberRevision();
    }

    /**
     * (non-PHPdoc)
     * @see Application_Revisionable_Interface::restoreRevision()
     */
    public function restoreRevision()
    {
        $this->revisions->restoreRevision();
    }

    public function revisionExists(int $number) : bool
    {
        return $this->revisions->revisionExists($number);
    }

    protected $transactionActive = false;
    
    protected $transactionSource = null;
    
    /**
     * Starts a modification transaction: does all modifications
     * in a new revision, and only commits the changes if all
     * goes well and if a new revision is required.
     *
     * Throws an exception if a transaction has already been started.
     *
     * @see endTransaction()
     * @throws Application_Exception
     * @return $this
     */
    public function startTransaction(int $newOwnerID, string $newOwnerName, ?string $comments = null) : self
    {
        $this->log('Starting new transaction.');

        if ($this->transactionActive) {
            throw new Application_Exception(
                'Cannot start new transaction',
                'A transaction has been run previously, changes have to be saved or discarded to start a new one.',
                self::ERROR_CANNOT_START_TRANSACTION
            );
        }
        
        // store the current revision details, in case we need
        // to restore them later. This is necessary, for example,
        // for revisionables with states, since a new revision
        // is automatically created for the current user. In some
        //  cases, though, when no state change is needed, the owner
        // needs to stay the same. 
        $this->transactionSource = array(
            'author' => $this->getOwnerID(),
            'author_name' => $this->getOwnerName(),
            'comments' => $this->getRevisionComments(),
            'date' => $this->getRevisionDate(),
            'pretty_revision' => $this->getPrettyRevision()
        );
        
        $this->log('Original author: ['.$this->transactionSource['author'].'], ['.$this->transactionSource['author_name'].']');
        $this->log('Original comments: ['.$this->transactionSource['comments'].']');
        $this->log('Original created on: ['.$this->transactionSource['date']->format('d.m.Y H:i:s').']');
        $this->log('Original pretty revision: ['.$this->transactionSource['pretty_revision'].']');
        
        $this->transactionActive = true;
        $this->requiresNewRevision = false;
        $this->transactionSourceRevision = $this->getRevision();
        $this->transactionTargetRevision = $this->revisions->addByCopy($this->transactionSourceRevision, $newOwnerID, $newOwnerName, $comments);
        
        $this->revisions->selectRevision($this->transactionTargetRevision);

        $this->log('New transaction initialized.');

        return $this;
    }
    
   /**
    * Starts a new transaction with the currently authenticated
    * user as owner of the transaction.
    * 
    * @param string $comments
    * @see startTransaction()
    */
    public function startCurrentUserTransaction($comments = '')
    {
        $user = Application::getUser();
        $this->startTransaction($user->getID(), $user->getName(), $comments);
    }

    /**
     * @var int|NULL
     */
    protected $lastTransactionAddedRevision = null;

    /**
     * Ends a transaction by either keeping the new revision
     * if it is required, or by dismissing the new revision but
     * keeping any minor changes.
     *
     * To set whether to add a new revision, the property
     * {@link requiresNewRevision} is used. You can use the
     * utility method {@link changesMade()} in your class to
     * have this set for you.
     *
     * Returns a boolean value indicating whether a new revision
     * has been added in the transaction.
     *
     * @see startTransaction()
     * @return boolean
     */
    public function endTransaction() : bool
    {
        if(!$this->transactionActive) {
            throw new Application_Exception(
                'Cannot end transaction',
                'Cannot end a transaction, no transaction has been started.',
                self::ERROR_CANNOT_END_TRANSACTION
            );
        }

        $this->log('Ending the transaction.');

        // Save the revision data manually here, since there may have
        // been non-structural changes that are not handled automatically.
        $this->revisions->writeRevdata();

        if (!$this->requiresNewRevision)
        {
            $this->log('No new revision was required in the transaction.');
            $this->log('Replacing the existing revision with the temporary revision, keeping the original revision author.');
            $this->revisions->copy(
                $this->transactionTargetRevision, 
                $this->transactionSourceRevision, 
                $this->transactionSource['author'], 
                $this->transactionSource['author_name'], 
                $this->transactionSource['comments'],
                $this->transactionSource['date']
            );

            // Ensure that the revision data is refreshed from the
            // database, now that it has been stored.
            $this->revisions->reload();

            $this->lastTransactionAddedRevision = null;
        }
        else
        {
            $this->log('A new revision was required in the transaction.');
            $this->lastTransactionAddedRevision = $this->transactionTargetRevision;
            $this->revisions->selectRevision($this->transactionTargetRevision);
        }

        $this->requiresNewRevision = false;
        $this->transactionSourceRevision = null;
        $this->transactionTargetRevision = null;
        $this->transactionSource = null;
        $this->transactionActive = false;

        $this->log('Committing changelog');
        $this->commitChangelog();

        $this->log('Transaction ended successfully.');
        
        $this->log(sprintf('Author: [%s %s]', $this->getOwnerID(), $this->getOwnerName()));
        $this->log(sprintf('Pretty revision: [%s].', $this->getPrettyRevision()));
        $this->log(sprintf('Comments: [%s].', $this->getRevisionComments()));
        $this->log(sprintf('Date: [%s].', $this->getRevisionDate()->format('d.m.Y H:i:s')));
        
        return $this->requiresNewRevision;
    }

    /**
     * Rolls back any new revision added by a transaction. Has no
     * effect if the transaction did not add a new revision.
     */
    public function rollBackTransaction()
    {
        if (!$this->transactionActive || !isset($this->lastTransactionAddedRevision)) {
            return;
        }

        $this->log('Rolling back the transaction.');

        $this->revisions->removeRevision($this->lastTransactionAddedRevision);
        $this->lastTransactionAddedRevision = null;
        $this->transactionActive = false;
        $this->revisions->selectLatest();
    }

    /**
     * Utility method to keep track of internal changes when
     * using transactions. Sets the {@link $requiresNewRevision}
     * property to true to trigger a new revision when ending
     * the current transaction.
     *
     * Call this method every time you have made changes for
     * which a new revision should be triggered.
     *
     * @see hasChanges()
     * @see resetChanges()
     */
    protected function changesMade()
    {
        if ($this->requiresNewRevision === true) {
            return;
        }

        $this->requiresNewRevision = true;
        $this->log('A change was made.');
    }

    /**
     * Checks whether this item has structural changes.
     * @return boolean
     * @see resetChanges()
     * @see changesMade()
     */
    public function hasChanges()
    {
        return $this->requiresNewRevision;
    }

    /**
     * Resets the internal changes tracking, for example after
     * a save operation.
     *
     * @see hasChanges()
     * @see changesMade()
     */
    protected function resetChanges()
    {
        $this->log('Resetting all internal changes.');
        
        $this->requiresNewRevision = false;
        $this->changedParts = array();
    }

    /**
     * Selects the most recent revision of the item.
     */
    public function selectLatestRevision()
    {
        $this->selectRevision($this->getLatestRevision());
    }
    
    public function selectFirstRevision()
    {
        $this->selectRevision($this->getFirstRevision());
    }
    
    public function getFirstRevision()
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
    * Selects the revision prior to the currently selected revision,
    * if any exists.
    * 
    * @return boolean Whether a previous revision existed and was selected
    */
    public function selectPreviousRevision()
    {
        $prev = $this->getPreviousRevision();
        if(!$prev) {
            return false;
        }
        
        $this->selectRevision($prev);
        return true;
    }

    protected $changelogQueue = array();

    /**
     * Adds a changelog entry to the pending queue. Nothing is
     * actually modified yet, it is just added internally to be
     * committed later if the user wishes to commit his changes.
     *
     * @param string $type
     * @param mixed $data The data to store (JSON encoded automatically)
     */
    protected function enqueueChangelog($type, $data = null)
    {
        if (!$this->changelogEnabled) {
            return;
        }

        $this->log('Changelog | '.$type);

        $this->changelogQueue[] = array(
            'type' => $type,
            'data' => $data
        );
    }

    /**
     * Commits the changelog queue. Extend this in your class if it 
     * needs a specific implementation.
     */
    protected function commitChangelog()
    {
        $this->getChangelog()->commitQueue();
    }

    /**
     * Whether changelog entries should be added for this
     * product type instance.
     *
     * @var boolean
     * @see enableChangelog()
     * @see disableChangelog()
     */
    protected $changelogEnabled = true;

    /**
     * Enables the changelog again after disabling it 
     * using the {@link disableChangelog()} method.
     *
     * @see disableChangelog()
     * @see addChangelog()
     */
    public function enableChangelog()
    {
        return $this->setChangelogEnabled(true);
    }

    /**
     * Disables the changelog. This is useful in cases where some 
     * operations should not be added to the changelog.
     *
     * @see enableChangelog()
     * @see addChangelog()
     */
    public function disableChangelog()
    {
        return $this->setChangelogEnabled(false);
    }

    /**
    * Enables or disables the changelog.
    * @param boolean $enabled
    */
    public function setChangelogEnabled($enabled=true)
    {
        if($enabled !== true) {
            $enabled = false;
        }
        
        if($this->changelogEnabled === $enabled) {
            return;
        }
        
        if($enabled !== true) {
            $this->log('Changelog | Disabling the changelog. New entries will be ignored.');
        } else {
            $this->log('Changelog | Enabling the changelog.');
        }
        
        $this->changelogEnabled = $enabled;
    }
    
   /**
    * Checks whether the changelog is currently enabled.
    * @return boolean
    */
    public function isChangelogEnabled()
    {
        return $this->changelogEnabled;
    }

    /**
     * Basic save implementation: checks whether any changes
     * were made, and if yes calls the {@link _save()} custom
     * implementation, commits the changelog and resets changes.
     *
     * If any parts have been marked as modified using the
     * {@link setPartChanged()} method, they are saved as well.
     *
     * @see Application_Revisionable_Interface::save()
     * @see _save()
     * @see saveParts()
     */
    public function save()
    {
        $this->log('SAVE!');
        
        if (!$this->hasChanges()) {
            return false;
        }

        $this->_save();
        $this->saveParts();

        $this->resetChanges();

        return true;
    }
    
   /**
    * Saves all individual parts of the revisionable item
    * that have been marked as changed using the {@link setPartChanged()}
    * method. Called automatically when the revisionable 
    * is saved.
    * 
    * @throws Application_Exception
    */
    protected function saveParts()
    {
        $this->log('Saving parts that have been set as changed.');
        
        foreach($this->changedParts as $part => $changed) {
            if(!$changed) {
                continue;
            }
            
            $method = '_savePart_'.$part;
            if(!method_exists($this, $method)) {
                throw new Application_Exception(
                    'Unknown revisionable part',
                    sprintf(
                        'Tried saving part [%s], but the revisionable does not implement the [%s] method.',
                        $part,
                        $method    
                    ),
                    self::ERROR_MISSING_PART_SAVE_METHOD
                );
            }

            $this->log(sprintf('Saving part [%s].', $part));
            $this->$method();
        }
        
        $this->log('Saving parts done.');
    }

    /**
     * Object-specific save method: this is where your class
     * must implement its save mechanism.
     */
    abstract protected function _save();

   /**
    * This is called by the revisionable storage when a new
    * revision has been loaded. Can be extended to add any
    * relevant custom implementations.
    */
    public function handle_revisionLoaded($number)
    {
        
    }
    
    protected $simulation;
    
   /**
    * Enables/disables the simulation mode: in this mode, transactions are
    * not committed.
    * 
    * @param boolean $active 
    * @see isSimulation()
    * @return Application_RevisionableStateless
    */
    public function setSimulation($active=true)
    {
        $this->simulation = $active;
        return $this;
    }
    
   /**
    * Whether the simulation mode is currently active.
    * @return boolean
    */
    public function isSimulation()
    {
        return $this->simulation;   
    }
    
    protected $changedParts = array();
    
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
    */
    protected function setPartChanged($part, $structural=false)
    {
        if (!$this->isTransactionStarted()) {
            throw new Application_Exception(
                'No transaction started',
                sprintf(
                    '%s [%s v%s] instance [%s]: Tried to set part [%s] as modified without starting a transaction.',
                    get_class($this),
                    $this->getID(),
                    $this->getRevision(),
                    $this->getInstanceID(),
                    $part
                ),
                self::ERROR_OPERATION_REQUIRES_TRANSACTION
            );
        }
        
        if(isset($this->changedParts[$part]) && $this->changedParts[$part]===true) {
            return;
        }
        
        $this->changesMade();
        
        $this->changedParts[$part] = true;
        
        $this->log(sprintf('Part [%s] has changed.', $part));
    }
    
   /**
    * Checks whether the specified part has changes.
    * 
    * @param string $part
    * @return boolean
    */
    protected function hasPartChanged($part)
    {
        if(isset($this->changedParts[$part])) {
            return $this->changedParts[$part];
        }
        
        return false;
    }
    
    protected ?string $revisionableTypeName = null;
    
    public function getRevisionableTypeName() : string
    {
        if(!isset($this->revisionableTypeName)) {
            $tokens = explode('_', get_class($this));
            $this->revisionableTypeName = array_pop($tokens);
        }
        
        return $this->revisionableTypeName;
    }
    
    public function getChangelogEntryText(string $type, array $data=array()) : string
    {
        return '';
    }
    
    protected int $changelogStringMaxlen = 40;
    
   /**
     * Formats a string for use in changelog text placeholders.
     * Cuts them to a specific size if too long, and converts
     * all special characters to HTML entities.
     *
     * @param string $string
     * @param integer $maxlen If set to 0, use the global length setting, see {@link $changelogStringMaxlen}.
     * @return string String with HTML code
     */
    protected function formatForChangelog($string, $maxlen=0)
    {
        if($maxlen < 1) {
            $maxlen = $this->changelogStringMaxlen;
        }
        
        $result = '';
        
        if(mb_strlen($string) > $maxlen) 
        {
            $result = mb_substr($string, 0, $maxlen);
            $result = '<b>'.htmlentities($result).'</b>';
            $result .= ' <span class="muted">[...]</span>';
        } 
        else 
        {
            $result = '<b>'.htmlentities($string).'</b>';
        }
        
        return $result;
    }
    
   /**
    * Retrieves the current changelog queue of the revisionable
    * item. This is an indexed array with the following structure:
    * 
    * <pre>
    * array(
    *     array(
    *         'type' => 'changelog_type',
    *         'data' => [mixed]
    *     ),
    *     ...
    * )
    * 
    * @see Application_Changelogable_Interface::getChangelogQueue()
    */
    public function getChangelogQueue()
    {
        return $this->changelogQueue;
    }

    /**
     * Retrieves a list of all the changelog operation types
     * that have been queued until now.
     *
     * @return string[]
     */
    public function getChangelogQueueTypes() : array
    {
        $result = array();

        foreach($this->changelogQueue as $entry)
        {
            $result[] = $entry['type'];
        }

        return $result;
    }
    
    public function getChangelogTable()
    {
        throw new Application_Exception(
            'Not implemented',
            sprintf(
                'The changelog handling feature is not configured for [%s] revisionables.',
                $this->getRevisionableTypeName()
            ),
            self::ERROR_CHANGELOG_FEATURE_NOT_IMPLEMENTED    
        );
    }
    
    /**
     * Retrieves the values for the item's primary key in the
     * changelog table. Must include the item's revision.
     *
     * @return array
     */
    public function getChangelogItemPrimary()
    {
        throw new Application_Exception(
            'Not implemented',
            sprintf(
                'The changelog handling feature is not configured for [%s] revisionables.',
                $this->getRevisionableTypeName()
            ),
            self::ERROR_CHANGELOG_FEATURE_NOT_IMPLEMENTED
        );
    }
    
    public function getChangelogOwner()
    {
        return Application::getUser()->createByID($this->getOwnerID());
    }
    
   /**
    * Retrieves the changelog for the current revision.
    * @return Application_Changelog
    */
    public function getChangelog()
    {
        require_once 'Application/Changelog.php';
        
        $changelog = $this->revisions->getKey('__changelog');
        if(!$changelog instanceof Application_Changelog) {
            $changelog = new Application_Changelog($this);
            $this->revisions->setKey('__changelog', $changelog);
        }
        
        return $changelog;
    }
    
    public function reload()
    {
        $this->revisions->reload();
    }
    
   /**
    * Counts the amount of changelog entries in the current revision.
    * @return integer
    */
    public function countChangelogEntries()
    {
        return $this->getChangelog()->countEntries(); 
    }

    public function getChangelogEntryDiff(string $type, array $data=array()) : ?array
    {
        return null;
    }
    
    public function getChangelogTypeLabel($type)
    {
        return $type;
    }
    
   /**
    * Checks whether a transaction has been started.
    * @return boolean
    */
    public function isTransactionStarted()
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
    * Gives the possibility to adjust the changelog query further if
    * needed, for example by adding joins or additional where 
    * statements.
    * 
    * @param Application_Changelog_FilterCriteria $filters
     */
    public function configureChangelogFilters(Application_Changelog_FilterCriteria $filters)
    {
          
    }

   /**
    * Retrieves the revisionable's first revision date.
    * @return DateTime
    */
    public function getCreationDate()
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
    public function getCreator()
    {
        $this->rememberRevision();
        $this->selectFirstRevision();
        $user = $this->getChangelogOwner();
        $this->restoreRevision();
        
        return $user;
    }
    
    public function getLockPrimary() : string
    {
        return $this->getRevisionableTypeName().'-'.$this->getID();
    }
    
    /**
     * Sets a revision key value.
     *
     * @param string $name The key name
     * @param mixed $value The key value
     * @param string $part The part that the key is a member of. Will be set as changed if the value is different.
     * @param bool $structural Whether the key is structural an requires a state change.
     * @param string $changelogID The changelog ID to use for adding a standardized changelog entry
     * @param array $changelogData Any data that should be stored alongside the changelog entry.
     * @return boolean Whether the value has changed, and a save will be needed.
     */
    protected function setRevisionKey(string $name, $value, string $part, bool $structural, string $changelogID='', array $changelogData=array())
    {
        return $this->_setRevisionKey('default', $name, $value, $part, $structural, $changelogID);
    }
    
    /**
     * Sets a revision key value in the revdata storage.
     *
     * @param string $name The key name
     * @param mixed $value The key value
     * @param bool $structural Whether the key is structural an requires a state change.
     * @param string $changelogID The changelog ID to use for adding a standardized changelog entry
     * @param array $changelogData Any data that should be stored alongside the changelog entry.
     * @return boolean Whether the value has changed, and a save will be needed.
     */
    protected function setRevdataKey(string $name, $value, bool $structural, string $changelogID='', array $changelogData=array())
    {
        return $this->_setRevisionKey('revdata', $name, $value, 'revdata', $structural, $changelogID, $changelogData);
    }
    
   /**
    * Sets a revision key, either via the regular revision data, or the revdata storage.
    * 
    * @param string $type The storage type: "default" or "revdata".
    * @param string $name The key name
    * @param mixed $value The key value
    * @param string $part The part that the key is a member of. Will be set as changed if the value is different.
    * @param bool $structural Whether the key is structural an requires a state change.
    * @param string $changelogID The changelog ID to use for adding a standardized changelog entry.
    * @param array $changelogData Any data that should be stored alongside the changelog entry.
    * @return boolean Whether the value has changed, and a save will be needed.
    */
    private function _setRevisionKey(string $type, string $name, $value, string $part, bool $structural, string $changelogID='', array $changelogData=array())
    {
        $this->requireTransaction();
        
        $revdata = $type == 'revdata';
        
        if($revdata) {
            $old = $this->revisions->getRevdataKey($name);
        } else {
            $old = $this->revisions->getKey($name);
        }
        
        if(AppUtils\ConvertHelper::areVariablesEqual($old, $value)) {
            return false;
        }
        
        if($value === '') { $value = null; }
        if($old === '') { $old = null; }
        
        $this->setPartChanged($part, $structural);
        
        if($revdata) {
            $this->revisions->setRevdataKey($name, $value);
        } else {
            $this->revisions->setKey($name, $value);
        }
        
        if(!empty($changelogID))
        {
            $this->enqueueChangelog(
                $changelogID,
                array_merge(
                    $changelogData,
                    array(
                        'old' => $old,
                        'new' => $value
                    )
                )
            );
        }
        
        return true;
    }
    
   /**
    * Retrieves a previously set revision key value from
    * the revdata storage.
    * 
    * @param string $name
    * @param mixed $default
    * @return mixed
    */
    public function getRevdataKey($name, $default=null)
    {
        return $this->revisions->getRevdataKey($name, $default);
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
    * @see Application_RevisionableStateless::saveParts()
    * @see Application_RevisionableStateless::setRevdataKey()
    */
    protected function _savePart_revdata()
    {
        // contrary to other revisionable data, this is
        // standardized and can be saved directly by the
        // revision storage itself: this is because the key names
        // can be used directly without mapping them internally
        // to something like a database column.
        
        $this->revisions->writeRevdata();
    }
    
   /**
    * Ensures that a transaction is active for operations that
    * may only be done within a transaction.
    */
    public function requireTransaction()
    {
    }

    // region: Event handling

    const REVISION_KEY_EVENT_HANDLERS = '__eventHandlers';
    const EVENT_BEFORE_SAVE = 'BeforeSave';
    const EVENT_REVISION_ADDED = 'RevisionAdded';
    const REVISION_KEY_IGNORED_EVENTS = '__ignored_events';

    /**
     * @var array<string,true>
     */
    protected static $revisionAgnosticEvents = array();

    /**
     * @var array<string,bool>
     */
    protected $agnosticIgnores = array();

    private function initRevisionEvents() : void
    {
        self::registerRevisionAgnosticEvent(self::EVENT_BEFORE_SAVE);
        self::registerRevisionAgnosticEvent(self::EVENT_REVISION_ADDED);

        $callback = array($this, 'callback_revisionAdded');

        $this->revisions->onRevisionAdded(NamedClosure::fromClosure(
            Closure::fromCallable($callback),
            ConvertHelper::callback2string($callback)
        ));

        $this->_registerEvents();
    }

    private function callback_revisionAdded(Application_RevisionStorage_Event_RevisionAdded $event) : void
    {
        $event = new Application_Revisionable_Event_RevisionAdded($this, $event);

        $this->triggerEvent(
            self::EVENT_REVISION_ADDED,
            array($event)
        );
    }

    abstract protected function _registerEvents() : void;

    /**
     * Registers the name of an event that is not revision-
     * specific, and can be triggered regardless of the
     * currently selected revision.
     *
     * @param string $name
     */
    protected static function registerRevisionAgnosticEvent(string $name) : void
    {
        self::$revisionAgnosticEvents[$name] = true;
    }

    /**
     * Checks if the specified event name is not revision specific.
     *
     * @param string $name
     * @return bool
     */
    public function isEventRevisionAgnostic(string $name) : bool
    {
        return isset(self::$revisionAgnosticEvents[$name]);
    }

    /**
     * @param string $name
     * @param array $args Indexed array of arguments for the callback function/method.
     */
    protected function triggerEvent(string $name, array $args=array()) : void
    {
        $handlers = $this->getRevisionEventHandlers($name);

        if(!isset($handlers[$name]))
        {
            $this->log(sprintf('Event [%s] | Ignoring, no listeners found.', $name));
            return;
        }

        if($this->isEventIgnored($name))
        {
            $this->log(sprintf('Event [%s] | On the ignore list, ignoring.', $name));
            return;
        }

        $this->log(sprintf('Event [%s] | Listeners found, calling them...', $name));

        if(!is_array($args))
        {
            $args = array($args);
        }

        array_unshift($args, $this);

        foreach($handlers[$name] as $handler)
        {
            call_user_func_array($handler, $args);
        }

        $this->log(sprintf('Event [%s] | Done', $name));
    }

    /**
     * @return array<string,bool>
     */
    private function getRevisionIgnoredEvents(string $name) : array
    {
        if($this->isEventRevisionAgnostic($name))
        {
            return $this->agnosticIgnores;
        }

        $events = $this->revisions->getKey(self::REVISION_KEY_IGNORED_EVENTS);
        if(is_array($events)) {
            return $events;
        }

        return array();
    }

    /**
     * @param array<string,bool> $eventNames
     * @return $this
     * @throws Application_Exception
     */
    private function setRevisionIgnoredEvents(string $targetEvent, array $eventNames)
    {
        if($this->isEventRevisionAgnostic($targetEvent))
        {
            $this->agnosticIgnores = $eventNames;
            return $this;
        }

        $this->revisions->setKey(self::REVISION_KEY_IGNORED_EVENTS, $eventNames);
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     * @throws Application_Exception
     */
    protected function ignoreEvent(string $name)
    {
        $events = $this->getRevisionIgnoredEvents($name);
        $events[$name] = true;

        $this->setRevisionIgnoredEvents($name, $events);

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     * @throws Application_Exception
     */
    protected function unignoreEvent(string $name)
    {
        $events = $this->getRevisionIgnoredEvents($name);

        if(isset($events[$name])) {
            unset($events[$name]);
        }

        return $this->setRevisionIgnoredEvents($name, $events);
    }

    public function isEventIgnored(string $name) : bool
    {
        $events = $this->getRevisionIgnoredEvents($name);

        return isset($events[$name]);
    }

    /**
     * Adds a callback to call before the revisionable is saved.
     *
     * This gets a single parameter:
     *
     * - The revisionable instance {@see Application_Revisionable_Interface}.
     *
     * @param callable $callback
     * @return $this
     * @throws Application_Exception
     */
    public function onBeforeSave(callable $callback)
    {
        return $this->addEventHandler(self::EVENT_BEFORE_SAVE, $callback);
    }

    /**
     * Adds a callback for when a new revision is added to the revisionable.
     *
     * The callback gets the following parameters:
     *
     * 1) The revisionable instance {@see Application_Revisionable_Interface}.
     * 2) The event instance {@see Application_Revisionable_Event_RevisionAdded}.
     *
     * @param callable $callback
     * @return $this
     * @throws Application_Exception
     * @see Application_Revisionable_Event_RevisionAdded
     */
    public function onRevisionAdded(callable $callback)
    {
        return $this->addEventHandler(self::EVENT_REVISION_ADDED, $callback);
    }

    /**
     * Adds an event handler for the specified event. The callback
     * always gets the revisionable instance as first parameter,
     * and any additional custom event parameters afterwards.
     *
     * @param string $eventName
     * @param callable $callback
     * @throws Application_Exception
     * @return $this
     */
    protected function addEventHandler(string $eventName, callable $callback)
    {
        $handlers = $this->getRevisionEventHandlers($eventName);

        if(!isset($handlers[$eventName])) {
            $handlers[$eventName] = array();
        }

        $this->log(sprintf('Event [%s] | Added a handler.', $eventName));

        $handlers[$eventName][] = $callback;

        return $this->setRevisionEventHandlers($eventName, $handlers);
    }

    /**
     * @var array<string,callable[]>
     */
    protected $revisionAgnosticHandlers = array();

    /**
     * @return array<string,callable[]>
     */
    private function getRevisionEventHandlers(string $eventName) : array
    {
        if($this->isEventRevisionAgnostic($eventName))
        {
            return $this->revisionAgnosticHandlers;
        }

        $handlers = $this->revisions->getKey(self::REVISION_KEY_EVENT_HANDLERS);

        if(!empty($handlers)) {
            return $handlers;
        }

        return array();
    }

    /**
     * @param string $eventName
     * @param array<string,callable[]> $handlers
     * @return $this
     * @throws Application_Exception
     */
    private function setRevisionEventHandlers(string $eventName, array $handlers)
    {
        if($this->isEventRevisionAgnostic($eventName))
        {
            $this->revisionAgnosticHandlers = $handlers;
            return $this;
        }

        $this->revisions->setKey(self::REVISION_KEY_EVENT_HANDLERS, $handlers);
        return $this;
    }

    // endregion
}
