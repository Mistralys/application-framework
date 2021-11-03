<?php

abstract class Application_Admin_Area_Mode_CollectionRecord extends Application_Admin_Area_Mode
{
    /**
     * @var DBHelper_BaseCollection
     */
    protected $collection;
    
    /**
     * @var DBHelper_BaseRecord
     */
    protected $record;
    
    abstract protected function createCollection();

    abstract protected function getRecordMissingURL() : string;
    
    protected function init() : void
    {
        $this->collection = $this->createCollection();
        $this->record = $this->collection->getByRequest();
        
        if(!$this->record) {
            $this->redirectWithErrorMessage(
                t('No such record found.'),
                $this->getRecordMissingURL()
            );
        }
        
        parent::init();
    }
    
   /**
    * @return DBHelper_BaseRecord
    */
    public function getRecord()
    {
        return $this->record;
    }
    
   /**
    * @return DBHelper_BaseCollection
    */
    public function getCollection()
    {
        return $this->collection;
    }
}