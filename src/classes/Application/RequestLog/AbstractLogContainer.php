<?php

declare(strict_types=1);

abstract class Application_RequestLog_AbstractLogContainer extends Application_RequestLog_AbstractLogItem
{
    public const ERROR_ID_DOES_NOT_EXIST = 100801;

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var array<string,Application_RequestLog_LogItemInterface>
     */
    private $containers = array();

    abstract protected function createContainer(string $id, string $storageFolder) : Application_RequestLog_LogItemInterface;

    protected function containerIDExists(string $id) : bool
    {
        $this->load();

        return isset($this->containers[$id]);
    }

    /**
     * @return Application_RequestLog_LogItemInterface[]
     */
    protected function getContainers() : array
    {
        $this->load();

        return array_values($this->containers);
    }

    protected function getContainerByID(string $id) : Application_RequestLog_LogItemInterface
    {
        $this->load();

        if(isset($this->containers[$id]))
        {
            return $this->containers[$id];
        }

        throw new Application_RequestLog_Exception(
            'Unknown request log item',
            sprintf(
                'The container [%s] has no sub-container with id [%s].',
                get_class($this),
                $id
            ),
            self::ERROR_ID_DOES_NOT_EXIST
        );
    }

    abstract protected function _load() : void;

    protected function load() : void
    {
        if($this->loaded === true)
        {
            return;
        }

        $this->loaded = true;

        $this->_load();
    }

    protected function addContainer(string $id, string $storageFolder) : void
    {
        $container = $this->createContainer($id, $storageFolder);
        $this->containers[$container->getID()] = $container;
    }

    /**
     * Resets the containers list, so they are fetched
     * anew when they are next requested.
     *
     * @return void
     */
    protected function clearContainers() : void
    {
        $this->containers = array();
    }
}
