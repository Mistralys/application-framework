<?php
/**
 * @package Application
 * @subpackage Traits
 * @see \Application\Traits\Admin\ScreenAccessTrait
 */

declare(strict_types=1);

namespace Application\Traits\Admin;

use Application\Interfaces\Admin\ScreenAccessInterface;
use Application_Admin_ScreenInterface;
use Application_Driver;

/**
 * Trait used to implement the {@see ScreenAccessInterface} interface
 * in a generic way: If the host class is an admins screen, it will
 * return itself. Otherwise, the active screen is fetched from the
 * driver.
 *
 * @package Application
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ScreenAccessInterface
 */
trait ScreenAccessTrait
{
    public function getAdminScreen() : Application_Admin_ScreenInterface
    {
        if($this instanceof Application_Admin_ScreenInterface) {
            return $this;
        }

        return Application_Driver::getInstance()->getActiveScreen();
    }
}
