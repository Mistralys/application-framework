<?php
/**
 * @package User Interface
 * @subpackage Interfaces
 */

declare(strict_types=1);

namespace UI\Bootstrap\ButtonGroup;

use AppUtils\Interfaces\RenderableInterface;
use UI\Interfaces\ActivatableInterface;
use UI\Interfaces\ButtonSizeInterface;
use UI\Interfaces\NamedItemInterface;

/**
 * Interface for items that can be added to a button group.
 *
 * @package User Interface
 * @subpackage Interfaces
 */
interface ButtonGroupItemInterface
    extends
    ButtonSizeInterface,
    ActivatableInterface,
    RenderableInterface,
    NamedItemInterface
{
}
