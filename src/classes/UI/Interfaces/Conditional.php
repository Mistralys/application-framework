<?php

interface UI_Interfaces_Conditional
{
   /**
    * @param bool $statement
    * @param string $reason
    * @return $this
    */
    public function requireTrue(bool $statement, string $reason='');

   /**
    * @param bool $statement
    * @param string $reason
    * @return $this
    */
    public function requireFalse(bool $statement, string $reason='');

   /**
    * @param Application_Revisionable $revisionable
    * @return $this
    */
    public function requireChanging(Application_Revisionable $revisionable);
    
   /**
    * @param Application_LockableRecord_Interface $record
    * @return $this
    */
    public function requireEditable(Application_LockableRecord_Interface $record);
    
   /**
    * @return bool
    */
    public function isValid() : bool;
    
    public function getInvalidReason() : string;
}
