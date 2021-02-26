<?php

declare(strict_types=1);

/**
 * 
 * @method Application_Countries_Selector setName($name)
 *
 */
class Application_Countries_Selector extends Application_Formable_RecordSelector
{
   /**
    * @var Application_Countries
    */
    protected $collection;
    
   /**
    * @var Application_Countries_FilterCriteria
    */
    protected $filters;
    
   /**
    * @var bool
    */
    protected $includeInvariant = true;
    
    public function excludeInvariant() : Application_Countries_Selector
    {
        $this->includeInvariant = false;
        
        return $this;
    }
    
    public function createCollection() : DBHelper_BaseCollection
    {
        return Application_Countries::getInstance();
    }
    
    protected function configureFilters() : void
    {
        if(!$this->includeInvariant)
        {
            $this->filters->excludeInvariant();
        }
    }
    
    protected function configureEntry(Application_Formable_RecordSelector_Entry $entry) : void
    {
        
    }
}
