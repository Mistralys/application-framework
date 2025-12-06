<?php
/**
 * @package Application
 * @subpackage Revisionable
 */

declare(strict_types=1);

use Application\FilterCriteria\Items\GenericIntegerItem;
use Application\Revisionable\Storage\BaseDBStandardizedStorage;

/**
 * Generic filter criteria implementation for revisionable
 * revisions. Allows selecting and fetching revision data
 * automatically without configuration if the revisionable
 * uses the DBStandardized revision storage.
 * 
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_FilterCriteria
 */
class Application_FilterCriteria_RevisionableRevisions extends Application_FilterCriteria_Database
{
    protected BaseDBStandardizedStorage $storage;
    
    protected bool $stateless = false;
    
    public function __construct(BaseDBStandardizedStorage $storage)
    {
        parent::__construct($storage);

        $this->setOrderBy('`date`', 'ASC');

        $this->storage = $storage;
    }
    
    public function getSearchFields() : array
    {
        return array(
            "`label`",
            "`comments`"
        );
    }
    
    protected function getQuery() : string
    {
        $query =
        "SELECT
            {WHAT}
        FROM
            `".$this->storage->getRevisionsTable()."`
            {JOINS}
            {WHERE}
            {ORDERBY}
            {LIMIT}";
        
        if(!$this->stateless && !empty($this->states)) {
            $this->addWhere("`state` IN('".implode("','", $this->states)."')");
        }
        
        $this->addWhere(sprintf(
            "`%s`=:revisionable_id",
            $this->storage->getIDColumn()
        ));
        
        $this->addPlaceholder('revisionable_id', $this->storage->getRevisionableID());
        
        $staticColums = $this->storage->getStaticColumns();
        foreach($staticColums as $name => $value) {
            $this->addWhere(sprintf("`%s`=:%s", $name, $name.'_value'));
            $this->addPlaceholder($name.'_value', $value);
        }
        
        return $query;
    }

    public function getIDKeyName(): string
    {
        return $this->storage->getRevisionColumn();
    }

    protected function getSelect() : array
    {
        $fields = array(
            '`'.$this->storage->getIDColumn().'` AS `revisionable_id`',
            '`'.$this->storage->getRevisionColumn().'` AS `revisionable_revision`',
            '`'.$this->storage->getIDColumn().'`',
            '`'.$this->storage->getRevisionColumn().'`',
            '`pretty_revision`',
            '`date`',
            '`author`',
            '`comments`'
        );
        
        if(!$this->stateless) {
            $fields[] = '`state`';
        }
        
        if(!empty($this->additionalColumns)) {
            foreach($this->additionalColumns as $column) {
                $fields[] = '`'.$column.'`';
            }
        }
        
        return $fields;
    }

    /**
     * @return GenericIntegerItem[]
     */
    public function getItemsObjects(): array
    {
        $items = array();

        foreach($this->getItems() as $itemData) {
            $items[] = new GenericIntegerItem(
                (int)$itemData['revisionable_revision'],
                (string)$itemData['pretty_revision'],
                $itemData
            );
        }

        return $items;
    }

    protected array $states = array();
    
   /**
    * Selects an additional state to limit the results to.
    * Several states can be selected.
    * 
    * @param string $state
    * @return Application_FilterCriteria_RevisionableRevisions
    */
    public function selectState($state)
    {
        if(!in_array($state, $this->states)) {
            $this->states[] = $state;
        }
        
        return $this;
    }

    protected $additionalColumns = array();

   /**
    * Adds a column name to the select statement. Use this
    * to add revisionable-specific columns to the query so
    * they are returned with the items.
    * 
    * @param string $columnName The column name, without quotes.
    * @return Application_FilterCriteria_RevisionableRevisions
    */
    public function addColumn($columnName)
    {
        $columnName = trim($columnName, '`');
        
        if(!in_array($columnName, $this->additionalColumns)) {
            $this->additionalColumns[] = $columnName;
        }
        
        return $this;
    }

    protected function _registerJoins() : void
    {
        // TODO: Implement _registerJoins() method.
    }

    protected function _registerStatementValues(DBHelper_StatementBuilder_ValuesContainer $container) : void
    {
        // TODO: Implement _registerStatementValues() method.
    }
}