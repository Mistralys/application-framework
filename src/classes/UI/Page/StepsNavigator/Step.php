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
use UI\AdminURLs\AdminURL;

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
     * @var string[]
     */
    protected array $classes = array();

    /**
     * @var array<string,mixed>
     */
    protected array $attributes = array();

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
    
    public function getID() : string
    {
        return (string)$this->getAttribute('id');
    }
    
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

    /**
     * @param string $name
     * @param string|int|float|NULL $value
     * @return $this
     */
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
    * @param string|AdminURL $url
    * @return $this
    */
    public function link($url) : self
    {
        $this->link = (string)$url;
        return $this;
    }
    
    public function isEnabled() : bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled=true) : self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Enables the step, so it becomes clickable in the UI.
     * @return $this
     */
    public function makeEnabled() : self
    {
        return $this->setEnabled();
    }

    /**
     * Enables the step and marks it as the active one.
     *
     * @return $this
     * @throws UI_Exception
     */
    public function makeActive() : self
    {
        $this->makeEnabled();
        $this->navigator->selectStep($this->getName());
        return $this;
    }
    
    public function render() : string
    {
        $this->page->getUI()->addStylesheet('ui-steps-navigator.css');
        
        $classes = $this->classes;
        $classes[] = 'steps-navigator-item';
        
        if($this->navigator->isStepSelected($this)) {
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
        $attributes['class'] = implode(' ', $classes);

        return
            '<li'.compileAttributes($attributes).'>'.
                $content.
            '</li>';
    }
}
