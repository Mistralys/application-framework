<?php

declare(strict_types=1);

use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\DBHelperFilterSettingsInterface;

abstract class DBHelper_BaseFilterSettings extends Application_FilterSettings implements DBHelperFilterSettingsInterface
{
    protected DBHelperCollectionInterface $collection;
    
    public function __construct(DBHelperCollectionInterface $collection)
    {
        $this->collection = $collection;
        
        parent::__construct($collection->getDataGridName());
    }

    public function getCollection() : DBHelperCollectionInterface
    {
        return $this->collection;
    }
    
    protected function inject_search() : HTML_QuickForm2_Element_InputText
    {
        return $this->addElementSearch($this->collection->getRecordSearchableLabels());
    }
}
