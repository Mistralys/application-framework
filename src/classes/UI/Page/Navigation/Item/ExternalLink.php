<?php

declare(strict_types=1);

class UI_Page_Navigation_Item_ExternalLink extends UI_Page_Navigation_Item
{
    protected string $url;
    protected string $target = '';

    /**
     * @param UI_Page_Navigation $nav
     * @param string $id
     * @param string $url
     * @param string|number|UI_Renderable_Interface $title
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function __construct(UI_Page_Navigation $nav, string $id, string $url, $title)
    {
        parent::__construct($nav, $id);
        $this->url = $url;
        $this->title = toString($title);
    }
    
    public function getType() : string
    {
        return 'externallink';
    }
    
    public function getURL() : string
    {
        return $this->url;
    }

    public function setTarget(string $target) : self
    {
        $this->target = $target;
        return $this;
    }

    public function makeNewTab() : self
    {
        return $this->setTarget('_blank');
    }

    public function render(array $attributes = array()) : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $attributes = array_merge(
            $attributes,
            array(
                'href' => $this->getURL(),
                'class' => implode(' ', $this->classes),
                'target' => $this->target
            )
        );

        $label = $this->getTitle();
        if (isset($this->icon)) {
            $label = $this->icon->render() . ' ' . $label;
        }

        return '<a' . compileAttributes($attributes) . '>' . $label . '</a>';
    }
}
