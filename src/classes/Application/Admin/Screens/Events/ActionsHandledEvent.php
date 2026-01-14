<?php
/**
 * @package Application
 * @subpackage Admin Screens - Events
 */

declare(strict_types=1);

namespace Application\Admin\Screens\Events;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onActionsHandled()
 * @see \Application_Traits_Admin_Screen::handleActions()
 */
class ActionsHandledEvent extends BaseScreenEvent
{
    public const string EVENT_NAME = 'ActionsHandled';
}
