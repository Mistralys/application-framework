<?php


/**
 * DBHelper-specific event class.
 *
 * @package Helpers
 * @subpackage DBHelper
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DBHelper_Event
{
    protected $name;
    
    protected $args;
    
    protected $cancel = false;
    
    protected $cancelReason;
    
    public function __construct($name, $args=array())
    {
        $this->name = $name;
        $this->args = $args;
    }
    
    public function getType()
    {
        return $this->getArgument(0);
    }
    
    /**
     * Retrieves all arguments of the event as an array.
     * @return mixed[]
     */
    public function getArguments()
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
    public function getArgument($index)
    {
        if(isset($this->args[$index])) {
            return $this->args[$index];
        }
        
        return null;
    }
    
    public function isWriteOperation()
    {
        return DBHelper_OperationTypes::isWriteOperation($this->getType());
    }
    
    public function getStatement($formatted=false)
    {
        $sql = $this->getArgument(1);
        if($formatted) {
            $sql = DBHelper::formatQuery($sql, $this->getVariables());
        }
        
        return $sql;
    }
    
    public function getVariables()
    {
        return $this->getArgument(2);
    }
    
    /**
     * Checks whether the event should be cancelled.
     * @return boolean
     */
    public function isCancelled()
    {
        return $this->cancel;
    }
    
    public function getCancelReason()
    {
        return $this->cancelReason;
    }
    
    /**
     * Specifies that the event should be cancelled. This is only
     * possible if the event is callable.
     *
     * @param string $reason The reason for which the event was cancelled
     * @return DBHelper_Event
     */
    public function cancel($reason)
    {
        $this->cancel = true;
        $this->cancelReason = $reason;
        return $this;
    }
}