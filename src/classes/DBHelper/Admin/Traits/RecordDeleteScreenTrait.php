<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use DBHelper_BaseCollection;
use DBHelper_BaseRecord;

/**
 *
 * @see RecordDeleteScreenInterface
 */
trait RecordDeleteScreenTrait
{
    /**
     * @var DBHelper_BaseCollection
     */
    protected $collection;

    /**
     * @var DBHelper_BaseRecord
     */
    protected $record;

    /**
     * @return DBHelper_BaseCollection
     */
    abstract protected function createCollection(): DBHelper_BaseCollection;

    public function getNavigationTitle(): string
    {
        return t('Delete');
    }

    public function getTitle(): string
    {
        return t('Delete');
    }

    public function getURLName(): string
    {
        return RecordDeleteScreenInterface::URL_NAME;
    }

    protected function _handleActions(): bool
    {
        $this->record = $this->collection->getByRequest();
        if (!$this->record) {
            $this->redirectWithInfoMessage(
                t('No such record found.'),
                $this->getBackOrCancelURL()
            );
        }

        $this->startTransaction();

        $this->collection->deleteRecord($this->record);

        $this->endTransaction();

        $this->redirectWithSuccessMessage(
            $this->getSuccessMessage(),
            $this->getBackOrCancelURL()
        );
    }

    protected function getSuccessMessage(): string
    {
        return t(
            'The record %1$s has been successfully deleted at %2$s.',
            $this->record->getLabel(),
            date('H:i:s')
        );
    }
}
