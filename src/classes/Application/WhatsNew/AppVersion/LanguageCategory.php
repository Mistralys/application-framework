<?php
/**
 * File containing the class {@see \Application\WhatsNew\AppVersion\LanguageCategory}.
 *
 * @package Application
 * @subpackage WhatsNew
 * @see \Application\WhatsNew\AppVersion\LanguageCategory
 */

declare(strict_types=1);

namespace Application\WhatsNew\AppVersion;

use Application\WhatsNew;
use SimpleXMLElement;

/**
 * Container for entries within a single category from
 * a what's new language entry.
 *
 * Path: whatsnew.version.language.item[category]
 *
 * @package Application
 * @subpackage WhatsNew
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class LanguageCategory
{
    protected VersionLanguage $language;
    protected string $label;

    /**
     * @var CategoryItem[]
     */
    protected array $items = array();

    public function __construct(VersionLanguage $language, $label)
    {
        $this->language = $language;
        $this->label = $label;
    }

    public function addItem(SimpleXMLElement $node) : void
    {
        $this->items[] = new CategoryItem($this, $node);
    }

    /**
     * @return CategoryItem[]
     */
    public function getItems() : array
    {
        return $this->items;
    }

    public function getWhatsNew() : WhatsNew
    {
        return $this->language->getWhatsNew();
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    public function renderLabel() : string
    {
        return $this->getWhatsNew()->getParseDown()->parse($this->getLabel());
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray() : array
    {
        $result = array(
            'label' => $this->getLabel(),
            'items' => array()
        );

        foreach ($this->items as $item)
        {
            $result['items'][] = $item->toArray();
        }

        return $result;
    }
}