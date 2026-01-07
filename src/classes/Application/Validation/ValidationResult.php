<?php
/**
 * File containing the {@link Application_ValidationResult} class.
 *
 * @package Application
 * @subpackage Core
 * @see Application_ValidationResult
 */

declare(strict_types=1);

/**
 * Validation result container: can be used to store a 
 * validation and offer a standardized interface to 
 * access the validation details.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_ValidationResult
{
   /**
    * @var string
    */
    protected $errorMessage = '';
    
   /**
    * @var bool
    */
    protected $valid = true;
  
   /**
    * @var object
    */
    protected $subject;
    
   /**
    * @var integer
    */
    protected $code = 0;
    
   /**
    * The subject being validated.
    * 
    * @param object $subject
    */
    public function __construct(object $subject)
    {
        $this->subject = $subject;
    }
    
   /**
    * Whether the validation was successful.
    * 
    * @return bool
    */
    public function isValid() : bool
    {
        return $this->valid;
    }
    
   /**
    * Retrieves the subject that was validated.
    * 
    * @return object
    */
    public function getSubject() : object
    {
        return $this->subject;
    }
    
   /**
    * Makes the result a succes, with the specified message.
    * 
    * @param string|number|UI_Renderable_Interface $message Should not contain a date, just the system specific info.
    * @return Application_ValidationResult
    */
    public function makeSuccess($message, int $code=0) : Application_ValidationResult
    {
        return $this->setMessage($message, $code, true);
    }
    
   /**
    * Sets the result as an error.
    * 
    * @param string|number|UI_Renderable_Interface $message Should be as detailed as possible.
    * @return Application_ValidationResult
    */
    public function makeError($message, int $code=0) : Application_ValidationResult
    {
        return $this->setMessage($message, $code, false);
    }
    
    protected function setMessage($message, int $code, bool $valid) : Application_ValidationResult
    {
        $this->valid = $valid;
        $this->errorMessage = toString($message);
        $this->code = $code;
        
        return $this;
    }
    
   /**
    * Retrieves the error message, if an error occurred.
    * 
    * @return string The error message, or an empty string if no error occurred.
    */
    public function getErrorMessage() : string
    {
        return $this->getMessage(false);
    }
    
   /**
    * Retrieves the success message, if one has been provided.
    * 
    * @return string
    */
    public function getSuccessMessage() : string
    {
        return $this->getMessage(true);
    }
    
    public function hasCode() : bool
    {
        return $this->code > 0;
    }
    
    public function getCode() : int
    {
        return $this->code;
    }
    
    protected function getMessage(bool $valid) : string
    {
        if($this->valid === $valid) {
            return $this->errorMessage;
        }
        
        return '';
    }
}
