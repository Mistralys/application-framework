<?php

declare(strict_types=1);

abstract class Application_RevisionStorage_CollectionDB extends Application_RevisionStorage_DBStandardized
{ 
    public const ERROR_INVALID_REVISIONABLE_TYPE = 14601;
    
    /**
     * @var Application_RevisionableCollection_DBRevisionable
     */
    protected $revisionable;
    
   /**
    * @var Application_RevisionableCollection
    */
    protected $collection;
    
    public function __construct(Application_RevisionableStateless $revisionable)
    {
        if(!$revisionable instanceof Application_RevisionableCollection_DBRevisionable) {
            throw new Application_Exception(
                'Invalid revisionable type',
                sprintf(
                    'The [%s] revision storage requires the revisionable to be of the [%s] type.',
                    'Application_RevisionStorage_CollectionDB',
                    'Application_RevisionableCollection_DBRevisionable'
                ),
                self::ERROR_INVALID_REVISIONABLE_TYPE
            );
        }
        
        $this->collection = $revisionable->getCollection();
        
        parent::__construct($revisionable);
        
        $campaignKeys = $this->collection->getCampaignKeys();
        foreach($campaignKeys as $keyName => $keyValue) {
            $this->setStaticColumn($keyName, $keyValue);
        }
    }
    
    public function getRevisionsTable() : string
    {
        return $this->collection->getRevisionsTableName();
    }
    
    public function getIDColumn() : string
    {
        return $this->collection->getPrimaryKeyName();
    }
    
    public function getRevisionColumn() : string
    {
        return $this->collection->getRevisionKeyName();
    }
    
    public function getRevisionableID() : int
    {
        return $this->revisionable->getID();
    }

    /**
     * Creates a new revision for the specified revisionable.
     *
     * @param integer $revisionable_id
     * @param string $label
     * @param Application_StateHandler_State $state
     * @param DateTime $date
     * @param Application_User|NULL $author
     * @param integer $prettyRevision
     * @param string|NULL $comments
     * @param string[] $customColumns
     * @return int
     */
    public function createRevision(int $revisionable_id, string $label, Application_StateHandler_State $state, DateTime $date, ?Application_User $author=null, int $prettyRevision=1, ?string $comments=null, array $customColumns=array()): int
    {
        if($author === null) {
            $author = Application::getUser();
        }

        $data = $customColumns;
        $data[$this->idColumn] = $revisionable_id;
        $data['label'] = $label;
        $data['state'] = $state->getName();
        $data['date'] = $date->format('Y-m-d H:i:s');
        $data['author'] = $author->getID();
        $data['comments'] = $comments;
        $data['pretty_revision'] = $prettyRevision;
        
        $campaignKeys = $this->getStaticColumns();
        foreach($campaignKeys as $keyName => $keyValue) {
            $data[$keyName] = $keyValue; 
        }
        
        return (int)DBHelper::insertDynamic(
            $this->revisionsTable, 
            $data
        );
    }

    protected function getRevisionCopyClass() : string
    {
        return $this->collection->getRecordCopyRevisionClass();
    }
}