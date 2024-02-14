<?php

abstract class DBHelper_BaseFilterSettings extends Application_FilterSettings
{
    protected DBHelper_BaseCollection $collection;
    
    public function __construct(DBHelper_BaseCollection $collection)
    {
        $this->collection = $collection;
        
        parent::__construct($collection->getDataGridName());
    }
    
    protected function inject_search() : HTML_QuickForm2_Element_InputText
    {
        return $this->addElementSearch($this->collection->getRecordSearchableLabels());
    }
}
