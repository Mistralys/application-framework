<?php
/**
 * @package Application
 * @subpackage Formable
 */

declare(strict_types=1);

namespace Application\Formable\Event;

use Application_EventHandler_Event;
use Application_Interfaces_Formable;

/**
 * Abstract base class for all events that are related to formable objects.
 *
 * @package Application
 * @subpackage Formable
 */
abstract class BaseFormableEvent extends Application_EventHandler_Event
{
    public function getFormable() : Application_Interfaces_Formable
    {
        return $this->getArgumentObject(0, Application_Interfaces_Formable::class);
    }
}
