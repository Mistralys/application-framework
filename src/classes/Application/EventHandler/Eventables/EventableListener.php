<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\Eventables;

use Application\EventHandler\Event\EventListener;

/**
 * Eventable-specific listener class which extends the base listener class:
 * it stores an instance of the owner object and adds the `getSubject()`
 * method to retrieve it.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventableTrait::addEventListener()
 */
class EventableListener extends EventListener
{
    /**
     * @var object
     */
    protected $subject;
    protected string $eventNameNS;

    public function __construct(int $id, string $eventName, $callback, object $subject, string $eventNameNS)
    {
        parent::__construct($id, $eventName, $callback, get_class($subject));

        $this->eventNameNS = $eventNameNS;
        $this->subject = $subject;
    }

    public function getEventNameNS(): string
    {
        return $this->eventNameNS;
    }

    /**
     * @return object
     */
    public function getSubject(): object
    {
        return $this->subject;
    }
}
