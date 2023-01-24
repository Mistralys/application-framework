<?php

abstract class Application_RevisionableCollection_AjaxMethod extends Application_AjaxMethod
{
   /**
    * @return Application_RevisionableCollection
    */
    abstract protected function getCollection();
    
    protected Application_RevisionableCollection $collection;
    
    protected ?Application_RevisionableCollection_DBRevisionable $revisionable = null;
    
   /**
    * @var int
    */
    protected $revisionableID;
    
    protected function init()
    {
        $this->collection = $this->getCollection();
    }
    
   /**
    * Ensures that a revisionable ID has been specified in the request,
    * and that it matches a valid revisionable. Stores the object instance
    * in the {@link $revisionable} property.
    * 
    * @return Application_RevisionableCollection_DBRevisionable
    */
    protected function requireRevisionable() : Application_RevisionableCollection_DBRevisionable
    {
        if(isset($this->revisionable)) {
            return $this->revisionable;
        }
        
        $revisionableID = $this->request->registerParam($this->collection->getPrimaryKeyName())->setInteger()->setCallback(array($this->collection, 'idExists'))->get();
        if(empty($revisionableID)) {
            $this->sendErrorUnknownElement(t('%1$s ID', $this->collection->getRecordReadableNameSingular()));
        }
        
        $this->revisionable = $this->collection->getByID($revisionableID);
        $this->revisionableID = $revisionableID;
        
        return $this->revisionable;
    }
    
    protected $initialRevisionableState;
    
    protected function startRevisionableTransaction($comments=null)
    {
        $revisionable = $this->requireRevisionable();
        $this->startTransaction();
        
        $this->initialRevisionableState = $revisionable->getStateName();
        
        $revisionable->setSimulation($this->isSimulationEnabled());
        $revisionable->startCurrentUserTransaction($comments);
    }
    
    protected function endRevisionableTransaction()
    {
        $this->requireRevisionable()->endTransaction();
        $this->endTransaction();
    }
    
    protected function hasStateChanged()
    {
        if($this->requireRevisionable()->getStateName() != $this->initialRevisionableState) {
            return true;
        }
        
        return false;
    }
}