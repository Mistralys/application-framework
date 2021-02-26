<?php
/**
 * File containing the {@link UI_Bootstrap_DropdownHeader} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_DropdownHeader
 */

/**
 * Bootstrap dropdown header element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method UI_Bootstrap_DropdownHeader setName(string $name)
 */
class UI_Bootstrap_DropdownHeader extends UI_Bootstrap
{
   /**
    * @var string
    */
    protected $title;
    
   /**
    * @param string $title
    * @return UI_Bootstrap_DropdownHeader
    */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
    
    protected function _render()
    {
        return '<li class="dropdown-item disabled menu-header"><a>'.$this->title.'</a></li>';
	}
}