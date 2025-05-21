<?php

declare(strict_types=1);

namespace UI\Interfaces;

use UI\Traits\ActivatableTrait;

/**
 * @see ActivatableTrait
 */
interface ActivatableInterface
{
    /**
     * @param bool $active
     * @return self
     */
    public function makeActive(bool $active=true) : self;

    public function isActive() : bool;
}
