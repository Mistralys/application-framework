<?php

declare(strict_types=1);

use Application\Application;
use Application\Revisionable\RevisionableInterface;

trait UI_Traits_Conditional
{
    protected bool $conditionalValid = true;
    protected string $invalidReason = '';

   /**
    * The element will only be shown if the specified condition evaluates to true.
    * 
    * @param bool $enabled
    * @param string $reason
    * @return $this
    */
    public function requireTrue(bool $enabled, string $reason='') : self
    {
        if($enabled !== true)
        {
            $this->setInvalid($reason);
        }
        
        return $this;
    }

    /**
     * @param string $rightName
     * @return $this
     * @throws Application_Exception
     */
    public function requireRight(string $rightName) : self
    {
        return $this->requireTrue(Application::getUser()->can($rightName));
    }

    /**
     * @param string|string[] $rightNames
     * @return $this
     * @throws Application_Exception
     */
    public function requireRights($rightNames) : self
    {
        if(is_string($rightNames))
        {
            $rightNames = array($rightNames);
        }

        foreach($rightNames as $rightName)
        {
            $this->requireRight($rightName);
        }

        return $this;
    }

   /**
    * @param string $reason
    * @return $this
    */
    protected function setInvalid(string $reason='') : self
    {
        $this->conditionalValid = false;
        $this->invalidReason = $reason;
        
        return $this;
    }
    
   /**
    * The element will only be shown if the specified condition evaluates to false.
    * 
    * @param bool $enabled
    * @param string $reason
    * @return $this
    */
    public function requireFalse(bool $enabled, string $reason='') : self
    {
        if($enabled !== false)
        {
            $this->setInvalid($reason);
        }
        
        return $this;
    }
    
    
    /**
     * The button will only be shown if the lockable item is editable.
     * @param Application_LockableRecord_Interface $record
     * @return $this
     */
    public function requireEditable(Application_LockableRecord_Interface $record) : self
    {
        if(!$record->isEditable()) 
        {
            $this->setInvalid(t('The record %1$s is not editable.', $record->getLabel()));
        }
        
        return $this;
    }
    
    /**
     * Requires the revisionable to be in a state that allows changes.
     * @param RevisionableInterface $revisionable
     * @return $this
     */
    public function requireChanging(RevisionableInterface $revisionable) : self
    {
        if(!$revisionable->isChangingAllowed()) 
        {
            $this->setInvalid(t('The record %1$s may not be modified.', $revisionable->getLabel()));
        }
        
        return $this;
    }
    
    public function isValid() : bool
    {
        return $this->conditionalValid;
    }

    /**
     * Retrieves the validation message (if any) that details
     * why this item is invalid.
     *
     * @return string
     */
    public function getInvalidReason() : string
    {
        if(!$this->isValid())
        {
            return $this->invalidReason;
        }
        
        return '';
    }
    
}
