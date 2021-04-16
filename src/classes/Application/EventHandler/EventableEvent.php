<?php
/**
 * File containing the class {@Application_EventHandler_EventableEvent}.
 *
 * @package Application
 * @subpackage EventHandler
 * @see Application_EventHandler_EventableEvent
 */

declare(strict_types=1);

/**
 * Eventable-specific event class which extends the base event class:
 * it stores an instance of the owner object, and adds the `getSubject()`
 * method to retrieve it.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Eventable::createEvent()
 */
class Application_EventHandler_EventableEvent extends Application_EventHandler_Event
{
    /**
     * @var object
     */
    private $subject;

    public function __construct(string $name, object $subject, array $args = array())
    {
        parent::__construct($name, $args);

        $this->subject = $subject;
    }

    public function getSubject() : object
    {
        return $this->subject;
    }
}
