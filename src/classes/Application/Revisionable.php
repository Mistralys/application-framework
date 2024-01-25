<?php
/**
 * File containing the {@link Application_Revisionable} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_Revisionable
 */

use Application\Revisionable\RevisionableException;
use Application\StateHandler\StateHandlerException;
use AppUtils\BaseException;
use AppUtils\ConvertHelper;

/**
 * Base class for data types that are revisionable and have states.
 * Provides a skeleton and common functionality for all revisionable
 * items, along with their states.
 *
 * @package Application
 * @subpackage Revisionable
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_RevisionableStateless
 */
abstract class Application_Revisionable extends Application_RevisionableStateless
{
    public const ERROR_SAVING_WITHOUT_TRANSACTION = 149301;
    public const ERROR_INVALID_STATE_CHANGE = 149303;
    public const ERROR_NO_STATE_AVAILABLE = 149304;
    public const ERROR_STUB_OBJECT_METHOD_NOT_IMPLEMENTED = 149305;

    protected Application_StateHandler $stateHandler;

    /**
     * Initializes the underlying objects like the revision
     * storage object and state handler. Make sure to call this
     * if you have your own constructor using parent::__construct().
     */
    public function __construct()
    {
        parent::__construct();
        $this->initStateHandler();
    }

    /**
     * Retrieves the item's state handler.
     * @return Application_StateHandler
     */
    public function getStateHandler() : Application_StateHandler
    {
        return $this->stateHandler;
    }

   /**
    * @var Application_StateHandler[]
    */
    protected static array $masterStateHandlers = array();
    
    /**
     * Initializes the state handler by retrieving the item-specific
     * state definitions, and configuring the state handler with this
     * information.
     */
    protected function initStateHandler() : void
    {
        $class = get_class($this);
        
        // to avoid having to go through the setup of the state definitions
        // for each single revisionable of the same class, we clone the
        // existing state handler and replace the revisionable instance 
        // with this one.
        if(isset(self::$masterStateHandlers[$class])) {
            $handler = clone self::$masterStateHandlers[$class];
            $handler->setRevisionable($this);
            $this->stateHandler = $handler;
            return;
        }

        $this->stateHandler = new Application_StateHandler($this);
        
        self::$masterStateHandlers[$class] = $this->stateHandler;
        
        $defs = $this->buildStateDefs();

        $states = array_keys($defs);
        $total = count($states);

        /* @var $collection array<string,Application_StateHandler_State> */
        $collection = array(); // keep the collection locally for performance reasons
        for($i=0; $i<$total; $i++) {
            $stateName = $states[$i];
            $def = $defs[$stateName];
            
            if(!isset($def['initial'])) {
                $def['initial'] = false;
            }
            
            if(!isset($def['increasesPrettyRevision'])) {
                $def['increasesPrettyRevision'] = false;
            }
            
            $collection[$stateName] = $this->stateHandler->registerState(
                $stateName,
                $def['label'],
                $def['uiType'],
                $def['changesAllowed'],
                $def['initial']
            );
        }

        // now that all states are known, add the dependencies
        for($i=0; $i<$total; $i++) {
            $stateName = $states[$i];
            $state = $collection[$stateName];
            $dependencies = $defs[$stateName]['dependencies'];
            foreach ($dependencies as $dependentName) {
                $state->addDependency($collection[$dependentName]);
            }
        }

        // and now we can manage additional settings
        for($i=0; $i<$total; $i++) {
            $stateName = $states[$i];
            $def = $defs[$stateName];
            $state = $collection[$stateName];
            if (isset($def['timedChangeTo'])) {
                $state->setTimedChange(
                    $collection[$def['timedChangeTo']],
                    $def['timerDelay']
                );
            }

            if (isset($def['onStructuralChange'])) {
                $changeTo = $collection[$def['onStructuralChange']];
                $state->setOnStructuralChange($changeTo);
            }
        }
    }

    /**
     * Retrieves the label for the current state the object is in,
     * or null if no state has been set yet.
     * @return string|NULL
     * @throws RevisionableException
     */
    public function getCurrentStateLabel() : ?string
    {
        $state = $this->getState();

        if ($state !== null) {
            return $state->getLabel();
        }

        return null;
    }

    public function getCurrentPrettyStateLabel() : ?string
    {
        $state = $this->getState();

        if ($state !== null) {
            return $state->getPrettyLabel();
        }

        return null;
    }

    /**
     * Returns the human-readable label for the specified state,
     * in the current application locale. If the state name is not
     * specified, the current item's state will be used.
     *
     * @param string|NULL $stateName If no state name is specified, uses the current state.
     * @return string
     * @throws RevisionableException
     */
    public function getStateLabel(?string $stateName = null) : string
    {
        if (empty($stateName)) {
            $stateName = $this->getStateName();
        }

        return $this->stateHandler->getStateByName($stateName)->getLabel();
    }

    /**
     * Retrieves the pretty human-readable state label.
     * Contains HTML.
     *
     * @param string|NULL $stateName If no state name is specified, uses the current state.
     * @return string
     * @throws RevisionableException
     */
    public function getPrettyStateLabel(?string $stateName = null) : string
    {
        if (empty($stateName)) {
            $stateName = $this->getStateName();
        }

        return $this->stateHandler->getStateByName($stateName)->getPrettyLabel();
    }

    /**
     * The state according to the current revision. Note that
     * this can be null if it has not been set.
     *
     * @return Application_StateHandler_State|NULL
     * @throws RevisionableException
     */
    public function getState() : ?Application_StateHandler_State
    {
        $state = $this->revisions->getKey('state');

        if($state instanceof Application_StateHandler_State) {
            return $state;
        }

        return null;
    }

    /**
     * Like {@see self::getState()}, but the method does not
     * return <code>NULL</code>. An exception is thrown instead
     * if no state is available.
     *
     * @return Application_StateHandler_State
     * @throws RevisionableException
     */
    public function requireState() : Application_StateHandler_State
    {
        $state = $this->getState();

        if($state !== null) {
            return $state;
        }

        throw new RevisionableException(
            'No revisionable state available',
            sprintf(
                'No state available in the revisionable [%s].',
                $this->getIdentification()
            ),
            self::ERROR_NO_STATE_AVAILABLE
        );
    }

    /**
     * Retrieves the name of the current state.
     *
     * @return string
     * @throws RevisionableException
     */
    public function getStateName() : string
    {
        return $this->requireState()->getName();
    }

    /**
     * A list of all available states for the item, as an indexed
     * array containing state objects.
     *
     * @return Application_StateHandler_State[]
     */
    public function getStates() : array
    {
        return $this->stateHandler->getStates();
    }

    protected bool $stateChanged = false;

    /**
     * Sets the state of the item, making sure the new
     * state is allowed to be set according to the
     * dependencies of the current state.
     *
     * @param Application_StateHandler_State $newState
     * @return $this
     * @throws RevisionableException
     * @throws StateHandlerException
     */
    public function setState(Application_StateHandler_State $newState) : self
    {
        $this->requireTransaction();

        if($newState->getName() === $this->getStateName()) {
            return $this;
        }

        $state = $this->getState();
        if (!is_null($state) && !$state->hasDependency($newState)) {
            throw new StateHandlerException(
                'Invalid state change.',
                sprintf(
                'Cannot set state to [%s], it is not allowed after the current [%s] state.',
                    $newState,
                    $state
                ),
                self::ERROR_INVALID_STATE_CHANGE
            );
        }

        $this->log('Setting state to [%1$s].', $newState->getName());

        $this->revisions->setKey('state', $newState);
        
        $this->structureChanged('State has changed');
        $this->stateChanged = true;

        $this->triggerEvent('StateChanged', array($newState));
        
        $this->log('State changed successfully.');

        return $this;
    }
    
   /**
    * Collection of validation messages when validating
    * a state change.
    *  
    * @var string[]
    * @see validateStateChange()
    */
    protected array $stateValidationMessages = array();
    
   /**
    * Checks if the revisionable can be safely changed to
    * the specified state.
    * 
    * @param Application_StateHandler_State $state
    * @return boolean
    * @see getStateChangeMessages()
    */
    public function validateStateChange(Application_StateHandler_State $state) : bool
    {
        $this->stateValidationMessages = array();
        
        $method = '_validateStateChange_'.$state->getName();
        if(method_exists($this, $method)) {
            $this->$method();
        }
        
        if(empty($this->stateValidationMessages)) {
            return true;
        }
        
        return false;
    }

    /**
     * @param string $message
     * @return $this
     */
    protected function addValidateStateMessage(string $message) : self
    {
        $this->stateValidationMessages[] = $message;
        return $this;
    }
    
   /**
    * Retrieves all messages added during the last
    * call to the {@link validateStateChange()} method.
    * @return string[]
    */
    public function getStateChangeMessages() : array
    {
        return $this->stateValidationMessages;
    }

    /**
     * Implement this in your class, and return an array that
     * looks like this:
     *
     * <pre>
     * array(
     *     'state_name_1' => array(
     *         'label' => 'State number one',
     *         'dependencies' => array(
     *             'state_name_2',
     *             'state_name_6',
     *             [...]
     *         )
     *     ),
     *     [...]
     * )
     * </pre>
     *
     * Each entry defines the name of the state as well as
     * a human-readable label and a list of dependencies.
     * The dependencies determine which states can be set
     * after the state.
     *
     * @return array
     */
    abstract protected function buildStateDefs() : array;

    /**
     * Checks whether the currently selected state has the
     * specified state name/object as dependency.
     *
     * @param string|Application_StateHandler_State $state_object_or_name
     * @return boolean
     */
    public function stateHasDependency($state_object_or_name) : bool
    {
        $state = $this->stateHandler->getStateByName($state_object_or_name);

        return $this->requireState()->hasDependency($state);
    }

    /**
     * Retrieves the specified state object by its name.
     *
     * @param string|Application_StateHandler_State $nameOrInstance
     * @return Application_StateHandler_State
     */
    public function getStateByName($nameOrInstance) : Application_StateHandler_State
    {
        return $this->stateHandler->getStateByName($nameOrInstance);
    }

    /**
     * Creates a stub object of this item's type,
     * which is used to access all the object's static
     * functions, for example, for the state information.
     *
     * @throws Application_Exception
     * @return Application_Revisionable
     */
    public static function createDummyObject() : Application_Revisionable
    {
        throw new RevisionableException(
            'Method must be implemented',
            sprintf(
                'The method [%s] must be implemented to use it.',
                array(self::class, 'createDummyObject')[1].'()'
            ),
            self::ERROR_STUB_OBJECT_METHOD_NOT_IMPLEMENTED
        );
    }

    /**
     * Checks whether the object is in the specified state.
     * @param string|Application_StateHandler_State $nameOrInstance
     * @return boolean
     */
    public function isState($nameOrInstance) : bool
    {
        if ($nameOrInstance instanceof Application_StateHandler_State) {
            $stateName = $nameOrInstance->getName();
        } else {
            $stateName = $nameOrInstance;
        }

        return $this->getStateName() === $stateName;
    }

    /**
     * @var boolean
     */
    protected bool $structuralChanges = false;

    /**
     * Resets the tracking of structural changes before a
     * transaction.
     *
     * @return $this
     */
    public function resetStructuralChanges() : self
    {
        $this->structuralChanges = false;
        return $this;
    }

    /**
     * Sets that structural changes have been made, which
     * will require a change of state.
     *
     * @param string $reason
     * @return $this
     */
    protected function structureChanged(string $reason) : self
    {
        if ($this->hasStructuralChanges() === true) {
            return $this;
        }

        if ($this->requiresNewRevision === false) {
            $this->changesMade();
        }

        $this->structuralChanges = true;

        $this->log('Transaction | Structural change: [%s]', $reason);

        return $this;
    }

    /**
     * Checks if the item has any changes that modify its structure,
     * which would mean that its state has to change.
     *
     * @return boolean
     */
    public function hasStructuralChanges() : bool
    {
        return $this->structuralChanges;
    }

    /**
     * Sets that the specified revisionable part has changed,
     * with the option to specify whether the change was structural
     * and will require a new revision.
     *
     * @param string $part
     * @param boolean $structural
     * @return Application_Revisionable
     * @throws Application_Exception
     * @see Application_RevisionableStateless::setPartChanged()
     */
    protected function setPartChanged(string $part, bool $structural=false) : self
    {
        parent::setPartChanged($part, $structural);

        if($structural) {
            $this->structureChanged(sprintf('Structural part [%s] changed.', $part));
        }

        return $this;
    }

    /**
     * Saves all changes after a transaction. The actual saving mechanism
     * is implemented in the extended class in the {@link _save} and {@link _saveWithStateChange()}
     * methods. These are called according to the kind of changes that
     * were made and the state configuration.
     *
     * @see Application_RevisionableStateless::save()
     * @see _save()
     * @see _saveWithStateChange()
     */
    public function save() : bool
    {
        $this->triggerEvent(self::EVENT_BEFORE_SAVE);

        $this->log(sprintf(
            'Saving | Has changes: [%s] | Revision added in last transaction: [%s]',
            ConvertHelper::bool2string($this->hasChanges()),
            $this->lastTransactionAddedRevision
        ));

        if (!isset($this->lastTransactionAddedRevision) && !$this->hasChanges()) {
            $this->log('Saving | No changes were made, skipping save.');
            $this->resetChanges();
            $this->resetStructuralChanges();
            return false;
        }

        // enforce that saving changes always has to be done in a transaction.
        if ($this->hasChanges()) {
            $this->requireTransaction();
        }

        $state = $this->requireState();

        // automatically change the state if any structural changes were made
        if ($this->hasStructuralChanges())
        {
            $newState = $state->getStructuralChangeState();

            if (!$this->stateChanged && $newState !== null) {
                $this->log(sprintf('Saving | Structural changes detected, automatically changing the state to [%s].', $newState->getName()));
                $this->setState($newState);
            }
        }

        if ($this->stateChanged) {
            $this->log('Saving | Calling the item\'s save implementation (with state change).');
            $this->_saveWithStateChange();
        } else {
            $this->log('Saving | Calling the item\'s save implementation (without state change).');
            $this->_save();
        }
        
        $this->saveParts();

        $this->log('Saving | Complete.');
        $this->resetChanges();
        $this->resetStructuralChanges();

        return true;
    }

   /**
    * Ensures that a transaction is active for operations that
    * may only be done within a transaction.
    * 
    * @throws RevisionableException
    */
    public function requireTransaction() : self
    {
        if($this->transactionActive) {
            return $this;
        }
        
        throw new RevisionableException(
            'No transaction active',
            'The current operation requires a transaction to be started.',
            self::ERROR_SAVING_WITHOUT_TRANSACTION
        );
    }

    /**
     * The item's custom save implementation that is called when the item
     * has changes that also changed the item's state, which usually means
     * the new revision has to be added permanently.
     *
     * @see save()
     */
    abstract protected function _saveWithStateChange() : void;

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
     * @param string $comments
     * @return boolean
     * @throws RevisionableException
     * @throws StateHandlerException
     */
    public function makeState(Application_StateHandler_State $state, ?string $comments=null) : bool
    {
        if($state->getName() === $this->getStateName()) {
            return false;
        }
        
        $transaction = false;
        if(!$this->isTransactionStarted()) {
            $this->startCurrentUserTransaction($comments);
            $transaction = true;
        }
        
        $this->setState($state);
        
        if($transaction) {
            $this->endTransaction();
        }

        return true;
    }

    /**
     * @param int $newOwnerID
     * @param string $newOwnerName
     * @param string|null $comments
     * @return $this
     * @throws Application_Exception
     */
    public function startTransaction(int $newOwnerID, string $newOwnerName, ?string $comments = null) : self
    {
        parent::startTransaction($newOwnerID, $newOwnerName, $comments);

        $this->log('Current state is [%1$s].', $this->getStateName());

        $this->stateChanged = false;

        return $this;
    }

    public function endTransaction() : bool
    {
        if ($this->stateChanged) {
            $this->setRequiresNewRevision('State has changed');
        }

        $result = parent::endTransaction();
        
        $this->triggerEvent('TransactionEnded');

        return $result;
    }

    /**
     * Renders a page title for this item using a template: this will
     * automatically add the current state of the item within the title,
     * including developer-specific information for developer users.
     *
     * @param string|NULL $title
     * @return UI_Page_RevisionableTitle
     */
    public function renderTitle(?string $title=null) : UI_Page_RevisionableTitle
    {
        return UI::getInstance()->getPage()->createRevisionableTitle($this)->setLabel($title);
    }

    /**
     * Checks whether the revisionable has a state by this name.
     * @param string $stateName
     * @return bool
     */
    public function hasState(string $stateName) : bool
    {
        return $this->stateHandler->isStateKnown($stateName);
    }
    
   /**
    * Whether changes may be made to the revisionable in its current state.
    * @return boolean
    */
    public function isChangingAllowed() : bool
    {
        return $this->requireState()->isChangingAllowed();
    }
    
    public function isEditable() : bool
    {
        if(!parent::isEditable())
        {
            return false;
        }

        return $this->isChangingAllowed();
    }

    /**
     * Retrieves the state the revisionable is initially created with.
     * @return Application_StateHandler_State
     * @throws StateHandlerException
     * @throws BaseException
     */
    public function getInitialState() : Application_StateHandler_State
    {
        return $this->stateHandler->getInitialState();
    }
}
