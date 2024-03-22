<?php

declare(strict_types=1);

namespace Application\Revisionable;

use Application_CollectionInterface;
use Application_RevisionableCollection_FilterCriteria;
use Application_RevisionableCollection_FilterSettings;
use Application_User;

interface RevisionableCollectionInterface extends Application_CollectionInterface
{
    public function createDummyRecord() : RevisionableInterface;
    public function createNewRecord(string $label, ?Application_User $author=null, array $data=array()) : RevisionableInterface;
    public function getInstanceID() : string;
    public function unloadRecord(RevisionableInterface $revisionable) : void;
    public function getRecordTableName() : string;
    /**
     * @return class-string
     */
    public function getRecordClassName() : string;

    /**
     * @return string
     */
    public function getRecordTypeName() : string;

    /**
     * @return class-string
     */
    public function getRecordFiltersClassName() : string;

    /**
     * @return class-string
     */
    public function getRecordFilterSettingsClassName() : string;

    /**
     * @return class-string
     */
    public function getRevisionsStorageClass() : string;

    /**
     * @return array<string,string|number>
     */
    public function getAdminURLParams() : array;

    /**
     * @return string
     */
    public function getRecordReadableNameSingular() : string;

    /**
     * @return string
     */
    public function getRecordReadableNamePlural() : string;

    /**
     * @return class-string
     */
    public function getRecordCopyRevisionClass() : string;

    /**
     * Retrieves the column names and human-readable labels for
     * all columns that are relevant for a search.
     *
     * @return array<string,string> Associative array with column name => readable label pairs.
     * @see self::getRecordSearchableKeys()
     */
    public function getRecordSearchableColumns() : array;
    public function getCurrentRevisionsTableName() : string;

    public function getPrimaryKeyName() : string;

    public function getTableName() : string;

    public function getRevisionsTableName() : string;

    public function getRevisionKeyName() : string;

    public function getRecordChangelogTableName() : string;
    public function getFilterCriteria() : Application_RevisionableCollection_FilterCriteria;
    public function getFilterSettings() : Application_RevisionableCollection_FilterSettings;
    public function getByID(int $record_id) : RevisionableInterface;
    public function getByRevision(int $revision) : RevisionableInterface;
    public function getByRequest() : ?RevisionableInterface;
    public function revisionExists(int $revision) : bool;
    public function getCurrentRevision(int $revisionableID) : ?int;

    /**
     * Sets the revisionable's current revision.
     *
     * @param integer $revisionableID
     * @param integer $revision
     * @return void
     */
    public function setCurrentRevision(int $revisionableID, int $revision) : void;
    /**
     * Destroys the target revisionable permanently by deleting it
     * from the database.
     *
     * @param RevisionableInterface $revisionable
     */
    public function destroy(RevisionableInterface $revisionable) : void;

    /**
     * Fetches the key names of the searchable columns.
     * @return string[]
     * @see self::getRecordSearchableColumns()
     */
    public function getRecordSearchableKeys() : array;
}
