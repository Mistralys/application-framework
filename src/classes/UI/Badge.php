<?php
/**
 * File containing the {@link UI_Badge} class.
 * 
 * @package Application
 * @subpackage UI
 * @see UI_Badge
 */

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
    
    const ERROR_INVALID_BADGE_TYPE = 430001;
    const ERROR_WRAPPER_PLACEHOLDER_MISSING = 430002;
    
    const WRAPPER_PLACEHOLDER = '{badge}';
    
    protected $label;
    
	protected $layout = 'default';
	
   /**
    * @var string
    */
	protected $classType;
	
   /**
    * @var string
    */
	protected $wrapper = '';
	
    public function __construct($label)
    {
        parent::__construct();
        
        $this->label = $label;
        $this->classType = 'badge';
    }
    
   /**
    * Sets HTML code that will wrap around the badge. 
    * 
    * A placeholder must be inserted in the code to
    * specify where the badge will be injected.
    * 
    * @param string $code
    * @throws Application_Exception
    * @return $this
    * 
    * @see UI_Badge::WRAPPER_PLACEHOLDER
    * @see UI_Badge::ERROR_WRAPPER_PLACEHOLDER_MISSING
    */
    public function setWrapper(string $code)
    {
        if(!strstr($code, self::WRAPPER_PLACEHOLDER)) 
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
    
    protected function _render()
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
    * @param string $label
    * @return $this
    */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
    
   /**
    * Styles the button as a button for a dangerous operation, like deleting records.
    * 
    * @returns $this
    */
	public function makeDangerous()
	{
		return $this->makeType(UI_CriticalityEnum::DANGEROUS);
	}
	
   /**
    * Styles the button as an informational button.
    * 
    * @returns $this
    */
	public function makeInfo()
	{
		return $this->makeType(UI_CriticalityEnum::INFO);
	}
	
   /**
    * Styles the button as a success button.
    * 
    * @returns $this
    */
	public function makeSuccess()
	{
		return $this->makeType(UI_CriticalityEnum::SUCCESS);
	}
	
   /**
    * Styles the button as a warning button for potentially dangerous operations.
    * 
    * @returns $this
    */
	public function makeWarning()
	{
		return $this->makeType(UI_CriticalityEnum::WARNING);
	}
	
   /**
    * Styles the button as an inverted button.
    * 
    * @returns $this
    */
	public function makeInverse()
	{
		return $this->makeType(UI_CriticalityEnum::INVERSE);
	}

    /**
     * Styles the label as inactive.
     *
     * @return $this
     * @throws Application_Exception
     */
	public function makeInactive()
	{
	    return $this->makeType(UI_CriticalityEnum::INACTIVE);
	}

    /**
     * @var string[]
     */
	protected $validTypes = array(
	    'default',
	    'info',
	    'warning',
	    'success',
	    'inverse',
	    'important'
	);

    /**
     * @param string $type
     * @return $this
     * @throws Application_Exception
     */
	public function makeType($type)
	{
	    if(!in_array($type, $this->validTypes)) {
	       throw new Application_Exception(
	           'Invalid badge type',
	           sprintf(
	               'The badge type [%s] is not valid. Valid types are: [%s].',
	               $type,
                   implode(', ', $this->validTypes)
               ),
	           self::ERROR_INVALID_BADGE_TYPE
           ); 
	    }
	    
		$this->layout = $type;
		return $this;
	}
	
   /**
    * Sets the cursor of the element to the "help" cursor.
    * @return UI_Badge
    */
	public function cursorHelp()
	{
	    return $this->addStyle('cursor', 'help');
	}
	
   /**
    * Makes the whole badge larger.
    * 
    * @return UI_Badge
    */
	public function makeLarge()
	{
	    return $this->addClass('badge-large');
	}

    /**
     * Makes the whole badge small.
     *
     * @return UI_Badge
     */
    public function makeSmall()
    {
        return $this->addClass('badge-small');
    }
}
