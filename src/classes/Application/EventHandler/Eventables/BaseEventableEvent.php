<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\Eventables;

use Application\EventHandler\Event\BaseEvent;

/**
 * Eventable-specific event class which extends the base event class:
 * it stores an instance of the owner object and adds the `getSubject()`
 * method to retrieve it.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableTrait::createEvent()
 */
abstract class BaseEventableEvent extends BaseEvent implements EventableEventInterface
{
    protected object $subject;

    public function __construct(string $name, object $subject, array $args = array())
    {
        parent::__construct($name, $args);

        $this->subject = $subject;
    }

    public function getSubject(): object
    {
        return $this->subject;
    }
}
