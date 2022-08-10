<?php
/**
 * @package Application
 * @subpackage Traits
 * @see \Application\Interfaces\Admin\ScreenAccessInterface
 */

declare(strict_types=1);

namespace Application\Interfaces\Admin;

use Application\Traits\Admin\ScreenAccessTrait;
use Application_Admin_ScreenInterface;

/**
 * Interface for classes that can return a subject administration
 * screen. In most cases, the trait {@see ScreenAccessTrait} can
 * be used to implement it.
 *
 * @package Application
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ScreenAccessTrait
 */
interface ScreenAccessInterface
{
    public function getAdminScreen() : Application_Admin_ScreenInterface;
}
