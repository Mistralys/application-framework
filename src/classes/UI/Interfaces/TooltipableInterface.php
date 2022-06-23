<?php

declare(strict_types=1);

namespace UI\Interfaces;

use AppUtils\Interfaces\RenderableInterface;
use UI\TooltipInfo;

interface TooltipableInterface extends RenderableInterface
{
    public function setTooltip(?TooltipInfo $tooltip);

    public function hasTooltip() : bool;

    public function getTooltip() : ?TooltipInfo;
}
