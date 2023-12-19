<?php
/**
 * File containing the {@link Application_Formable_Header} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see Application_Formable_Header
 */

use AppUtils\HTMLTag;
use AppUtils\Interfaces\StringableInterface;

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
class Application_Formable_Header
    implements
    Application_Interfaces_Iconizable,
    UI_Interfaces_StatusElementContainer
{
    use Application_Traits_Iconizable;
    use UI_Traits_StatusElementContainer;

    public const PROPERTY_HEADER_INSTANCE = 'header-instance';

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
    protected array $abstractClasses = array();

    /**
     * @param Application_Formable $formable
     * @param string|number|StringableInterface|NULL $label
     * @throws UI_Exception
     */
    public function __construct(Application_Formable $formable, $label)
    {
        $this->formable = $formable;

        $this->setLabel($label);
    }

    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getAbstract() : string
    {
        return $this->abstract;
    }

    /**
     * @param string|number|StringableInterface|NULL $label
     * @return $this
     * @throws UI_Exception
     */
    public function setLabel($label) : Application_Formable_Header
    {
        $this->label = toString($label);
        return $this;
    }
    
   /**
    * Sets the container element to use (defaults to the form).
    * @param HTML_QuickForm2_Container $container
    * @return $this
    */
    public function setContainer(HTML_QuickForm2_Container $container) : Application_Formable_Header
    {
        $this->container = $container;
        return $this;
    }

    /**
     * Sets the abstract text to show below the header.
     *
     * @param string|number|UI_Renderable_Interface|NULL $abstract
     * @return $this
     * @throws UI_Exception
     */
    public function setAbstract($abstract) : Application_Formable_Header
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
    public function setAnchor(string $anchor) : Application_Formable_Header
    {
        $this->anchor = $anchor;
        return $this;
    }
    
   /**
    * Hides the header when the form is in readonly mode.
    *
    * @param bool $hidden
    * @return $this
    */
    public function makeHiddenWhenReadonly(bool $hidden=true) : Application_Formable_Header
    {
        $this->readonlyHidden = $hidden;
        return $this;
    }
    
   /**
    * Expands the header. It is collapsed by default.
    * 
    * @return $this
    */
    public function expand() : Application_Formable_Header
    {
        $this->collapsed = false;
        return $this;
    }

    /**
     * @return $this
     */
    public function collapse() : Application_Formable_Header
    {
        $this->collapsed = true;
        return $this;
    }

    /**
     * @param bool $expanded
     * @return $this
     */
    public function setExpanded(bool $expanded=true) : Application_Formable_Header
    {
        if($expanded) {
            $this->expand();
        } else {
            $this->collapse();
        }
        
        return $this;
    }

    public function isApplied() : bool
    {
        return $this->applied;
    }

    /**
     * @var bool
     */
    protected $applied = false;
    
   /**
    * Applies the configuration by adding the 
    * elements to the form. This must be called last,
    * and if it is not called, the header will not
    * be added to the form.
    * 
    * @return $this
    */
    public function apply() : Application_Formable_Header
    {
        if($this->applied) {
            return $this;
        }
        
        $this->applied = true;
        
        $header = $this->formable->addElementHeader(
            $this->renderLabel(),
            $this->container, 
            $this->anchor, 
            $this->collapsed
        );

        $header->setRuntimeProperty(self::PROPERTY_HEADER_INSTANCE, $this);
        
        if($this->readonlyHidden) {
            $this->formable->makeHiddenWhenReadonly($header);
        }
        
        if(!empty($this->abstract)) {
            $this->formable->addElementAbstract($this->abstract, $this->abstractClasses);
        }
        
        return $this;
    }

    private function renderLabel() : string
    {
        return (string)sb()
            ->add($this->icon)
            ->add($this->label);
    }

    public function configureSection(UI_Page_Section $section) : void
    {
        if(empty($this->statusElements))
        {
            return;
        }

        foreach($this->statusElements as $element)
        {
            $section->addStatusElement($element);
        }
    }
}
