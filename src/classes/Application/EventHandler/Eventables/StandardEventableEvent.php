<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\Eventables;

/**
 * Standard implementation of an eventable event, which uses
 * the specified event name. This is the default event class
 * and cannot be extended further.
 *
 * Use the {@see BaseEventableEvent} class to create custom
 * eventable event classes.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableTrait::createEvent()
 */
final class StandardEventableEvent extends BaseEventableEvent
{
    public function getName(): string
    {
        return $this->name;
    }
}
