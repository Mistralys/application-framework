<?php

require_once 'Application/Admin/Area/Mode/Submode.php';

abstract class Application_Admin_Area_Mode_Submode_Revisionable extends Application_Admin_Area_Mode_Submode
{
    const ERROR_INVALID_REVISIONABLE_ID = 15901;
    
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
    
    protected function init()
    {
        $this->collection = $this->createCollection();
        $this->recordTypeName = $this->collection->getRecordTypeName();
    }
    
    abstract protected function createCollection();

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
        $this->revisionableID = intval($this->request->registerParam($this->collection->getPrimaryKeyName())->setInteger()->get());
        if(empty($this->revisionableID) || !$this->collection->idExists($this->revisionableID)) {
            throw new Application_Exception(
                'Invalid or missing record ID',
                sprintf(
                    'The ID specified to edit the settings of the revisionable [%s] was not valid or empty.',
                    $this->collection->getRecordTypeName()
                ),
                self::ERROR_INVALID_REVISIONABLE_ID
            );
        }
        
        $this->revisionable = $this->collection->getByID($this->revisionableID);
        return $this->revisionable;
    }
}