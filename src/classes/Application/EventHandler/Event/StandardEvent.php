<?php
/**
 * @package Application
 * @subpackeage Core
 */

declare(strict_types=1);

namespace Application\EventHandler\Event;

/**
 * This is the default event class used when no specific
 * event class is defined. It uses the event name as provided
 * and cannot be extended with additional functionality.
 *
 * To create a custom event, extend the {@see BaseEvent} class.
 *
 * @package Application
 * @subpackage Core
 */
final class StandardEvent extends BaseEvent
{
    public function getName(): string
    {
        return $this->name;
    }
}
