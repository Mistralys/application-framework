<?php
/**
 * File containing the {@link Application_Environments_Environment} class.
 *
 * @package Application
 * @subpackage Environments
 * @see Application_Environments_Environment
 */

declare(strict_types=1);

/**
 * Container for a single environment definition, with
 * an interface to access information on it.
 *
 * @package Application
 * @subpackage Environments
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_Environments_Environment
{
    const REQUIRE_CLI = 'cli';
    
    const REQUIRE_WIN = 'win';
    
    const REQUIRE_HOSTNAME_CONTAINS = 'hostname-contains';
    
   /**
    * @var string
    */
    protected $id;
    
   /**
    * @var string
    */
    protected $type;
    
   /**
    * @var array[int]Application_Environments_Environment_Requirement[]
    */
    protected $requirements = array();
    
    protected $requirementSetCounter = 0;
    
    public function __construct(string $id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }
    
    public function isDev() : bool
    {
        return $this->type === Application_Environments::TYPE_DEV;
    }

    public function isProd() : bool
    {
        return $this->type === Application_Environments::TYPE_PROD;
    }
    
    public function getID() : string
    {
        return $this->id;
    }
    
    public function or() : Application_Environments_Environment
    {
        $this->requirementSetCounter++;
        
        return $this;
    }

    public function requireTrue(bool $condition) : Application_Environments_Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_BoolTrue($condition));
    }
    
    public function requireCLI() : Application_Environments_Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_CLI());
    }
    
    public function requireWindows() : Application_Environments_Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_Windows());
    }
    
    public function requireHostNameContains(string $search) : Application_Environments_Environment
    {
        return $this->addRequirement(new Application_Environments_Environment_Requirement_HostNameContains($search));
    }
    
    protected function addRequirement(Application_Environments_Environment_Requirement $requirement) : Application_Environments_Environment
    {
        if(!isset($this->requirements[$this->requirementSetCounter]))
        {
            $this->requirements[$this->requirementSetCounter] = array();
        }
        
        $this->requirements[$this->requirementSetCounter][] = $requirement;
        
        return $this;
    }
    
    public function isMatch() : bool
    {
        if(empty($this->requirements))
        {
            $this->log('No requirements defined, skipping.');
            
            return false;
        }
        
        // go through all requirement sets: if any
        // of the sets is valid, the environment is
        // a match.
        foreach($this->requirements as $setID => $set)
        {
            $setValid = true;
            
            foreach($set as $requirement)
            {
                if(!$requirement->isValid())
                {
                    $this->log(sprintf(
                        'Set [%s] | Requirement [%s] | Failed.', 
                        $setID, 
                        $requirement->getID()
                    ));
                    
                    $setValid = false;
                }
                else 
                {
                    $this->log(sprintf(
                        'Set [%s] | Requirement [%s] | Passed.', 
                        $setID, 
                        $requirement->getID()
                    ));
                }
            }
            
            if($setValid)
            {
                $this->log(sprintf('Set [%s] | Passed.', $setID));
                
                return true;
            }
        }
        
        return false;
    }
    
    protected function log(string $message) : void
    {
        Application::log(sprintf(
            'Environments | [%s] | %s',
            $this->getID(),
            $message
        ));
    }
}
