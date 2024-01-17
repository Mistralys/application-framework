<?php
/**
 * File containing the {@link UI_Bootstrap_Tabs} class.
 * 
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_Tabs
 */

/**
 * Handles individual tabs in a tab container.
 * 
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method UI_Bootstrap_Tabs getParent()
 */
class UI_Bootstrap_Tab extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
    use Application_Traits_Iconizable;
    
    const TYPE_TOGGLE = 'Toggle';
    const TYPE_LINK = 'Link';
    const TYPE_MENU = 'Menu';

    /**
     * @var string
     */
    protected $label = '';

    /**
     * @var string
     */
    protected $type = self::TYPE_TOGGLE;

    /**
     * @var UI_Bootstrap_DropdownMenu|NULL
     */
    protected $menu;
    
   /**
    * @var string
    */
    protected $url = '';

    /**
     * @var string
     */
    protected $tooltip;

    /**
     * @var bool
     */
    protected $selected = false;

    /**
     * @var array<string,array<int,string>>
     */
    protected array $clientEvents = array();
    private string $urlTarget = '';

    /**
     * @param string|int|float|UI_Renderable_Interface $label
     * @return $this
     */
    public function setLabel($label) : UI_Bootstrap_Tab
    {
        $this->label = toString($label);
        return $this;
    }
    
    public function getLabel() : string
    {
        return $this->label;
    }

    public function select() : UI_Bootstrap_Tab
    {
        $this->selected = true;
        return $this;
    }
    
    public function deselect() : UI_Bootstrap_Tab
    {
        $this->selected = false;
        return $this;
    }
    
    public function isSelected() : bool
    {
        return $this->selected;
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function clientOnSelect($statement)
    {
        return $this->addClientEvent('select', strval($statement));
    }

    /**
     * @param string $type
     * @param string $statement
     * @return $this
     */
    protected function addClientEvent(string $type, string $statement) : UI_Bootstrap_Tab
    {
        $statement = rtrim($statement, ';');
        
        if(!isset($this->clientEvents[$type])) {
            $this->clientEvents[$type] = array();
        }
        
        if(!in_array($statement, $this->clientEvents[$type])) {
            $this->clientEvents[$type][] = $statement;
        }
        
        return $this;
    }
    
   /**
    * Retrieves the javascript statement string for the specified
    * event, if any.
    * 
    * @param string $eventName The name of the event, e.g. "select".
    * @return string The statement string, or an empty string if none present.
    */
    public function getEventStatement(string $eventName) : string
    {
        if(isset($this->clientEvents[$eventName])) {
            return implode(';', $this->clientEvents[$eventName]).';';
        }
        
        return '';
    }
    
   /**
    * @return UI_Bootstrap_Tabs
    */
    public function getTabs() : UI_Bootstrap_Tabs
    {
        return $this->getParent();
    }
    
    public function getID() : string
    {
        return 'tab-'.$this->getTabs()->getName().'-'.$this->getName();
    }

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @param string|int|float|UI_Renderable_Interface $content
     * @return $this
     */
    public function setContent($content) : self
    {
        $this->content = toString($content);
        return $this;
    }

    /**
     * @return string
     */
    public function getURLTarget() : string
    {
        return $this->urlTarget;
    }

    protected function _render() : string
    {
        return $this->content;
    }
    
   /**
    * Whether the tab has a toggleable body.
    * 
    * @return bool
    */
    public function hasBody() : bool
    {
        return $this->type === self::TYPE_TOGGLE;
    }
    
    public function getLinkID() : string
    {
        return $this->getID().'-link';
    }
    
   /**
    * Turns the tab into a static link that does not have any content.
    * 
    * @param string $url
    * @param bool $newTab
    * @return UI_Bootstrap_Tab
    */
    public function makeLinked(string $url, bool $newTab=false) : UI_Bootstrap_Tab
    {
        $this->type = self::TYPE_LINK;
        $this->url = $url;

        if($newTab) {
            $this->setURLTarget('_blank');
        }

        return $this;
    }

    /**
     * Sets a tooltip to shown when hovering over the tab.
     *
     * @param string|int|float|UI_Renderable_Interface $tooltip
     * @return UI_Bootstrap_Tab
     */
    public function setTooltip($tooltip) : UI_Bootstrap_Tab
    {
        $this->tooltip = toString($tooltip);
        return $this;
    }

    public function makeDropdown(UI_Bootstrap_DropdownMenu $menu) : UI_Bootstrap_Tab
    {
        $this->type = self::TYPE_MENU;
        $this->menu = $menu;

        return $this;
    }

    public function getMenu() : ?UI_Bootstrap_DropdownMenu
    {
        return $this->menu;
    }
    
   /**
    * Returns the URL the tab links to, if it is a static link
    * instead of a toggleable tab (see <code>makeLinked</code>).
    * 
    * @return string
    */
    public function getURL() : string
    {
        return $this->url;
    }

    public function renderTab() : string
    {
        if($this->isSelected()) {
            $this->addClass('active');
        }

        $rendered = $this->createRenderer()->render();

        $atts = $this->attributes;
        $atts['class'] = $this->classesToString();

        if(!empty($this->tooltip))
        {
            $atts['title'] = $this->tooltip;
            JSHelper::tooltipify($this->getID());
        }

        ob_start();

?>
<li<?php echo compileAttributes($atts) ?>>
    <?php echo $rendered ?>
</li>
<?php

        return $this->ob_get_clean();
    }
    
   /**
    * Creates the type renderer used to create the markup. This
    * differentiates between a regular link tab, or the tab toggle.
    * 
    * @return UI_Bootstrap_Tab_Renderer
    * 
    * @see UI_Bootstrap_Tab_Renderer_Link
    * @see UI_Bootstrap_Tab_Renderer_Toggle
    * @see UI_Bootstrap_Tab_Renderer_Menu
    */
    protected function createRenderer() : UI_Bootstrap_Tab_Renderer
    {
        $class = 'UI_Bootstrap_Tab_Renderer_'.$this->type;
        return new $class($this);
    }

    /**
     * Sets the target of the URL to open when the tab
     * is in URL mode.
     *
     * @param string $target
     * @return $this
     */
    public function setURLTarget(string $target) : self
    {
        $this->urlTarget = $target;
        return $this;
    }
}
