<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\FileHelper;

class Application_RequestLog_EnabledStatus
{
    /**
     * @var Application_RequestLog
     */
    private $log;

    /**
     * @var string
     */
    private $storageFile;

    public function __construct(Application_RequestLog $log)
    {
        $this->log = $log;
        $this->storageFile = Application::getStorageFolder().'/request-logging.txt';
    }

    public function getAdminToggleURL(array $params=array()) : string
    {
        return $this->getAdminSetStatusURL(!$this->isEnabled(), $params);
    }

    public function getAdminSetStatusURL(bool $enabled, array $params=array()) : string
    {
        $params[Application_Bootstrap_Screen_RequestLog::REQUEST_PARAM_TOGGLE_STATUS] = ConvertHelper::boolStrict2string($enabled, true);

        return $this->log->getAdminURL($params);
    }

    public function getToggleLabel() : string
    {
        if($this->isEnabled())
        {
            return t('Disable');
        }

        return t('Enable');
    }

    public function getEnabledLabel() : string
    {
        if($this->isEnabled())
        {
            return (string)sb()->danger(t('Enabled'));
        }

        return (string)sb()->muted(t('Disabled'));
    }

    public function isEnabled() : bool
    {
        return file_exists($this->storageFile) && FileHelper::readContents($this->storageFile) === 'yes';
    }

    public function setEnabled(bool $enabled) : Application_RequestLog_EnabledStatus
    {
        FileHelper::saveFile($this->storageFile, ConvertHelper::boolStrict2string($enabled, true));
        return $this;
    }
}
