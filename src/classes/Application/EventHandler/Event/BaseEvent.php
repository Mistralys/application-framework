<?php

declare(strict_types=1);

namespace Application\EventHandler\Event;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\ClassNotExistsException;
use AppUtils\ClassHelper\ClassNotImplementsException;
use AppUtils\ConvertHelper;
use EventHandlingException;

/**
 * Abstract base class for individual events: an instance of this is
 * given as an argument to event listener callbacks. May be extended
 * to provide a more specialized API depending on the event.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class BaseEvent implements EventInterface
{
    protected ?EventListener $selectedListener = null;
    protected bool $cancel = false;
    protected string $cancelReason = '';
    protected string $name;

    /**
     * @var array<int,mixed>
     */
    protected array $args;

    /**
     * @param string $name
     * @param array<int,mixed> $args Indexed array with a list of arguments for the event.
     */
    public function __construct(string $name, array $args = array())
    {
        $this->name = $name;
        $this->args = $args;

        $this->init();
    }

    protected function init(): void
    {

    }

    final public function getID(): string
    {
        return $this->getName();
    }

    public function cancel(string $reason): self
    {
        if (!$this->isCancellable()) {
            throw new EventHandlingException(
                'Event cannot be cancelled',
                sprintf(
                    'The event [%s] cannot be cancelled.',
                    $this->getName()
                ),
                EventHandlingException::ERROR_EVENT_NOT_CANCELLABLE
            );
        }

        $this->cancel = true;
        $this->cancelReason = $reason;

        return $this;
    }

    final public function getArguments(): array
    {
        return $this->args;
    }

    final public function getArgument(int $index): mixed
    {
        return $this->args[$index] ?? null;
    }

    final public function getArgumentString(int $index): string
    {
        return (string)$this->getArgument($index);
    }

    final public function getArgumentArray(int $index): array
    {
        $arg = $this->getArgument($index);

        if (is_array($arg)) {
            return $arg;
        }

        return array();
    }

    final public function getArgumentInt(int $index): int
    {
        return (int)$this->getArgument($index);
    }

    final public function getArgumentBool(int $index): bool
    {
        return ConvertHelper::string2bool($this->getArgument($index));
    }

    /**
     * Fetches an object instance as argument, for the specified class.
     *
     * @template ClassInstanceType
     * @param int $int Zero-based index of the argument.
     * @param class-string<ClassInstanceType> $class
     * @return ClassInstanceType
     *
     * @throws ClassNotExistsException
     * @throws ClassNotImplementsException
     */
    protected function getArgumentObject(int $int, string $class)
    {
        return ClassHelper::requireObjectInstanceOf($class, $this->getArgument($int));
    }

    public function isCancelled(): bool
    {
        return $this->cancel;
    }

    public function getCancelReason(): string
    {
        return $this->cancelReason;
    }

    /**
     * Whether this event can be cancelled.
     * @return boolean
     */
    public function isCancellable(): bool
    {
        return true;
    }

    public function selectListener(EventListener $listener): self
    {
        $this->selectedListener = $listener;
        return $this;
    }

    public function getSource(): string
    {
        if (isset($this->selectedListener)) {
            return $this->selectedListener->getSource();
        }

        return '';
    }

    public function startTrigger(): void
    {

    }

    public function stopTrigger(): void
    {
        $this->selectedListener = null;
    }

    /**
     * Sets the argument at the specified index to the specified value.
     * The index is Zero-Based.
     *
     * @param int $index
     * @param mixed $value
     * @return $this
     */
    protected function setArgument(int $index, mixed $value): self
    {
        $this->args[$index] = $value;
        return $this;
    }
}
