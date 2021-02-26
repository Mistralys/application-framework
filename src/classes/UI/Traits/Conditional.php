<?php

declare(strict_types=1);

trait UI_Traits_Conditional
{
   /**
    * @var boolean
    */
    protected $conditionalValid = true;
    
    /**
     * @var string
     */
    protected $invalidReason = '';

   /**
    * The element will only be shown if the specified condition evaluates to true.
    * 
    * @param bool $enabled
    * @param string $reason
    * @return $this
    */
    public function requireTrue(bool $enabled, string $reason='')
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
     */
    public function requireRight(string $rightName)
    {
        return $this->requireTrue(Application::getUser()->can($rightName));
    }

   /**
    * @param string $reason
    * @return $this
    */
    protected function setInvalid(string $reason='')
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
    public function requireFalse(bool $enabled, string $reason='')
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
    public function requireEditable(Application_LockableRecord_Interface $record)
    {
        if(!$record->isEditable()) 
        {
            $this->setInvalid(t('The record %1$s is not editable.', $record->getLabel()));
        }
        
        return $this;
    }
    
    /**
     * Requires the revisionable to be in a state that allows changes.
     * @param Application_Revisionable $revisionable
     * @return $this
     */
    public function requireChanging(Application_Revisionable $revisionable)
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
