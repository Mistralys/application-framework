<?php
/**
 * File containing the {@link UI_Bootstrap_Anchor} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_Anchor
 */

use UI\AdminURLs\AdminURLInterface;

/**
 * Bootstrap anchor element.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_Anchor extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;
    
    protected string $label = '';

    /**
     * @param string|AdminURLInterface $href
     * @return UI_Bootstrap|UI_Bootstrap_Anchor
     */
    public function setHref($href)
    {
        return $this->setAttribute('href', (string)$href);
    }
    
    public function setClick($statement)
    {
        return $this->setAttribute('onclick', $statement);
    }
    
    public function setTarget($target)
    {
        return $this->setAttribute('target', $target);
    }

    protected function _render() : string
    {
        $label = $this->label;
        if (isset($this->icon)) {
            $label = $this->icon->render() . ' ' . $label;
        }

        return '<a' . compileAttributes($this->attributes) . '>' . $label . '</a>';
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @return $this
     * @throws UI_Exception
     */
    public function setLabel($label) : self
    {
        $this->label = toString($label);
        return $this;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
}