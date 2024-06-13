<?php
/**
 * @package Application
 * @subpackage Lookup Items
 */

declare(strict_types=1);

use Application\LookupItems\BaseLookupItem;
use AppUtils\Interfaces\StringableInterface;
use UI\AdminURLs\AdminURLInterface;

/**
 * Represents a result of a lookup item search.
 *
 * @package Application
 * @subpackage Lookup Items
 */
class Application_LookupItems_Result
{
    private BaseLookupItem $item;
    private string $label;
    private string $url;

    /**
     * @param BaseLookupItem $item
     * @param string|number|StringableInterface $label
     * @param string|AdminURLInterface $url
     * @throws UI_Exception
     */
    public function __construct(BaseLookupItem $item, $label, $url)
    {
        $this->item = $item;
        $this->label = toString($label);
        $this->url = (string)$url;
    }

    public function getItem(): BaseLookupItem
    {
        return $this->item;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getURL(): string
    {
        return $this->url;
    }
    
    public function toArray() : array
    {
        return array(
            'label' => $this->label,
            'url' => $this->url
        );
    }
}
