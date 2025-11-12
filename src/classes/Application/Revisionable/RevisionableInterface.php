<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable;

use Application\Disposables\DisposableDisposedException;
use Application\Revisionable\Storage\RevisionStorageException;
use Application\StateHandler\StateHandlerException;
use Application_FilterCriteria_RevisionableRevisions;
use Application_StateHandler;
use Application_StateHandler_State;
use BaseRevisionable;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Interface for revisionable objects that can be in different states.
 *
 * @package Application
 * @subpackage Revisionables
 *
 * @see BaseRevisionable
 */
interface RevisionableInterface
    extends
    RevisionableStatelessInterface,
    DBHelperRecordInterface
{
    public const ERROR_INVALID_STATE_CHANGE = 149303;
    public const ERROR_NO_STATE_AVAILABLE = 149304;
    public const ERROR_CANNOT_UNDO_REVISION = 149305;
    public const ERROR_OPERATION_NOT_ALLOWED_ON_STUB = 149306;

    public function getStateHandler() : Application_StateHandler;

    public function setState(Application_StateHandler_State $newState): self;

    /**
     * Checks whether the object is in the specified state.
     * @param string|Application_StateHandler_State $nameOrInstance
     * @return boolean
     */
    public function isState($nameOrInstance) : bool;

    public function getCurrentStateLabel() : ?string;

    public function getCurrentPrettyStateLabel() : ?string;

    public function getState() : ?Application_StateHandler_State;

    public function getStateName() : string;

    /**
     * Revisionables do not have parent records.
     * @return null
     */
    public function getParentRecord(): null;

    /**
     * Like {@see self::getState()}, but the method does not
     * return <code>NULL</code>. An exception is thrown instead
     * if no state is available.
     *
     * @return Application_StateHandler_State
     * @throws RevisionableException
     */
    public function requireState() : Application_StateHandler_State;

    /**
     * Whether the record's current state allows it to be modified.
     * @return bool
     */
    public function isChangingAllowed() : bool;


    public function getRevisionsFilterCriteria() : Application_FilterCriteria_RevisionableRevisions;

    /**
     * Changes the state of the item to the specified new state.
     *
     * NOTE: This starts a transaction. It should be done outside regular
     * transactions to allow the internal changes that are necessary for
     * a state change.
     *
     * Returns a boolean flag indicating whether the state has been changed.
     * For example, this will return false if you try to set the state to
     * the same state.
     *
     * @param Application_StateHandler_State $state
     * @param string|NULL $comments
     * @return boolean
     * @throws RevisionableException
     * @throws StateHandlerException
     */
    public function makeState(Application_StateHandler_State $state, ?string $comments=null) : bool;

    /**
     * Checks whether the revisionable has a state by this name.
     * @param string $stateName
     * @return bool
     */
    public function hasState(string $stateName) : bool;

    /**
     * Retrieves the state the revisionable is initially created with,
     * as defined by the revisionable's state setup.
     *
     * @return Application_StateHandler_State
     * @throws StateHandlerException
     */
    public function getInitialState() : Application_StateHandler_State;

    /**
     * Attempts to find the most recent revision number that
     * matches the given state.
     *
     * @param Application_StateHandler_State $state
     * @return int|null
     */
    public function getLatestRevisionByState(Application_StateHandler_State $state) : ?int;

    public function isStub() : bool;

    /**
     * A list of all available states for the item, as an indexed
     * array containing state objects.
     *
     * @return Application_StateHandler_State[]
     */
    public function getStates() : array;

    /**
     * Checks whether the currently selected state has the
     * specified state name/object as dependency.
     *
     * @param string|Application_StateHandler_State $state_object_or_name
     * @return boolean
     */
    public function stateHasDependency($state_object_or_name) : bool;

    /**
     * Retrieves the specified state object by its name.
     *
     * @param string|Application_StateHandler_State $nameOrInstance
     * @return Application_StateHandler_State
     */
    public function getStateByName($nameOrInstance) : Application_StateHandler_State;

    /**
     * Creates a stub object of this item's type,
     * which is used to access all the object's static
     * functions, for example, for the state information.
     *
     * @return BaseRevisionable
     *@throws RevisionableException
     */
    public static function createStubObject() : BaseRevisionable;

    public function getAdminChangelogURL(array $params = array()): string;

    public function getAdminStatusURL(array $params = array()): string;

    /**
     * Selects the revisionable's current revision.
     * @return $this
     *
     * @throws \Application\Disposables\DisposableDisposedException
     * @throws RevisionableException
     * @throws RevisionStorageException
     */
    public function selectCurrentRevision(): self;

    /**
     * Selects the last revision of the record by a specific state.
     *
     * @param Application_StateHandler_State $state
     * @return integer|false The revision number, or false if no revision matches.
     */
    public function selectLastRevisionByState(Application_StateHandler_State $state) : int|false;

    /**
     * Retrieves the last revision of the record by a specific state.
     *
     * @param Application_StateHandler_State $state
     * @return integer|false The revision number, or false if no revision matches.
     */
    public function getLastRevisionByState(Application_StateHandler_State $state) : int|false;

    /**
     * Retrieves the revision currently in use. This is tracked in
     * a dedicated table, and namespaced to any campaign keys that
     * may have been defined.
     *
     * @return integer|NULL
     */
    public function getCurrentRevision(): ?int;

    /**
     * Sets the label for the current revision.
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label): self;
}
