<?php

use AppUtils\OutputBuffering;

class UI_Bootstrap_DropdownSubmenu
    extends UI_Bootstrap_DropdownMenu
    implements
        Application_Interfaces_Iconizable,
        UI_Interfaces_Bootstrap_DropdownItem
{
    public const ERROR_MENU_INSTANCE_NOT_SET = 101101;

    use Application_Traits_Iconizable;

    /**
     * @var string
     */
    protected string $title = '';
    
   /**
    * @var UI_Bootstrap_DropdownMenu|NULL
    */
    protected ?UI_Bootstrap_DropdownMenu $menu = null;

    /**
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @return $this
     * @throws UI_Exception
     */
    public function setTitle($title) : self
    {
        $this->title = toString($title);
        return $this;
    }
    
    public function setMenu(UI_Bootstrap_DropdownMenu $menu) : self
    {
        $this->menu = $menu;
        return $this;
    }
    
    protected function _render() : string
    {
        $this->addLIClass('dropdown-submenu');

        $html = parent::_render();
        
        OutputBuffering::start();

        ?>
        <li class="<?php echo implode(' ', $this->liClasses) ?>">
            <a tabindex="-1" href="javascript:void(0)">
                <?php echo $this->renderIconLabel($this->title) ?>
            </a>
            <?php echo $html ?>
        </li>
        <?php

        return OutputBuffering::get();
    }

    /**
     * @var string[]
     */
    protected array $liClasses = array();

    /**
     * @param string $class
     * @return $this
     */
    public function addLIClass(string $class) : self
    {
        if(!in_array($class, $this->liClasses, true))
        {
            $this->liClasses[] = $class;
        }
        
        return $this;
    }

    /**
     * @return $this
     * @throws UI_Exception
     */
    public function makeOpenUp() : self
    {
        $this->requireMenu()->addClass('dropup');
        return $this;
    }

    /**
     * @return $this
     */
    public function makeOpenLeft() : self
    {
        return $this->addLIClass('pull-left');
    }

    private function requireMenu() : UI_Bootstrap_DropdownMenu
    {
        if(isset($this->menu))
        {
            return $this->menu;
        }

        throw new UI_Exception(
            'No menu specified',
            'The menu instance must be set with [setMenu()].',
            self::ERROR_MENU_INSTANCE_NOT_SET
        );
    }
}
