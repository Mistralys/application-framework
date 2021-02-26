<?php
/**
 * File containing the {@link UI_Traits_RenderableGeneric} trait.
 * 
 * @package UI
 * @subpackage Traits
 * @see UI_Traits_RenderableGeneric
 */

declare(strict_types=1);

/**
 * Trait used to implement the interface methods for a renderable
 * object in a generic way, without requiring a UI instance or 
 * page to be set. Uses the active global UI instance.
 * 
 * The only method left to implement is the actual `render()` method.
 * 
 * @package UI
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see UI_Renderable_Interface
 */
trait UI_Traits_RenderableGeneric
{
   /**
    * @var string
    */
    protected $renderableInstanceID = '';
    
    public function getUI(): UI
    {
        return UI::getInstance();
    }
    
    public function getTheme(): UI_Themes_Theme
    {
        return $this->getUI()->getTheme();
    }
    
    public function getInstanceID(): string
    {
        if(empty($this->renderableInstanceID))
        {
            $this->renderableInstanceID = nextJSID();
        }
        
        return $this->renderableInstanceID;
    }
    
    public function getRenderer(): UI_Themes_Theme_ContentRenderer
    {
        return $this->getPage()->getRenderer();
    }
    
    public function getPage(): UI_Page
    {
        return $this->getUI()->getPage();
    }
    
    public function display() : void
    {
        echo $this->render();
    }
    
    public function __toString()
    {
        return $this->render();
    }

    protected function ob_get_clean() : string
    {
        $content = ob_get_clean();

        if($content !== false)
        {
            return $content;
        }

        return '';
    }
}
