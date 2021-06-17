<?php

abstract class Application_Driver_Storage
{
    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {

    }

    /**
     * @param string $name
     * @return string|null
     */
    abstract public function get($name);

    /**
     * @param string $name
     * @param mixed $value
     * @param string $role persistent|cache
     */
    abstract public function set($name, $value, $role);

    /**
     * @param string $name
     */
    abstract public function delete($name);

    abstract public function setExpiry(string $name, DateTime $date) : void;

    abstract public function getExpiry(string $name) : ?DateTime;
}
