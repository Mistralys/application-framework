<?php

declare(strict_types=1);

/**
 * @see Application_Interfaces_Simulatable
 */
trait Application_Traits_Simulatable
{
    protected bool $simulation = false;

    /**
     * @param bool $simulate
     * @return $this
     */
    public function setSimulation(bool $simulate = true): self
    {
        $this->simulation = $simulate;

        return $this;
    }

    public function isSimulationEnabled(): bool
    {
        return $this->simulation;
    }
}
