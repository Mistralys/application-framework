<?php

use Application\Interfaces\Admin\MissingRecordInterface;

abstract class Application_Admin_Area_Mode_Submode_Action_CollectionRecord
    extends Application_Admin_Area_Mode_Submode_Action
    implements MissingRecordInterface
{
    /**
     * @var DBHelper_BaseCollection
     */
    protected $collection;
    
    /**
     * @var DBHelper_BaseRecord
     */
    protected $record;
    
    abstract protected function createCollection() : DBHelper_BaseCollection;
    
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
        
        $this->validateRequest();
    }

    /**
     * Called after the screen's `init()` method. Can be overwritten
     * in the extending class as replacement for the `init()` method.
     */
    protected function validateRequest() : void
    {
        
    }
    
   /**
    * @return DBHelper_BaseRecord
    */
    public function getRecord() : DBHelper_BaseRecord
    {
        return $this->record;
    }
    
   /**
    * @return DBHelper_BaseCollection
    */
    public function getCollection() : DBHelper_BaseCollection
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
    * @inheritDoc
    */
    public function createFormableForm(string $name, $defaultData=array()) : self
    {
        parent::createFormableForm($name, $defaultData);
        
        $this->addFormablePageVars();
        
        $record = $this->getRecord();
        
        $this->addHiddenVar($record->getRecordPrimaryName(), (string)$record->getID());
        
        $collection = $record->getCollection();
        
        if($collection->hasParentCollection())
        {
            $parent = $collection->requireParentRecord();
            
            $this->addHiddenVar($parent->getRecordPrimaryName(), (string)$parent->getID());
        }

        return $this;
    }
}
