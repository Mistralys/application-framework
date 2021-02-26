<?php
/**
 * File containing the {@link Application_Environments_Environment_Requirement_Windows} class.
 *
 * @package Application
 * @subpackage Environments
 * @see Application_Environments_Environment_Requirement_Windows
 */

declare(strict_types=1);

/**
 * Requires the environment to be running on Windows.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Environments_Environment_Requirement_Windows extends Application_Environments_Environment_Requirement
{
   /**
    * @var bool
    */
    protected static $isWin;
    
    public function __construct()
    {
        if(!isset(self::$isWin))
        {
            self::$isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        }
    }
    
    public function isValid() : bool
    {
        return self::$isWin;
    }
}
