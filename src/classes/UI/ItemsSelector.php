<?php

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;
use UI\AdminURLs\AdminURL;

class UI_ItemsSelector extends UI_Renderable
{
   /**
    * @var array
    */
    protected array $items = array();
    protected string $id;
    
    protected function initRenderable() : void
    {
        $this->id = nextJSID();
    }
    
   /**
    * Sets the ID of the selector's HTML wrapping element.
    * @param string $id
    * @return $this
    */
    public function setID(string $id) : self
    {
        $this->id = $id;
        return $this;
    }
    
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Adds a new item to link to.
     *
     * @param string|int|float|StringableInterface|NULL $label
     * @param string|AdminURL $url
     * @param string|int|float|StringableInterface|NULL $description Optional description text to show as help.
     * @return $this
     * @throws UI_Exception
     */
    public function addItem($label, $url, $description=null) : self
    {
        $this->items[] = array(
            'label' => toString($label),
            'url' => (string)$url,
            'description' => toString($description)
        );
        
        return $this;
    }
    
    protected function _render() : string
    {
        $this->ui->addStylesheet('ui-items-selector.css');
        
        $html =
        '<ul class="unstyled items-selector" id="'.$this->id.'">';
            foreach($this->items as $item) {
                $html .=
                '<li class="items-selector-entry" onclick="application.redirect(\''.$item['url'].'\')">'.
                    '<a href="'.$item['url'].'" class="items-selector-link">'.
                        $item['label'].
                    '</a>';
                    if(!empty($item['description'])) {
                        $html .= '<p class="items-selector-description">'.$item['description'].'</p>';
                    }
                    $html .=
                '</li>';
            }
            $html .=
        '</ul>';
            
        return $html;
    }
}
