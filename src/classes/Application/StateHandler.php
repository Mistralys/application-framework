<?php

require_once 'Application/StateHandler/State.php';

class Application_StateHandler
{
    public const ERROR_CANNOT_REPLACE_REVISIONABLE = 14001;
    
    public const ERROR_DUPLICATE_INITIAL_STATE = 14002;
    
    public const ERROR_NO_INITIAL_STATE_DEFINED = 14003;
    
    protected $states = array();

    protected $item;

    protected ?Application_StateHandler_State $initial = null;
    
    public function __construct(Application_Revisionable_Interface $item)
    {
        $this->item = $item;
    }

    /**
     * @param string $stateName
     * @param string $label
     * @param string $uiType 
     * @param boolean $changesAllowed
     * @param boolean $isInitial
     * @throws InvalidArgumentException
     * @return Application_StateHandler_State
     */
    public function registerState($stateName, $label, $uiType = null, $changesAllowed=false, $isInitial=false)
    {
        if ($this->isStateKnown($stateName)) {
            throw new InvalidArgumentException('Cannot add the same state twice');
        }

        $state = new Application_StateHandler_State(
            $stateName, 
            $label, 
            $uiType, 
            $changesAllowed,
            $isInitial,
            $this, 
            $this->item
        );

        if($state->isInitial()) {
            if(isset($this->initial)) {
                throw new Application_Exception(
                    'Duplicate initial state defined.',
                    sprintf(
                        'The state [%s] is set as initial state, but the state [%s] is already set as initial. Only one state may be flagged as initial.',
                        $state->getName(),
                        $this->initial->getName()
                    ),
                    self::ERROR_DUPLICATE_INITIAL_STATE
                );
            }
            
            $this->initial = $state;
        }
        
        $this->states[$stateName] = $state;
        return $state;
    }
    
    public function getInitalState()
    {
        if(isset($this->initial)) {
            return $this->initial;
        }
        
        throw new Application_Exception(
            'No initial state defined.',
            sprintf(
                'No initial state has been defined for the revisionable [%s].',
                get_class($this->item)
            ),
            self::ERROR_NO_INITIAL_STATE_DEFINED
        );
    }

    public function isStateKnown($stateName)
    {
        return isset($this->states[$stateName]);
    }

    /**
     * @param string|Application_StateHandler_State $stateName
     * @throws InvalidArgumentException
     * @return Application_StateHandler_State
     */
    public function getStateByName($stateName)
    {
        if ($stateName instanceof Application_StateHandler_State) {
            return $stateName;
        }

        if (!$this->isStateKnown($stateName)) {
            throw new InvalidArgumentException('Cannot get state: no such state');
        }

        return $this->states[$stateName];
    }

    /**
     * Retrieves an indexed array with state objects for all available states.
     * @return Application_StateHandler_State[]
     */
    public function getStates()
    {
        $states = array_values($this->states);

        return $states;
    }
    
    public function __clone()
    {
        $this->item = null;
        foreach($this->states as $name => $state) {
            $this->states[$name] = clone $state;
        }
    }
    
    public function setRevisionable(Application_Revisionable $revisionable)
    {
        if(!isset($this->item)) {
            $this->item = $revisionable;
            foreach($this->states as $state) {
                $state->setRevisionable($revisionable);
            }
            return;
        }
        
        // the item can only be null after the state handler was cloned,
        // so ensure that this is not done outside of that context.
        throw new Application_Exception(
            'Cannot replace existing revisionable',
            sprintf(
                'Cannot switch the state handler\'s revisionable to [%s], it already uses the revisionable [%s].',
                $revisionable->getID(),
                $this->item->getID()
            ),
            self::ERROR_CANNOT_REPLACE_REVISIONABLE
        );
    }
}