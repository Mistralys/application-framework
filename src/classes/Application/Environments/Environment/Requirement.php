<?php
/**
 * File containing the {@link Application_Environments_Environment_Requirement} class.
 *
 * @package Application
 * @subpackage Environments
 * @see Application_Environments_Environment_Requirement
 */

declare(strict_types=1);

/**
 * Abstract base class for environment requirements.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class Application_Environments_Environment_Requirement
{
    public function getID() : string
    {
        return str_replace('Application_Environments_Environment_Requirement_', '', get_class($this));
    }
    
    abstract public function isValid() : bool;
}
