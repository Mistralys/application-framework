<?php

declare(strict_types=1);

namespace Application\Revisionable\Collection;

use Application\Collection\IntegerCollectionInterface;
use Application\Interfaces\Admin\AdminScreenInterface;
use Application\Revisionable\RevisionableInterface;
use Application_Admin_Skeleton;
use Application_StateHandler_State;
use Application_User;
use AppUtils\Interfaces\StringableInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use UI\AdminURLs\AdminURLInterface;
use UI_DataGrid;

/**
 * @method RevisionableInterface[] getAll()
 */
interface RevisionableCollectionInterface extends IntegerCollectionInterface, DBHelperCollectionInterface
{
    // region: DBHelper collection overloads

    public const int STUB_OBJECT_ID = -9999;
    public const string COL_REV_DATE = 'date';
    public const string COL_REV_AUTHOR = 'author';
    public const string COL_REV_LABEL = 'label';
    public const string COL_REV_STATE = 'state';
    public const string COL_CURRENT_REVISION = 'current_revision';
    public const string COL_REV_COMMENTS = 'comments';
    public const string COL_REV_PRETTY_REVISION = 'pretty_revision';

    public function createStubRecord() : RevisionableInterface;
    public function getFilterCriteria() : RevisionableFilterCriteriaInterface;
    public function getFilterSettings() : RevisionableFilterSettingsInterface;
    public function getByID(int $record_id) : RevisionableInterface;
    public function getByRequest() : ?RevisionableInterface;
    /**
     * @return class-string<RevisionableInterface>
     */
    public function getRecordClassName() : string;

    /**
     * @return class-string<RevisionableFilterCriteriaInterface>
     */
    public function getRecordFiltersClassName() : string;

    /**
     * @return class-string<RevisionableFilterSettingsInterface>
     */
    public function getRecordFilterSettingsClassName() : string;

    // endregion

    public function createNewRevisionable(string $label, ?Application_User $author=null, array $data=array()) : RevisionableInterface;

    /**
     * Unloads the record from the internal object instance cache,
     * and calls the record's {@see \Application\Disposables\DisposableInterface::dispose()} method.
     *
     * @param RevisionableInterface $revisionable
     * @return $this
     */
    public function unloadRecord(RevisionableInterface $revisionable) : self;

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

    public function getCurrentRevisionsTableName() : string;

    public function getRevisionsTableName() : string;

    /**
     * The name of the revision ID column.
     * @return string
     */
    public function getRevisionKeyName() : string;

    public function getRecordChangelogTableName() : string;
    public function getByRevision(int $revision) : RevisionableInterface;
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
     * 3. Call the record's {@see \Application\Disposables\DisposableInterface::dispose()} method.
     * 4. Unload the record instance from the collection.
     *
     * @param RevisionableInterface $revisionable
     */
    public function destroy(RevisionableInterface $revisionable) : void;

    /**
     * Checks whether the target revisionable fills the conditions
     * to be destroyed permanently.
     *
     * @param RevisionableInterface $revisionable
     * @return bool
     */
    public function canRecordBeDestroyed(RevisionableInterface $revisionable) : bool;

    /**
     * Creates a datagrid multi action handler: these are used to handle common
     * revisionable tasks like publishing, deleting etc. via the multi-action
     * datagrid feature.
     *
     * @param class-string<BaseRevisionableDataGridMultiAction> $className The name of the multi action class to use. Must extend the base class.
     * @param AdminScreenInterface $adminScreen The administration screen in which the grid is used.
     * @param UI_DataGrid $grid The grid to apply the action to.
     * @param string|int|float|StringableInterface|null $label The label of the item.
     * @param string|AdminURLInterface $redirectURL The URL to redirect to when this action completes.
     * @param boolean $confirm Whether this is an action that displays a confirmation message.
     * @return BaseRevisionableDataGridMultiAction
     */
    public function createListMultiAction(string $className, AdminScreenInterface $adminScreen, UI_DataGrid $grid, string|int|float|StringableInterface|null $label, string|AdminURLInterface $redirectURL, bool $confirm = false): BaseRevisionableDataGridMultiAction;

}
