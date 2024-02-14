<?php

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Traits\ClassableTrait;

class UI_Page_Title extends UI_Renderable
    implements
    Application_Interfaces_Iconizable,
    ClassableInterface
{
    use Application_Traits_Iconizable;
    use ClassableTrait;
    
   /**
    * @var string
    */
    protected $text = '';
    
   /**
    * @var string[]
    */
    protected $subline = array();
    
   /**
    * The HTML tag used to render the title.
    * @var string
    */
    protected $tagName = 'h1';

   /**
    * @var string
    */
    protected $baseClass = 'title';
    
   /**
    * @var UI_Interfaces_Badge[]
    */
    protected $badges = array();
    
   /**
    * @var UI_Renderable_Interface[]
    */
    protected $contextElements = array();
    
   /**
    * @var string[]
    */
    protected $appends = array();
    
   /**
    * Sets the title text.
    * 
    * @param string|number|UI_Renderable_Interface $text
    * @return UI_Page_Title
    */
    public function setText($text) : UI_Page_Title
    {
        $this->text = toString($text);
        
        return $this;
    }

    /**
     * Adds a bit of text that will be appended to the text.
     *
     * The advantage of using this instead of adding it to the
     * text itself and using setText() is that these bits of
     * text stay separate - the original text can still be
     * retrieved with getText().
     *
     * NOTE: Empty strings or NULL values are ignored.
     *
     * @param string|number|UI_Renderable_Interface|NULL $text
     * @return UI_Page_Title
     * @throws UI_Exception
     */
    public function addTextAppend($text) : UI_Page_Title
    {
        $value = toString($text);

        if($value !== '')
        {
            $this->appends[] = $value;
        }
        
        return $this;
    }

    public function hasAppends() : bool
    {
        return !empty($this->appends);
    }
    
    public function getAppends() : array
    {
        return $this->appends;
    }
    
   /**
    * Sets a subline to the title that is shown directly beneath, in a much smaller text.
    * 
    * @param string|number|UI_Renderable_Interface $subline
    * @return UI_Page_Title
    */    
    public function setSubline($subline) : UI_Page_Title
    {
        $this->subline = array(toString($subline));
        
        return $this;
    }

    /**
     * Adds a subline, appending it to any already existing sublines.
     *
     * @param string|number|UI_Renderable_Interface|NULL $subline
     * @return UI_Page_Title
     * @throws UI_Exception
     */
    public function addSubline($subline) : UI_Page_Title
    {
        $value = toString($subline);

        if($value !== '')
        {
            $this->subline[] = $value;
        }
        
        return $this;
    }
    
    public function getBaseClass() : string
    {
        return $this->baseClass;
    }
    
   /**
    * Whether a subline text has been set.
    * 
    * @return bool
    */
    public function hasSubline() : bool
    {
        return !empty($this->subline);
    }
    
    public function getSubline() : string
    {
        return implode('<br>', $this->subline);
    }
    
    public function getText() : string
    {
        return $this->text;
    }

    public function hasText() : bool
    {
        return !empty($this->text);
    }
    
   /**
    * Retrieves the name of the HTML tag to use for the title.
    * 
    * @return string
    */
    public function getTagName() : string
    {
        return $this->tagName;
    }
    
   /**
    * Adds a badge that is displayed next to the title.
    * 
    * @param UI_Interfaces_Badge|NULL $badge Accepts null values for method chaining without additional checks.
    * @return UI_Page_Title
    */
    public function addBadge(?UI_Interfaces_Badge $badge) : UI_Page_Title
    {
        if($badge !== null)
        {
            $this->badges[] = $badge;
        }
        
        return $this;
    }

    /**
     * @param UI_Interfaces_Badge|null $badge Accepts null values for method chaining without additional checks.
     * @return $this
     */
    public function prependBadge(?UI_Interfaces_Badge $badge) : UI_Page_Title
    {
        if($badge !== null)
        {
            array_unshift($this->badges, $badge);
        }
        
        return $this;
    }
    
   /**
    * Whether the title has any badges attached.
    * 
    * @return bool
    */
    public function hasBadges() : bool
    {
        return !empty($this->badges);
    }
    
   /**
    * Retrieves all badges that have been added to the title.
    * 
    * @return UI_Interfaces_Badge[]
    */
    public function getBadges() : array
    {
        return $this->badges;
    }

    /**
     * Adds a context element to the title, like a badge.
     *
     * @param UI_Renderable_Interface|null $element Allows null values as utility for method chaining without additional checks.
     * @return $this
     */
    public function addContextElement(?UI_Renderable_Interface $element) : UI_Page_Title
    {
        if($element !== null)
        {
            $this->contextElements[] = $element;
        }
        
        return $this;
    }
    
   /**
    * Whether any context elements were added (buttons, menus, etc).
    * 
    * @return bool
    */
    public function hasContextElements() : bool
    {
        return !empty($this->contextElements);
    }
    
   /**
    * Retrieves all context elements that were added.
    * 
    * @return UI_Renderable_Interface[]
    */
    public function getContextElements()
    {
        return $this->contextElements;
    }
    
   /**
    * Whether the title has enough information to be displayed.
    *  
    * @return bool
    */
    public function isValid() : bool
    {
        return !empty($this->text) || !empty($this->contextElements);
    }
    
    protected function _render() : string
    {
        $this->addClass('page-'.$this->baseClass);
        
        return $this->renderTemplate('frame.page-title', array('title' => $this));
    }
}
