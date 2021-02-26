<?php

abstract class DBHelper_BaseFilterSettings extends Application_FilterSettings
{
   /**
    * @var DBHelper_BaseCollection
    */
    protected $collection;
    
    public function __construct(DBHelper_BaseCollection $collection)
    {
        $this->collection = $collection;
        
        parent::__construct($collection->getDataGridName());
    }
    
    protected function inject_search()
    {
        $this->addElementSearch($this->collection->getRecordSearchableLabels(), $this->container);
    }
}