<?php
/**
 * File containing the {@link UI_Bootstrap_DropdownStatic} class.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Bootstrap_DropdownStatic
 */

declare(strict_types=1);

/**
 * Bootstrap dropdown static HTML element.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class UI_Bootstrap_DropdownStatic
    extends UI_Bootstrap
    implements UI_Interfaces_Bootstrap_DropdownItem
{
    protected string $content;

    /**
     * @param string|number|UI_Renderable_Interface|NULL $content
     * @return $this
     * @throws UI_Exception
     */
    public function setContent($content) : self
    {
        $this->content = toString($content);
        return $this;
    }
    
    protected function _render() : string
    {
        return '<li class="static">'.$this->content.'</li>';
	}
}
