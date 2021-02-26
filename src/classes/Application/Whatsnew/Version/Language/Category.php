<?php

require_once 'Application/Whatsnew/Version/Language/Category/Item.php';

class Application_Whatsnew_Version_Language_Category
{
   /**
    * @var Application_Whatsnew_Version_Language
    */
    protected $language;
    
   /**
    * @var string
    */
    protected $label;
    
    public function __construct(Application_Whatsnew_Version_Language $language, $label)
    {
        $this->language = $language;
        $this->label = $label;
    }
    
   /**
    * @var Application_Whatsnew_Version_Language_Category_Item[]
    */
    protected $items = array();

    public function addItem(SimpleXMLElement $node)
    {
        $this->items[] = new Application_Whatsnew_Version_Language_Category_Item($this, $node);
    }
    
   /**
    * @return Application_Whatsnew_Version_Language_Category_Item[]
    */
    public function getItems() : array
    {
        return $this->items;
    }

    public function getWhatsnew() : Application_Whatsnew
    {
        return $this->language->getWhatsnew();
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
    
    public function renderLabel() : string
    {
        return $this->getWhatsnew()->getParsedown()->parse($this->getLabel());
    }
    
    public function toArray()
    {
        $result = array(
            'label' => $this->getLabel(),
            'items' => array()
        );
        
        foreach($this->items as $item) {
            $result['items'][] = $item->toArray();
        }
        
        return $result;
    }
}