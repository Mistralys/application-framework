<?php

declare(strict_types=1);

namespace DBHelper\Traits;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use DBHelper\BaseCollection\DBHelperCollectionException;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\BeforeCreateRecordEvent;

trait BeforeCreateEventTrait
{
    private function handleOnBeforeCreateRecord(array $data) : void
    {
        $event = $this->triggerBeforeCreateRecord($data);

        if($event === null || !$event->isCancelled()) {
            return;
        }

        throw new DBHelperCollectionException(
            'Creating new record has been cancelled.',
            sprintf(
                'The event has been cancelled. Reason given: %s',
                $event->getCancelReason()
            ),
            DBHelperCollectionException::ERROR_CREATE_RECORD_CANCELLED
        );
    }

    /**
     * Triggers the BeforeCreatedRecord event.
     *
     * @param array<string,mixed> $data
     *
     * @return BeforeCreateRecordEvent|NULL
     * @throws BaseClassHelperException
     */
    final protected function triggerBeforeCreateRecord(array $data) : ?BeforeCreateRecordEvent
    {
        $event = $this->triggerEvent(
            DBHelperCollectionInterface::EVENT_BEFORE_CREATE_RECORD,
            array(
                $this,
                $data
            )
        );

        if($event !== null)
        {
            return ClassHelper::requireObjectInstanceOf(
                BeforeCreateRecordEvent::class,
                $event
            );
        }

        return null;
    }
}
