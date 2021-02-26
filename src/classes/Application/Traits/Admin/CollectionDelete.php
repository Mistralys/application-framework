<?php

/**
 * 
 * @property Application_Driver $driver
 * @property Application_Request $request
 */
trait Application_Traits_Admin_CollectionDelete
{
    /**
     * @var DBHelper_BaseCollection
     */
    protected $collection;
    
    /**
     * @var DBHelper_BaseRecord
     */
    protected $record;
    
   /**
    * @return DBHelper_BaseCollection
    */
    abstract protected function createCollection();

   /**
    * @return string
    */
    abstract public function getBackOrCancelURL();
    
    public function getNavigationTitle()
    {
        return t('Delete');
    }
    
    public function getTitle()
    {
        return t('Delete');
    }
    
    public function getURLName()
    {
        return 'delete';
    }
    
    protected function init()
    {
        $this->collection = $this->createCollection();
    }
    
    protected function _handleActions()
    {
        $this->record = $this->collection->getByRequest();
        if(!$this->record) {
            $this->redirectWithInfoMessage(
                t('No such record found.'),
                $this->getBackOrCancelURL()
            );
        }
        
        $this->startTransaction();
        
        $this->collection->deleteRecord($this->record);
        
        $this->endTransaction();
        
        $this->redirectWithSuccessMessage(
            $this->getSuccessMessage(),
            $this->getBackOrCancelURL()
        );
    }
    
    protected function getSuccessMessage() : string
    {
        return t(
            'The record %1$s has been successfully deleted at %2$s.', 
            $this->record->getLabel(), 
            date('H:i:s')
        );
    }
}
