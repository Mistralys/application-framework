<?php

declare(strict_types=1);

namespace Application;

use Application_Admin_Area_Devel;
use Application_Admin_Area_Devel_WhatsNewEditor;
use Application_Admin_Area_Devel_WhatsNewEditor_Create;
use Application_Admin_ScreenInterface;
use Application_Driver;
use Application_Exception;
use Application\WhatsNew\AppVersion;
use AppUtils\FileHelper;
use Parsedown;
use const APP_ROOT;

class WhatsNew
{
    public const ERROR_WHATS_NEW_FILE_NOT_FOUND = 30001;
    public const ERROR_COULD_NOT_PARSE_XML = 30002;

    protected string $file;
    private Parsedown $parseDown;

    /**
     * @var AppVersion[]
     */
    protected array $versions = array();

    public function __construct()
    {
        $this->file = APP_ROOT . '/WHATSNEW.xml';
        $this->parseDown = new Parsedown();

        if (!file_exists($this->file))
        {
            throw new Application_Exception(
                sprintf('Could not find file [%s].', basename($this->file)),
                '',
                self::ERROR_WHATS_NEW_FILE_NOT_FOUND
            );
        }

        $this->parse();
    }

    /**
     * @return Parsedown
     */
    public function getParseDown() : Parsedown
    {
        return $this->parseDown;
    }

    protected function parse() : void
    {
        $xml = simplexml_load_string(FileHelper::readContents($this->file));

        if ($xml === false)
        {
            throw new Application_Exception(
                sprintf('Could not read file [%s].', basename($this->file)),
                'Trying to parse the XML failed. Syntax error?',
                self::ERROR_COULD_NOT_PARSE_XML
            );
        }

        foreach ($xml->version as $versionNode)
        {
            $this->versions[] = new AppVersion($this, $versionNode);
        }
    }

    /**
     * Retrieves the current (highest) version.
     *
     * @return AppVersion|NULL
     */
    public function getCurrentVersion() : ?AppVersion
    {
        if (!empty($this->versions))
        {
            return $this->versions[0];
        }

        return null;
    }

    /**
     * @return AppVersion[]
     */
    public function getVersions() : array
    {
        return $this->versions;
    }

    /**
     * @param string $langID
     * @return AppVersion[]
     */
    public function getVersionsByLanguage(string $langID) : array
    {
        $result = array();
        foreach ($this->versions as $version)
        {
            if ($version->hasLanguage($langID))
            {
                $result[] = $version;
            }
        }

        return $result;
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function getAdminURLCreate(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = Application_Admin_Area_Devel_WhatsNewEditor_Create::URL_NAME;

        return $this->getAdminURL($params);
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function getAdminURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_PAGE] = Application_Admin_Area_Devel::URL_NAME;
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_MODE] = Application_Admin_Area_Devel_WhatsNewEditor::URL_NAME;

        return Application_Driver::getInstance()
            ->getRequest()
            ->buildURL($params);
    }
}
