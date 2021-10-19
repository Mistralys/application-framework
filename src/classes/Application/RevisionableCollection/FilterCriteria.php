<?php

abstract class Application_RevisionableCollection_FilterCriteria extends Application_FilterCriteria_Database
{
   /**
    * @var Application_RevisionableCollection
    */
    protected $collection;

    /**
     * @var string
     */
    protected $primaryKeyName;

    /**
     * @var string
     */
    protected $revisionsTable;

    /**
     * @var string
     */
    protected $revisionKeyName;

    /**
     * @var string
     */
    protected $currentRevisionsTable;
    
    public function __construct(Application_RevisionableCollection $collection)
    {
        parent::__construct($collection);

        $this->collection = $collection;
        $this->primaryKeyName = $collection->getPrimaryKeyName();
        $this->revisionsTable = $collection->getRevisionsTableName();
        $this->currentRevisionsTable = $collection->getCurrentRevisionsTableName();
        $this->revisionKeyName = $collection->getRevisionKeyName();
    }
    
   /**
    * @return Application_RevisionableCollection
    */
    public function getCollection()
    {
        return $this->collection;
    }
    
    protected function getQuery()
    {
        $this->prepareFilters();
        
        return sprintf(
            "SELECT 
                {WHAT} 
            FROM 
                `%s` AS `revs` 
            {JOINS} 
            {WHERE} 
            {GROUPBY} 
            {ORDERBY} 
            {LIMIT}",
            $this->revisionsTable
        );
    }
    
    protected function getSelect()
    {
        return array(
            sprintf("`revs`.`%s`", $this->primaryKeyName),
            sprintf("`revs`.`%s`", $this->revisionKeyName)
        );
    }
    
    protected function getCountColumn() : string
    {
        return sprintf('`revs`.`%s`', $this->revisionKeyName);
    }
    
    protected function prepareFilters() : void
    {
        $this->addJoin(
            sprintf(
                "LEFT JOIN
                    `%s` AS `current`
                ON
                    `revs`.`%s`=`current`.`%s`",
                $this->currentRevisionsTable,
                $this->primaryKeyName,
                $this->primaryKeyName
            )
        );
        
        $this->addWhere(sprintf(
            "`revs`.`%s`=`current`.`current_revision`",
            $this->revisionKeyName
        ));
        
        $this->addWhereColumnIN('`revs`.`state`', $this->getCriteriaValues('include_state'));
        $this->addWhereColumnNOT_IN('`revs`.`state`', $this->getCriteriaValues('exclude_state'));
        
        $campaignKeys = $this->collection->getCampaignKeys();
        foreach($campaignKeys as $keyName => $keyValue) {
            $this->addWhere(sprintf(
                "`revs`.`%s`=:%s",
                $keyName,
                $keyName
            ));
            
            $this->addPlaceholder($keyName, $keyValue);
        }
    }
    
    protected function getSearchFields()
    {
        $keys = $this->collection->getRecordSearchableKeys();
        $result = array();
        foreach($keys as $key) {
            $result[] = sprintf('`revs`.`%s`', $key);
        }
        
        return $result;
    }
    
   /**
    * @return Application_RevisionableCollection_DBRevisionable[]
    */
    public function getItemsObjects() : array
    {
        $entries = $this->getItems();
        $result = array();
        $total = count($entries);
        for($i=0; $i<$total; $i++) {
            $result[] = $this->collection->getByID((int)$entries[$i][$this->primaryKeyName]);
        }
        
        return $result;
    }

    /**
     * Selects only lists with or without the specified state.
     *
     * @param string $stateName
     * @param boolean $exclude Whether to exclude this state. Defaults to including it.
     * @return Application_FilterCriteria
     * @throws Application_Exception
     */
    public function selectState(string $stateName, bool $exclude=false)
    {
        $name = 'include_state';
        if($exclude) {
            $name = 'exclude_state';
        }
        
        return $this->selectCriteriaValue($name, $stateName);
    }
    
   /**
    * Retrieves all revisionable IDs for the current filters.
    * @return integer[]
    */
    public function getIDs() : array
    {
        $items = $this->getItems();
        $ids = array();
        foreach($items as $item) {
            $ids[] = (int)$item[$this->primaryKeyName];
        }
        
        return $ids;
    }

   /**
    * Retrieves all revisionable revisions for the current filters.
    * These revisions are always the current revisions for the records.
    * 
    * @return integer[]
    */
    public function getRevisions() : array
    {
        $items = $this->getItems();
        $revs = array();
        foreach($items as $item) {
            $revs[] = (int)$item[$this->revisionKeyName];
        }
        
        return $revs;
    }
}