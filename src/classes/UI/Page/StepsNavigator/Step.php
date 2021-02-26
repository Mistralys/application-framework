<?php
/**
 * File containing the {@link UI_Page_StepsNavigator_Step} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Page_StepsNavigator_Step
 */

/**
 * Container for individual steps in the navigator.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Page_StepsNavigator_Step
{
   /**
    * @var UI_Page_StepsNavigator
    */
    protected $navigator;
    
   /**
    * @var UI_Page
    */
    protected $page;
    
    protected $number;
    
    protected $name;
    
    protected $label;
    
    public function __construct(UI_Page_StepsNavigator $navigator, $number, $name, $label)
    {
        $this->navigator = $navigator;
        $this->page = $navigator->getPage();
        $this->number = $number;
        $this->name = $name;
        $this->label = $label;
        
        $this->setAttribute('id', nextJSID());
    }
    
    protected $link;
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setID($id)
    {
        return $this->setAttribute('id', $id);
    }
    
    public function getID()
    {
        return $this->getAttribute('id');
    }
    
    protected $classes = array();
    
    public function addClass($class)
    {
        if(!$this->hasClass($class)) {
            $this->classes[] = $class;
        }
        
        return $this;
    }
    
    public function hasClass($class)
    {
        return in_array($class, $this->classes);
    }
    
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
    public function getAttribute($name)
    {
        if(isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        
        return null;
    }
    
   /**
    * Turns the step into a linked text.
    * @param string $url
    * @return UI_Page_StepsNavigator_Step
    */
    public function link($url)
    {
        $this->link = $url;
        return $this;
    }
    
    protected $enabled = false;
    
    public function isEnabled()
    {
        return $this->enabled;
    }
    
    public function setEnabled($enabled=true)
    {
        $this->enabled = $enabled;
        return $this;
    }
    
    protected $attributes = array();
    
    public function render()
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

        $atts = $this->attributes;
        $atts['data-name'] = $this->name;
        $atts['class'] = null;
        if(!empty($classes)) {
            $atts['class'] = implode(' ', $classes);
        }
        
        $html = 
        '<li'.compileAttributes($atts).'>'.
            $content.
        '</li>';
        
        return $html;
    }
}