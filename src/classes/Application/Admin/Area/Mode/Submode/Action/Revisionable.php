<?php

require_once 'Application/Admin/Area/Mode/Submode/Action.php';

abstract class Application_Admin_Area_Mode_Submode_Action_Revisionable extends Application_Admin_Area_Mode_Submode_Action
{
    const ERROR_INVALID_REVISIONABLE_ID = 15301;
    
    /**
    * @return Application_RevisionableCollection
    */
    abstract protected function createCollection();
    
    /**
     * @var Application_RevisionableCollection
     */
    protected $collection;
    
    protected $recordTypeName;
    
    protected $revisionableID;
    
    /**
     * @var Application_RevisionableCollection_DBRevisionable
     */
    protected $revisionable;
 
    protected function _handleBeforeActions()
    {
        $this->requireRevisionable();
    }

   /**
    * Retrieves the revisionable ID from the request, and attempts to retrieve
    * the instance. Stores the instance in the {@link $revisionable} property
    * on success.
    * 
    * @throws Application_Exception
    * @return Application_RevisionableCollection_DBRevisionable
    */
    protected function requireRevisionable()
    {   
        $this->collection = $this->createCollection();
        $this->recordTypeName = $this->collection->getRecordTypeName();
        
        $this->revisionableID = intval(Application_Driver::getInstance()->getRequest()->registerParam($this->collection->getPrimaryKeyName())->setInteger()->get());
        if(empty($this->revisionableID) || !$this->collection->idExists($this->revisionableID)) {
            throw new Application_Exception(
                'Invalid or missing record ID',
                sprintf(
                    'The ID specified via [%s] to edit the settings of the revisionable [%s] was not valid or empty.',
                    $this->collection->getPrimaryKeyName(),
                    $this->collection->getRecordTypeName()
                ),
                self::ERROR_INVALID_REVISIONABLE_ID
            );
        }
        
        $this->revisionable = $this->collection->getByID($this->revisionableID);
        return $this->revisionable;
    }

    protected function startSimulation($outputToConsole=false)
    {
        if(isset($this->revisionable)) {
            $this->revisionable->setSimulation(true);
        }

        return parent::startSimulation($outputToConsole);
    }
    
    protected function endSimulation()
    {
        parent::endSimulation();
        
        if(isset($this->revisionable)) {
            $this->revisionable->setSimulation(false);
        }
    }

    protected function startRevisionableTransaction($comments='')
    {
        parent::startTransaction();
        
        $this->revisionable->startCurrentUserTransaction($comments);
    }
    
    protected function endRevisionableTransaction()
    {
        $this->revisionable->endTransaction();
        
        parent::endTransaction();
    }
}