<?php

use Application\Interfaces\FilterCriteriaInterface;
use Application\Revisionable\RevisionableInterface;

interface Application_CollectionInterface extends Application_Interfaces_Disposable
{
   /**
    * @return FilterCriteriaInterface
    */
    public function getFilterCriteria();
    
   /**
    * Checks whether the specified collection record ID exists.
    * @param integer $record_id
    * @return boolean
    */
    public function idExists(int $record_id) : bool;
    
   /**
    * @return Application_CollectionItemInterface
    */
    public function createDummyRecord();
    
   /**
    * Retrieves all available collection records.
    * @return Application_CollectionItemInterface[]
    */
    public function getAll() : array;
    
   /**
    * Retrieves a collection record by its primary key.
    * @param integer $record_id
    * @return Application_CollectionItemInterface
    */
    public function getByID(int $record_id) : Application_CollectionItemInterface;
}
