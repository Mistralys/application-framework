<?php

class UI_Page_Navigation_Item_ExternalLink extends UI_Page_Navigation_Item
{
   /**
    * @var string
    */
    protected $url;
    
   /**
    * @var string
    */
    protected $title;
    
    public function __construct(UI_Page_Navigation $nav, $id, $url, $title)
    {
        parent::__construct($nav, $id);
        $this->url = $url;
        $this->title = $title;
    }
    
    public function getType()
    {
        return 'externallink';
    }
    
    public function getURL()
    {
        return $this->url;
    }

    public function render($attributes = array())
    {
        if(!$this->isValid())
        {
            return '';
        }

        $attributes = array(
            'href' => $this->getURL(),
            'class' => implode(' ', $this->classes)
        );

        $label = $this->getTitle();
        if (isset($this->icon)) {
            $label = $this->icon->render() . ' ' . $label;
        }

        return '<a' . compileAttributes($attributes) . '>' . $label . '</a>';
    }
}