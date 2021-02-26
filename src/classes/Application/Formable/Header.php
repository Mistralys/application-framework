<?php
/**
 * File containing the {@link Application_Formable_Header} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see Application_Formable_Header
 */

/**
 * Utility class used as a wrapper to add a collapsible
 * header to a form, with an optional abstract. Offers
 * an easy to use API around adding both elements separately.
 * 
 * Usage: configure as applicable, then call the 
 * <code>apply()</code> method last to have the elements
 * added to the form.
 * 
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Application_Formable::addElementHeaderII()
 */
class Application_Formable_Header implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;
    
   /**
    * @var Application_Formable
    */
    protected $formable;
    
    protected $collapsed = true;

   /**
    * @var HTML_QuickForm2_Container
    */
    protected $container = null;
    
    protected $label;
    
    protected $anchor = null;
    
    protected $abstract;
    
    protected $readonlyHidden = false;
    
    protected $abstractClasses = array();
    
    public function __construct(Application_Formable $formable, $label)
    {
        $this->formable = $formable;
        $this->label = $label;
    }
    
   /**
    * Sets the container element to use (defaults to the form).
    * @param HTML_QuickForm2_Container $container
    * @return Application_Formable_Header
    */
    public function setContainer(HTML_QuickForm2_Container $container)
    {
        $this->container = $container;
        return $this;
    }
    
   /**
    * Sets the abstract text to show below the header.
    * 
    * @param string $abstract
    * @return Application_Formable_Header
    */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;
        return $this;
    }
    
   /**
    * Sets the name of the anchor that can be used to link to this header.
    * 
    * @param string $anchor
    * @return Application_Formable_Header
    */
    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
        return $this;
    }
    
   /**
    * Hides the header when the form is in readonly mode.
    * @return Application_Formable_Header
    */
    public function makeHiddenWhenReadonly()
    {
        $this->readonlyHidden = true;
        return $this;
    }
    
   /**
    * Expands the header. It is collapsed by default.
    * 
    * @return Application_Formable_Header
    */
    public function expand()
    {
        $this->collapsed = false;
        return $this;
    }
    
    public function collapse()
    {
        $this->collapsed = true;
        return $this;
    }
    
    public function setExpanded($expanded=true)
    {
        if($expanded) {
            $this->expand();
        } else {
            $this->collapse();
        }
        
        return $this;
    }
    
    protected $applied = false;
    
   /**
    * Applies the configuration by adding the 
    * elements to the form. This must be called last,
    * and if it is not called, the header will not
    * be added to the form.
    * 
    * @return Application_Formable_Header
    */
    public function apply()
    {
        if($this->applied) {
            return $this;
        }
        
        $this->applied = true;
        
        $label = $this->label;
        if($this->hasIcon()) {
            $label = $this->icon.' '.$label;
        }
        
        $header = $this->formable->addElementHeader(
            $label, 
            $this->container, 
            $this->anchor, 
            $this->collapsed
        );
        
        if($this->readonlyHidden) {
            $this->formable->makeHiddenWhenReadonly($header);
        }
        
        if(!empty($this->abstract)) {
            $this->formable->addElementAbstract($this->abstract, $this->abstractClasses);
        }
        
        return $this;
    }
}