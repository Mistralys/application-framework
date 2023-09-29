<?php

declare(strict_types=1);

use AppUtils\Interface_Stringable;
use AppUtils\OutputBuffering;
use function AppUtils\parseURL;

class UI_Page_Navigation_Item_DropdownMenu extends UI_Page_Navigation_Item
{
   /**
    * @var UI_Bootstrap_DropdownMenu
    */
    protected $menu;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var bool
     */
    protected $split = false;

    /**
     * @var string
     */
    protected $link = '';

    /**
     * @var string
     */
    protected $click = '';

    private bool $autoActivate = true;
    private bool $caret = true;

    /**
     * UI_Page_Navigation_Item_DropdownMenu constructor.
     * @param UI_Page_Navigation $nav
     * @param string $id
     * @param string|UI_Renderable_Interface|int|float $label
     * @throws Application_Exception
     */
    public function __construct(UI_Page_Navigation $nav, string $id, $label)
    {
        parent::__construct($nav, $id);
        
        $this->menu = UI::getInstance()->createDropdownMenu();
        $this->setLabel($label);
    }

    public function getMenu() : UI_Bootstrap_DropdownMenu
    {
        return $this->menu;
    }

    /**
     * @param string|UI_Renderable_Interface|int|float $label
     * @return $this
     * @throws UI_Exception
     */
    public function setLabel($label) : UI_Page_Navigation_Item_DropdownMenu
    {
        $this->label = toString($label);
        return $this;
    }

   /**
    * Creates a split button for the menu, the menu itself
    * opening by clicking the caret, and the main button label
    * linking to its own destination.
    * 
    * Use the {@link link()} or {@link click()} methods to
    * set the target of the button.
    * 
    * @return $this
    */
    public function makeSplit() : UI_Page_Navigation_Item_DropdownMenu
    {
        $this->split = true;
        return $this;
    }
    
   /**
    * Links the menu button to its own URL. Automatically
    * turns the button into a split button with the caret
    * used to access the menu.
    * 
    * @param string $url
    * @return UI_Page_Navigation_Item_DropdownMenu
    */
    public function link(string $url) : UI_Page_Navigation_Item_DropdownMenu
    {
        $this->makeSplit();
        $this->link = $url;
        return $this;
    }

    /**
     * Links the menu button to its own javascript statement. 
     * Automatically turns the button into a split button with 
     * the caret used to access the menu.
     *
     * @param string $statement
     * @return UI_Page_Navigation_Item_DropdownMenu
     */
    public function click(string $statement) : UI_Page_Navigation_Item_DropdownMenu
    {
        $this->makeSplit();
        $this->click = $statement;
        return $this;
    }
    
    public function getType() : string
    {
        return 'dropdownmenu';
    }

    /**
     * Makes this the active menu item.
     * @return $this
     */
    public function makeActive() : UI_Page_Navigation_Item_DropdownMenu
    {
        $this->active = true;
        return $this;
    }

    public function render(array $attributes = array()) : string
    {
        if (!$this->isValid())
        {
            return '';
        }

        $this->addClass('dropdown');

        if ($this->isActive())
        {
            $this->addClass('active');
        }

        if ($this->split && (!empty($this->link) || !empty($this->click)))
        {
            return $this->renderSplit();
        }

        return $this->renderDefault();
    }

    private function renderDefault() : string
    {
        return UI::getInstance()->createButtonDropdown()
            ->setLabel($this->label)
            ->setIcon($this->getIcon())
            ->setTooltip($this->tooltipInfo)
            ->setCaretEnabled($this->caret)
            ->makeNavItem()
            ->addClasses($this->classes)
            ->setMenu($this->menu)
            ->render();
    }

    private function renderSplit() : string
    {
        OutputBuffering::start();

        ?>
        <li class="<?php echo implode(' ', $this->classes) ?>">
            <a <?php echo compileAttributes($this->getLinkAttributes()) ?>>
                <?php echo $this->renderLabel() ?>
            </a>
            <a href="#" class="dropdown-toggle split-caret" data-toggle="dropdown">
                <b class="caret"></b>
            </a>
            <?php echo $this->menu->render() ?>
        </li>
        <?php

        return OutputBuffering::get();
    }

    /**
     * @return array<string,string>
     */
    private function getLinkAttributes() : array
    {
        $attributes = array(
            'href' => 'javascript:void(0)',
            'class' => 'dropdown-toggle split-link',
        );

        if(!empty($this->link)) {
            $attributes['href'] = $this->link;
        } else {
            $attributes['onclick'] = $this->click;
        }

        return $attributes;
    }

    /**
     * Adds a menu item that links to a regular URL.
     *
     * @param string $label
     * @param string $url
     * @return UI_Bootstrap_DropdownAnchor
     */
    public function addLink(string $label, string $url) : UI_Bootstrap_DropdownAnchor
    {
        $this->registerURL($url);

        return $this->menu->addLink($label, $url);
    }

    /**
     * @var string[]
     */
    private array $trackURLs = array();

    private function registerURL(string $url) : void
    {
        if(!array_key_exists($url, $this->trackURLs) && strpos($url, APP_URL) !== false)
        {
            $this->trackURLs[$url] = null;
        }
    }

    public function setAutoActivate(bool $auto) : self
    {
        $this->autoActivate = $auto;
        return $this;
    }

    /**
     * @return UI_Bootstrap_DropdownMenu
     * @throws Application_Exception
     */
    public function addSeparator() : UI_Bootstrap_DropdownMenu
    {
        return $this->menu->addSeparator();
    }

    /**
     * @param string|int|float|Interface_Stringable|NULL $label
     * @return UI_Bootstrap_DropdownHeader
     */
    public function addHeader($label) : UI_Bootstrap_DropdownHeader
    {
        return $this->menu->addHeader($label);
    }

    public function isActive() : bool
    {
        if($this->active || !$this->autoActivate)
        {
            return $this->active;
        }

        if(empty($this->trackURLs))
        {
            return false;
        }

        $urls = array_keys($this->trackURLs);

        foreach($urls as $url)
        {
            if($this->isURLActive($url)) {
                return true;
            }
        }

        return false;
    }

    private function isURLActive(string $url) : bool
    {
        if(isset($this->trackURLs[$url]))
        {
            $parsed = $this->trackURLs[$url];
        }
        else
        {
            $parsed = parseURL($url);
            $this->trackURLs[$url] = $parsed;
        }

        $urlParams = $parsed->getParams();

        foreach($urlParams as $name => $value)
        {
            if(!isset($_REQUEST[$name]) || $_REQUEST[$name] !== $value)
            {
                return false;
            }
        }

        return true;
    }

    /**
     * @return $this
     */
    public function noCaret() : self
    {
        $this->caret = false;
        return $this;
    }
}
