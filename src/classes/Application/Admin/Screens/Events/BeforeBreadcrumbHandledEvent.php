<?php
/**
 * @package Application
 * @subpackage Admin Screens - Events
 */

declare(strict_types=1);

namespace Application\Admin\Screens\Events;

use UI_Page_Breadcrumb;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onBeforeBreadcrumbHandled()
 * @see \Application_Traits_Admin_Screen::handleBreadcrumb()
 */
class BeforeBreadcrumbHandledEvent extends BaseScreenEvent
{
    public const EVENT_NAME = 'BeforeBreadcrumbHandled';

    public function getBreadcrumb() : UI_Page_Breadcrumb
    {
        return $this->getScreen()->getBreadcrumb();
    }
}
