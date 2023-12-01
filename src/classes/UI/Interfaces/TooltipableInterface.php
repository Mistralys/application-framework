<?php
/**
 * @package Application
 * @subpackage UserInterface
 * @see \UI\Interfaces\TooltipableInterface
 */

declare(strict_types=1);

namespace UI\Interfaces;

use AppUtils\Interfaces\RenderableInterface;
use UI\TooltipInfo;
use UI\Traits\TooltipableTrait;

/**
 * Interface for UI elements that can be assigned a tooltip text.
 *
 * @package Application
 * @subpackage UserInterface
 *
 * @see TooltipableTrait
 */
interface TooltipableInterface extends RenderableInterface
{
    public function setTooltip(?TooltipInfo $tooltip) : self;

    public function hasTooltip() : bool;

    public function getTooltip() : ?TooltipInfo;
}
