<?php
/**
 * @package Application
 * @subpackage Validation
 */

use AppUtils\Interfaces\StringableInterface;

/**
 * Trait used to implement the interface {@see Application_Interfaces_Validatable}.
 *
 * @package Application
 * @subpackage Validation
 *
 * @see Application_Interfaces_Validatable
 */
trait Application_Traits_Validatable
{
    protected ?string $validationMessage = null;
    protected ?int $validationCode = null;

    /**
     * @var array<string,mixed>
     */
    protected array $validationOptions;

    abstract protected function _isValid() : bool;
    
   /**
    * Overwritable: use to initialize the default option values,
    * if any.
    * 
    * @return array<string,mixed>
    */
    public function getValidationOptionDefaults() : array
    {
        return array();
    }
    
    public function setValidationOption(string $name, $value) : void
    {
        if(!isset($this->validationOptions)) {
            $this->validationOptions = $this->getValidationOptionDefaults();
        }
        
        $this->validationOptions[$name] = $value;
    }

    /**
     * @param string $name
     * @param mixed|NULL $default
     * @return mixed|null
     */
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

    /**
     * @param string|number|StringableInterface $message
     * @param int|null $code
     * @return bool
     * @throws UI_Exception
     */
    protected function setValidationError($message, ?int $code=null) : bool
    {
        $this->validationMessage = toString($message);
        $this->validationCode = $code;
        
        return false;
    }
    
    public function getValidationMessage() : ?string
    {
        return $this->validationMessage;
    }

    public function getValidationCode() : ?int
    {
        return $this->validationCode;
    }
}
