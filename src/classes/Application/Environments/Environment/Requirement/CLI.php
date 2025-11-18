<?php
/**
 * @package Application
 * @subpackage Environments
 */

declare(strict_types=1);

/**
 * Requires the environment to be running in command line mode.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Environments_Environment_Requirement_CLI extends Application_Environments_Environment_Requirement
{
    protected static ?bool $isCLI = null;
    
    public function __construct()
    {
        if(!isset(self::$isCLI))
        {
            self::$isCLI = http_response_code() === false;
        }
    }
    
    public function isValid() : bool
    {
        return self::$isCLI;
    }
}
