<?php
/**
 * @package Application
 * @subpackage Admin Screens - Events
 */

declare(strict_types=1);

namespace Application\Admin\Screens\Events;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application_EventHandler_EventableEvent;

/**
 * Abstract base class for admin screen events.
 *
 * @package Application
 * @subpackage Admin Screens - Events
 */
abstract class BaseScreenEvent extends Application_EventHandler_EventableEvent
{
    public function getScreen() : AdminScreenInterface
    {
        return $this->getArgumentObject(0, AdminScreenInterface::class);
    }
}
