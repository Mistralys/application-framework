<?php

require_once 'Application/FilterCriteria.php';

class Application_Changelog_FilterCriteria extends Application_FilterCriteria
{
   /**
    * @var Application_Changelog
    */
    protected $changelog;
    
    protected $objects = false;
    
    protected $orderField = 'chlog.`changelog_date`';
    
    protected $orderDir = 'DESC';
    
    public function __construct(Application_Changelog $changelog)
    {
        $this->changelog = $changelog;
    }
    
    protected function getQuery()
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
    
    protected function createPristine()
    {
        return new Application_Changelog_FilterCriteria($this->changelog);
    }
    
    protected function getSearchFields()
    {
        return array(
            'chlog.`changelog_data`',
            'chlog.`changelog_type`'
        );
    }
    
    protected function getSelect()
    {
        if($this->objects) {
            return array('chlog.`changelog_id`');
        }
        
        return array("chlog.*");
    }
    
    public function limitByType($type)
    {
        $this->addWhere('chlog.`changelog_type`=:type');
        $this->addPlaceholder('type', $type);
        return $this;
    }
    
    public function limitByAuthorID($author_id)
    {
        $this->addWhere('chlog.`changelog_author`=:author_id');
        $this->addPlaceholder('author_id', $author_id);
        return $this;
    }
    
    public function getItemsObjects()
    {
        $this->objects = true;

        $items = array();
        $entries = $this->getItems();
        foreach($entries as $entry) {
            $items[] = $this->changelog->getByID($entry['changelog_id']);
        }
        
        $this->objects = false;
        return $items;
    }
}
