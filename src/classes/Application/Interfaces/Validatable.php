<?php
/**
 * @package Application
 * @subpackage Interfaces
 */

declare(strict_types=1);

use AppUtils\OperationResult_Collection;

/**
 * Interface for an element that can be validated.
 *
 * NOTE: Only supports a single validation error
 * message to be generated. As an alternative,
 * consider using {@see OperationResult_Collection}.
 *
 * @package Application
 * @subpackage Interfaces
 *
 * @see Application_Traits_Validatable
 */
interface Application_Interfaces_Validatable
{
    public function isValid() : bool;
    
    public function getValidationMessage() : ?string;
    public function getValidationCode() : ?int;
    
    public function setValidationOption(string $name, $value) : void;
    
    public function getValidationOption(string $name, $default=null);
    
    public function getValidationOptionDefaults() : array;
}
