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

use Application\Application;
use Application\WhatsNew\AppVersion;
use Application\WhatsNew\WhatsNew;
use Application_Exception;
use AppUtils\ClassHelper;
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
    protected int $itemCounter = 0;

    /**
     * @var CategoryItem[]
     */
    private array $items = array();

    public function __construct(AppVersion $version, ?SimpleXMLElement $node=null)
    {
        $this->version = $version;

        if($node !== null)
        {
            $this->parse($node);
        }
    }

    abstract public function isDeveloperOnly() : bool;
    abstract public function getMiscLabel() : string;

    public static function createLanguage(string $langID, AppVersion $appVersion, ?SimpleXMLElement $node=null) : VersionLanguage
    {
        self::requireLangExists($langID);

        $class = ClassHelper::requireResolvedClass(__CLASS__.'_'.$langID);

        return ClassHelper::requireObjectInstanceOf(
            self::class,
            new $class($appVersion, $node)
        );
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

        $languageIDs = FileHelper::createFileFinder(__DIR__.'/VersionLanguage')
            ->getPHPClassNames();

        $devLang = WhatsNew::getDeveloperLangID();

        $key = array_search($devLang, $languageIDs, true);
        if($key !== false) {
            unset($languageIDs[$key]);
        }

        usort($languageIDs, static function(string $a, string $b) : int
        {
            return strnatcasecmp($a, $b);
        });

        if($key !== false) {
            $languageIDs[] = $devLang;
        }

        self::$languageIDs = $languageIDs;

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
    protected array $knownCategories = array();

    protected function parse(SimpleXMLElement $node) : void
    {
        foreach ($node->item as $itemNode)
        {
            $category = $this->getCategoryByLabel((string)$itemNode['category']);
            $this->registerItem($category, $itemNode);
        }
    }

    private function registerItem(LanguageCategory $category, ?SimpleXMLElement $node) : CategoryItem
    {
        $this->itemCounter++;

        $item = new CategoryItem($category, $this->itemCounter, $node);

        $this->items[] = $item;

        return $item;
    }

    public function getCategoryByLabel(string $label) : LanguageCategory
    {
        if (empty($label))
        {
            $label = $this->getMiscLabel();
        }

        if (!isset($this->knownCategories[$label]))
        {
            $this->knownCategories[$label] = new LanguageCategory($this, $label);
        }

        return $this->knownCategories[$label];
    }

    /**
     * @return LanguageCategory[]
     */
    public function getCategories() : array
    {
        $stack = array();

        foreach($this->items as $item)
        {
            $category = $item->getCategory();
            $label = $category->getLabel();

            if(!isset($stack[$label]))
            {
                $stack[$label] = $category;
            }
        }

        ksort($stack);

        return array_values($stack);
    }

    public function hasCategories() : bool
    {
        $categories = $this->getCategories();
        return !empty($categories);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function toArray() : array
    {
        $result = array();
        $categories = $this->getCategories();

        foreach ($categories as $category)
        {
            $result[] = $category->toArray();
        }

        return $result;
    }

    /**
     * @return CategoryItem[]
     */
    public function getItems() : array
    {
        return $this->items;
    }

    public function addItem(string $category, string $text, string $author, string $issue) : CategoryItem
    {
        $category = $this->getCategoryByLabel($category);

        return $this->registerItem($category, null)
            ->setText($text)
            ->setAuthor($author)
            ->setIssue($issue);
    }
}