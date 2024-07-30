<?php

declare(strict_types=1);

namespace Application\Revisionable;

use Application\RevisionableCollection\RevisionableFilterCriteriaInterface;
use Application_CollectionInterface;
use Application_RevisionableCollection_FilterCriteria;
use Application_RevisionableCollection_FilterSettings;
use Application_StateHandler_State;
use Application_User;

interface RevisionableCollectionInterface extends Application_CollectionInterface
{
    public const ERROR_CANNOT_DESTROY_RECORD = 16103;
    public const ERROR_REVISION_DOES_NOT_EXIST = 16102;
    public const ERROR_INVALID_MULTI_ACTION_CLASS = 16101;

    public function createDummyRecord() : RevisionableInterface;
    public function createNewRecord(string $label, ?Application_User $author=null, array $data=array()) : RevisionableInterface;
    public function getInstanceID() : string;

    /**
     * Unloads the record from the internal object instance cache,
     * and calls the record's {@see \Application_Interfaces_Disposable::dispose()} method.
     *
     * @param RevisionableInterface $revisionable
     * @return $this
     */
    public function unloadRecord(RevisionableInterface $revisionable) : self;

    /**
     * Resets the internal object instance cache, and disposes
     * of all existing instances.
     *
     * @return $this
     */
    public function resetCollection() : self;
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

    public function getAdminListURL(array $params=array()) : string;

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

    /**
     * The name of the revision ID column.
     * @return string
     */
    public function getRevisionKeyName() : string;

    public function getRecordChangelogTableName() : string;
    public function getFilterCriteria() : RevisionableFilterCriteriaInterface;
    public function getFilterSettings() : Application_RevisionableCollection_FilterSettings;
    public function getByID(int $record_id) : RevisionableInterface;
    public function getByRevision(int $revision) : RevisionableInterface;
    public function getByRequest() : ?RevisionableInterface;
    public function revisionExists(int $revision) : bool;
    public function getIDByRevision(int $revision) : ?int;
    public function getCurrentRevision(int $revisionableID) : ?int;
    public function getLatestRevisionByState(int $revisionableID, Application_StateHandler_State $state) : ?int;

    /**
     * @return array<string,string>
     */
    public function getCampaignKeys() : array;

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
     * This does the following things:
     *
     * 1. Delete the record from the database.
     * 2. Add an entry in the application message log.
     * 3. Call the record's {@see \Application_Interfaces_Disposable::dispose()} method.
     * 4. Unload the record instance from the collection.
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

    /**
     * Checks whether the target revisionable fills the conditions
     * to be destroyed permanently.
     *
     * @return bool
     */
    public function canRecordBeDestroyed(RevisionableInterface $revisionable) : bool;
}
