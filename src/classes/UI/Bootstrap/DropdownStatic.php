<?php
/**
 * File containing the {@link UI_Bootstrap_DropdownStatic} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_DropdownStatic
 */

/**
 * Bootstrap dropdown static HTML element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @method UI_Bootstrap_DropdownStatic setName(string $name)
 */
class UI_Bootstrap_DropdownStatic extends UI_Bootstrap
{
    protected $content;
    
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    protected function _render()
    {
        return '<li class="static">'.$this->content.'</li>';
	}
}