<?php

declare(strict_types=1);

namespace UI\Bootstrap\ButtonGroup;

use AppUtils\Interfaces\RenderableInterface;
use UI\Interfaces\ActivatableInterface;
use UI\Interfaces\ButtonSizeInterface;

interface ButtonGroupItemInterface
    extends
    ButtonSizeInterface,
    ActivatableInterface,
    RenderableInterface
{
    public function getName() : string;

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name) : self;
}
