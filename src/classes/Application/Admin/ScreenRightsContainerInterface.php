<?php
/**
 * @package Application
 * @subpackage Admin
 */

declare(strict_types=1);

namespace Application\Admin;

/**
 * Interface for the {@see ScreenRightsContainerTrait} trait.
 *
 * @package Application
 * @subpackage Admin
 *
 * @see ScreenRightsContainerTrait
 */
interface ScreenRightsContainerInterface
{
    public function getAdminScreens() : ScreenRightsInterface;
}
