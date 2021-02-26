<?php

class Application_StateHandler_State
{
    const ERROR_CANNOT_REPLACE_REVISIONABLE = 14101; 
    
    protected $name;

    protected $label;

    /**
     * @var Application_StateHandler
     */
    protected $handler;

    protected $dependencies = array();

    protected $timedState = null;

    protected $timedDelay = null;

    /**
     * @var Application_Revisionable|NULL
     */
    protected $item = null;

    protected $uiType;
    
    protected $isInitial = false;

   /**
    * Whether changes are allowed in this state.
    * @var boolean
    */
    protected $changesAllowed = false;
    
    public function __construct($name, $label, $uiType, $changesAllowed, $isInitial, Application_StateHandler $handler, Application_Revisionable $item)
    {
        $this->name = $name;
        $this->label = $label;
        $this->handler = $handler;
        $this->item = $item;
        $this->uiType = $uiType;
        $this->changesAllowed = $changesAllowed;
        $this->isInitial = $isInitial;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getLabel()
    {
        return $this->label;
    }
    
   /**
    * Checks whether this is the state that an item of this
    * type is created with initially.
    * 
    * @return boolean
    */
    public function isInitial()
    {
        return $this->isInitial;
    }

   /**
    * Whether changes may be made to the revisionable in this state.
    * @return boolean
    */
    public function isChangingAllowed()
    {
        return $this->changesAllowed;
    }
    
    public function getPrettyLabel()
    {
        switch ($this->uiType) {
            case 'success':
                return
                    '<span class="text-success state-label">' .
                    UI::icon()->published() . ' ' .
                    $this->getLabel() .
                    '</span>';

            case 'inactive':
                return
                    '<span class="muted state-label">' .
                    UI::icon()->inactive() . ' ' .
                    $this->getLabel() .
                    '</span>';

            case 'danger':
                return
                    '<span class="text-error state-label">' .
                    UI::icon()->deleted() . ' ' .
                    $this->getLabel() .
                    '</span>';

            case 'warning':
                return
                    '<span class="text-warning state-label">' .
                    UI::icon()->draft() . ' ' .
                    $this->getLabel() .
                    '</span>';

            default:
                return $this->getLabel();
        }
    }
    
   /**
    * Retrieves a badge instance of the item's state.
    * @return UI_Label
    */
    public function getBadge()
    {
        $badge = UI::label($this->getLabel());
        $badge->addClass('state-badge');
        
        switch ($this->uiType) {
            case 'success':
                $badge->makeSuccess();
                break;
        
            case 'danger':
                $badge->makeDangerous();
        
            case 'warning':
                $badge->makeWarning();
        }
        
        return $badge;
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    public function addDependency(Application_StateHandler_State $state)
    {
        $name = $state->getName();
        if (!in_array($name, $this->dependencies)) {
            $this->dependencies[] = $name;
        }
    }

    public function hasDependency(Application_StateHandler_State $state)
    {
        return in_array($state->getName(), $this->dependencies);
    }

    public function setTimedChange(Application_StateHandler_State $state, $delay)
    {
        if (!$this->hasDependency($state)) {
            throw new InvalidArgumentException('Cannot set the state to have a timed change to the target state, it is not a valid dependency of this state.');
        }

        $this->timedState = $state;
        $this->timedDelay = $delay;
    }

    public function hasTimedChange()
    {
        return isset($this->timedState);
    }

    public function getTimeLeft()
    {
        return ($this->item->getRevisionTimestamp() + $this->timedDelay) - time();
    }

    public function getTimeLeftLabel()
    {
        return convert_time2string($this->getTimeLeft());
    }

    public function activate($ownerID, $ownerName)
    {
        if (!isset($this->timedState)) {
            return;
        }

        // check if the delay is depleted
        $timeLeft = $this->getTimeLeft();
        if ($timeLeft > 0) {
            //Application::log('Timed state change: Item '.$this->item->getIdentification().' will automatically be changed to '.$this->timedState->getName().' in '.$this->getTimeLeftLabel().'.');
            return;
        }

        // start a transaction to change the state
        // so the item can decide whether to create
        // a new revision for it.
        $this->item->startTransaction($ownerID, $ownerName, 'Automatic timed state change');
        $this->item->setState($this->timedState);
        $this->item->endTransaction();

        // we need to persist this right away.
        $this->item->save();

        Application::log('Timed state change: Item ' . $this->item->getIdentification() . ' automatically changed from state ' . $this->getName() . ' to ' . $this->timedState->getName() . '.');
    }

    protected $onStructuralChange;

    /**
     * Sets the state the item should automatically be changed to
     * if any structural changes are made. If set, the state will
     * automatically be changed to the new state on saving the item.
     *
     * @param Application_StateHandler_State $state
     * @see Application_Revisionable::save()
     */
    public function setOnStructuralChange(Application_StateHandler_State $state)
    {
        $this->onStructuralChange = $state;
    }

    /**
     * Checks whether this state should be changed into another state
     * when the item has structural changes.
     *
     * @return boolean
     */
    public function hasStructuralStateChange()
    {
        return isset($this->onStructuralChange);
    }

    /**
     * Retrieves the state to change to after structural changes to the item.
     *
     * @return Application_StateHandler_State
     */
    public function getStructuralChangeState()
    {
        return $this->onStructuralChange;
    }
    
    public function __clone()
    {
        $this->item = null;
    }
    
    public function setRevisionable(Application_Revisionable $revisionable)
    {
        if(!isset($this->item)) {
            $this->item = $revisionable;
            return;
        }
        
        throw new Application_Exception(
            'Cannot replace existing revisionable',
            sprintf(
                'Cannot switch the state\'s revisionable to [%s], it already uses the revisionable [%s].',
                $revisionable->getID(),
                $this->item->getID()
            ),
            self::ERROR_CANNOT_REPLACE_REVISIONABLE    
        );
    }
}