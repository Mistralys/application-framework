<?php
/**
 * @package Application
 * @subpackage EventHandler
 */

declare(strict_types=1);

namespace Application\EventHandler\Event;

use Application\EventHandler\Eventables\EventableInterface;
use AppUtils\ConvertHelper;

/**
 * Listener class for a specific event: Used to store information
 * on the listener. It also allows removing specific listeners by
 * their instance.
 *
 * @package Application
 * @subpackage EventHandler
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see EventManager::addListener()
 * @see EventableInterface::addEventListener()
 *
 */
class EventListener
{
    private string $eventName;

    /**
     * @var callable
     */
    private $callback;

    private string $source;

    private int $id;

    public function __construct(int $id, string $eventName, callable $callback, string $source = '')
    {
        $this->id = $id;
        $this->eventName = $eventName;
        $this->callback = $callback;
        $this->source = $source;
    }

    /**
     * Unique ID of the listener, within the same request.
     *
     * @return int
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * The name of the event the listener listens to.
     *
     * @return string
     */
    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * Human-readable label of where the listener comes from.
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @var string|NULL
     */
    private ?string $humanReadable = null;

    public function getCallbackAsString(): string
    {
        if (!isset($this->humanReadable)) {
            $this->humanReadable = ConvertHelper::callback2string($this->callback);
        }

        return $this->humanReadable;
    }
}
