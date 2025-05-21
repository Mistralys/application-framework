<?php

declare(strict_types=1);

use AppUtils\Microtime;

class Application_Changelog_FilterCriteria extends Application_FilterCriteria_Database
{
    protected Application_Changelog $changelog;
    protected bool $objects = false;
    
    public function __construct(Application_Changelog $changelog)
    {
        parent::__construct($changelog);

        $this->setOrderBy('chlog.`changelog_date`', 'DESC');

        $this->changelog = $changelog;
    }

    protected function getQuery() : string
    {
        $primary = $this->changelog->getPrimary();
        foreach($primary as $name => $value) {
            $this->addWhere('chlog.`' . $name . '`=:'.$name);
            $this->addPlaceholder($name, $value);
        }
        
        $query =
        "SELECT
            {WHAT}
        FROM
            `".$this->changelog->getTableName()."` AS chlog
        {JOINS}
        {WHERE}
        {ORDERBY}
        {LIMIT}";
        
        // let the changelog owner adjust the filters as needed
        $this->changelog->getOwner()->configureChangelogFilters($this);
        
        return $query;
    }
    
    protected function getSearchFields() : array
    {
        return array(
            'chlog.`changelog_data`',
            'chlog.`changelog_type`'
        );
    }
    
    protected function getSelect() : array
    {
        if($this->objects) {
            return array('chlog.`changelog_id`');
        }
        
        return array("chlog.*");
    }

    /**
     * @param string $type
     * @return $this
     * @throws Application_Exception
     */
    public function limitByType(string $type) : self
    {
        $this->addWhere('chlog.`changelog_type`=:type');
        $this->addPlaceholder('type', $type);
        return $this;
    }

    /**
     * @param string|int $author_id
     * @return $this
     * @throws Application_Exception
     */
    public function limitByAuthorID($author_id) : self
    {
        $this->addWhere('chlog.`changelog_author`=:author_id');
        $this->addPlaceholder('author_id', $author_id);
        return $this;
    }

    /**
     * @param string $name
     * @param string|int $value
     * @return $this
     */
    public function limitByCustomField(string $name, $value) : self
    {
        $placeholder = $this->generatePlaceholder($value);
        $this->addWhere('chlog.`'.$name.'`='.$placeholder);
        return $this;
    }

    /**
     * @param Microtime $dateTo
     * @return $this
     */
    public function limitByDateTo(Microtime $dateTo) : self
    {
        $value = $dateTo->getMySQLDate();
        $placeholder = $this->generatePlaceholder($value);
        $this->addWhere('chlog.`'.Application_Changelog::COL_DATE.'`<='.$placeholder);
        return $this;
    }

    /**
     * @param Microtime $dateFrom
     * @return $this
     */
    public function limitByDateFrom(Microtime $dateFrom) : self
    {
        $value = $dateFrom->getMySQLDate();
        $placeholder = $this->generatePlaceholder($value);
        $this->addWhere('chlog.`'.Application_Changelog::COL_DATE.'`>='.$placeholder);
        return $this;
    }

    /**
     * @return Application_Changelog_Entry[]
     * @throws Application_Exception
     * @throws DBHelper_Exception
     */
    public function getItemsObjects() : array
    {
        $this->objects = true;

        $items = array();
        $entries = $this->getItems();
        foreach($entries as $entry) {
            $items[] = $this->changelog->getByID((int)$entry[Application_Changelog::COL_PRIMARY_ID]);
        }
        
        $this->objects = false;
        return $items;
    }

    public function getLatest() : ?Application_Changelog_Entry
    {
        $this->setLimit(1);
        $items = $this->getItemsObjects();

        if(!empty($items[0])) {
            return $items[0];
        }

        return null;
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
