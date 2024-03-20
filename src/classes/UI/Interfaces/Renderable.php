<?php
/**
 * File containing the {@see UI_Renderable_Interface} interface.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI_Renderable_Interface
 */

declare(strict_types=1);

use AppUtils\Interfaces\RenderableInterface;

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
interface UI_Renderable_Interface extends RenderableInterface
{
    public function getPage() : UI_Page;
    
    public function getTheme() : UI_Themes_Theme;
    
    public function getUI() : UI;
    
    public function getInstanceID() : string;
    
    public function getRenderer() : UI_Themes_Theme_ContentRenderer;
}
