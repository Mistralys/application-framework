<?php

class Application_Environments_Environment_Requirement_LocalTest extends Application_Environments_Environment_Requirement
{
    /**
     * @var bool
     */
    protected $value;

    public function __construct()
    {
        if (defined('APP_TESTS_RUNNING'))
        {
            $this->value = APP_TESTS_RUNNING;
        }
        else
        {
            $this->value = false;
        }
    }

    public function isValid() : bool
    {
        return $this->value === true;
    }
}