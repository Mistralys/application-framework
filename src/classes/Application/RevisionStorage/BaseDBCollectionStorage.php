<?php

declare(strict_types=1);

/**
 * @property Application_RevisionableCollection_DBRevisionable $revisionable
 */
abstract class BaseDBCollectionStorage extends BaseDBStandardizedStorage
{ 
    public const ERROR_INVALID_REVISIONABLE_TYPE = 14601;
    
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
                    'BaseDBCollectionStorage',
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
        $this->log('Revisionable [#%s] | Create new revision.', $revisionable_id);

        if($author === null) {
            $author = Application::getUser();
        }

        $data = $customColumns;
        $data[$this->idColumn] = $revisionable_id;
        $data[Application_RevisionableCollection::COL_REV_LABEL] = $label;
        $data[Application_RevisionableCollection::COL_REV_STATE] = $state->getName();
        $data[Application_RevisionableCollection::COL_REV_DATE] = $date->format('Y-m-d H:i:s');
        $data[Application_RevisionableCollection::COL_REV_AUTHOR] = $author->getID();
        $data[Application_RevisionableCollection::COL_REV_COMMENTS] = $comments;
        $data[Application_RevisionableCollection::COL_REV_PRETTY_REVISION] = $prettyRevision;
        
        $campaignKeys = $this->getStaticColumns();
        foreach($campaignKeys as $keyName => $keyValue) {
            $data[$keyName] = $keyValue; 
        }

        $revision = (int)DBHelper::insertDynamic(
            $this->revisionTable,
            $data
        );

        $this->log('Revisionable [#%s] | Created revision [v%s]', $revisionable_id, $revision);

        return $revision;
    }

    protected function getRevisionCopyClass() : string
    {
        return $this->collection->getRecordCopyRevisionClass();
    }

    public function getNextRevisionData(): array
    {
        return $this->revisionable->getCustomKeyValues();
    }
}
