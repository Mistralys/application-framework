<?php

use Application\Revisionable\RevisionableException;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;

abstract class Application_RevisionableCollection_DBRevisionable extends Application_Revisionable
{
    public const ERROR_NO_CURRENT_REVISION_FOUND = 14701;
    public const ERROR_INVALID_REVISION_STORAGE = 14702;
    public const COL_REV_STATE = 'state';
    public const COL_REV_DATE = 'date';
    public const COL_REV_AUTHOR = 'author';

    protected Application_RevisionableCollection $collection;
    protected int $id;
    protected array $customKeys;
    protected int $currentRevision;
    
    public function __construct(Application_RevisionableCollection $collection, int $id, $customColumnValues=array())
    {
        $this->collection = $collection;
        $this->id = $id;
        $this->customKeys = $customColumnValues;
        
        parent::__construct();
        
        if($this->isDummy()) {
            return;
        }
        
        $this->currentRevision = $this->collection->getCurrentRevision($id);
        if(!$this->currentRevision) {
            throw new Application_Exception(
                'Error loading current revision',
                sprintf(
                    'Could not load %s [%s] from database, no current revision found. Custom column values: [%s]',
                    $this->getRevisionableTypeName(),
                    $this->id,
                    json_encode($this->customKeys)
                ),
                self::ERROR_NO_CURRENT_REVISION_FOUND
            );
        }
        
        $this->selectCurrentRevision();
    }
    
   /**
    * Selects the revisionable's current revision.
    * @return Application_RevisionableCollection_DBRevisionable
    */
    public function selectCurrentRevision()
    {
        $this->selectRevision($this->currentRevision);
        return $this;
    }
    
   /**
    * Whether this is a dummy object instance.
    * @return boolean
    */
    public function isDummy() : bool
    {
        return $this->id === Application_RevisionableCollection::DUMMY_ID;
    }
    
   /**
    * Retrieves any custom column values to limit the revisionable table to.
    * This is set on instantiation.
    * 
    * @return array
    */
     public function getCustomColumnValues()
     {
         return $this->customKeys;
     }
    
   /**
    * Retrieves the revisionable's collection instance.
    * @return Application_RevisionableCollection
    */
    public function getCollection()
    {
        return $this->collection;
    }
    
   /**
    * @see Application_RevisionStorage_CollectionDB
    * @throws RevisionableException
    */
    protected function createRevisionStorage() : Application_RevisionStorage_CollectionDB
    {
        try
        {
            $className = $this->collection->getRevisionsStorageClass();

            return ClassHelper::requireObjectInstanceOf(
                Application_RevisionStorage_CollectionDB::class,
                new $className($this)
            );
        }
        catch (BaseClassHelperException $e)
        {
            throw new RevisionableException(
                'Invalid revision storage',
                sprintf(
                    'The revision storage for [%s] must extend the base [%s] class.',
                    get_class($this),
                    'Application_RevisionStorage_CollectionDB'
                ),
                self::ERROR_INVALID_REVISION_STORAGE,
                $e
            );
        }
    }
    
    public function getID() : int
    {
        return $this->id;
    }

    protected function _saveWithStateChange() : void
    {
        $this->saveRevisionData(array(self::COL_REV_STATE));
    }

    protected function _save() : void
    {
    }
    
   /**
    * Saves the current values of the specified data keys to
    * the revision table for the current revision.
    * 
    * @param string[] $columnNames
    */
    protected function saveRevisionData(array $columnNames) : void
    {
        $this->log(sprintf('Saving revision data for keys [%s].', implode(', ', $columnNames)));
        
        $revKey = $this->collection->getRevisionKeyName();
        $primaryKey = $this->collection->getPrimaryKeyName();
        
        $data = $this->collection->getCampaignKeys();
        
        foreach($columnNames as $columnName) 
        {
            $value = null;
            
            switch($columnName) 
            {
                // these may not be set
                case $primaryKey:
                case $revKey:
                    $value = '__ignore_key';
                    break;
                    
                case self::COL_REV_STATE:
                    $value = $this->getStateName();
                    break;
                    
                case self::COL_REV_DATE:
                    $value = $this->getRevisionDate()->format('Y-m-d H:i:s');
                    break;
                    
                case self::COL_REV_AUTHOR:
                    $value = $this->getOwnerID();
                    break;
                
                default:
                    $value = $this->revisions->getKey($columnName);
                    break;
            }

            if($value !== '__ignore_key') {
                $data[$columnName] = $value; 
            }
        }

        $data[$revKey] = $this->getRevision();
        
        DBHelper::updateDynamic(
            $this->collection->getRevisionsTableName(),
            $data,
            array($revKey)
        );
    }
    
   /**
    * Retrieves the base URL parameters collection used to
    * administrate this revisionable. Presupposes that an
    * administration interface exists for it.
    * 
    * @return array
    */
    public function getAdminURLParams() : array
    {
        $params = $this->collection->getAdminURLParams();
        $params[$this->collection->getPrimaryKeyName()] = $this->getID();
        return $params;
    }
    
    protected function getAdminURL(array $params=array()) : string
    {
        $params = array_merge($params, $this->getAdminURLParams());
        return Application_Driver::getInstance()->getRequest()->buildURL($params);
    }
    
    protected bool $handleDBTransaction = false;

    public function startTransaction(int $newOwnerID, string $newOwnerName, ?string $comments = null) : self
    {
        // to allow this transaction to be wrapped in an 
        // existing transaction, we check if we have to 
        // start one automatically or not.
        $this->handleDBTransaction = false;

        if(!DBHelper::isTransactionStarted()) {
            $this->handleDBTransaction = true;
            DBHelper::startTransaction();
        }
        
        return parent::startTransaction($newOwnerID, $newOwnerName, $comments);
    }

    public function endTransaction() : bool
    {
        $this->save();

        // avoid creating a new revision if the structure has not been changed.
        if (!$this->hasStructuralChanges()) {
            $this->log('No structural changes made, no new revision will be created.');
            $this->requiresNewRevision = false;
        }

        // we need to do this, because we want to trigger it later
        $this->ignoreEvent('TransactionEnded');
        
        $result = parent::endTransaction();

        // now make sure the current revision is set correctly, regardless
        // of whether we added a new revision or not.
        $this->collection->setCurrentRevision($this->id, $this->getRevision());

        // do we handle the DB transaction here? 
        if($this->handleDBTransaction) {
            if ($this->simulation) {
                $this->log('Simulation mode, transaction will not be committed.');
                DBHelper::rollbackTransaction();
                return $result;
            }

            DBHelper::commitTransaction();
        }
        
        $this->log('Reloading the revision data.');
        $this->revisions->reload();

        // now that everything's through, we can trigger the event.
        $this->unignoreEvent('TransactionEnded');
        $this->triggerEvent('TransactionEnded');
        
        $this->log('Comments: '.$this->getRevisionComments());
        
        return $result;
    }

    /**
     * @return $this
     * @throws DBHelper_Exception
     */
    public function rollBackTransaction() : self
    {
        parent::rollBackTransaction();

        DBHelper::rollbackTransaction();

        return $this;
    }

    public function getChangelogTable()
    {
        return $this->collection->getRecordChangelogTableName();
    }

    public function getChangelogItemPrimary()
    {
        return array(
            $this->collection->getPrimaryKeyName() => $this->getID(),
            $this->collection->getRevisionKeyName() => $this->getRevision()
        );
    }

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    abstract public function getAdminStatusURL(array $params=array()) : string;

    /**
     * @param array<string,string|number> $params
     * @return string
     */
    abstract public function getAdminChangelogURL(array $params=array()) : string;
    
   /**
    * Selects the last revision of the record by a specific state.
    * 
    * @param Application_StateHandler_State $state
    * @return integer|boolean The revision number, or false if no revision matches.
    */
    public function selectLastRevisionByState(Application_StateHandler_State $state)
    {
        $revision = $this->getLastRevisionByState($state);
        if($revision) {
            $this->selectRevision($revision);
            return $revision;
        }
        
        return false;
    }
    
    /**
     * Retrieves the last revision of the record by a specific state.
     *
     * @param Application_StateHandler_State $state
     * @return integer|boolean The revision number, or false if no revision matches.
     */
    public function getLastRevisionByState(Application_StateHandler_State $state)
    {
        $revisionKey = $this->collection->getRevisionKeyName();
        $primaryKey = $this->collection->getPrimaryKeyName();
        
        $where = $this->collection->getCampaignKeys();
        $where[$primaryKey] = $this->getID();
        $where[self::COL_REV_STATE] = $state->getName();
        
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
        
        if(!empty($revision)) {
            return $revision;
        }
        
        return false;
    }
    
   /**
    * Retrieves the revision currently in use. This is tracked in
    * a dedicated table, and namespaced to any campaign keys that
    * may have been defined.
    * 
    * @return integer|NULL
    */
    public function getCurrentRevision() : ?int
    {
        return $this->collection->getCurrentRevision($this->getID());
    }

    public function getPrettyRevision() : int
    {
        return (int)$this->revisions->getKey('pretty_revision');
    }
}