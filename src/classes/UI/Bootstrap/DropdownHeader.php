<?php
/**
 * File containing the {@link UI_Bootstrap_DropdownHeader} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_DropdownHeader
 */

declare(strict_types=1);

use AppUtils\OutputBuffering;

/**
 * Bootstrap dropdown header element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_DropdownHeader
    extends UI_Bootstrap
    implements
        Application_Interfaces_Iconizable,
        UI_Interfaces_Bootstrap_DropdownItem
{
    use Application_Traits_Iconizable;

   /**
    * @var string
    */
    protected string $title = '';

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
    
    protected function _render()
    {
        OutputBuffering::start();

        ?>
        <li class="dropdown-item disabled menu-header">
            <a>
                <?php echo $this->renderIconLabel($this->title) ?>
            </a>
        </li>
        <?php

        return OutputBuffering::get();
	}
}