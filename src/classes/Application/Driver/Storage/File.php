<?php

use Application\Application;
use AppUtils\FileHelper;

class Application_Driver_Storage_File extends Application_Driver_Storage
{
    const string COL_EXPIRY = 'expiry';
    const string COL_KEYS = 'keys';

    protected $dataFile;

    protected $data;

    /**
     * @var string
     */
    private $dateFormat = 'Y-m-d H:i:s';

    protected function init()
    {
        $this->dataFile = Application::getStorageSubfolderPath('settings').'/driver.json';

        // to avoid multiple writes for single setting values,
        // we use the shutdown event to save everything at the
        // end of the request.
        Application_EventHandler::addListener(
            Application::EVENT_SYSTEM_SHUTDOWN,
            array($this, 'handle_shutDown')
        );
    }

    public function get($name)
    {
        $this->load();

        if(isset($this->data[self::COL_KEYS][$name])) {
            return $this->data[self::COL_KEYS][$name];
        }

        return null;
    }

    public function set($name, $value, $role)
    {
        $this->load();

        $this->data[self::COL_KEYS][$name] = $value;
    }

    public function setExpiry(string $name, DateTime $date) : void
    {
        $this->load();

        $this->data[self::COL_EXPIRY][$name] = $date->format($this->dateFormat);
    }

    public function getExpiry(string $name) : ?DateTime
    {
        $this->load();

        if(isset($this->data[self::COL_EXPIRY][$name])) {
            return new DateTime($this->data[self::COL_EXPIRY][$name]);
        }

        return null;
    }

    public function delete($name)
    {
        $this->load();

        if(isset($this->data[self::COL_KEYS][$name])) {
            unset($this->data[self::COL_KEYS][$name]);
        }

        if(isset($this->data[self::COL_EXPIRY][$name])) {
            unset($this->data[self::COL_EXPIRY][$name]);
        }
    }

    protected function load()
    {
        if(isset($this->data)) {
            return;
        }

        $this->data = array(
            self::COL_KEYS => array(),
            self::COL_EXPIRY => array()
        );

        if(file_exists($this->dataFile)) {
            $this->data = FileHelper::parseJSONFile($this->dataFile);
        }
    }

    public function handle_shutDown(Application_EventHandler_Event_SystemShutDown $event)
    {
        $this->writeToDisk();
    }

    protected function writeToDisk()
    {
        $this->load();

        AppUtils\FileHelper::saveAsJSON($this->data, $this->dataFile);
    }
}