<?php

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;
use UI\AdminURLs\AdminURL;

class Application_LookupItems_Result
{
    private Application_LookupItems_Item $item;
    private string $label;
    private string $url;

    /**
     * @param Application_LookupItems_Item $item
     * @param string|number|StringableInterface $label
     * @param string|AdminURL $url
     * @throws UI_Exception
     */
    public function __construct(Application_LookupItems_Item $item, $label, $url)
    {
        $this->item = $item;
        $this->label = toString($label);
        $this->url = (string)$url;
    }

    public function getItem(): Application_LookupItems_Item
    {
        return $this->item;
    }
    
    public function toArray() : array
    {
        return array(
            'label' => $this->label,
            'url' => $this->url
        );
    }
}
