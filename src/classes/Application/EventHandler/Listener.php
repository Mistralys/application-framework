<?php

declare(strict_types=1);

class Application_EventHandler_Listener
{
   /**
    * @var string
    */
    private $eventName;
    
   /**
    * @var callable
    */
    private $callback;
    
   /**
    * @var string
    */
    private $source;
    
   /**
    * @var integer
    */
    private $id;
    
    public function __construct(int $id, string $eventName, $callback, string $source='')
    {
        $this->id = $id;
        $this->eventName = $eventName;
        $this->callback = $callback;
        $this->source = $source;
    }
    
   /**
    * Unique ID of the listener, within the same request.
    * 
    * @return int
    */
    public function getID() : int
    {
        return $this->id;
    }
    
   /**
    * The name of the event the listener listens to.
    * 
    * @return string
    */
    public function getEventName() : string
    {
        return $this->eventName;
    }
    
   /**
    * Human readable label of where the listener comes from.
    * 
    * @return string
    */
    public function getSource() : string
    {
        return $this->source;
    }
    
   /**
    * @return callable
    */
    public function getCallback() 
    {
        return $this->callback;
    }
}
