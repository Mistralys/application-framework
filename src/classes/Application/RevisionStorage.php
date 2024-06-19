<?php
/**
 * File containing the {@link Application_RevisionStorage} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_RevisionStorage
 */

declare(strict_types=1);

use Application\Revisionable\RevisionableException;
use Application\Revisionable\RevisionDependentInterface;
use Application\RevisionStorage\Copy\BaseRevisionCopy;
use Application\RevisionStorage\RevisionStorageException;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use AppUtils\TypeFilter\StrictType;
use testsuites\Traits\RenderableTests;

/**
 * Utility class for storing revision data: stores data sets
 * by revision number, and allows selecting revisions / switching
 * between revisions to retrieve revision-specific data.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @implements ArrayAccess<string,mixed>
 */
abstract class Application_RevisionStorage
    implements
    ArrayAccess,
    Application_Interfaces_Eventable,
    Application_Interfaces_Disposable
{
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;
    use Application_Traits_Disposable;

    public const DATA_KEY_MAX_LENGTH = 180;
    
    public const ERROR_REVISION_DOES_NOT_EXIST = 15557001;
    public const ERROR_COPYTO_NOT_IMPLEMENTED = 15557002;
    public const ERROR_CANNOT_SET_KEY_UNKNOWN_REVISION = 15557003;
    public const ERROR_CANNOT_SET_KEYS_UNKNOWN_REVISION = 15557004;
    public const ERROR_INVALID_KEY_LOADER_CALLBACK = 15557005;
    public const ERROR_INVALID_COPY_REVISION_CLASS = 15557006;
    public const ERROR_NO_REVISIONS_AVAILABLE = 15557007;
    public const ERROR_REVISION_REQUIRED = 15557008;
    public const ERROR_NO_REVISION_REMEMBERED = 15557009;
    public const ERROR_KEY_REVISION_UNKNOWN = 15557010;
    public const ERROR_CANNOT_REMOVE_PRIOR_REVISION = 15557011;
    public const ERROR_CANNOT_REMOVE_LAST_REVISION = 15557012;

    public const KEY_OWNER_ID = 'ownerID';
    public const KEY_OWNER_NAME = 'ownerName';
    public const PRIVATE_KEY_PREFIX = '__';

    /**
    * @var array<int,array<string,mixed>>
    */
    protected array $data = array();

   /**
    * @var array<string,mixed>
    */
    protected array $defaults = array();

    protected ?int $revision = null;

   /**
    * @var integer[]
    */
    protected array $revisionsToRemember = array();

    protected Application_RevisionableStateless $revisionable;
    protected int $revisionable_id;
    
   /**
    * @var array<string,callable>
    */
    protected array $keyLoaders = array();
    
   /**
    * @var boolean
    */
    protected bool $locked = false;
    
   /**
    * @var array<string,mixed>
    */
    protected array $staticColumns = array();
    
   /**
    * @var array<int,array<string,mixed>>
    */
    protected array $dataKeys = array();
    
    public function __construct(Application_RevisionableStateless $revisionable)
    {
        $this->revisionable = $revisionable;
        $this->revisionable_id = $revisionable->getID();
        $this->logName = ucfirst($this->revisionable->getRevisionableTypeName());
        $this->configure();
    }

    public function getRevisionable() : Application_RevisionableStateless
    {
        return $this->revisionable;
    }
    
    protected function configure() : void
    {
    
    }
    
   /**
    * Sets the defaults for a range of data keys.
    * 
    * @param array<string,mixed> $defaults
    * @return $this
    */
    public function setKeyDefaults(array $defaults) : self
    {
        $this->defaults = $defaults;
        return $this;
    }

   /**
    * Adds a new revision.
    * 
    * @param integer $number The revision number.
    * @param integer $ownerID The ID of the user that is the author of the revision.
    * @param string $ownerName The name of the user. 
    * @param int|NULL $timestamp
    * @param string|NULL $comments
    */
    public function addRevision(int $number, int $ownerID, string $ownerName, ?int $timestamp = null, ?string $comments = null) : void
    {
        if ($timestamp === null)
        {
            $timestamp = time();
        }

        $this->data[$number] = array(
            '__timestamp' => $timestamp, // the time the revision was created
            self::KEY_OWNER_ID => $ownerID,
            self::KEY_OWNER_NAME => $ownerName,
            '__comments' => (string)$comments
        );

        $this->triggerRevisionAdded($number, $timestamp, $ownerID, $ownerName, $comments);
    }

   /**
    * @return integer
    */
    abstract public function countRevisions() : int;

   /**
    * Stores the current revision number, so it can be
    * restored later using {@link restoreRevision()}.
    * This is useful if you have to select other revisions,
    * but want to restore the originally selected one
    * afterwards.
    *
    * @return $this
    * @see restoreRevision()
    */
    public function rememberRevision() : self
    {
        $this->revisionsToRemember[] = $this->getRevision();
        return $this;
    }

   /**
    * Selects the revision that was flagged to be remembered
    * earlier using {@link rememberRevision()}. Throws an
    * exception if no revision was previously remembered.
    *
    * @return $this
    * @throws Exception
    * @see rememberRevision()
    */
    public function restoreRevision() : self
    {
        if (empty($this->revisionsToRemember)) {
            throw new RevisionableException(
                'Cannot restore revision, no revision was selected to remember.',
                '',
                self::ERROR_NO_REVISION_REMEMBERED
            );
        }

        $revNumber = array_pop($this->revisionsToRemember);

        $this->selectRevision($revNumber);

        return $this;
    }

    /**
     * @return string
     * @throws RevisionableException
     */
    public function getComments() : string
    {
        return (string)$this->getKey('__comments');
    }

    /**
     * Sets the revision comments.
     *
     * @param string|NULL $comments
     * @return $this
     * @throws RevisionableException
     */
    public function setComments(?string $comments) : self
    {
        return $this->setKey('__comments', $comments);
    }

    /**
     * Selects the specified revision number, loading
     * it if it has not been loaded yet. Has no effect
     * if it is already the active version.
     *
     * Note: this will fail silently if revision switching
     * is locked using the {@link lock()} method.
     *
     * @param int $number
     * @return Application_RevisionStorage
     * @throws Application_Exception
     * @see lock()
     * @see unlock()
     * @see isLocked()
     */
    public function selectRevision(int $number) : self
    {
        if($this->locked) {
            return $this;
        }
        
        if($this->revision === $number) {
            return $this;
        }
        
        if (!isset($this->loadedRevisions[$number])) {
            $this->loadRevision($number);
        } else {
            $this->log(sprintf(
                'Switching from [%s] to [%s].',
                $this->revision,
                $number
            ));
        }
        
        $this->revision = $number;
        return $this;
    }

   /**
    * @var array<integer,bool>
    */
    protected array $loadedRevisions = array();

   /**
    * @param integer $number
    * @return bool
    */
    public function isLoaded(int $number) : bool
    {
        return isset($this->loadedRevisions[$number]);
    }

   /**
    * @var bool|NULL
    */
    protected ?bool $hasDataKeys = null;
    
   /**
    * @return boolean
    */
    public function hasDataKeys() : bool
    {
        if(!isset($this->hasDataKeys)) {
            $this->hasDataKeys = $this->_hasDataKeys();
        }
        
        return $this->hasDataKeys;
    }
    
   /**
    * @return bool
    */
    abstract protected function _hasDataKeys() : bool;
    
   /**
    * @param int $number
    * @throws Application_Exception
    */
    protected function loadRevision(int $number) : void
    {
        if(isset($this->loadedRevisions[$number]) && $this->loadedRevisions[$number] === true)
        {
            return;
        }
        
        if (!$this->revisionExists($number)) 
        {
            throw new Application_Exception(
                'Revision does not exist',
                sprintf(
                    'Tried selecting the revision [%1$s], but it does not exist.',
                    $number
                ),
                self::ERROR_REVISION_DOES_NOT_EXIST
            );
        }
        
        // set it as loaded
        $this->loadedRevisions[$number] = true;
        
        // make this the active revision, so we can work with it
        $this->revision = $number;

        $this->_loadRevision($number);

        $this->revisionable->handle_revisionLoaded($number);
    }
    
   /**
    * Loads the data for the specified revision.
    * 
    * @param int $number
    */
    abstract protected function _loadRevision(int $number) : void;

    public function selectLatest() : self
    {
        return $this->selectRevision($this->getLatestRevision());
    }

   /**
    * Whether the specified revision is the one currently selected.
    * 
    * @param int $number
    * @return boolean
    */
    public function isSelected(int $number) : bool
    {
        return $this->revision === $number;
    }

   /**
    * Checks whether the specified revision exists in storage.
    * 
    * @param int $number
    * @param bool $forceLiveCheck Do not use any cache, check the live system.
    * @return bool
    */
    abstract public function revisionExists(int $number, bool $forceLiveCheck=false) : bool;

   /**
    * Whether any revisions are available.
    * 
    * @return boolean
    */
    public function hasRevisions() : bool
    {
        return $this->countRevisions() > 0;
    }
    
   /**
    * Clears/removes/deletes a key. Has no effect if the key
    * did not exist to begin with.
    * 
    * @param string $name
    * @return boolean Whether the key existed
    */
    public function clearKey(string $name) : bool
    {
        $revision = $this->getRevision();
        
        if(isset($this->data[$revision][$name])) {
            unset($this->data[$revision][$name]);
            return true;
        }
        
        return false;
    }

    public function clearPrivateKey(string $name) : bool
    {
        return $this->clearKey($this->resolvePrivateKey($name));
    }

   /**
    * Sets a key to the specified value. Any existing values are
    * overwritten, and if the key did not exist, it is created.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    * @throws RevisionableException
    */
    public function setKey(string $name, $value) : self
    {
        $revision = $this->getRevision();

        if (!isset($this->data[$revision])) {
            throw new RevisionableException(
                'Cannot set key for unknown revision',
                sprintf(
                    'Tried setting the key [%s] in revision [%s], but that revision is not present.',
                    $name,
                    $revision    
                ),
                self::ERROR_CANNOT_SET_KEY_UNKNOWN_REVISION
            );
        }

        $this->data[$revision][$name] = $value;

        return $this;
    }

   /**
    * Sets a range of data keys at once.
    * 
    * @param array<string,mixed> $keys
    * @throws RevisionableException
    */
    public function setKeys(array $keys) : self
    {
        $revision = $this->getRevision();
        
        if (!isset($this->data[$revision])) 
        {
            throw new RevisionableException(
                'Cannot set key for unknown revision',
                sprintf(
                    'Tried setting the keys [%s] in revision [%s], but that revision is not present.',
                    implode(', ', array_keys($keys)),
                    $revision    
                ),
                self::ERROR_CANNOT_SET_KEYS_UNKNOWN_REVISION
            );
        }

        foreach ($keys as $key => $value) 
        {
            $this->setKey($key, $value);
        }

        return $this;
    }

    /**
     * Sets a callback function to use for automatically populating
     * a key value when it is accessed. It is only called when the
     * key is not set, so it can be modified as per usual.
     *
     * @param string $key
     * @param callable $callback
     * @return $this
     * @throws Application_Exception
     */
    public function setKeyLoader(string $key, callable $callback) : self
    {
        Application::requireCallableValid($callback, self::ERROR_INVALID_KEY_LOADER_CALLBACK);

        $this->keyLoaders[$key] = $callback;

        return $this;
    }

    /**
     * Adds a new revision by copying the data from the
     * specified revision. Returns the new revision number.
     * If the source revision does not exist, an exception
     * is triggered.
     *
     * All reference types get cloned to avoid reference
     * issues in the data keys.
     *
     * @param int $sourceRevision
     * @param int $ownerID
     * @param string $ownerName
     * @param string|NULL $comments
     * @return int
     * @throws InvalidArgumentException|Application_Exception
     */
    public function addByCopy(int $sourceRevision, int $ownerID, string $ownerName, ?string $comments=null) : int
    {
        $this->log('Adding revision by copy.');

        $newRev = $this->nextRevision($ownerID, $ownerName, $comments);

        $this->selectRevision($sourceRevision);
        $this->copy($sourceRevision, $newRev, $ownerID, $ownerName, $comments);

        if(!$this->revisionExists($newRev, true)) {
            throw new RevisionableException(
                'Copy operation failed to create the target revision.',
                sprintf(
                    'Tried adding a new revision [%s] by copying [%s], but the new revision does not exist.',
                    $newRev,
                    $sourceRevision
                ),
                self::ERROR_REVISION_DOES_NOT_EXIST
            );
        }

        $this->log('Switching to new revision [%s].', $newRev);

        $this->selectRevision($newRev);

        // Ensure that we're using the correct author information.
        $this->setOwnerID($ownerID);
        $this->setOwnerName($ownerName);
        $this->setComments($comments);

        return $newRev;
    }

    /**
     * Copies the data from the source revision to the
     * target revision number. Uses the specialized
     * revision copy class instance by default.
     *
     * @param int $sourceRevision
     * @param int $targetRevision
     * @param int $targetOwnerID
     * @param string $targetOwnerName
     * @param string|NULL $targetComments
     * @param DateTime|NULL $targetDate
     * @return Application_RevisionStorage
     * @throws RevisionableException
     */
    public function copy(int $sourceRevision, int $targetRevision, int $targetOwnerID, string $targetOwnerName, ?string $targetComments, ?DateTime $targetDate=null) : self
    {
        $copy = $this->createCopyRevision(
            $sourceRevision,
            $targetRevision,
            $targetOwnerID,
            $targetOwnerName,
            $targetComments,
            $targetDate
        );

        $copy->process();

        return $this;
    }

    /**
     * Removes a revision. Note that only the latest
     * revision may be removed. If you wish to remove
     * an earlier revision, you will need to remove all
     * revisions that came after it.
     *
     * @param int $number
     * @throws Application_Exception
     * @return $this
     */
    public function removeRevision(int $number) : self
    {
        $this->log(sprintf('Removing revision [%1$s].', $number));
        
        if ($number !== $this->getLatestRevision()) {
            throw new RevisionStorageException(
                'Cannot remove a revision prior to the latest revision.',
                sprintf(
                    'Tried removing revision [%s], but the latest revision is [%s].',
                    $number,
                    $this->getLatestRevision()
                ),
                self::ERROR_CANNOT_REMOVE_PRIOR_REVISION
            );
        }

        if($this->countRevisions() === 1) {
            throw new RevisionStorageException(
                'Cannot remove the last available revision.',
                'Tried removing the last available revision, which is not allowed.',
                self::ERROR_CANNOT_REMOVE_LAST_REVISION
            );
        }

        $this->collectChildDisposables();

        $this->_removeRevision($number);

        if (isset($this->loadedRevisions[$number])) {
            unset($this->loadedRevisions[$number]);
        }

        $this->disposeChildDisposables();

        $this->selectRevision($this->getLatestRevision());

        return $this;
    }

    private function disposeChildDisposables() : void
    {
        foreach($this->childDisposables as $disposable) {
            $disposable->dispose();
        }
    }

    /**
     * Removes all data that has been loaded for this revision,
     * forcing it to be loaded again next time it is selected.
     * If it is the active revision, it is reloaded automatically.
     *
     * @param integer $number
     * @throws Application_Exception
     * @return $this
     */
    public function unloadRevision(int $number) : self
    {
        $this->collectChildDisposables();

        if (isset($this->loadedRevisions[$number])) {
            unset($this->loadedRevisions[$number]);
            if($this->revision === $number) {
                $this->loadRevision($number);
            }
        }

        if(isset($this->dataKeys[$number]))
        {
            unset($this->dataKeys[$number]);
        }

        $this->disposeChildDisposables();

        return $this;
    }
    
   /**
    * Reloads the currently selected revision's data.
    */
    public function reload() : self
    {
        $this->unloadRevision($this->revision);
        return $this;
    }

   /**
    * @param int $number
    * @return $this
    */
    abstract protected function _removeRevision(int $number) : self;

    /**
     * Replaces the target revision with the source revision, deleting
     * the source revision in the process.
     *
     * @param integer $targetRevision
     * @param integer $sourceRevision
     * @return $this
     * @throws Application_Exception
     */
    public function replaceRevision(int $targetRevision, int $sourceRevision) : self
    {
        // Select both revisions once to allow the custom
        // implementation to load them if needed.
        // Also check whether they exist at all.
        $this->selectRevision($targetRevision);
        $this->selectRevision($sourceRevision);

        $this->data[$targetRevision] = $this->data[$sourceRevision];
        $this->removeRevision($sourceRevision);

        $this->selectRevision($targetRevision);

        return $this;
    }

   /**
    * @return int|NULL
    */
    public function getRevision() : ?int
    {
        if (!isset($this->revision)) {
            $this->selectLatest();
        }

        return $this->revision;
    }

    abstract public function nextRevision(int $ownerID, string $ownerName, ?string $comments) : int;

   /**
    * Retrieves the revision's timestamp.
    * 
    * @return int|NULL
    */
    public function getTimestamp() : ?int
    {
        return StrictType::createStrict($this->getKey('__timestamp'))->getIntOrNull();
    }

    /**
     * @param string $name
     * @return $this
     * @throws RevisionableException
     */
    public function setOwnerName(string $name) : self
    {
        return $this->setPrivateKey(self::KEY_OWNER_NAME, $name);
    }

   /**
    * @return string
    */
    public function getOwnerName() : string
    {
        return StrictType::createStrict($this->getPrivateKey(self::KEY_OWNER_NAME))->getString();
    }

    /**
     * @param int $id
     * @return $this
     * @throws RevisionableException
     */
    public function setOwnerID(int $id) : self
    {
        return $this->setPrivateKey(self::KEY_OWNER_ID, $id);
    }

   /**
    * @return int
    */
    public function getOwnerID() : int
    {
        return StrictType::createStrict($this->getPrivateKey(self::KEY_OWNER_ID))->getInt();
    }

   /**
    * Retrieves a data key, or the specified default.
    * 
    * @param string $name
    * @param mixed $default
    * @throws RevisionableException {@see self::ERROR_KEY_REVISION_UNKNOWN}
    * @return mixed
    */
    public function getKey(string $name, $default = null)
    {
        if (!isset($this->revision)) {
            $this->selectLatest();
        }
        
        if (!isset($this->data[$this->revision])) {
            throw new RevisionableException(
                'Cannot get key for unknown revision',
                sprintf(
                    'The key [%s] does not exist in revision [%s] for [%s]',
                    $name,
                    $this->revision,
                    $this->revisionable->getIdentification()
                ),
                self::ERROR_KEY_REVISION_UNKNOWN
            );
        }

        // let the callback populate the value if present
        if (
            isset($this->keyLoaders[$name])
            &&
            !isset($this->data[$this->revision][$name])
        ) {
            $this->data[$this->revision][$name] = call_user_func($this->keyLoaders[$name], $this->revision, $name);
        }

        // using isset first for performance reasons, since it is much
        // more likely that a revision has data than not.
        if (isset($this->data[$this->revision][$name])) {
            return $this->data[$this->revision][$name];
        }

        // we have to use array_key_exists, since a key may be present
        // with a null or empty value, which would still be important:
        // we don't want to use a default value in that case.
        if (array_key_exists($name, $this->data[$this->revision])) {
            return $this->data[$this->revision][$name];
        }

        if ($default !== null) {
            return $default;
        }

        if (isset($this->defaults[$name])) {
            return $this->defaults[$name];
        }

        return null;
    }

   /**
    * @param string $name
    * @return boolean
    */
    public function hasKey(string $name) : bool
    {
        $revision = $this->getRevision();
        if (!isset($this->data[$revision])) {
            return false;
        }

        if (array_key_exists($name, $this->data[$revision])) {
            return true;
        }

        return false;
    }

    public function hasPrivateKey(string $name) : bool
    {
        return $this->hasKey($this->resolvePrivateKey($name));
    }

    private function resolvePrivateKey(string $name) : string
    {
        if(strpos($name, self::PRIVATE_KEY_PREFIX) === 0) {
            return $name;
        }

        return self::PRIVATE_KEY_PREFIX.$name;
    }

    // region: X - ArrayAccess methods

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return $this->hasKey($offset);
    }

    /**
     * @param string $offset
     * @return mixed|null
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->getKey($offset);
    }

    /**
     * @param string $offset
     * @param mixed $value
     * @return void
     * @throws Application_Exception
     */
    public function offsetSet($offset, $value) : void
    {
        $this->setKey($offset, $value);
    }

    /**
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        $revision = $this->getRevision();
        unset($this->data[$revision][$offset]);
    }

    // endregion

   /**
    * Retrieves all available revision numbers, from oldest
    * to newest.
    * 
    * @return integer[]
    */
    abstract public function getRevisions() : array;

   /**
    * Creates a filter criteria instance for accessing the
    * revisionable's available revisions list.
    * 
    * @return Application_FilterCriteria_RevisionableRevisions
    */
    abstract public function getFilterCriteria() : Application_FilterCriteria_RevisionableRevisions;

    /**
     * Must be implemented if the revisionable is to allow copying
     * to another revisionable of the same type. The target class
     * has to extend the <code>Application_RevisionStorage_TYPE_CopyRevision</code>
     * class, where <code>TYPE</code> is the storage type ID, e.g. <code>DB</code>.
     *
     * @return class-string
     * @throws RevisionableException
     */
    abstract protected function getRevisionCopyClass() : string;

   /**
    * @throws RevisionableException
    * @return integer
    */
    public function getLatestRevision() : int
    {
        $revisions = $this->getRevisions();
        if (empty($revisions)) {
            throw new RevisionableException(
                'No revisions available to select',
                'Tried retrieving a list of revisions, but it was empty.',
                self::ERROR_NO_REVISIONS_AVAILABLE
            );
        }

        return array_pop($revisions);
    }

   /**
    * Retrieves the number of the first ever available revision.
    * @throws RevisionableException
    * @return integer
    */
    public function getFirstRevision() : int
    {
        $revisions = $this->getRevisions();

        if (empty($revisions)) {
            throw new RevisionableException(
                'No revisions available to select',
                'Tried retrieving a list of revisions, but it was empty.',
                self::ERROR_NO_REVISIONS_AVAILABLE
            );
        }

        return array_shift($revisions);
    }

    protected string $logName;

    /**
     * @var array<int,string>
     */
    protected array $logFormat = array();
    
    public function getLogIdentifier() : string
    {
        // creating the base string once per revision for
        // performance reasons, and not using sprintf
        // for the same reason.
        if(!isset($this->logFormat[$this->revision])) {
            $this->logFormat[$this->revision] = $this->logName.' ['.$this->revisionable_id.'] | RevisionStorage ['.$this->revision.'] | '; 
        }
        
        return $this->logFormat[$this->revision];
    }

    /**
     * Copies the current revision of the owner revisionable over
     * to the currently selected revision of the target revisionable
     * instance.
     *
     * NOTE: Only revisionables of the same class may be copied.
     *
     * @param Application_Revisionable $revisionable
     * @return $this
     * @throws Application_Exception
     */
    public function copyTo(Application_Revisionable $revisionable) : self
    {
        $this->log(sprintf(
            'Copying %s [%s v%s] to %s [%s v%s].',
            $revisionable->getRevisionableTypeName(),
            $this->revisionable->getID(),
            $this->revision,
            $revisionable->getRevisionableTypeName(),
            $revisionable->getID(),
            $revisionable->getRevision()
        ));
    
        $user = Application::getUser();
        
        $copy = $this->createCopyRevision(
            $this->revision, 
            $revisionable->getRevision(), 
            $user->getID(), 
            $user->getName(), 
            t(
                'Copied from the %s %s.', 
                $revisionable->getRevisionableTypeName(),
                $revisionable->getLabel()
            )
        );
        
        $copy->setTarget($revisionable);
        $copy->process();

        return $this;
    }

    /**
     * Creates the class instance of the object that will handle
     * copying the revisions.
     *
     * @param int $sourceRevision
     * @param int $targetRevision
     * @param int $targetOwnerID
     * @param string $targetOwnerName
     * @param string|NULL $targetComments
     * @param DateTime|NULL $targetDate
     * @return BaseRevisionCopy
     * @throws BaseClassHelperException
     * @throws RevisionableException
     */
    protected function createCopyRevision(int $sourceRevision, int $targetRevision, int $targetOwnerID, string $targetOwnerName, ?string $targetComments=null, ?DateTime $targetDate=null) : BaseRevisionCopy
    {
        if(!$targetDate) 
        {
            $targetDate = new DateTime();
        }
        
        $class = $this->getRevisionCopyClass();
        
        $this->log(sprintf('Preparing to copy revision [%s] to [%s].', $sourceRevision, $targetRevision));
        $this->log(sprintf('Author: [%s], [%s]', $targetOwnerID, $targetOwnerName));
        $this->log(sprintf('Comments: [%s]', $targetComments));
        $this->log(sprintf('Date: [%s]', $targetDate->format('d.m.Y H:i:s')));
        
        return ClassHelper::requireObjectInstanceOf(
            BaseRevisionCopy::class,
            new $class(
                $this,
                $this->revisionable,
                $sourceRevision,
                $targetRevision,
                $targetOwnerID,
                $targetOwnerName,
                $targetComments,
                $targetDate
            ),
            self::ERROR_INVALID_COPY_REVISION_CLASS
        );
    }

   /**
    * Retrieves the revision storage type, e.g. "DB" or "Memory".
    * 
    * @return string
    */
    abstract public function getTypeID() : string;
    
    /**
     * @return $this
     */
    public function lock() : self
    {
        $this->locked = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function unlock() : self
    {
        $this->locked = false;
        return $this;
    }
    
   /**
    * @return boolean
    */
    public function isLocked() : bool
    {
        return $this->locked;
    }

   /**
    * Sets a static column value.
    * 
    * @param string $name
    * @param mixed $value
    * @return $this
    */
    public function setStaticColumn(string $name, $value) : self
    {
        if(!isset($this->staticColumns[$name]) || $this->staticColumns[$name] !== $value) {
            $this->log(sprintf('Set the static column [%s] to [%s].', $name, $value));
            $this->staticColumns[$name] = $value;
        }
        
        return $this;
    }
    
   /**
    * @return array<string,mixed>
    */
    public function getStaticColumns() : array
    {
        return $this->staticColumns;
    }
    
   /**
    * Retrieves the value of a static column, or the specified default if none.
    * 
    * @param string $name
    * @param mixed $default
    * @return mixed
    */
    public function getStaticColumn(string $name, $default=null)
    {
        if(isset($this->staticColumns[$name])) {
            return $this->staticColumns[$name];
        }
        
        return $default;
    }

    /**
     * Ensures that a revision exists, and throws an exception otherwise.
     * @return $this
     * @throws RevisionableException
     */
    protected function requireRevision() : self
    {
        $revision = $this->getRevision();

        if(isset($revision)) {
            return $this;
        }
        
        throw new RevisionableException(
            'No revision available',
            'No revision is available to select.',
            self::ERROR_REVISION_REQUIRED
        );
    }

    /**
     * Retrieves a revision data key.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     * @throws RevisionableException
     */
    public function getDataKey(string $name, $default=null)
    {
        if(!$this->hasDataKeys())
        {
            return $default;
        }
        
        $this->requireRevision();
        
        $revision = $this->getRevision();

        if(!isset($this->dataKeys[$revision]) || !$this->isLoaded($revision)) {
            $this->dataKeys[$revision] = array();
        }
        
        $adjusted = $this->adjustDataKeyName($name);

        // load the data key on demand
        if(!array_key_exists($adjusted, $this->dataKeys[$revision])) {
            $this->dataKeys[$revision][$adjusted] = $this->_loadDataKey($adjusted);
        }
        
        if(isset($this->dataKeys[$revision][$adjusted])) {
            return $this->dataKeys[$revision][$adjusted];
        }
        
        return $default;
    }

   /**
    * Handles loading a revision data key.
    * 
    * @param string $name
    * @return string
    */
    abstract protected function _loadDataKey(string $name) : string;

    /**
     * Sets a revision data key.
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     * @throws RevisionableException
     */
    public function setDataKey(string $name, $value) : self
    {
        if(!$this->hasDataKeys()) {
            return $this;
        }
        
        $this->requireRevision();
        
        $revision = $this->getRevision();
        
        if(!isset($this->dataKeys[$revision]) || !$this->isLoaded($revision)) {
            $this->dataKeys[$revision] = array();
        }
        
        $adjusted = $this->adjustDataKeyName($name);
        
        $this->dataKeys[$revision][$adjusted] = $value;

        return $this;
    }
    
   /**
    * Ensures that the data key name does not exceed the maximum
    * length of the column in the revdata table. If it does exceed
    * it, the name is replaced by a hash of the name.
    * 
    * @param string $name
    * @return string
    */
    protected function adjustDataKeyName(string $name) : string
    {
        if(strlen($name) > self::DATA_KEY_MAX_LENGTH) {
            return md5($name);
        }
        
        return $name;
    }

    /**
     * Write all revdata keys that have been loaded
     * for the currently selected revision to the permanent
     * storage.
     *
     * @return $this
     * @throws RevisionableException
     */
    public function writeDataKeys() : self
    {
        $this->log('Writing data keys.');

        $this->requireRevision();
        
        $revision = $this->getRevision();
        
        if(!isset($this->dataKeys[$revision])) {
            $this->log('No data keys to write in the revision.');
            return $this;
        }

        $written = 0;
        foreach($this->dataKeys[$revision] as $key => $value)
        {
            if($value !== null)
            {
                $written++;
                $this->_writeDataKey($key, $value);
            }
        }

        $this->log('Wrote [%s] data keys.', $written);

        return $this;
    }

    public function writeRevisionKeys(array $data) : self
    {
        $this->_writeRevisionKeys($data);
        return $this;
    }

    public function writeCustomKeys() : self
    {
        $data = $this->revisionable->getCustomKeyValues();

        // We have to add the label here, because it is not part
        // of the essential revision keys handled by the storage.
        $data[Application_RevisionableCollection::COL_REV_LABEL] = $this->revisionable->getLabel();

        $this->_writeRevisionKeys($data);

        return $this;
    }

    abstract protected function _writeRevisionKeys(array $data) : void;

    /**
     * @return array<string,mixed>
     */
    public function getDataKeys() : array
    {
        $revision = $this->getRevision();

        if(isset($this->dataKeys[$revision])) {
            return $this->dataKeys[$revision];
        }

        return array();
    }

   /**
    * Handles saving the specified revision data key.
    * 
    * @param string $key
    * @param mixed $value
    */
    abstract protected function _writeDataKey(string $key, $value) : void;

    // region: Event handling

    public const EVENT_REVISION_ADDED = 'RevisionAdded';

    protected function triggerRevisionAdded(int $number, int $timestamp, int $ownerID, string $ownerName, ?string $comments=null) : void
    {
        $this->triggerEvent(
            self::EVENT_REVISION_ADDED,
            array($number, $timestamp, $ownerID, $ownerName, (string)$comments),
            Application_RevisionStorage_Event_RevisionAdded::class
        );
    }

    /**
     * The callback gets the event instance as single parameter.
     *
     * @param callable $callback
     * @return Application_EventHandler_EventableListener
     * @see Application_RevisionStorage_Event_RevisionAdded
     */
    public function onRevisionAdded(callable $callback) : Application_EventHandler_EventableListener
    {
        return $this->addEventListener(self::EVENT_REVISION_ADDED, $callback);
    }

    // endregion

    /**
     * Sets a key that is to be treated as private, i.e., it is
     * not part of the regular data keys, but is stored separately.
     *
     * The same key names will not conflict with each other if
     * used in {@see self::setKey()} and {@see self::setPrivateKey()}.
     *
     * @param string $name
     * @param mixed|NULL $value
     * @return $this
     * @throws RevisionableException
     */
    public function setPrivateKey(string $name, $value) : self
    {
        return $this->setKey($this->resolvePrivateKey($name), $value);
    }

    /**
     * @param string $name
     * @return mixed|NULL
     * @throws RevisionableException
     */
    public function getPrivateKey(string $name)
    {
        return $this->getKey($this->resolvePrivateKey($name));
    }

    public function getIdentification(): string
    {
        return $this->getLogIdentifier();
    }

    public function getChildDisposables(): array
    {
        $disposables = $this->childDisposables;

        unset($this->childDisposables);

        return $disposables;
    }

    /**
     * @var Application_Interfaces_Disposable[]
     */
    private array $childDisposables = array();

    private function collectChildDisposables() : void
    {
        $this->childDisposables = array();

        foreach($this->data as $data)
        {
            foreach($data as $value)
            {
                if(!$value instanceof Application_Interfaces_Disposable) {
                    continue;
                }

                if(!$value instanceof RevisionDependentInterface) {
                    continue;
                }

                if($value->getRevisionable() === $this->revisionable && $value->getRevision() === $this->revision) {
                    $this->childDisposables[] = $value;
                }
            }
        }
    }

    protected function _dispose(): void
    {
        // Child disposables are collected after _dispose() has been called.
        // As we want to unset the data keys array, we have to temporarily
        // store the children to be processed later.
        $this->collectChildDisposables();

        unset(
            $this->revisionable,
            $this->keyLoaders,
            $this->loadedRevisions,
            $this->data,
            $this->dataKeys,
            $this->staticColumns
        );
    }
}
