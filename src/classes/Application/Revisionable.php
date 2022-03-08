<?php
/**
 * File containing the {@link Application_Revisionable} class.
 *
 * @package Application
 * @subpackage Revisionable
 * @see Application_Revisionable
 */

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
    /**
     * @var Application_StateHandler
     */
    protected $stateHandler;

    public const ERROR_SAVING_WITHOUT_TRANSACTION = 68439001;
    public const ERROR_INVALID_EVENT_CALLBACK = 68439002;

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
    public function getStateHandler()
    {
        return $this->stateHandler;
    }

   /**
    * @var Application_StateHandler[]
    */
    protected static $masterStateHandlers = array();
    
    /**
     * Initializes the state handler by retrieving the item-specific
     * state definitions, and configuring the state handler with this
     * information.
     */
    protected function initStateHandler()
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
     */
    public function getCurrentStateLabel()
    {
        $state = $this->getState();
        if ($state instanceof Application_StateHandler_State) {
            return $state->getLabel();
        }

        return null;
    }

    public function getCurrentPrettyStateLabel()
    {
        $state = $this->getState();
        if ($state instanceof Application_StateHandler_State) {
            return $state->getPrettyLabel();
        }

        return null;
    }

    /**
     * Returns the human-readable label for the specified state,
     * in the current application locale. If the state name is not
     * specified, the current item's state will be used.
     *
     * @param string $stateName
     * @return string
     * @throws Application_Exception
     */
    public function getStateLabel($stateName = null)
    {
        if (empty($stateName)) {
            $stateName = $this->getStateName();
        }

        $state = $this->stateHandler->getStateByName($stateName);

        return $state->getLabel();
    }

    /**
     * Retrieves the pretty human readable state label.
     * Contains HTML.
     *
     * @param string $stateName
     * @return string
     */
    public function getPrettyStateLabel($stateName = null)
    {
        if (empty($stateName)) {
            $stateName = $this->getStateName();
        }

        $state = $this->stateHandler->getStateByName($stateName);

        return $state->getPrettyLabel();
    }

    public function getCategoryName($id) {

        if ($id == 0) {
            return false;
        }

        $cat = DBHelper::fetch('SELECT * FROM `categories_tags` WHERE tag_id = :tag_id', Array(
            ':tag_id' => $id
        ));

        return $cat['label'];

    }

    /**
     * The state according to the current revision. Note that
     * this can be null if it has not been set.
     *
     * @return Application_StateHandler_State|NULL
     */
    public function getState()
    {
        return $this->revisions->getKey('state');
    }

    /**
     * Retrieves the name of the current state.
     *
     * @return string
     */
    public function getStateName()
    {
        return $this->getState()->getName();
    }

    /**
     * A list of all available states for the item, as an indexed
     * array containing state objects.
     *
     * @return Application_StateHandler_State[]
     */
    public function getStates()
    {
        return $this->stateHandler->getStates();
    }

    protected $stateChanged = false;

    /**
     * Sets the state of the item, making sure the new
     * state is allowed to be set according to the
     * dependencies of the current state.
     *
     * @param Application_StateHandler_State $newState
     * @throws InvalidArgumentException
     */
    public function setState(Application_StateHandler_State $newState)
    {
        $state = $this->getState();
        if (!is_null($state) && !$state->hasDependency($newState)) {
            throw new InvalidArgumentException('Cannot set state to ' . $newState . ', it is not allowed after the current ' . $state . ' state.');
        }

        $this->log(sprintf(
            'Setting state to [%1$s].',
            $newState->getName()
        ));

        $method = 'setState_' . $newState->getName();
        if (method_exists($this, $method)) {
            $this->log(sprintf(
                'Calling the item\'s [%1$s] method.',
                $method
            ));
            $this->$method();
        }

        $this->revisions->setKey('state', $newState);
        
        $this->structureChanged();
        $this->stateChanged = true;

        $this->triggerEvent('StateChanged', array($newState));
        
        $this->log('State changed successfully.');
    }
    
   /**
    * Collection of validation messages when validating
    * a state change.
    *  
    * @var string[]
    * @see validateStateChange()
    */
    protected $stateValidationMessages;
    
   /**
    * Checks if the revisionable can be safely changed to
    * the specified state.
    * 
    * @param Application_StateHandler_State $state
    * @return boolean
    * @see getStateChangeMessages()
    */
    public function validateStateChange(Application_StateHandler_State $state)
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
    
    protected function addValidateStateMessage($message)
    {
        $this->stateValidationMessages[] = $message;
    }
    
   /**
    * Retrieves all messages that were added during the last
    * call to the {@link validateStateChange()} method.
    * @return string[]
    */
    public function getStateChangeMessages()
    {
        return $this->stateValidationMessages;
    }

    /**
     * Implement this in your class, and return an array that
     * looks like this:
     *
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
     *
     * Each entry defines the name of the state as well as
     * a human readable label and a list of dependencies.
     * The dependencies determine which states can be set
     * after the state.
     *
     * @return array
     */
    abstract protected function buildStateDefs();

    /**
     * Checks whether the currently selected state has the
     * specified state name/object as dependency.
     *
     * @param string|Application_StateHandler_State $state_object_or_name
     * @return boolean
     */
    public function stateHasDependency($state_object_or_name)
    {
        $state = $this->stateHandler->getStateByName($state_object_or_name);

        return $this->getState()->hasDependency($state);
    }

    /**
     * Retrieves the specified state object by its name.
     *
     * @param string $stateName
     * @return Application_StateHandler_State
     * @throws Application_Exception
     */
    public function getStateByName($stateName)
    {
        return $this->stateHandler->getStateByName($stateName);
    }

    /**
     * Creates a dummy object of this item's type,
     * which is used to access all the object's static
     * functions, for example for the state information.
     *
     * @throws Application_Exception
     * @return Application_Revisionable
     */
    public static function createDummyObject()
    {
        throw new Application_Exception(
            'Method must be implemented'
        );
    }

    /**
     * Checks whether the object is in the specified state.
     * @param string|Application_StateHandler_State $stateNameOrObject
     * @return boolean
     */
    public function isState($stateNameOrObject)
    {
        if ($stateNameOrObject instanceof Application_StateHandler_State) {
            $stateName = $stateNameOrObject->getName();
        } else {
            $stateName = $stateNameOrObject;
        }

        if ($this->getStateName() == $stateName) {
            return true;
        }

        return false;
    }

    /**
     * @var boolean
     */
    protected $structuralChanges = false;

    /**
     * Resets the tracking of structural changes before a
     * transaction.
     */
    public function resetStructuralChanges()
    {
        $this->structuralChanges = false;
    }

    /**
     * Sets that structural changes have been made, which
     * will require a change of state.
     */
    protected function structureChanged()
    {
        if ($this->structuralChanges === true) {
            return;
        }

        if ($this->requiresNewRevision === false) {
            $this->changesMade();
        }

        $this->structuralChanges = true;
        $this->log('A structural change was made.');
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
     * @see Application_RevisionableStateless::setPartChanged()
     */
    protected function setPartChanged($part, $structural=false)
    {
        parent::setPartChanged($part, $structural);

        if($structural) {
            $this->structureChanged();
        }
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
    public function save()
    {
        $this->triggerEvent(self::EVENT_BEFORE_SAVE);

        $this->log(sprintf(
            'Saving | Has changes: [%s] | Revision added in last transaction: [%s]',
            \AppUtils\ConvertHelper::bool2string($this->hasChanges()),
            $this->lastTransactionAddedRevision
        ));

        if (!$this->hasChanges() && !isset($this->lastTransactionAddedRevision)) {
            $this->log('Saving | No changes were made, skipping save.');
            return false;
        }

        // enforce that saving changes always has to be done in a transaction.
        if ($this->hasChanges()) {
            $this->requireTransaction();
        }

        $state = $this->getState();

        // automatically change the state if any structural changes were made
        if ($this->hasStructuralChanges()) {
            if (!$this->stateChanged && $state->hasStructuralStateChange()) {
                $newState = $state->getStructuralChangeState();
                $this->log(sprintf('Saving | Structural changes detected, automatically changing the state to [%s].', $newState->getName()));
                $this->setState($newState);
            }
        }

        if ($this->stateChanged) {
            $this->log(sprintf('Saving | Calling the item\'s save implementation (with state change).'));
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
    * @throws Application_Exception
    */
    public function requireTransaction()
    {
        if($this->transactionActive) {
            return;
        }
        
        throw new Application_Exception(
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
    abstract protected function _saveWithStateChange();

    /**
     * Changes the state of the item to the specified new state. Note that
     * this starts its transaction, and should be done outside of regular
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
     */
    public function makeState(Application_StateHandler_State $state, string $comments) : bool
    {
        if($state->getName()==$this->getStateName()) {
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

    public function startTransaction($newOwnerID, $newOwnerName, $comments = '')
    {
        parent::startTransaction($newOwnerID, $newOwnerName, $comments);

        $this->stateChanged = false;
    }

    public function endTransaction()
    {
        if ($this->stateChanged) {
            $this->requiresNewRevision = true;
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
     * @param string $title
     * @return UI_Page_RevisionableTitle
     */
    public function renderTitle($title='')
    {
        return UI::getInstance()->getPage()->createRevisionableTitle($this)->setLabel($title);
    }
    
   /**
    * Checks whether the revisionable has a state by this name.
    * @param string $stateName
    */
    public function hasState($stateName)
    {
        return $this->stateHandler->isStateKnown($stateName);
    }
    
   /**
    * Whether changes may be made to the revisionable in its current state.
    * @return boolean
    */
    public function isChangingAllowed() : bool
    {
        return $this->getState()->isChangingAllowed();
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
    */
    public function getInitialState()
    {
        return $this->stateHandler->getInitalState();
    }
}
