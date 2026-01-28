<?php
/**
 * @package Application
 * @subpackage Admin Screens - Events
 */

declare(strict_types=1);

namespace Application\Admin\Screens\Events;

use UI_Page_Sidebar;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onSidebarHandled()
 * @see \Application_Traits_Admin_Screen::handleSidebar()
 */
class SidebarHandledEvent extends BaseScreenEvent
{
    public const string EVENT_NAME = 'SidebarHandled';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getSidebar() : UI_Page_Sidebar
    {
        return $this->getScreen()->getSidebar();
    }
}
