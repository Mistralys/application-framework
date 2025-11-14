<?php

declare(strict_types=1);

namespace DBHelper\Traits;

use AppUtils\ClassHelper;
use AppUtils\ClassHelper\BaseClassHelperException;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\BaseCollection\Event\AfterCreateRecordEvent;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseCollection_OperationContext_Create;

trait AfterRecordCreatedEventTrait
{
    private function handleAfterRecordCreated(DBHelperRecordInterface $record, bool $silent, array $options) : void
    {
        $context = new DBHelper_BaseCollection_OperationContext_Create($record);
        $context->setOptions($options);

        if($silent)
        {
            $context->makeSilent();
        }

        $record->onCreated($context);

        $this->triggerAfterCreateRecord($record, $context);
    }

    /**
     * Triggered after a new record has been created, and after the record's
     * {@see DBHelperRecordInterface::onCreated()} method has been called.
     *
     * @param DBHelperRecordInterface $record
     * @param DBHelper_BaseCollection_OperationContext_Create $context
     * @return AfterCreateRecordEvent|NULL
     *
     * @throws BaseClassHelperException
     */
    final protected function triggerAfterCreateRecord(DBHelperRecordInterface $record, DBHelper_BaseCollection_OperationContext_Create $context) : ?AfterCreateRecordEvent
    {
        $event = $this->triggerEvent(
            DBHelperCollectionInterface::EVENT_AFTER_CREATE_RECORD,
            array(
                $this,
                $record,
                $context
            ),
            AfterCreateRecordEvent::class
        );

        if($event !== null)
        {
            return ClassHelper::requireObjectInstanceOf(
                AfterCreateRecordEvent::class,
                $event
            );
        }

        return null;
    }
}