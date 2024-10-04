<?php
/**
 * @package Application
 * @subpackage Admin Screens - Events
 */

declare(strict_types=1);

namespace Application\Admin\Screens\Events;

use Application_Admin_ScreenInterface;
use Application_EventHandler_EventableEvent;

/**
 * Abstract base class for admin screen events.
 *
 * @package Application
 * @subpackage Admin Screens - Events
 */
abstract class BaseScreenEvent extends Application_EventHandler_EventableEvent
{
    public function getScreen() : Application_Admin_ScreenInterface
    {
        return $this->getArgumentObject(0, Application_Admin_ScreenInterface::class);
    }
}
