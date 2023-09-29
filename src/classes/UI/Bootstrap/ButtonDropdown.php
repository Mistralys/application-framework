<?php

class UI_Bootstrap_ButtonDropdown extends UI_Bootstrap_BaseDropdown
{
    protected $size;

    protected function init(): void
    {
        parent::init();

        $this->setID(nextJSID());
    }

    public function makeMini()
    {
        $this->size = 'btn-mini';
        return $this;
    }
    
    public function makeSmall()
    {
        $this->size = 'btn-small';
        return $this;
    }
    
    public function makeLink()
    {
        $this->isLink = true;
        return $this;
    }

    /**
     * Makes the menu open on the left side of the toggle,
     * instead of the default right side.
     *
     * @return $this
     */
    public function openLeft()
    {
        $this->menu->openLeft();
        return $this;
    }

    protected $linkClasses = array(
        'dropdown-toggle'
    );
    
    public function addLinkClass($class)
    {
        if(!in_array($class, $this->linkClasses)) {
            $this->linkClasses[] = $class;
        }
    }

    protected $linkAttributes = array();
    
    public function setLinkAttribute($name, $value)
    {
        $this->linkAttributes[$name] = $value;
    }
    
    protected function _render()
    {
        if(isset($this->size)) {
            $this->addLinkClass($this->size);
        }
        
        if($this->inNavigation)
        {
            $tagName = 'li';
            $this->addClass('dropdown');
        } 
        else 
        {
            $tagName = 'div';            
            $this->addClass('btn-group');
            $this->addLinkClass('btn');
            $this->addLinkClass('btn-'.$this->layout);
            if($this->isLink) {
                $this->addLinkClass('btn-link');
            }
        }

        if(isset($this->tooltipInfo))
        {
            $this->tooltipInfo->attachToID($this->getID())->injectJS();
            $this->setAttribute('title', $this->tooltipInfo->getContent());
        }
    
        $this->setLinkAttribute('data-toggle', 'dropdown');
        $this->setLinkAttribute('href', '#');
        $this->setLinkAttribute('class', implode(' ', $this->linkClasses));
        
        $html = 
        '<'.$tagName.$this->renderAttributes().'>'.
            '<a'.AppUtils\ConvertHelper::array2attributeString($this->linkAttributes).'>' .
                $this->icon . ' ' .
                $this->label;
                if($this->caret) {
                    $html .= ' '.$this->renderCaret();
                }
                $html .= 
            '</a>' .
            $this->menu->render().
        '</'.$tagName.'>';

        return $html;
    }
}
