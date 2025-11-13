<?php

namespace Application\Revisionable\Collection;

use Application\Revisionable\RevisionableInterface;
use Application\Revisionable\Storage\Copy\BaseDBRevisionCopy;
use DBHelper;

/**
 * @property RevisionableInterface $revisionable
 */
abstract class Application_RevisionableCollection_RevisionCopy extends BaseDBRevisionCopy
{
    protected RevisionableCollectionInterface $collection;
    protected string $primaryKey;
    protected string $revisionKey;
    protected string $revisionTable;

    protected function init(): void
    {
        $this->collection = $this->revisionable->getCollection();
        $this->primaryKey = $this->collection->getRecordPrimaryName();
        $this->revisionKey = $this->collection->getRevisionKeyName();
        $this->revisionTable = $this->collection->getRevisionsTableName();
    }

    protected function getRevisionCopyClass()
    {
        return $this->collection->getRecordCopyRevisionClass();
    }

    protected function processParts(RevisionableInterface $targetRevisionable): void
    {
        // ensure this is always done first, as it is 
        // the basis for the rest.
        $this->processSettings($targetRevisionable);

        parent::processParts($targetRevisionable);
    }

    protected function processSettings(RevisionableInterface $targetRevisionable): void
    {
        $this->log(sprintf('Copying revision data from table [%s].', $this->revisionTable));

        // to ensure we also copy all custom columns from the revisions
        // table, we fetch the full record.
        $data = DBHelper::fetch(
            sprintf(
                "SELECT
                    *
                FROM
                    `%s`
                WHERE
                    `%s`=:revision",
                $this->revisionTable,
                $this->revisionKey
            ),
            array(
                'revision' => $this->sourceRevision
            )
        );

        unset($data[RevisionableCollectionInterface::COL_REV_PRETTY_REVISION]);

        $keys = $this->storage->getStaticColumns();
        foreach ($keys as $key => $value) {
            $data[$key] = $value;
        }

        // overwrite the required keys with the target information
        $data[RevisionableCollectionInterface::COL_REV_COMMENTS] = $this->comments;
        $data[RevisionableCollectionInterface::COL_REV_DATE] = $this->date->format('Y-m-d H:i:s');
        $data[RevisionableCollectionInterface::COL_REV_AUTHOR] = $this->ownerID;
        $data[$this->revisionKey] = $this->targetRevision;
        $data[$this->primaryKey] = $targetRevisionable->getID();

        DBHelper::updateDynamic(
            $this->revisionTable,
            $data,
            array(
                $this->revisionKey
            )
        );
    }
}