<?php

declare(strict_types=1);

namespace UI\Traits;

use UI\Interfaces\ActivatableInterface;

/**
 * @see ActivatableInterface
 */
trait ActivatableTrait
{
    private bool $active = false;

    /**
     * @param bool $active
     * @return $this
     */
    public function makeActive(bool $active=true) : self
    {
        $this->active = $active;
        return $this;
    }

    public function isActive() : bool
    {
        return $this->active;
    }
}
