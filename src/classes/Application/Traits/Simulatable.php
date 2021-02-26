<?php

trait Application_Traits_Simulatable
{
   /**
    * @var bool
    */
    protected $simulation = false;
    
    public function setSimulation(bool $simulate=true) : Application_Interfaces_Simulatable 
    {
        $this->simulation = $simulate;
        
        return $this;
    }
    
    public function isSimulationEnabled() : bool
    {
        return $this->simulation;
    }
}
