<?php
/**
 * File containing the {@link UI_Page_Section_Content} class.
 * 
 * @package UI
 * @see UI_Page_Section_Content
 */

declare(strict_types=1);

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Traits\OptionableTrait;

/**
 * Base class for section contents: these are specialized
 * content types that are rendered automatically and can
 * be freely added to a section.
 * 
 * @package UI
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
abstract class UI_Page_Section_Content
    extends UI_Renderable
    implements OptionableInterface
{
    use OptionableTrait;
    
    protected UI_Page_Section $section;
    
    public function __construct(UI_Page_Section $section)
    {
        parent::__construct($section->getPage());
        
        $this->section = $section;
        
        $this->init();
    }
    
    protected function init() : void
    {
        
    }
}
