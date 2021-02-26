<?php

abstract class Application_RevisionableCollection_DBRevisionable extends Application_Revisionable
{
    const ERROR_NO_CURRENT_REVISION_FOUND = 14701; 
    const ERROR_INVALID_REVISION_STORAGE = 14702;
    
   /**
    * @var Application_RevisionableCollection
    */
    protected $collection;
    
   /**
    * @var integer
    */
    protected $id;
    
    protected $customKeys;
    
    protected $currentRevision;
    
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
    public function isDummy()
    {
        if($this->id === Application_RevisionableCollection::DUMMY_ID) {
            return true;
        }
        
        return false;
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
    */
    protected function createRevisionStorage()
    {
        $className = $this->collection->getRevisionsStorageClass();
        
        Application::requireClass($className);
        
        $storage = new $className($this);
        
        if(!$storage instanceof Application_RevisionStorage_CollectionDB) {
            throw new Application_Exception(
                'Invalid revision storage',
                sprintf(
                    'The revision storage for [%s] must extend the base [%s] class.',
                    get_class($this),
                    'Application_RevisionStorage_CollectionDB'
                ),
                self::ERROR_INVALID_REVISION_STORAGE
            );
        }
        
        return $storage;
    }
    
    public function getID() : int
    {
        return $this->id;
    }

    protected function _saveWithStateChange()
    {
        $this->saveRevisionData(array('state'));
    }

    protected function _save()
    {
    }
    
   /**
    * Saves the current values of the specified data keys to
    * the revision table for the current revision.
    * 
    * @param string[] $columnNames
    */
    protected function saveRevisionData($columnNames)
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
                    
                case 'state':
                    $value = $this->getStateName();
                    break;
                    
                case 'date':
                    $value = $this->getRevisionDate()->format('Y-m-d H:i:s');
                    break;
                    
                case 'author':
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
    public function getAdminURLParams()
    {
        $params = $this->collection->getAdminURLParams();
        $params[$this->collection->getPrimaryKeyName()] = $this->getID();
        return $params;
    }
    
    protected function getAdminURL($params=array())
    {
        $params = array_merge($params, $this->getAdminURLParams());
        return Application_Driver::getInstance()->getRequest()->buildURL($params);
    }
    
    protected $handleDBTransaction = false;

    public function startTransaction($newOwnerID, $newOwnerName, $comments = '')
    {
        // to allow this transaction to be wrapped in an 
        // existing transaction, we check if we have to 
        // start one automatically or not.
        $this->handleDBTransaction = false;
        if(!DBHelper::isTransactionStarted()) {
            $this->handleDBTransaction = true;
            DBHelper::startTransaction();
        }
        
        $this->log(sprintf('Current state is [%1$s].', $this->getStateName()));
        
        parent::startTransaction($newOwnerID, $newOwnerName, $comments);
    }

    public function endTransaction()
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

        // now that everything's through we can trigger the event.
        $this->unignoreEvent('TransactionEnded');
        $this->triggerEvent('TransactionEnded');
        
        $this->log('Comments: '.$this->getRevisionComments());
        
        return $result;
    }

    public function rollBackTransaction()
    {
        parent::rollBackTransaction();

        DBHelper::rollbackTransaction();
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
    
    abstract public function getAdminStatusURL($params=array());
    
    abstract public function getAdminChangelogURL($params=array());
    
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
        $where['state'] = $state->getName();
        
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
    public function getCurrentRevision()
    {
        return $this->collection->getCurrentRevision($this->getID());
    }

    public function getPrettyRevision()
    {
        return $this->revisions->getKey('pretty_revision');
    }

    /**
     * Retrieves the revisonable's master revision for the export.
     * Note that this can be empty if the revisionable does not
     * support this, or none has been selected yet.
     *
     * @return string|NULL
     */
    public function getExportRevision()
    {
        $table = $this->collection->getRecordExportRevisionsTableName();
        if(!DBHelper::tableExists($table)) {
            return null;
        }
        
        $where = $this->collection->getCampaignKeys();
        $where[$this->collection->getPrimaryKeyName()] = $this->getID();
        
        return DBHelper::fetchKey(
            'export_revision',
            sprintf(
                "SELECT
                    `export_revision`
                FROM
                    `%s`
                WHERE
                    %s",
                $table,
                DBHelper::buildWhereFieldsStatement($where)
            ),
            $where
        );
    }
    
    public function getExportRevisionPretty()
    {
        $rev = $this->getExportRevision();
        if(!empty($rev)) {
            return $rev;
        }
        
        return UI::icon()->notRequired()
        ->makeMuted()
        ->cursorHelp()
        ->setTooltip(t('No specific export version has been selected.'));
    }
}