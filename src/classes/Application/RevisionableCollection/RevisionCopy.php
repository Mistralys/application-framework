<?php

require_once 'Application/RevisionStorage/DB/CopyRevision.php';

abstract class Application_RevisionableCollection_RevisionCopy extends Application_RevisionStorage_DB_CopyRevision
{
    public const ERROR_REVISION_DOES_NOT_EXIST = 15401;
    
   /**
    * @var Application_RevisionableCollection_DBRevisionable
    */
    protected $revisionable;
    
   /**
    * @var Application_RevisionableCollection
    */
    protected $collection;
    
    protected $primaryKey;
    
    protected $revisionKey;
    
    protected $revisionTable; 
    
    protected function init()
    {
        $this->collection = $this->revisionable->getCollection();
        $this->primaryKey = $this->collection->getPrimaryKeyName();
        $this->revisionKey = $this->collection->getRevisionKeyName();
        $this->revisionTable = $this->collection->getRevisionsTableName();
    }
    
    protected function getRevisionCopyClass()
    {
        return $this->collection->getRecordCopyRevisionClass();
    }

    protected function processParts(Application_RevisionableStateless $targetRevisionable) : void
    {
        // ensure this is always done first, as it is 
        // the basis for the rest.
        $this->processSettings($targetRevisionable);
        
        parent::processParts($targetRevisionable);
    }
    
    protected function processSettings(Application_RevisionableStateless $targetRevisionable) : void
    {
        $this->log(sprintf('Copying revision data from table [%s].', $this->revisionTable));

        // to ensure we also copy all custom columns from the revisions
        // table, we fetch the full record.
        $data = DBHelper::fetch(
            sprintf(
                "SELECT
                    *
                FROM
                    `%s`
                WHERE
                    `%s`=:revision",
                $this->revisionTable,
                $this->revisionKey
            ),
            array(
                'revision' => $this->sourceRevision
            )
        );
        
        unset($data['pretty_revision']);
        
        $keys = $this->storage->getStaticColumns();
        foreach($keys as $key => $value) {
            $data[$key] = $value;
        }
        
        // overwrite the required keys with the target information
        $data['comments'] = $this->comments;
        $data['date'] = $this->date->format('Y-m-d H:i:s');
        $data['author'] = $this->ownerID; 
        $data[$this->revisionKey] = $this->targetRevision;
        $data[$this->primaryKey] = $targetRevisionable->getID();
        
        DBHelper::updateDynamic(
            $this->revisionTable,
            $data,
            array(
                $this->revisionKey
            )
        );
    }
}