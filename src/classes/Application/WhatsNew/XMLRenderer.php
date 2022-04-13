<?php
/**
 * File containing the class {@see \Application\WhatsNew\XMLRenderer}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\XMLRenderer
 */

declare(strict_types=1);

namespace Application\WhatsNew;

use Application\WhatsNew;
use Application\WhatsNew\AppVersion\CategoryItem;
use Application\WhatsNew\AppVersion\LanguageCategory;
use Application\WhatsNew\AppVersion\VersionLanguage;
use AppUtils\ConvertHelper;
use AppUtils\XMLHelper;

/**
 * Renders a what's new versions list to the native XML format.
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class XMLRenderer
{
    private int $textWrapping = 65;
    private WhatsNew $whatsNew;

    public function __construct(WhatsNew $whatsNew)
    {
        $this->whatsNew = $whatsNew;
    }

    public function render() : string
    {
        $xml = <<<'EOT'
<?xml version="1.0" encoding="UTF-8"?>
<whatsnew>
%1$s
</whatsnew>
EOT;

        return sprintf($xml, $this->renderVersions());
    }

    private function renderVersions() : string
    {
        $result = array();
        $versions = $this->whatsNew->getVersions();

        foreach($versions as $version)
        {
            $result[] = $this->renderVersion($version);
        }

        return implode(PHP_EOL, $result);
    }

    private function renderVersion(AppVersion $version) : string
    {
        $xml = <<<'EOT'
    <version id="%1$s">
%2$s
    </version>
EOT;

        return sprintf(
            $xml,
            $version->getNumber(),
            $this->renderLanguages($version)
        );
    }

    private function renderLanguages(AppVersion $version) : string
    {
        $languages = $version->getLanguages();

        $result = array();

        foreach($languages as $language)
        {
            $result[] = $this->renderLanguage($language);
        }

        return implode(PHP_EOL, $result);
    }

    private function renderLanguage(VersionLanguage $language) : string
    {
        if(!$language->hasCategories())
        {
            return '';
        }

        $xml = <<<'EOT'
        <%1$s>
%2$s
        </%1$s>
EOT;

        return sprintf(
            $xml,
            strtolower($language->getID()),
            $this->renderCategories($language)
        );
    }

    private function renderCategories(VersionLanguage $language) : string
    {
        $categories = $language->getCategories();
        $result = array();

        foreach($categories as $category)
        {
            $result[] = $this->renderCategory($category);
        }

        return implode(PHP_EOL, $result);
    }

    private function renderCategory(LanguageCategory $category) : string
    {
        $items = $category->getItems();
        $result = array();

        foreach($items as $item)
        {
            $result[] = $this->renderItem($item, $category);
        }

        return implode(PHP_EOL, $result);
    }

    private function renderItem(CategoryItem $item, LanguageCategory $category) : string
    {
        $attributes = array(
            'category' => $category->getLabel(),
        );

        if($item->hasAuthor())
        {
            $attributes['author'] = $item->getAuthor();
        }

        if($item->hasIssue())
        {
            $attributes['issue'] = $item->getIssue();
        }

        $xml = <<<'EOT'
            <item %1$s>
%2$s
            </item>
EOT;

        return sprintf(
            $xml,
            trim(compileAttributes($attributes)),
            XMLHelper::string2xml($this->indentText($item->getRawText()))
        );
    }

    private function unindentText(string $text) : string
    {
        return implode(PHP_EOL, ConvertHelper::explodeTrim("\n", $text));
    }

    private function indentText(string $text) : string
    {
        $wrapped = ConvertHelper::wordwrap($this->unindentText($text), $this->textWrapping);

        $lines = ConvertHelper::explodeTrim("\n", $wrapped);
        $result = array();
        foreach($lines as $line)
        {
            $result[] = str_repeat('    ', 4).$line;
        }

        return implode(PHP_EOL, $result);
    }
}
