<?php

interface Application_Interfaces_Simulatable
{
   /**
    * @param bool $simulate
    * @return $this
    */
    function setSimulation(bool $simulate=true);
    
    function isSimulationEnabled() : bool;
}
