<?php
/**
 * File containing the {@link Application_Environments} class.
 * 
 * @package Application
 * @subpackage Environments
 * @see Application_Environments
 */

declare(strict_types=1);

/**
 * Environment manager: handles detecting the environment 
 * in which the application runs. This is used in the
 * configuration to determine the settings to use based
 * on the environment (local, dev, prod).
 * 
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Environments
{
    public const ERROR_NO_ENVIRONMENTS_REGISTERED = 47601;
    public const ERROR_ENVIRONMENT_ALREADY_REGISTERED = 47602;
    public const ERROR_UNREGISTERED_ENVIRONMENT = 47603;
    
    const TYPE_DEV = 'dev';
    const TYPE_PROD = 'prod';
    
   /**
    * @var array[string]Application_Environments_Environment[]
    */
    protected $environments = array();
    
   /**
    * @var Application_Environments
    */
    protected static $instance;
    
   /***
    * @var Application_Environments_Environment|NULL
    */
    protected $detected = null;
    
    protected function __construct()
    {
        
    }
    
    public static function getInstance() : Application_Environments
    {
        if(!isset(self::$instance))
        {
            self::$instance = new Application_Environments();
        }
        
        return self::$instance;
    }
    
    public function register(string $id, string $type) : Application_Environments_Environment
    {
        if(isset($this->environments[$id]))
        {
            throw new Application_Exception(
                'Cannot register the same environment twice',
                sprintf(
                    'Tried registering the environment [%s] although it has already been registered.',
                    $id
                ),
                self::ERROR_ENVIRONMENT_ALREADY_REGISTERED
            );
        }
        
        $env = new Application_Environments_Environment($id, $type);
        
        $this->environments[$id] = $env;
        
        $this->log(sprintf('Registered environment [%s].', $id));
        
        return $env;
    }
    
    public function registerDev(string $id) : Application_Environments_Environment
    {
        return $this->register($id, self::TYPE_DEV);
    }
    
    public function registerProd(string $id) : Application_Environments_Environment
    {
        return $this->register($id, self::TYPE_PROD);
    }
    
    public function detect(string $defaultID) : Application_Environments_Environment
    {
        if(isset($this->detected))
        {
            return $this->detected;
        }
        
        $this->detected = $this->_detect($defaultID);
        
        boot_define('APP_ENVIRONMENT', $this->detected->getID());
        
        return $this->detected;
    }
    
    protected function _detect(string $defaultID) : Application_Environments_Environment
    {
        $this->log('Detecting current environment.');
        
        if(empty($this->environments))
        {
            throw new Application_Exception(
                'No environments registered',
                '',
                self::ERROR_NO_ENVIRONMENTS_REGISTERED
            );
        }
        
        /* @var Application_Environments_Environment $environment */
        foreach($this->environments as $environment)
        {
            if($environment->isMatch())
            {
                $this->log(sprintf('Current environment matches [%s].', $environment->getID()));
                
                return $environment;
            }
        }
        
        $this->log(sprintf('None of the environments matched, using default [%s].', $defaultID));
        
        return $this->getByID($defaultID);
    }
    
    public function getByID(string $id) : Application_Environments_Environment
    {
        if(isset($this->environments[$id]))
        {
            return $this->environments[$id];
        }
        
        throw new Application_Exception(
            'No such environment registered.',
            sprintf(
                'The environment [%s] has not been registered.',
                $id
            ),
            self::ERROR_UNREGISTERED_ENVIRONMENT
        ); 
    }
    
    public static function getEnvironment() : Application_Environments_Environment
    {
        return self::getInstance()->detect('');
    }
    
    protected function log(string $message) : void
    {
        Application::log('Environments | '.$message);
    }
}
