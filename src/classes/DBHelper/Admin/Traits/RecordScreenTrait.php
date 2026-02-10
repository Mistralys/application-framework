<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI_DataGrid;

/**
 * @see RecordScreenInterface
 */
trait RecordScreenTrait
{
    protected DBHelperCollectionInterface $collection;
    protected DBHelperRecordInterface $record;

    protected function init() : void
    {
        $this->collection = $this->createCollection();

        $record = $this->collection->getByRequest();

        if($record === null) {
            $this->redirectWithErrorMessage(
                t('No such record found.'),
                $this->getRecordMissingURL()
            );
        }

        $this->record = $record;

        parent::init();

        $this->validateRequest();
    }

    abstract protected function createCollection() : DBHelperCollectionInterface;

    /**
     * Called after the screen's `init()` method. Can be overwritten
     * in the extending class as replacement for the `init()` method.
     */
    protected function validateRequest() : void
    {

    }

    public function getRecord() : DBHelperRecordInterface
    {
        return $this->record;
    }

    public function getCollection() : DBHelperCollectionInterface
    {
        return $this->collection;
    }

    /**
     * Updated to automatically add the record's primary
     * key value to the data grid's hidden parameters.
     *
     * {@inheritDoc}
     * @see Application_Admin_Skeleton::configureDataGrid()
     */
    protected function configureDataGrid(string $id='') : UI_DataGrid
    {
        $grid = parent::configureDataGrid($id);

        $record = $this->getRecord();

        $grid->addHiddenVar($record->getRecordPrimaryName(), (string)$record->getID());

        $collection = $record->getCollection();

        $parent = $collection->getParentRecord();
        if($parent !== null)
        {
            $grid->addHiddenVar(
                $parent->getRecordPrimaryName(),
                (string)$parent->getID()
            );
        }

        return $grid;
    }

    /**
     * Updated to automatically add the record's primary
     * key value to the form's hidden variables. Also adds
     * the parent record's ID if present.
     *
     * @inheritDoc
     */
    public function createFormableForm(string $name, $defaultData=array()) : self
    {
        parent::createFormableForm($name, $defaultData);

        $this->addFormablePageVars();

        $record = $this->getRecord();

        $this->addHiddenVar($record->getRecordPrimaryName(), (string)$record->getID());

        $collection = $record->getCollection();

        $parent = $collection->getParentRecord();

        if($parent !== null)
        {
            $this->addHiddenVar(
                $parent->getRecordPrimaryName(),
                (string)$parent->getID()
            );
        }

        return $this;
    }
}
