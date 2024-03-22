<?php
/**
 * @package Application
 * @subpackage Revisionables
 */

declare(strict_types=1);

namespace Application\Revisionable;

use Application\StateHandler\StateHandlerException;
use Application\Interfaces\ChangelogableInterface;
use Application_Revisionable;
use Application_StateHandler;
use Application_StateHandler_State;

/**
 * Interface for revisionable objects that can be in different states.
 *
 * @package Application
 * @subpackage Revisionables
 *
 * @see Application_Revisionable
 */
interface RevisionableInterface
    extends
    RevisionableStatelessInterface
{
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
     * @throws RevisionableException
     * @return Application_Revisionable
     */
    public static function createStubObject() : Application_Revisionable;
}
