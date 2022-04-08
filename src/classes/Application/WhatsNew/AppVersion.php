<?php

declare(strict_types=1);

namespace Application\WhatsNew;

use Application\WhatsNew;
use Application_Admin_Area_Devel_WhatsNewEditor_Edit;
use Application_Admin_ScreenInterface;
use Application_Exception;
use Application\WhatsNew\AppVersion\VersionLanguage;
use SimpleXMLElement;

class AppVersion
{
    public const ERROR_UNKNOWN_LANGUAGE = 31201;

    protected string $version;
    private WhatsNew $whatsNew;

    /**
     * @var VersionLanguage[]
     */
    protected array $languages = array();

    public function __construct(WhatsNew $whatsNew, SimpleXMLElement $node)
    {
        $this->whatsNew = $whatsNew;
        $this->version = (string)$node['id'];

        $langIDs = VersionLanguage::getLanguageIDs();

        foreach ($langIDs as $langID)
        {
            if (!isset($node->$langID))
            {
                continue;
            }

            $lang = VersionLanguage::createLanguage($langID, $this, $node->$langID);

            if ($lang->isValid())
            {
                $this->languages[$langID] = $lang;
            }
        }
    }

    /**
     * @return string
     */
    public function getNumber() : string
    {
        return $this->version;
    }

    public function getWhatsNew() : WhatsNew
    {
        return $this->whatsNew;
    }

    public function hasLanguage(string $langID) : bool
    {
        return isset($this->languages[$langID]);
    }

    public function getLanguage(string $langID) : VersionLanguage
    {
        if (isset($this->languages[$langID]))
        {
            return $this->languages[$langID];
        }

        throw new Application_Exception(
            'Unknown language for version',
            sprintf(
                'Tried retrieving language [%s] for version [%s]. Available languages are [%s].',
                $langID,
                $this->getNumber(),
                implode(', ', array_keys($this->languages))
            ),
            self::ERROR_UNKNOWN_LANGUAGE
        );
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function getAdminEditURL(array $params=array()) : string
    {
        $params[Application_Admin_ScreenInterface::REQUEST_PARAM_SUBMODE] = Application_Admin_Area_Devel_WhatsNewEditor_Edit::URL_NAME;

        return $this->getWhatsNew()->getAdminURL($params);
    }
}
