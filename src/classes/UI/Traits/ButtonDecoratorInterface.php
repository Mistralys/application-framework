<?php
/**
 * @package User Interface
 * @subpackage Traits
 */

declare(strict_types=1);

namespace UI\Traits;

use UI_Button;
use UI_Interfaces_Button;

/**
 * Interface for classes using the {@see ButtonDecoratorTrait}.
 *
 * @package User Interface
 * @subpackage Traits
 * @see ButtonDecoratorTrait
 */
interface ButtonDecoratorInterface extends UI_Interfaces_Button
{
    public function getButtonInstance() : UI_Button;
}
