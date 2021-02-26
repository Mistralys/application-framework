<?php

abstract class Application_Admin_Area_Mode_Submode_Action_CollectionRecord extends Application_Admin_Area_Mode_Submode_Action
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
    
    abstract protected function getRecordMissingURL();

    protected function init()
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
        
        $this->validateRequest();
    }
    
    protected function validateRequest() : void
    {
        
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
    
   /**
    * Updated to automatically add the record's primary
    * key value to the data grid's hidden parameters.
    * 
    * {@inheritDoc}
    * @see Application_Admin_Skeleton::configureDataGrid()
    */
    protected function configureDataGrid(string $id='') : UI_DataGrid
    {
        $grid = parent::configureDataGrid($id);
        
        $record = $this->getRecord();
        
        $grid->addHiddenVar($record->getRecordPrimaryName(), (string)$record->getID());
        
        $collection = $record->getCollection();
        
        if($collection->hasParentCollection())
        {
            $parent = $collection->getParentRecord();
            
            $grid->addHiddenVar($parent->getRecordPrimaryName(), (string)$parent->getID());
        }
        
        return $grid;
    }
    
   /**
    * Updated to automatically add the record's primary
    * key value to the form's hidden variables. Also adds
    * the parent record's ID if present. 
    * 
    * @param string $name
    * @param array $defaultData
    */
    public function createFormableForm(string $name, array $defaultData=array()) : void
    {
        parent::createFormableForm($name, $defaultData);
        
        $this->addFormablePageVars();
        
        $record = $this->getRecord();
        
        $this->addHiddenVar($record->getRecordPrimaryName(), (string)$record->getID());
        
        $collection = $record->getCollection();
        
        if($collection->hasParentCollection())
        {
            $parent = $collection->getParentRecord();
            
            $this->addHiddenVar($parent->getRecordPrimaryName(), (string)$parent->getID());
        }
    }
}
