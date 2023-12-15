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

    public function __construct(VersionLanguage $language, $label)
    {
        $this->language = $language;
        $this->label = $label;
    }

    public function getLanguage(): VersionLanguage
    {
        return $this->language;
    }

    /**
     * @return CategoryItem[]
     */
    public function getItems() : array
    {
        $items = $this->language->getItems();
        $result = array();

        foreach($items as $item)
        {
            if($item->getCategory() === $this)
            {
                $result[] = $item;
            }
        }

        return $result;
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
        return $this->getWhatsNew()->getMarkdownRenderer()->render($this->getLabel());
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

        $items = $this->getItems();

        foreach ($items as $item)
        {
            $result['items'][] = $item->toArray();
        }

        return $result;
    }
}
