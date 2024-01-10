<?php
/**
 * File containing the {@link UI_Page_StepsNavigator_Step} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_StepsNavigator_Step
 */

declare(strict_types=1);

use AppUtils\Interfaces\StringableInterface;

/**
 * Container for individual steps in the navigator.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_StepsNavigator_Step
{
    protected UI_Page_StepsNavigator $navigator;
    protected UI_Page $page;
    protected int $number;
    protected string $name;
    protected string $label;
    protected string $link;
    protected bool $enabled = false;

    /**
     * @param UI_Page_StepsNavigator $navigator
     * @param int $number
     * @param string $name
     * @param string|number|StringableInterface|NULL $label
     * @throws UI_Exception
     */
    public function __construct(UI_Page_StepsNavigator $navigator, int $number, string $name, $label)
    {
        $this->navigator = $navigator;
        $this->page = $navigator->getPage();
        $this->number = $number;
        $this->name = $name;
        $this->label = toString($label);
        
        $this->setAttribute('id', nextJSID());
    }
    
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setID(string $id) : self
    {
        return $this->setAttribute('id', $id);
    }
    
    public function getID()
    {
        return $this->getAttribute('id');
    }
    
    protected array $classes = array();
    
    public function addClass(string $class) : self
    {
        if(!$this->hasClass($class)) {
            $this->classes[] = $class;
        }
        
        return $this;
    }
    
    public function hasClass(string $class) : bool
    {
        return in_array($class, $this->classes, true);
    }
    
    public function setAttribute(string $name, $value) : self
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
    public function getAttribute(string $name)
    {
        return $this->attributes[$name] ?? null;
    }
    
   /**
    * Turns the step into a linked text.
    * @param string $url
    * @return $this
    */
    public function link(string $url) : self
    {
        $this->link = $url;
        return $this;
    }
    
    public function isEnabled() : bool
    {
        return $this->enabled;
    }
    
    public function setEnabled(bool $enabled=true) : self
    {
        $this->enabled = $enabled;
        return $this;
    }
    
    protected array $attributes = array();
    
    public function render() : string
    {
        $this->page->getUI()->addStylesheet('ui-steps-navigator.css');
        
        $classes = $this->classes;
        $classes[] = 'steps-navigator-item';
        
        if($this->name == $this->navigator->getSelectedName()) {
            $classes[] = 'active';
        } else {
            $classes[] = 'inactive';
        }

        if($this->isEnabled()) {
            $classes[] = 'enabled';
        } else {
            $classes[] = 'disabled';
        }
        
        $content = '';
        
        if($this->navigator->getOption('numbered')===true) {
            $content .= 
            '<span class="steps-navigator-number">'.
                $this->number.
            '</span>';
        }

        $content .= 
        '<span class="steps-navigator-label">';
            if(isset($this->link) && $this->isEnabled()) {
                $classes[] = 'linked';
                $content .= 
                '<a href="'.$this->link.'">'.
                    $this->label.
                '</a>';
            } else {
                $content .= $this->label;
            }
            $content .=
        '</span>';

        $attributes = $this->attributes;
        $attributes['data-name'] = $this->name;
        $attributes['class'] = null;
        if(!empty($classes)) {
            $attributes['class'] = implode(' ', $classes);
        }

        return
            '<li'.compileAttributes($attributes).'>'.
                $content.
            '</li>';
    }
}
