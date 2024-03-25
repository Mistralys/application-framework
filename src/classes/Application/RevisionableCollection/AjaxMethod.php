<?php

declare(strict_types=1);

use Application\Revisionable\RevisionableInterface;

abstract class Application_RevisionableCollection_AjaxMethod extends Application_AjaxMethod
{
   /**
    * @return Application_RevisionableCollection
    */
    abstract protected function getCollection();
    
    protected Application_RevisionableCollection $collection;
    protected ?RevisionableInterface $revisionable = null;
    protected int $revisionableID;
    
    protected function init() : void
    {
        $this->collection = $this->getCollection();
    }
    
   /**
    * Ensures that a revisionable ID has been specified in the request,
    * and that it matches a valid revisionable. Stores the object instance
    * in the {@link $revisionable} property.
    * 
    * @return RevisionableInterface
    */
    protected function requireRevisionable() : RevisionableInterface
    {
        if(isset($this->revisionable)) {
            return $this->revisionable;
        }

        $revisionable = $this->collection->getByRequest();
        if($revisionable === null) {
            $this->sendErrorUnknownElement(t('%1$s ID', $this->collection->getRecordReadableNameSingular()));
        }
        
        $this->revisionable = $revisionable;
        $this->revisionableID = $revisionable->getID();
        
        return $this->revisionable;
    }
    
    protected string $initialRevisionableState;
    
    protected function startRevisionableTransaction(?string $comments=null) : void
    {
        $revisionable = $this->requireRevisionable();
        $this->startTransaction();
        
        $this->initialRevisionableState = $revisionable->getStateName();
        
        $revisionable->setSimulation($this->isSimulationEnabled());
        $revisionable->startCurrentUserTransaction($comments);
    }
    
    protected function endRevisionableTransaction() : void
    {
        $this->requireRevisionable()->endTransaction();
        $this->endTransaction();
    }
    
    protected function hasStateChanged() : bool
    {
        return $this->requireRevisionable()->getStateName() !== $this->initialRevisionableState;
    }
}
