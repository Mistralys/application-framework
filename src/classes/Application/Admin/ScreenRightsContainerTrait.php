<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\Admin;

/**
 * Trait used by any classes that give access to
 * admin screen definitions.
 *
 * @package Application
 * @subpackage Admin
 *
 * @see ScreenRightsContainerInterface
 */
trait ScreenRightsContainerTrait
{
    private ?ScreenRightsInterface $screenRights = null;

    public function getAdminScreens() : ScreenRightsInterface
    {
        if($this->screenRights === null) {
            $this->screenRights = $this->createAdminScreens();
        }

        return $this->screenRights;
    }

    abstract protected function createAdminScreens() : ScreenRightsInterface;
}
