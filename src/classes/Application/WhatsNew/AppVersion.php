<?php
/**
 * File containing the class {@see \Application\WhatsNew\AppVersion}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\AppVersion
 */

declare(strict_types=1);

namespace Application\WhatsNew;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application\WhatsNew\Admin\Screens\EditSubmode;
use Application\WhatsNew\AppVersion\VersionLanguage;
use Application_Exception;
use SimpleXMLElement;

/**
 * Container for a single version in a what's new file.
 *
 * Path: whatsnew.version
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class AppVersion
{
    public const int ERROR_UNKNOWN_LANGUAGE = 31201;
    public const int ERROR_CANNOT_OVERWRITE_LANGUAGE = 31202;

    public const string REQUEST_PARAM_NUMBER = 'app_version';
    const string REQUEST_PARAM_LANG_ID = 'lang_id';

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
            $lower = strtolower($langID);
            $found = null;

            if (isset($node->$langID))
            {
                $found = $node->$langID;
            }
            else if(isset($node->$lower))
            {
                $found = $node->$lower;
            }

            if($found === null)
            {
                continue;
            }

            $lang = VersionLanguage::createLanguage($langID, $this, $found);

            if ($lang->isValid())
            {
                $this->languages[$langID] = $lang;
            }
        }

        uasort($this->languages, static function(VersionLanguage $a, VersionLanguage $b) : int
        {
            if($b->isDeveloperOnly())
            {
                return -1;
            }

            return strnatcasecmp($a->getID(), $b->getID());
        });
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

    /**
     * @return VersionLanguage[]
     */
    public function getLanguages() : array
    {
        return array_values($this->languages);
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

    public function addLanguage(string $langID) : VersionLanguage
    {
        if($this->hasLanguage($langID))
        {
            throw new WhatsNewException(
                'Cannot overwrite existing language.',
                '',
                self::ERROR_CANNOT_OVERWRITE_LANGUAGE
            );
        }

        $lang = VersionLanguage::createLanguage($langID, $this);

        $this->languages[$langID] = $lang;

        return $lang;
    }

    /**
     * @param array<string,string> $params
     * @return string
     */
    public function getAdminEditURL(array $params=array()) : string
    {
        $params[AdminScreenInterface::REQUEST_PARAM_SUBMODE] = EditSubmode::URL_NAME;
        $params[self::REQUEST_PARAM_NUMBER] = $this->getNumber();

        return $this->getWhatsNew()->getAdminURL($params);
    }

    public function getAdminLanguageURL(string $language, array $params=array()) : string
    {
        $params[self::REQUEST_PARAM_LANG_ID] = $language;

        return $this->getAdminEditURL($params);
    }
}
