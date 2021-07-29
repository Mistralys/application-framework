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

    /**
     * @var bool
     */
    protected $collapsed = true;

   /**
    * @var HTML_QuickForm2_Container|NULL
    */
    protected $container = null;

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string|NULL
     */
    protected $anchor = null;

    /**
     * @var string
     */
    protected $abstract = '';

    /**
     * @var bool
     */
    protected $readonlyHidden = false;

    /**
     * @var string[]
     */
    protected $abstractClasses = array();

    /**
     * @param Application_Formable $formable
     * @param string|UI_Renderable_Interface $label
     * @throws UI_Exception
     */
    public function __construct(Application_Formable $formable, $label)
    {
        $this->formable = $formable;
        $this->label = toString($label);
    }
    
   /**
    * Sets the container element to use (defaults to the form).
    * @param HTML_QuickForm2_Container $container
    * @return $this
    */
    public function setContainer(HTML_QuickForm2_Container $container)
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Sets the abstract text to show below the header.
     *
     * @param string|UI_Renderable_Interface $abstract
     * @return $this
     * @throws UI_Exception
     */
    public function setAbstract($abstract)
    {
        $this->abstract = toString($abstract);
        return $this;
    }
    
   /**
    * Sets the name of the anchor that can be used to link to this header.
    * 
    * @param string $anchor
    * @return $this
    */
    public function setAnchor(string $anchor)
    {
        $this->anchor = $anchor;
        return $this;
    }
    
   /**
    * Hides the header when the form is in readonly mode.
    * @return $this
    */
    public function makeHiddenWhenReadonly()
    {
        $this->readonlyHidden = true;
        return $this;
    }
    
   /**
    * Expands the header. It is collapsed by default.
    * 
    * @return $this
    */
    public function expand()
    {
        $this->collapsed = false;
        return $this;
    }

    /**
     * @return $this
     */
    public function collapse()
    {
        $this->collapsed = true;
        return $this;
    }

    /**
     * @param bool $expanded
     * @return $this
     */
    public function setExpanded(bool $expanded=true)
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
    * @return $this
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
