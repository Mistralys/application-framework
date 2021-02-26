<?php

trait Application_Traits_Validatable
{
    /**
     * @var string|NULL
     */
    protected $validationMessage = null;
    
    protected $validationOptions;

    abstract protected function _isValid() : bool;
    
   /**
    * Overwritable: use to initialize the default option values,
    * if any.
    * 
    * @return array
    */
    public function getValidationOptionDefaults() : array
    {
        return array();
    }
    
    public function setValidationOption($name, $value) : void 
    {
        if(!isset($this->validationOptions)) {
            $this->validationOptions = $this->getValidationOptionDefaults();
        }
        
        $this->validationOptions[$name] = $value;
    }
    
    public function getValidationOption(string $name, $default=null)
    {
        if(!isset($this->validationOptions)) {
            $this->validationOptions = $this->getValidationOptionDefaults();
        }
        
        if(isset($this->validationOptions[$name])) {
            return $this->validationOptions[$name];
        }
        
        return $default;
    }
    
    public function isValid() : bool
    {
        $this->validationMessage = null;
        
        return $this->_isValid();
    }
    
    protected function setValidationError($message) : bool
    {
        $this->validationMessage = $message;
        
        return false;
    }
    
    public function getValidationMessage() : ?string
    {
        return $this->validationMessage;
    }
}
