<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;
use AppUtils\Interfaces\StringableInterface;
use UI\Bootstrap\ButtonGroup\ButtonGroupItemInterface;
use UI\Traits\ActivatableTrait;
use UI\Traits\ButtonSizeTrait;

class UI_Bootstrap_ButtonDropdown extends UI_Bootstrap_BaseDropdown
    implements
    ButtonGroupItemInterface
{
    use ButtonSizeTrait;
    use ActivatableTrait;

    /**
     * Makes the button a link.
     *
     * @return $this
     */
    public function makeLink() : self
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
    public function openLeft() : self
    {
        $this->menu->openLeft();
        return $this;
    }

    protected array $linkClasses = array(
        'dropdown-toggle'
    );

    /**
     * @param string $class
     * @return $this
     */
    public function addLinkClass(string $class) : self
    {
        if(!in_array($class, $this->linkClasses, true)) {
            $this->linkClasses[] = $class;
        }

        return $this;
    }

    /**
     * @var array<string,string>
     */
    protected array $linkAttributes = array();

    /**
     * @param string $name
     * @param string|number|StringableInterface|NULL $value
     * @return $this
     */
    public function setLinkAttribute(string $name, $value) : self
    {
        $this->linkAttributes[$name] = toString($value);
        return $this;
    }

    protected function _render() : string
    {
        $sizeClass = $this->getSizeClass();
        if(!empty($sizeClass)) {
            $this->addLinkClass($sizeClass);
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

        if($this->ajax !== null) {
            $this->setLinkAttribute('id', $this->getID().'-toggle');
        }

        $this->setLinkAttribute('href', '#');
        $this->setLinkAttribute('class', implode(' ', $this->linkClasses));
        
        $html = 
        '<'.$tagName.$this->renderAttributes().'>'.
            '<a'.ConvertHelper::array2attributeString($this->linkAttributes).'>' .
                $this->icon . ' ' .
                $this->label;
                if($this->caret) {
                    $html .= ' '.$this->renderCaret();
                }
                $html .= 
            '</a>' .
            $this->renderContent() .
        '</'.$tagName.'>';

        return $html;
    }
}
