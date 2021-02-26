<?php

require_once 'Application/FilterCriteria.php';

abstract class Application_RevisionableCollection_FilterCriteria extends Application_FilterCriteria
{
   /**
    * 
    * @var Application_RevisionableCollection
    */
    protected $collection;
    
    protected $primaryKeyName;
    
    protected $revisionsTable;
    
    protected $revisionKeyName;
    
    protected $currentRevisionsTable;
    
    public function __construct(Application_RevisionableCollection $collection)
    {
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
    
    protected function getCountColumn()
    {
        return sprintf('`revs`.`%s`', $this->revisionKeyName);
    }
    
    protected function prepareFilters()
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
    public function getItemsObjects()
    {
        $entries = $this->getItems();
        $result = array();
        $total = count($entries);
        for($i=0; $i<$total; $i++) {
            $result[] = $this->collection->getByID($entries[$i][$this->primaryKeyName]);
        }
        
        return $result;
    }

   /**
    * Selects only lists with or without the specified state.
    * 
    * @param string $stateName
    * @param boolean $exlude Whether to exlude this state. Defaults to including it.
    * @return Application_FilterCriteria
    */
    public function selectState($stateName, $exlude=false)
    {
        $name = 'include_state';
        if($exlude) {
            $name = 'exclude_state';
        }
        
        return $this->selectCriteriaValue($name, $stateName);
    }
    
    protected function createPristine()
    {
        $class = get_class($this);
        return new $class($this->collection);
    }

   /**
    * Retrieves all revisionable IDs for the current filters.
    * @return integer[]
    */
    public function getIDs()
    {
        $items = $this->getItems();
        $ids = array();
        foreach($items as $item) {
            $ids[] = $item[$this->primaryKeyName];
        }
        
        return $ids;
    }

   /**
    * Retrieves all revisionable revisions for the current filters.
    * These revisions are always the current revisions for the records.
    * 
    * @return integer[]
    */
    public function getRevisions()
    {
        $items = $this->getItems();
        $revs = array();
        foreach($items as $item) {
            $revs[] = $item[$this->revisionKeyName];
        }
        
        return $revs;
    }
}