<?php

trait Application_Traits_Simulatable
{
   /**
    * @var bool
    */
    protected $simulation = false;

    /**
     * @param bool $simulate
     * @return $this
     */
    public function setSimulation(bool $simulate=true)
    {
        $this->simulation = $simulate;
        
        return $this;
    }
    
    public function isSimulationEnabled() : bool
    {
        return $this->simulation;
    }
}
