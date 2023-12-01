<?php
/**
 * @package Application
 * @subpackage UserInterface
 * @see \UI\Traits\TooltipableTrait
 */

declare(strict_types=1);

namespace UI\Traits;

use UI\TooltipInfo;

/**
 * @package Application
 * @subpackage UserInterface
 * @see TooltipableInterface
 */
trait TooltipableTrait
{
    protected ?TooltipInfo $tooltipInfo = null;

    /**
     * @param TooltipInfo|NULL $tooltip
     * @return $this
     */
    public function setTooltip(?TooltipInfo $tooltip) : self
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
