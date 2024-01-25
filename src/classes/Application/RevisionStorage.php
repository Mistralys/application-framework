<?php
/**
 * File containing the {@link Application_RevisionStorage} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_RevisionStorage
 */

use Application\Revisionable\RevisionableException;

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
abstract class Application_RevisionStorage implements ArrayAccess, Application_Interfaces_Eventable
{
    use Application_Traits_Eventable;
    use Application_Traits_Loggable;

    const KEY_TYPE_ARRAY = 'array';
    const KEY_TYPE_STRING = 'string';

    const REVDATA_KEY_MAX_LENGTH = 180;
    
    public const ERROR_REVISION_DOES_NOT_EXIST = 15557001;
    public const ERROR_COPYTO_NOT_IMPLEMENTED = 15557002;
    public const ERROR_CANNOT_SET_KEY_UNKNOWN_REVISION = 15557003;
    public const ERROR_CANNOT_SET_KEYS_UNKNOWN_REVISION = 15557004;
    public const ERROR_INVALID_KEY_LOADER_CALLBACK = 15557005;
    public const ERROR_INVALID_COPY_REVISION_CLASS = 15557006;
    public const ERROR_NO_REVISIONS_AVAILABLE = 15557007;
    public const ERROR_REVISION_REQUIRED = 15557008;

    /**
    * @var array<int,array<string,mixed>>
    */
    protected $data = array();

   /**
    * @var array<string,mixed>
    */
    protected $defaults = array();

   /**
    * @var integer|NULL
    */
    protected $revision = null;

   /**
    * @var integer[]
    */
    protected $revisionsToRemember = array();

   /**
    * @var Application_RevisionableStateless
    */
    protected $revisionable;

   /**
    * @var integer
    */
    protected $revisionable_id;
    
   /**
    * @var array<string,callable>
    */
    protected $keyLoaders = array();
    
   /**
    * @var boolean
    */
    protected bool $locked = false;
    
   /**
    * @var array<string,mixed>
    */
    protected $staticColumns = array();
    
   /**
    * @var array<int,array<string,mixed>>
    */
    protected $revdata = array();
    
    public function __construct(Application_RevisionableStateless $revisionable)
    {
        $this->revisionable = $revisionable;
        $this->revisionable_id = $revisionable->getID();
        $this->logName = ucfirst($this->revisionable->getRevisionableTypeName());
        $this->configure();
    }

   /**
    * @return Application_RevisionableStateless
    */
    public function getRevisionable()
    {
        return $this->revisionable;
    }
    
    protected function configure()
    {
    
    }
    
   /**
    * Sets the defaults for a range of data keys.
    * 
    * @param array<string,mixed> $defaults
    */
    public function setKeyDefaults($defaults)
    {
        $this->defaults = $defaults;
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
            '__ownerID' => $ownerID,
            '__ownerName' => $ownerName,
            '__comments' => (string)$comments
        );

        $this->triggerRevisionAdded($number, $timestamp, $ownerID, $ownerName, $comments);
    }

   /**
    * @return integer
    */
    abstract public function countRevisions() : int;

   /**
    * Stores the current revision number so it can be
    * restored later using {@link restoreRevision()}.
    * This is useful if you have to select other revisions,
    * but want to restore the originally selected one
    * afterwards.
    *
    * @see restoreRevision()
    */
    public function rememberRevision()
    {
        $this->revisionsToRemember[] = $this->getRevision();
    }

   /**
    * Selects the revision that was flagged to be remembered
    * earlier using {@link rememberRevision()}. Throws an
    * exception if no revision was previously remembered.
    *
    * @throws Exception
    * @see rememberRevision()
    */
    public function restoreRevision()
    {
        if (empty($this->revisionsToRemember)) {
            throw new Exception('Cannot restore revision, no revision was selected to remember.');
        }

        $revNumber = array_pop($this->revisionsToRemember);

        $this->selectRevision($revNumber);
    }

   /**
    * @return string
    */
    public function getComments()
    {
        return strval($this->getKey('__comments'));
    }

   /**
    * Sets the revision comments.
    * 
    * @param string $comments
    */
    public function setComments($comments)
    {
        $this->setKey('__comments', $comments);
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
    protected ?bool $hasRevdata = null;
    
   /**
    * @return boolean
    */
    public function hasRevdata() : bool
    {
        if(!isset($this->hasRevdata)) 
        {
            $this->hasRevdata = $this->_hasRevdata();
        }
        
        return $this->hasRevdata;
    }
    
   /**
    * @return bool
    */
    abstract protected function _hasRevdata() : bool;
    
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
    * @return bool
    */
    abstract public function revisionExists(int $number) : bool;

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
    public function clearKey($name)
    {
        $revision = $this->getRevision();
        
        if(isset($this->data[$revision]) && isset($this->data[$revision][$name])) {
            unset($this->data[$revision][$name]);
            return true;
        }
        
        return false;
    }

   /**
    * Sets a key to the specified value. Any existing values are
    * overwritten, and if the key did not exist it is created.
    * 
    * @param string $name
    * @param mixed $value
    * @throws Application_Exception
    */
    public function setKey($name, $value)
    {
        $revision = $this->getRevision();

        if (!isset($this->data[$revision])) {
            throw new Application_Exception(
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
    }

   /**
    * Sets a range of data keys at once.
    * 
    * @param array<string,mixed> $keys
    * @throws Application_Exception
    */
    public function setKeys($keys)
    {
        $revision = $this->getRevision();
        
        if (!isset($this->data[$revision])) 
        {
            throw new Application_Exception(
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
    }

   /**
    * Sets a callback function to use for automatically populating
    * a key value when it is accessed. It is only called when the
    * key is not set, so it can modified as per usual.
    *
    * @param string $key
    * @param callable $callback
    */
    public function setKeyLoader(string $key, $callback)
    {
        Application::requireCallableValid($callback, self::ERROR_INVALID_KEY_LOADER_CALLBACK);

        $this->keyLoaders[$key] = $callback;
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
     * @param string $comments
     * @return int
     * @throws InvalidArgumentException|Application_Exception
     */
    public function addByCopy(int $sourceRevision, int $ownerID, string $ownerName, ?string $comments=null)
    {
        $newRev = $this->nextRevision();
        $this->addRevision($newRev, $ownerID, $ownerName, null, $comments);
        $this->selectRevision($sourceRevision);
        $this->copy($sourceRevision, $newRev, $ownerID, $ownerName, $comments);

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
     * @throws Application_Exception
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
            throw new InvalidArgumentException('Cannot remove a revision prior to the latest revision.');
        }

        $this->_removeRevision($number);

        if (isset($this->loadedRevisions[$number])) {
            unset($this->loadedRevisions[$number]);
        }

        $this->selectRevision($this->getLatestRevision());

        return $this;
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
        if (isset($this->loadedRevisions[$number])) {
            unset($this->loadedRevisions[$number]);
            if($this->revision === $number) {
                $this->loadRevision($number);
            }
        }

        if(isset($this->revdata[$number]))
        {
            unset($this->revdata[$number]);
        }

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
     * @throws Application_Exception
     */
    public function replaceRevision($targetRevision, $sourceRevision)
    {
        // select both revisions once to allow the custom
        // implementation to load them if needed. Also checks
        // whether they exist at all.
        $this->selectRevision($targetRevision);
        $this->selectRevision($sourceRevision);

        $this->data[$targetRevision] = $this->data[$sourceRevision];
        $this->removeRevision($sourceRevision);

        $this->selectRevision($targetRevision);
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

    abstract public function nextRevision() : int;

   /**
    * Retrieves the revision's timestamp.
    * 
    * @return int|NULL
    */
    public function getTimestamp()
    {
        return $this->getKey('__timestamp');
    }

    /**
     * @param string $name
     * @throws Application_Exception
     */
    public function setOwnerName($name)
    {
        $this->setKey('__ownerName', $name);
    }

   /**
    * @return string
    */
    public function getOwnerName()
    {
        return strval($this->getKey('__ownerName'));
    }

    /**
     * @param int $id
     * @throws Application_Exception
     */
    public function setOwnerID($id)
    {
        $this->setKey('__ownerID', $id);
    }

   /**
    * @return int
    */
    public function getOwnerID() : int
    {
        return intval($this->getKey('__ownerID'));
    }

   /**
    * Retrieves a data key, or the specified default.
    * 
    * @param string $name
    * @param mixed $default
    * @throws InvalidArgumentException
    * @return mixed
    */
    public function getKey($name, $default = null)
    {
        if (!isset($this->revision)) {
            $this->selectLatest();
        }
        
        if (!isset($this->data[$this->revision])) {
            throw new InvalidArgumentException('Cannot get key for unknown revision');
        }

        // let the callback populate the value if present
        if (isset($this->keyLoaders[$name])) {
            if (!isset($this->data[$this->revision][$name])) {
                $this->data[$this->revision][$name] = call_user_func($this->keyLoaders[$name], $this->revision, $name);
            }
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
    public function hasKey($name) : bool
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
    abstract public function getFilterCriteria();
    
   /**
    * @throws Application_Exception
    * @return integer
    */
    public function getLatestRevision()
    {
        $revisions = $this->getRevisions();
        if (empty($revisions)) {
            throw new Application_Exception(
                'No revisions available to select',
                'Tried retrieving a list of revisions, but it was empty.',
                self::ERROR_NO_REVISIONS_AVAILABLE
            );
        }

        return array_pop($revisions);
    }

   /**
    * Retrieves the number of the first ever available revision.
    * @throws Application_Exception
    * @return integer
    */
    public function getFirstRevision()
    {
        $revisions = $this->getRevisions();
        if (empty($revisions)) {
            throw new Application_Exception(
                'No revisions available to select',
                'Tried retrieving a list of revisions, but it was empty.',
                self::ERROR_NO_REVISIONS_AVAILABLE
            );
        }

        return array_shift($revisions);
    }

    /**
     * @var string
     */
    protected $logName;

    /**
     * @var array<int,string>
     */
    protected $logFormat = array();
    
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
     * Must be implemented if the revisionable is to allow copying
     * to another revisionable of the same type. The target class
     * has to extend the <code>Application_RevisionStorage_TYPE_CopyRevision</code>
     * class, where <code>TYPE</code> is the storage type ID, e.g. <code>DB</code>.
     *
     * @return class-string
     * @throws Application_Exception
     */
    protected function getRevisionCopyClass() : string
    {
        throw new Application_Exception(
            'Revision copy not implemented',
            'To enable copying revisions between revisionables, the [getRevisionCopyClass] method has to be implemented.',
            self::ERROR_COPYTO_NOT_IMPLEMENTED    
        );
    }

    /**
     * Copies the current revision of the owner revisionable over
     * to the currently selected revision of the target revisionable
     * instance.
     *
     * NOTE: Only revisionables of the same class may be copied.
     *
     * @param Application_Revisionable $revisionable
     * @throws Application_Exception
     */
    public function copyTo(Application_Revisionable $revisionable) : void
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
    * @throws RevisionableException
    * @return Application_RevisionStorage_CopyRevision
    */    
    protected function createCopyRevision(int $sourceRevision, int $targetRevision, int $targetOwnerID, string $targetOwnerName, ?string $targetComments=null, ?DateTime $targetDate=null) : Application_RevisionStorage_CopyRevision
    {
        if(!$targetDate) 
        {
            $targetDate = new DateTime();
        }
        
        $baseClass = 'Application_RevisionStorage_'.$this->getTypeID().'_CopyRevision';
        
        $class = $this->getRevisionCopyClass();
        
        $this->log(sprintf('Preparing to copy revision [%s] to [%s].', $sourceRevision, $targetRevision));
        $this->log(sprintf('Author: [%s], [%s]', $targetOwnerID, $targetOwnerName));
        $this->log(sprintf('Comments: [%s]', $targetComments));
        $this->log(sprintf('Date: [%s]', $targetDate->format('d.m.Y H:i:s')));
        
        $copy = new $class(
            $this,
            $this->revisionable,
            $sourceRevision,
            $targetRevision,
            $targetOwnerID,
            $targetOwnerName,
            $targetComments,
            $targetDate
        );
        
        if(!$copy instanceof $baseClass) {
            throw new RevisionableException(
                'Invalid copy revision instance',
                sprintf(
                    'The class [%s] is not an instance of [%s].',
                    get_class($copy),
                    $baseClass
                ),
                self::ERROR_INVALID_COPY_REVISION_CLASS    
            );
        }
    
        return $copy;
    }

   /**
    * Retrieves the revision storage type, e.g. "DB" or "Memory".
    * 
    * @return string
    */
    abstract public function getTypeID() : string;
    
   /**
    * Disposes of all internal session data.
    */
    public function dispose() : void
    {
        $this->data = array();
        $this->loadedRevisions = array();
        $this->revision = null;
        $this->revisionsToRemember = array();
    }

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
    * @return Application_RevisionStorage
    */
    public function setStaticColumn($name, $value)
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
    public function getStaticColumns()
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
    public function getStaticColumn($name, $default=null)    
    {
        if(isset($this->staticColumns[$name])) {
            return $this->staticColumns[$name];
        }
        
        return $default;
    }

    protected function requireRevision()
    {
        $revision = $this->getRevision();

        if(isset($revision)) {
            return;
        }
        
        throw new Application_Exception(
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
    */
    public function getRevdataKey($name, $default=null)
    {
        if(!$this->hasRevdata()) 
        {
            return $default;
        }
        
        $this->requireRevision();
        
        $revision = $this->getRevision();

        if(!isset($this->revdata[$revision]) || !$this->isLoaded($revision)) {
            $this->revdata[$revision] = array();
        }
        
        $adjusted = $this->adjustRevdataKeyName($name);

        // load the data key on demand
        if(!array_key_exists($adjusted, $this->revdata[$revision])) {
            $this->revdata[$revision][$adjusted] = $this->_loadRevdataKey($adjusted);
        }
        
        if(isset($this->revdata[$revision][$adjusted])) {
            return $this->revdata[$revision][$adjusted];
        }
        
        return $default;
    }

   /**
    * Handles loading a revision data key.
    * 
    * @param string $name
    * @return string
    */
    abstract protected function _loadRevdataKey(string $name) : string;
    
   /**
    * Sets a revision data key.
    * 
    * @param string $name
    * @param mixed $value
    */
    public function setRevdataKey($name, $value)
    {
        if(!$this->hasRevdata()) {
            return;
        }
        
        $this->requireRevision();
        
        $revision = $this->getRevision();
        
        if(!isset($this->revdata[$revision]) || !$this->isLoaded($revision)) {
            $this->revdata[$revision] = array();
        }
        
        $adjusted = $this->adjustRevdataKeyName($name);
        
        $this->revdata[$revision][$adjusted] = $value;
    }
    
   /**
    * Ensures that the data key name does not exceed the maximum
    * length of the column in the revdata table. If it does exceed
    * it, the name is replaced by a hash of the name.
    * 
    * @param string $name
    * @return string
    */
    protected function adjustRevdataKeyName(string $name) : string
    {
        if(strlen($name) > self::REVDATA_KEY_MAX_LENGTH) {
            return md5($name);
        }
        
        return $name;
    }
    
   /**
    * Writes all revdata keys that have been loaded
    * for the currently selected revision to the permanent
    * storage.
    */
    public function writeRevdata()
    {
        $this->requireRevision();
        
        $revision = $this->getRevision();
        
        if(!isset($this->revdata[$revision])) {
            return;
        }
        
        foreach($this->revdata[$revision] as $key => $value) 
        {
            if($value !== null)
            {
                $this->_writeRevdataKey($key, $value);
            }
        }
    }

    public function getRevdata() : array
    {
        $revision = $this->getRevision();

        if(isset($this->revdata[$revision])) {
            return $this->revdata[$revision];
        }

        return array();
    }

   /**
    * Handles saving the specified revision data key.
    * 
    * @param string $key
    * @param mixed $value
    */
    abstract protected function _writeRevdataKey(string $key, $value) : void;

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
}
