<?php

declare(strict_types=1);

class Application_LookupItems_Result
{
   /**
    * @var Application_LookupItems_Item
    */
    private $item;
    
   /**
    * @var string
    */
    private $label;
    
   /**
    * @var string
    */
    private $url;
    
    public function __construct(Application_LookupItems_Item $item, string $label, string $url)
    {
        $this->item = $item;
        $this->label = $label;
        $this->url = $url;
    }
    
    public function toArray() : array
    {
        return array(
            'label' => $this->label,
            'url' => $this->url
        );
    }
}
