<?php

class UI_ItemsSelector extends UI_Renderable
{
   /**
    * @var array
    */
    protected $items = array();

   /**
    * @var string
    */
    protected $id;
    
    protected function initRenderable() : void
    {
        $this->id = nextJSID();
    }
    
   /**
    * Sets the ID of the main HTML wrapping element of the selector.
    * @param string $id
    * @return UI_ItemsSelector
    */
    public function setID($id)
    {
        $this->id = $id;
        return $this;
    }
    
    public function getID()
    {
        return $this->id;
    }
    
   /**
    * Adds a new item to link to.
    * 
    * @param string $label
    * @param string $url
    * @param string $description Optional description text to show as help.
    * @return UI_ItemsSelector
    */
    public function addItem($label, $url, $description=null)
    {
        $this->items[] = array(
            'label' => $label,
            'url' => $url,
            'description' => $description
        );
        
        return $this;
    }
    
    protected function _render()
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
