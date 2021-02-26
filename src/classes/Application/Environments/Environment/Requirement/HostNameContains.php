<?php
/**
 * File containing the {@link Application_Environments_Environment_Requirement_HostNameContains} class.
 *
 * @package Application
 * @subpackage Environments
 * @see Application_Environments_Environment_Requirement_HostNameContains
 */

declare(strict_types=1);

/**
 * Requires the environment to be running on a host
 * containing a specific search string in its host name.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Environments_Environment_Requirement_HostNameContains extends Application_Environments_Environment_Requirement
{
   /**
    * @var string
    */
    protected $search;
 
    /**
     * @var string
     */
    protected static $hostName;
    
    public function __construct(string $search)
    {
        $this->search = $search;
        
        if(!isset(self::$hostName))
        {
            $this->detectHostname();
            
            self::$hostName = $this->detectHostname();
        }
    }
    
    protected function detectHostname() : string
    {
        if(function_exists('apache_getenv'))
        {
            return apache_getenv('HTTP_HOST');
        }

        if(isset($_SERVER['HTTP_HOST']))
        {
            return $_SERVER['HTTP_HOST'];
        }

        return '';
    }
    
    public function isValid() : bool
    {
        return strpos(self::$hostName, $this->search) !== false;
    }
}
