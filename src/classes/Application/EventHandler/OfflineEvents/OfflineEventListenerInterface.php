<?php

declare(strict_types=1);

namespace Application\EventHandler\OfflineEvents;

use AppUtils\Interfaces\StringPrimaryRecordInterface;
use AppUtils\NamedClosure;

interface OfflineEventListenerInterface extends StringPrimaryRecordInterface
{
    /**
     * Unique identifier for this listener.
     *
     * > NOTE: This is typically derived from the class name.
     *
     * @return string
     */
    public function getID() : string;

    /**
     * The name of the offline event this listener is associated with.
     *
     * @return string
     */
    public function getEventName() : string;

    /**
     * Returns the callable that will be invoked when the event is fired.
     *
     * @return NamedClosure
     */
    public function getCallable() : NamedClosure;

    /**
     * Higher priority listeners are called first, giving the
     * possibility to influence the order in which they are
     * executed.
     *
     * @return int
     */
    public function getPriority() : int;
}
