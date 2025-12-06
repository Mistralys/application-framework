<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 *
 * @see RecordDeleteScreenInterface
 */
trait RecordDeleteScreenTrait
{
    protected DBHelperCollectionInterface $collection;
    protected ?DBHelperRecordInterface $record = null;

    abstract protected function createCollection(): DBHelperCollectionInterface;

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
