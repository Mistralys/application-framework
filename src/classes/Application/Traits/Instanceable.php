<?php

declare(strict_types=1);

trait Application_Traits_Instanceable
{
    private static $instanceCounter = 0;

    private $instanceID = 0;

    public final function getInstanceID() : int
    {
        if($this->instanceID === 0)
        {
            self::$instanceCounter++;
            $this->instanceID = self::$instanceCounter;
        }

        return $this->instanceID;
    }
}
