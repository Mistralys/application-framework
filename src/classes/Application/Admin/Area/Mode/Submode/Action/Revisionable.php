<?php

declare(strict_types=1);

use Application\Revisionable\Collection\BaseRevisionableCollection;
use Application\Revisionable\RevisionableInterface;

/**
 * @property RevisionableInterface $revisionable
 */
abstract class Application_Admin_Area_Mode_Submode_Action_Revisionable extends Application_Admin_Area_Mode_Submode_Action
{
    public const ERROR_INVALID_REVISIONABLE_ID = 15301;
    
    /**
    * @return BaseRevisionableCollection
    */
    abstract protected function createCollection();
    
    protected BaseRevisionableCollection $collection;
    protected string $recordTypeName;
    protected int $revisionableID;
    
    protected function _handleBeforeActions() : void
    {
        $this->requireRevisionable();
    }

   /**
    * Retrieves the revisionable ID from the request, and attempts to retrieve
    * the instance. Stores the instance in the {@link $revisionable} property
    * on success.
    * 
    * @throws Application_Exception
    * @return RevisionableInterface
    */
    protected function requireRevisionable() : RevisionableInterface
    {   
        $this->collection = $this->createCollection();
        $this->recordTypeName = $this->collection->getRecordTypeName();
        
        $this->revisionableID = (int)Application_Driver::getInstance()->getRequest()->registerParam($this->collection->getRecordPrimaryName())->setInteger()->get();
        if(empty($this->revisionableID) || !$this->collection->idExists($this->revisionableID)) {
            throw new Application_Exception(
                'Invalid or missing record ID',
                sprintf(
                    'The ID specified via [%s] to edit the settings of the revisionable [%s] was not valid or empty.',
                    $this->collection->getRecordPrimaryName(),
                    $this->collection->getRecordTypeName()
                ),
                self::ERROR_INVALID_REVISIONABLE_ID
            );
        }
        
        $this->revisionable = $this->collection->getByID($this->revisionableID);
        return $this->revisionable;
    }

    protected function startSimulation(bool $outputToConsole=false) : bool
    {
        if(isset($this->revisionable)) {
            $this->revisionable->setSimulation(true);
        }

        return parent::startSimulation($outputToConsole);
    }
    
    protected function endSimulation() : void
    {
        if(isset($this->revisionable)) {
            $this->revisionable->setSimulation(false);
        }

        parent::endSimulation();
    }

    protected function startRevisionableTransaction(string $comments='') : void
    {
        parent::startTransaction();
        
        $this->revisionable->startCurrentUserTransaction($comments);
    }
    
    protected function endRevisionableTransaction() : void
    {
        $this->revisionable->endTransaction();
        
        parent::endTransaction();
    }
}
