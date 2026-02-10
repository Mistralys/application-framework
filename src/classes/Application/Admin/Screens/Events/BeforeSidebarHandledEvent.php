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
 * @see \Application_Traits_Admin_Screen::onBeforeSidebarHandled()
 * @see \Application_Traits_Admin_Screen::handleSidebar()
 */
class BeforeSidebarHandledEvent extends BaseScreenEvent
{
    public const string EVENT_NAME = 'BeforeSidebarHandled';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getSidebar() : UI_Page_Sidebar
    {
        return $this->getScreen()->getSidebar();
    }
}
