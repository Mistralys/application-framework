<?php

declare(strict_types=1);

use Application\Revisionable\RevisionableInterface;
use Application\RevisionableCollection\RevisionableFilterCriteriaInterface;
use Application\RevisionableCollection\RevisionableStateFilterTrait;

abstract class Application_RevisionableCollection_FilterCriteria
    extends Application_FilterCriteria_DatabaseExtended
    implements RevisionableFilterCriteriaInterface
{
    use RevisionableStateFilterTrait;

    // region: X - Interface methods

    protected Application_RevisionableCollection $collection;
    protected string $primaryKeyName;
    protected string $revisionsTable;
    protected string $revisionKeyName;
    protected string $currentRevisionsTable;
    
    public function __construct(Application_RevisionableCollection $collection)
    {
        parent::__construct($collection);

        $this->collection = $collection;
        $this->primaryKeyName = $collection->getPrimaryKeyName();
        $this->revisionsTable = $collection->getRevisionsTableName();
        $this->currentRevisionsTable = $collection->getCurrentRevisionsTableName();
        $this->revisionKeyName = $collection->getRevisionKeyName();
    }
    
    public function getCollection() : Application_RevisionableCollection
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

    protected function getSelect() : array
    {
        return array(
            sprintf("`revs`.`%s`", $this->primaryKeyName),
            sprintf("`revs`.`%s`", $this->revisionKeyName)
        );
    }

    public function getRevisionColumn() : string
    {
        return '`revs`.`'.$this->revisionKeyName.'`';
    }

    public function getStatusColumn() : string
    {
        return '`revs`.`'.Application_RevisionableCollection::COL_REV_STATE.'`';
    }
    
    protected function getCountColumn() : string
    {
        return sprintf('`revs`.`%s`', $this->revisionKeyName);
    }

    protected function getSearchFields() : array
    {
        $keys = $this->collection->getRecordSearchableKeys();
        $result = array();
        foreach($keys as $key) {
            $result[] = sprintf('`revs`.`%s`', $key);
        }
        
        return $result;
    }

    /**
     * @return RevisionableInterface[]
     */
    public function getItemsObjects() : array
    {
        $result = array();

        foreach($this->getIDs() as $id) {
            $result[] = $this->collection->getByID($id);
        }

        return $result;
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
        $revKey = $this->getRevisionColumn();
        $items = $this->getItems();
        $revs = array();
        foreach($items as $item) {
            $revs[] = (int)$item[$revKey];
        }

        return $revs;
    }
    
    // endregion

    // region: Applying filters

    protected function prepareFilters() : void
    {
        $this->applyCurrentRevision();
        $this->applyIncludeStates();
        $this->applyExcludeStates();
        $this->applyCampaignKeys();
    }

    protected function applyCampaignKeys() : void
    {
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

    protected function applyCurrentRevision() : void
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
    }

    // endregion
}
