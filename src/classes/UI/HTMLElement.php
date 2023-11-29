<?php
/**
 * File containing the {@link UI_HTMLElement} class.
 * 
 * @package Application
 * @subpackage UI
 * @see UI_HTMLElement
 */

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Traits\ClassableTrait;

/**
 * Base class for dynamically generated HTML UI elements. Offers
 * a basic API for modifying element attributes and common 
 * characteristics.
 * 
 * @package Application
 * @subpackage UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_HTMLElement extends UI_Renderable
    implements
    ClassableInterface
{
    use ClassableTrait;
    
    protected array $attributes = array();
    
    protected array $styles = array();
    
    protected bool $tooltip = false;
    
    protected function initRenderable() : void
    {
        $this->setAttribute('id', nextJSID());
    }

   /**
    * Sets an attribute of the element: adds it if it does
    * not exist yet, and overwrites it otherwise.
    * 
    * @param string $name
    * @param string $value
    * @return $this
    */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
   /**
    * Retrieves the value of an attribute.
    * 
    * @param string $name
    * @param string $default The default value to return if it does not exist.
    * @return string|null
    */
    public function getAttribute($name, $default=null)
    {
        if(isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
    
        return $default;
    }
    
   /**
    * Adds a style part for the <code>style</code> attribute of the element.
    * 
    * Example:
    * 
    * <pre>
    * addStyle('display', 'none');
    * </pre>
    * 
    * @param string $name
    * @param string $value
    * @return $this
    */
    public function addStyle(string $name, string $value) : self
    {
        $this->styles[$name] = $value;
        return $this;
    }
    
   /**
    * Sets the element's ID for the <code>id</code> attribute.
    * @param string $id
    * @return $this
    */
    public function setID($id)
    {
        return $this->setAttribute('id', $id);
    }

   /**
    * Renders all attributes and returns them as a string, ready
    * to be inserted into a tag.
    * 
    * @return string
    */
    protected function renderAttributes()
    {
        $atts = $this->compileAttributes();
        return AppUtils\ConvertHelper::array2attributeString($atts);
    }
    
    protected function compileAttributes()
    {
        $atts = $this->attributes;
        $classes = $this->classes;
        
        if($this->tooltip) {
            $classes[] = 'help';
            JSHelper::tooltipify($this->getAttribute('id'));
        }
        
        if(!empty($classes)) {
            $atts['class'] = implode(' ', $classes);
        }
        
        if(!empty($this->styles)) {
            $atts['style'] = compileStyles($this->styles);
        } 
        
        return $atts;
    }
    
   /**
    * Sets the <code>title</code> attribute of the element.
    * 
    * @param string $title
    * @return $this
    */
    public function setTitle($title)
    {
        return $this->setAttribute('title', $title);
    }
    
   /**
    * Sets a tooltip for the element, which will be shown
    * as a tooltip popup when the user hovers over it.
    * 
    * @param string|number|UI_Renderable_Interface $tooltip
    * @return $this
    */
    public function setTooltip($tooltip)
    {
        $this->tooltip = true;
        return $this->setAttribute('title', toString($tooltip));
    }
    
    public function getTooltip() : string
    {
        return (string)$this->getAttribute('title');
    }
}
