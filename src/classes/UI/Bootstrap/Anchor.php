<?php
/**
 * File containing the {@link UI_Bootstrap_Anchor} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_Anchor
 */

/**
 * Bootstrap anchor element.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @method UI_Bootstrap_Anchor setName($name) setName(string $name)
 * @method UI_Bootstrap_Anchor setIcon($icon) setIcon(UI_Icon $icon)
 */
class UI_Bootstrap_Anchor extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;
    
   /**
    * @var string
    */
    protected $label = '';
    
    public function setHref($href)
    {
        return $this->setAttribute('href', $href);
    }
    
    public function setClick($statement)
    {
        return $this->setAttribute('onclick', $statement);
    }
    
    public function setTarget($target)
    {
        return $this->setAttribute('target', $target);
    }

    protected function _render()
    {
        $label = $this->label;
        if (isset($this->icon)) {
            $label = $this->icon->render() . ' ' . $label;
        }

        return '<a' . compileAttributes($this->attributes) . '>' . $label . '</a>';
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
    
    public function getLabel()
    {
        return $this->label;
    }
}