<?php

declare(strict_types=1);

/**
 * @see Application_Traits_Simulatable
 */
interface Application_Interfaces_Simulatable
{
   /**
    * @param bool $simulate
    * @return $this
    */
    public function setSimulation(bool $simulate=true) : self;
    
    public function isSimulationEnabled() : bool;
}
