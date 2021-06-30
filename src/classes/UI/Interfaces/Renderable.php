<?php
/**
 * File containing the {@see UI_Renderable_Interface} interface.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Renderable_Interface
 */

use AppUtils\StringBuilder_Interface;

/**
 * Interface for renderable elements, which can generate HTML.
 *
 * @package Application
 * @subpackage UserInterface
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see UI_Renderable
 * @see UI_Traits_RenderableGeneric
 */
interface UI_Renderable_Interface extends StringBuilder_Interface
{
    function getPage() : UI_Page;
    
    function getTheme() : UI_Themes_Theme;
    
    function getUI() : UI;
    
    function getInstanceID() : string;
    
    function getRenderer() : UI_Themes_Theme_ContentRenderer;
}
