<?php
/**
 * File containing the interface {@see Application_Interfaces_Loggable}.
 *
 * @package Application
 * @subpackage Logger
 * @see Application_Interfaces_Loggable
 */

declare(strict_types=1);

/**
 * Interface for classes that use the loggable trait.
 *
 * @package Application
 * @subpackage Logger
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Loggable 
 */
interface Application_Interfaces_Loggable
{
    public function getLogIdentifier() : string;

    public function isLoggingEnabled() : bool;

    public function getLogger() : Application_Logger;
}
