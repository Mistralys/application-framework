<?php

declare(strict_types=1);

namespace UI\Traits;

use UI\TooltipInfo;

trait TooltipableTrait
{
    protected ?TooltipInfo $tooltipInfo = null;

    /**
     * @param TooltipInfo|NULL $tooltip
     * @return $this
     */
    public function setTooltip(?TooltipInfo $tooltip)
    {
        $this->tooltipInfo = $tooltip;
        return $this;
    }

    public function hasTooltip() : bool
    {
        return isset($this->tooltipInfo);
    }

    public function getTooltip() : ?TooltipInfo
    {
        return $this->tooltipInfo;
    }
}
