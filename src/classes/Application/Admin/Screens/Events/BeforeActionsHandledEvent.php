<?php
/**
 * @package Application
 * @subpackage Admin Screens - Events
 */

declare(strict_types=1);

namespace Application\Admin\Screens\Events;

use TestDriver\Area\TestingScreen\CancelHandleActionsScreen;

/**
 * NOTE: This event is cancellable, which causes the
 * screen's {@see \Application_Traits_Admin_Screen::_handleActions()}
 * method to be skipped entirely.
 *
 * Example: {@see CancelHandleActionsScreen}.
 *
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onBeforeActionsHandled()
 * @see \Application_Traits_Admin_Screen::handleActions()
 */
class BeforeActionsHandledEvent extends BaseScreenEvent
{
    public const string EVENT_NAME = 'BeforeActionsHandled';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }
}
