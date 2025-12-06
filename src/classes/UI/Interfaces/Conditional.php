<?php

declare(strict_types=1);

use Application\Revisionable\RevisionableInterface;

interface UI_Interfaces_Conditional
{
   /**
    * @param bool $statement
    * @param string $reason
    * @return $this
    */
    public function requireTrue(bool $statement, string $reason='') : self;

   /**
    * @param bool $statement
    * @param string $reason
    * @return $this
    */
    public function requireFalse(bool $statement, string $reason='') : self;

   /**
    * @param RevisionableInterface $revisionable
    * @return $this
    */
    public function requireChanging(RevisionableInterface $revisionable) : self;
    
   /**
    * @param Application_LockableRecord_Interface $record
    * @return $this
    */
    public function requireEditable(Application_LockableRecord_Interface $record) : self;
    
   /**
    * @return bool
    */
    public function isValid() : bool;
    
    public function getInvalidReason() : string;
}
