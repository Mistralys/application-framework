<?php
/**
 * @package Application
 * @subpackage Environments
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
    protected static ?bool $isWin = null;
    
    public function __construct()
    {
        if(!isset(self::$isWin))
        {
            self::$isWin = PHP_OS_FAMILY === 'Windows';
        }
    }
    
    public function isValid() : bool
    {
        return self::$isWin;
    }
}
