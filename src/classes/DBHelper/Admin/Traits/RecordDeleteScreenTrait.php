<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use AppUtils\OperationResult;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 *
 * @see RecordDeleteScreenInterface
 */
trait RecordDeleteScreenTrait
{
    protected DBHelperCollectionInterface $collection;
    protected DBHelperRecordInterface $record;

    abstract public function createCollection(): DBHelperCollectionInterface;

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
        $record = $this->collection->getByRequest();

        if ($record === null) {
            $this->redirectWithInfoMessage(
                t('No such record found.'),
                $this->getBackOrCancelURL()
            );
        }

        $this->record = $record;

        $result = $this->checkPrerequisites();

        if(!$result->isValid()) {
            $this->redirectWithErrorMessage(
                $result->getErrorMessage(),
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

    protected function checkPrerequisites() : OperationResult
    {
        $result = new OperationResult($this);
        $this->_checkPrerequisites($result);
        return $result;
    }

    /**
     * If there are any prerequisites to check before deleting the record,
     * implement this method in the using class. Use the result's {@see OperationResult::makeError()}
     * method to indicate failure. The message provided will be shown to the user.
     *
     * @param OperationResult $result
     * @return void
     */
    abstract protected function _checkPrerequisites(OperationResult $result) : void;

    protected function getSuccessMessage(): string
    {
        return t(
            'The record %1$s has been successfully deleted at %2$s.',
            $this->record->getLabel(),
            date('H:i:s')
        );
    }
}
