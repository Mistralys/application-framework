<?php

declare(strict_types=1);

use Application\Revisionable\RevisionableInterface;
use Application\StateHandler\StateHandlerException;
use AppUtils\Interfaces\StringableInterface;

class Application_StateHandler_State implements StringableInterface
{
    public const int ERROR_CANNOT_REPLACE_REVISIONABLE = 14101;
    public const int ERROR_INVALID_TIMED_CHANGE = 14102;

    public const string UI_TYPE_SUCCESS = 'success';
    public const string UI_TYPE_INACTIVE = 'inactive';
    public const string UI_TYPE_DANGER = 'danger';
    public const string UI_TYPE_WARNING = 'warning';
    public const string UI_TYPE_DEFAULT = 'default';

    protected string $name;
    protected string $label;
    protected Application_StateHandler $handler;
    protected ?Application_StateHandler_State $timedState = null;
    protected int $timedDelay = 0;
    protected ?RevisionableInterface $item = null;
    protected string $uiType;
    protected bool $changesAllowed = false;
    protected bool $isInitial = false;

    /**
     * @var string[]
     */
    protected array $dependencies = array();

    public function __construct(string $name, string $label, string $uiType, bool $changesAllowed, bool $isInitial, Application_StateHandler $handler, RevisionableInterface $item)
    {
        $this->name = $name;
        $this->label = $label;
        $this->handler = $handler;
        $this->item = $item;
        $this->uiType = $uiType;
        $this->changesAllowed = $changesAllowed;
        $this->isInitial = $isInitial;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getLabel() : string
    {
        return $this->label;
    }
    
   /**
    * Checks whether this is the state that an item of this
    * type is created with initially.
    * 
    * @return boolean
    */
    public function isInitial() : bool
    {
        return $this->isInitial;
    }

   /**
    * Whether changes may be made to the revisionable in this state.
    * @return boolean
    */
    public function isChangingAllowed() : bool
    {
        return $this->changesAllowed;
    }
    
    public function getPrettyLabel() : string
    {
        return match ($this->uiType) {
            self::UI_TYPE_SUCCESS => '<span class="text-success state-label">' .
                UI::icon()->published() . ' ' .
                $this->getLabel() .
                '</span>',
            self::UI_TYPE_INACTIVE => '<span class="muted state-label">' .
                UI::icon()->inactive() . ' ' .
                $this->getLabel() .
                '</span>',
            self::UI_TYPE_DANGER => '<span class="text-error state-label">' .
                UI::icon()->deleted() . ' ' .
                $this->getLabel() .
                '</span>',
            self::UI_TYPE_WARNING => '<span class="text-warning state-label">' .
                UI::icon()->draft() . ' ' .
                $this->getLabel() .
                '</span>',
            default => $this->getLabel(),
        };
    }

    public function getIcon() : UI_Icon
    {
        return match ($this->uiType) {
            self::UI_TYPE_SUCCESS => UI::icon()->published(),
            self::UI_TYPE_INACTIVE => UI::icon()->inactive(),
            self::UI_TYPE_DANGER => UI::icon()->deleted(),
            default => UI::icon()->draft()
        };
    }

    /**
     * Retrieves a badge instance of the item's state.
     * @return UI_Label
     *
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function getBadge() : UI_Label
    {
        $badge = UI::label($this->getLabel());
        $badge->addClass('state-badge');
        
        switch ($this->uiType) {
            case self::UI_TYPE_SUCCESS:
                $badge->makeSuccess();
                break;
        
            case self::UI_TYPE_DANGER:
                $badge->makeDangerous();
                break;
        
            case self::UI_TYPE_WARNING:
                $badge->makeWarning();
                break;

            case self::UI_TYPE_INACTIVE:
                $badge->makeInactive();
                break;
        }
        
        return $badge;
    }

    public function __toString() : string
    {
        return $this->getLabel();
    }

    /**
     * Adds a dependency to this state (i.e. a state that can be
     * transitioned to from this state).
     *
     * @param Application_StateHandler_State $state
     * @return void
     */
    public function addDependency(Application_StateHandler_State $state) : void
    {
        $name = $state->getName();

        if (!in_array($name, $this->dependencies, true)) {
            $this->dependencies[] = $name;
        }
    }

    /**
     * Gets a list of state names that are dependencies of this state
     * (i.e. states that can be transitioned to from this state).
     *
     * @return string[]
     */
    public function getDependencies() : array
    {
        return $this->dependencies;
    }

    public function hasDependency(Application_StateHandler_State $state) : bool
    {
        return in_array($state->getName(), $this->dependencies, true);
    }

    public function setTimedChange(Application_StateHandler_State $state, $delay) : self
    {
        if (!$this->hasDependency($state)) {
            throw new StateHandlerException(
                'Invalid state dependency',
                sprintf(
                    'Cannot set the state [%s] to have a timed change to the target state [%s], it is not a valid dependency of this state.',
                    $this->getName(),
                    $state->getName()
                ),
                self::ERROR_INVALID_TIMED_CHANGE
            );
        }

        $this->timedState = $state;
        $this->timedDelay = $delay;

        return $this;
    }

    public function hasTimedChange() : bool
    {
        return isset($this->timedState);
    }

    public function getTimeLeft() : int
    {
        return ($this->item->getRevisionTimestamp() + $this->timedDelay) - time();
    }

    public function activate($ownerID, $ownerName) : self
    {
        if (!isset($this->timedState)) {
            return $this;
        }

        // check if the delay is depleted
        $timeLeft = $this->getTimeLeft();
        if ($timeLeft > 0) {
            //Application::log('Timed state change: Item '.$this->item->getIdentification().' will automatically be changed to '.$this->timedState->getName().' in '.$this->getTimeLeftLabel().'.');
            return $this;
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

        return $this;
    }

    protected ?Application_StateHandler_State $onStructuralChange = null;

    /**
     * Sets the state the item should automatically be changed to
     * if any structural changes are made. If set, the state will
     * automatically be changed to the new state on saving the item.
     *
     * @param Application_StateHandler_State $state
     * @return $this
     * @see RevisionableInterface::save()
     */
    public function setOnStructuralChange(Application_StateHandler_State $state) : self
    {
        $this->onStructuralChange = $state;
        return $this;
    }

    /**
     * Checks whether this state should be changed into another state
     * when the item has structural changes.
     *
     * @return boolean
     */
    public function hasStructuralStateChange() : bool
    {
        return isset($this->onStructuralChange);
    }

    /**
     * Retrieves the state to change to after structural changes to the item.
     *
     * @return Application_StateHandler_State|NULL
     */
    public function getStructuralChangeState() : ?Application_StateHandler_State
    {
        return $this->onStructuralChange;
    }
    
    public function __clone()
    {
        $this->item = null;
    }

    /**
     * @param RevisionableInterface $revisionable
     * @return $this
     * @throws StateHandlerException {@see self::ERROR_CANNOT_REPLACE_REVISIONABLE}
     */
    public function setRevisionable(RevisionableInterface $revisionable) : self
    {
        if(!isset($this->item)) {
            $this->item = $revisionable;
            return $this;
        }
        
        throw new StateHandlerException(
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