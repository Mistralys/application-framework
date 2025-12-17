<?php
/**
 * @package Application
 * @subpackage Events
 */

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents;

use Application\EventHandler\OfflineEvents\Index\EventIndex;
use Application_EventHandler;
use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use Throwable;

/**
 * Specialized offline event class used to handle an offline
 * event that can wake up listeners and trigger them.
 *
 * @package Application
 * @subpackage Events
 */
class OfflineEventContainer
{
    private string $eventName;

    /**
     * @var class-string<OfflineEventInterface>|NULL
     */
    private ?string $eventClass;
    private bool $listenersLoaded = false;
    private string $triggerName;
    private static int $eventCounter = 0;
    private ?OfflineEventInterface $triggeredEvent = null;

    /**
     * @var OfflineEventListenerInterface[]
     */
    private array $listeners = array();

    /**
     * @var array<int,mixed>
     */
    private array $args;

    /**
     * @param string $eventName
     * @param array<int,mixed> $args
     */
    public function __construct(string $eventName, array $args = array())
    {
        self::$eventCounter++;

        $this->eventName = $eventName;
        $this->eventClass = EventIndex::getInstance()->getEventClass($eventName);
        $this->triggerName = 'offline-event-' . self::$eventCounter;
        $this->args = $args;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    /**
     * @return array<int,mixed>
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @return OfflineEventListenerInterface[]
     */
    public function getListeners(): array
    {
        $this->loadListeners();

        return $this->listeners;
    }

    private function loadListeners(): void
    {
        if ($this->listenersLoaded) {
            return;
        }

        $this->listenersLoaded = true;

        foreach ($this->getListenerClasses() as $class) {
            $this->listeners[] = $this->createListener($class);
        }

        // Sort the listeners by priority and ID if no
        // priority is set.
        usort($this->listeners, static function (OfflineEventListenerInterface $a, OfflineEventListenerInterface $b): int {
            $prioA = $a->getPriority();
            $prioB = $b->getPriority();

            if ($prioA > 0 || $prioB > 0) {
                return ($prioA <=> $prioB) * -1;
            }

            return get_class($a) <=> get_class($b);
        });
    }

    public function getListenerClasses(): array
    {
        return EventIndex::getInstance()->getListenerClasses($this->getEventName());
    }

    public function hasListeners(): bool
    {
        $this->loadListeners();

        return !empty($this->listeners);
    }

    public function getTriggeredEvent(): ?OfflineEventInterface
    {
        return $this->trigger();
    }

    public function trigger(): ?OfflineEventInterface
    {
        if (isset($this->triggeredEvent)) {
            return $this->triggeredEvent;
        }

        if (!isset($this->eventClass) || !class_exists($this->eventClass) || !$this->hasListeners()) {
            return null;
        }

        $listeners = $this->getListeners();

        foreach ($listeners as $listener) {
            Application_EventHandler::addListener(
                $this->triggerName,
                $listener->getCallable(),
                $this->getEventName()
            );
        }

        $this->triggeredEvent = ClassHelper::requireObjectInstanceOf(
            OfflineEventInterface::class,
            Application_EventHandler::trigger(
                $this->triggerName,
                $this->args,
                $this->eventClass
            )
        );

        return $this->triggeredEvent;
    }

    /**
     * @param class-string<OfflineEventListenerInterface> $className
     * @return OfflineEventListenerInterface
     *
     * @throws BaseClassHelperException
     * @throws Throwable
     */
    private function createListener(string $className): OfflineEventListenerInterface
    {
        return ClassHelper::requireObjectInstanceOf(
            OfflineEventListenerInterface::class,
            new $className($this)
        );
    }
}
