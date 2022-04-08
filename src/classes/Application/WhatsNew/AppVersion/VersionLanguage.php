<?php
/**
 * File containing the class {@see \Application\WhatsNew\AppVersion\VersionLanguage}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\AppVersion\VersionLanguage
 */

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion;

use Application;
use Application\WhatsNew;
use Application\WhatsNew\AppVersion;
use Application_Exception;
use Application_Exception_UnexpectedInstanceType;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use SimpleXMLElement;

/**
 * Container for a single language in a what's new version entry.
 *
 * Path: whatsnew.version.language
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class VersionLanguage
{
    public const ERROR_UNKNOWN_LANGUAGE_ID = 30101;

    protected static ?array $languageIDs = null;

    protected AppVersion $version;

    public function __construct(AppVersion $version, SimpleXMLElement $node)
    {
        $this->version = $version;

        $this->parse($node);
    }

    abstract public function isDeveloperOnly() : bool;
    abstract public function getMiscLabel() : string;

    public static function createLanguage(string $langID, AppVersion $appVersion, SimpleXMLElement $node) : VersionLanguage
    {
        self::requireLangExists($langID);

        $base = __CLASS__;
        $class = $base.'\\'.$langID;

        Application::requireClassExists($class);

        $language = new $class($appVersion, $node);

        if($language instanceof self)
        {
            return $language;
        }

        throw new Application_Exception_UnexpectedInstanceType(VersionLanguage::class, $language);
    }

    public function getWhatsNew() : WhatsNew
    {
        return $this->version->getWhatsNew();
    }

    public function getID() : string
    {
        return getClassTypeName($this);
    }

    public function getVersion() : AppVersion
    {
        return $this->version;
    }

    /**
     * @return string[]
     * @throws FileHelper_Exception
     */
    public static function getLanguageIDs() : array
    {
        if(isset(self::$languageIDs))
        {
            return self::$languageIDs;
        }

        self::$languageIDs = FileHelper::createFileFinder(__DIR__.'/VersionLanguage')
            ->getPHPClassNames();

        return self::$languageIDs;
    }

    private static function requireLangExists(string $langID) : void
    {
        $ids = self::getLanguageIDs();

        if (in_array($langID, $ids))
        {
            return;
        }

        throw new Application_Exception(
            sprintf('Unknown language [%s].', $langID),
            sprintf('Known languages are [%s].', implode(', ', $ids)),
            self::ERROR_UNKNOWN_LANGUAGE_ID
        );
    }

    public function isValid() : bool
    {
        return !$this->isDeveloperOnly() || Application::getUser()->isDeveloper();
    }

    /**
     * @var LanguageCategory[]
     */
    protected array $categories = array();

    protected function parse(SimpleXMLElement $node) : void
    {
        foreach ($node->item as $itemNode)
        {
            $categoryLabel = (string)$itemNode['category'];
            if (empty($categoryLabel))
            {
                $categoryLabel = $this->getMiscLabel();
            }

            $category = $this->getCategoryByLabel($categoryLabel);
            $category->addItem($itemNode);
        }

        ksort($this->categories);
    }

    public function getCategoryByLabel(string $label) : LanguageCategory
    {
        if (!isset($this->categories[$label]))
        {
            $this->categories[$label] = new LanguageCategory($this, $label);
        }

        return $this->categories[$label];
    }

    /**
     * @return LanguageCategory[]
     */
    public function getCategories() : array
    {
        return array_values($this->categories);
    }

    public function hasCategories() : bool
    {
        return !empty($this->categories);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function toArray() : array
    {
        $result = array();

        foreach ($this->categories as $category)
        {
            $result[] = $category->toArray();
        }

        return $result;
    }
}