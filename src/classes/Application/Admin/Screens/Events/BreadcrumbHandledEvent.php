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
 * @see \Application_Traits_Admin_Screen::onBreadcrumbHandled()
 * @see \Application_Traits_Admin_Screen::handleBreadcrumb()
 */
class BreadcrumbHandledEvent extends BaseScreenEvent
{
    public const string EVENT_NAME = 'BreadcrumbHandled';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getBreadcrumb() : UI_Page_Breadcrumb
    {
        return $this->getScreen()->getBreadcrumb();
    }
}
