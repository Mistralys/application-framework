<?php
/**
 * File containing the {@link UI_Badge} class.
 * 
 * @package Application
 * @subpackage UI
 * @see UI_Badge
 */

use UI\CriticalityEnum;

/**
 * UI helper class for creating colored badges.
 * 
 * @package Application
 * @subpackage UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Badge extends UI_HTMLElement implements Application_Interfaces_Iconizable, UI_Interfaces_Badge
{
    use Application_Traits_Iconizable;
    
    public const ERROR_WRAPPER_PLACEHOLDER_MISSING = 430002;
    
    public const WRAPPER_PLACEHOLDER = '{badge}';

    public const TYPE_DEFAULT = 'default';

    protected string $label;
	protected string $classType;
	protected string $wrapper = '';
    protected string $layout = self::TYPE_DEFAULT;

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @throws UI_Exception
     */
    public function __construct($label)
    {
        parent::__construct();
        
        $this->label = toString($label);
        $this->classType = 'badge';
    }
    
   /**
    * Sets HTML code that will wrap around the badge. 
    * 
    * A placeholder must be inserted in the code to
    * specify where the badge will be injected.
    * 
    * @param string|number|UI_Renderable_Interface|NULL $code
    * @throws Application_Exception
    * @return $this
    * 
    * @see UI_Badge::WRAPPER_PLACEHOLDER
    * @see UI_Badge::ERROR_WRAPPER_PLACEHOLDER_MISSING
    */
    public function setWrapper($code) : self
    {
        $code = toString($code);

        if(strpos($code, self::WRAPPER_PLACEHOLDER) === false)
        {
            throw new Application_Exception(
                'Badge placeholder missing in the wrapper',
                'The code must contain the badge placeholder.',
                self::ERROR_WRAPPER_PLACEHOLDER_MISSING
            );
        }
        
        $this->wrapper = $code;
        
        return $this;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }
    
    protected function _render() : string
    {
        $this->addClass($this->classType);
        $this->addClass($this->classType . '-' . $this->layout);
        
        $label = $this->label;
        if(isset($this->icon)) {
            $label = $this->icon.' '.$this->label;
        }
        
        $html = sprintf(
            '<span %s>%s</span>',
            $this->renderAttributes(),
            $label
        );
        
        if(!empty($this->wrapper)) 
        {
            $html = str_replace(
                self::WRAPPER_PLACEHOLDER,
                $html,
                $this->wrapper,    
            );
        }
        
        return $html;
    }

    /**
     * Sets the badge's label, overwriting the existing label.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @return $this
     * @throws UI_Exception
     */
    public function setLabel($label) : self
    {
        $this->label = toString($label);
        return $this;
    }
    
   /**
    * Styles the button as a button for a dangerous operation, like deleting records.
    * 
    * @returns $this
    */
	public function makeDangerous() : self
	{
		return $this->makeType(CriticalityEnum::DANGEROUS);
	}
	
   /**
    * Styles the button as an informational button.
    * 
    * @returns $this
    */
	public function makeInfo() : self
	{
		return $this->makeType(CriticalityEnum::INFO);
	}
	
   /**
    * Styles the button as a success button.
    * 
    * @returns $this
    */
	public function makeSuccess() : self
	{
		return $this->makeType(CriticalityEnum::SUCCESS);
	}
	
   /**
    * Styles the button as a warning button for potentially dangerous operations.
    * 
    * @returns $this
    */
	public function makeWarning() : self
	{
		return $this->makeType(CriticalityEnum::WARNING);
	}
	
   /**
    * Styles the button as an inverted button.
    * 
    * @returns $this
    */
	public function makeInverse() : self
	{
		return $this->makeType(CriticalityEnum::INVERSE);
	}

    /**
     * Styles the label as inactive.
     *
     * @return $this
     * @throws Application_Exception
     */
	public function makeInactive() : self
	{
	    return $this->makeType(CriticalityEnum::INACTIVE);
	}

    /**
     * @param string $type
     * @return $this
     * @throws Application_Exception
     */
	public function makeType(string $type) : self
	{
	    CriticalityEnum::requireValidValue($type);
	    
		$this->layout = $type;
		return $this;
	}
	
   /**
    * Sets the cursor of the element to the "help" cursor.
    * @return $this
    */
	public function cursorHelp() : self
	{
	    return $this->addStyle('cursor', 'help');
	}
	
   /**
    * Makes the whole badge larger.
    * 
    * @return $this
    */
	public function makeLarge() : self
	{
	    return $this->addClass('badge-large');
	}

    /**
     * Makes the whole badge small.
     *
     * @return $this
     */
    public function makeSmall() : self
    {
        return $this->addClass('badge-small');
    }
}
