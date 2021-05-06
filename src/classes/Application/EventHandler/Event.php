<?php
/**
 * File containing the {@link Application_EventHandler_Event} class.
 *
 * @package Application
 * @subpackeage Core
 * @see Application_EventHandler_Event
 */

use AppUtils\ConvertHelper;

/**
 * Event class for individual events: an instance of this is
 * given as argument to event listener callbacks. May be extended
 * to provide a more specialized API depending on the event.
 *
 * @package Application
 * @subpackage Core
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class Application_EventHandler_Event
{
    const ERROR_EVENT_NOT_CANCELLABLE = 13701;

   /**
    * @var Application_EventHandler_Listener|NULL
    */
    protected $selectedListener = null;
    
   /**
    * @var boolean
    */
    protected $cancel = false;

   /**
    * @var string
    */
    protected $cancelReason = '';

   /**
    * @var string
    */
    protected $name;

   /**
    * @var array<int,mixed>
    */
    protected $args;

   /**
    * @param string $name
    * @param array<int,mixed> $args Indexed array with a list of arguments for the event.
    */
    public function __construct(string $name, array $args=array())
    {
        $this->name = $name;
        $this->args = $args;
    }
    
    public function getName() : string
    {
        return $this->name;
    }

   /**
    * Specifies that the event should be cancelled. This is only
    * possible if the event is callable.
    *
    * @param string $reason The reason for which the event was cancelled
    * @throws Application_Exception
    * @return Application_EventHandler_Event
    */
    public function cancel(string $reason) : Application_EventHandler_Event
    {
        if(!$this->isCancellable()) 
        {
            throw new Application_Exception(
                'Event cannot be cancelled',
                sprintf(
                    'The event [%s] cannot be cancelled.',
                    $this->getName()
                ),
                self::ERROR_EVENT_NOT_CANCELLABLE
            );
        }

        $this->cancel = true;
        $this->cancelReason = $reason;
        
        return $this;
    }

   /**
    * Retrieves all arguments of the event as an array.
    * 
    * @return array<int,mixed>
    */
    public function getArguments() : array
    {
        return $this->args;
    }

   /**
    * Retrieves the argument at the specified index, or null
    * if it does not exist. The index is Zero-Based.
    *
    * @param int $index
    * @return NULL|mixed
    */
    public function getArgument(int $index)
    {
        if(isset($this->args[$index])) 
        {
            return $this->args[$index];
        }

        return null;
    }

    public function getArgumentArray(int $index) : array
    {
        $arg = $this->getArgument($index);

        if(is_array($arg)) {
            return $arg;
        }

        return array();
    }

    public function getArgumentInt(int $index) : int
    {
        return intval($this->getArgument($index));
    }

    public function getArgumentBool(int $index) : bool
    {
        return ConvertHelper::string2bool($index);
    }

   /**
    * Checks whether the event should be cancelled.
    * @return boolean
    */
    public function isCancelled() : bool
    {
        return $this->cancel;
    }

   /**
    * @return string
    */
    public function getCancelReason() : string
    {
        return $this->cancelReason;
    }

   /**
    * Whether this event can be cancelled.
    * @return boolean
    */
    public function isCancellable() : bool
    {
        return true;
    }

   /**
    * Called automatically when a listener for this event is called,
    * to provide information about the listener.
    *
    * @param Application_EventHandler_Listener $listener
    * @return Application_EventHandler_Event
    */
    public function selectListener(Application_EventHandler_Listener $listener) : Application_EventHandler_Event
    {
        $this->selectedListener = $listener;
        return $this;
    }

   /**
    * Retrieves the source of the listener that handled this event.
    * This is an optional string that can be specified when adding
    * an event listener. It can be empty.
    *
    * @return string
    */
    public function getSource() : string
    {
        if(isset($this->selectedListener)) 
        {
            return $this->selectedListener->getSource();
        }

        return '';
    }

    public function startTrigger() : void
    {

    }

    public function stopTrigger() : void
    {
        $this->selectedListener = null;
    }
}
