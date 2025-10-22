<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use DBHelper\BaseCollection\BaseChildCollection;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;
use UI_DataGrid;

trait RecordScreenTrait
{
    /**
     * @var DBHelper_BaseCollection
     */
    protected $collection;

    /**
     * @var DBHelper_BaseRecord
     */
    protected $record;

    abstract protected function createCollection() : DBHelper_BaseCollection;

    protected function init() : void
    {
        $this->collection = $this->createCollection();
        $this->record = $this->collection->getByRequest();

        if(!$this->record) {
            $this->redirectWithErrorMessage(
                t('No such record found.'),
                $this->getRecordMissingURL()
            );
        }

        parent::init();

        $this->validateRequest();
    }

    /**
     * Called after the screen's `init()` method. Can be overwritten
     * in the extending class as replacement for the `init()` method.
     */
    protected function validateRequest() : void
    {

    }

    /**
     * @return DBHelper_BaseRecord
     */
    public function getRecord() : DBHelper_BaseRecord
    {
        return $this->record;
    }

    /**
     * @return DBHelper_BaseCollection
     */
    public function getCollection() : DBHelper_BaseCollection
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

        if($collection instanceof BaseChildCollection)
        {
            $grid->addHiddenVar(
                $collection->getParentCollection()->getRecordPrimaryName(),
                (string)$collection->getParentRecord()->getID()
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

        if($collection instanceof BaseChildCollection)
        {
            $parent = $collection->getParentRecord();

            $this->addHiddenVar($parent->getRecordPrimaryName(), (string)$parent->getID());
        }

        return $this;
    }
}
