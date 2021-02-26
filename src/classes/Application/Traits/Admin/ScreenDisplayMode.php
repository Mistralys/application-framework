<?php
/**
 * File containing the {@see Application_Traits_Admin_ScreenDisplayMode} trait.
 * 
 * @package Application
 * @subpackage Admin
 * @see Application_Traits_Admin_ScreenDisplayMode
 */

declare(strict_types=1);

/**
 * This trait is used to namespace the _handle methods of the 
 * screen to a display mode. Typically this is practical when
 * working in an Action screen, and subscreens need to be added
 * nevertheless.
 * 
 * In practice, this means that for each possible result
 * of the `resolveDisplayMode()` method, matching handling
 * methods can be added.
 * 
 * Example: Assuming display mode is `list`, the following
 * can be added in the screen:
 * 
 * _handleActions_list()
 * _handleBreadcrumb_list()
 * _handleSidebar_list()
 * _handleSubnavigation_list()
 * _handleTabs_list()
 * _handleHelp_list()
 * _renderContent_list()
 * 
 * Usage:
 * 
 * - Add the trait: `use Application_Traits_Admin_ScreenDisplayMode`
 * - Implement the interface: `implements Application_Interfaces_Admin_ScreenDisplayMode`
 * - Implement the abstract methods
 * - Implement the according handling methods
 * 
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see Application_Interfaces_Admin_ScreenDisplayMode

 * @property UI_Themes_Theme_ContentRenderer $renderer
 */
trait Application_Traits_Admin_ScreenDisplayMode
{
   /**
    * Should return the active display mode, or an empty
    * string if the default should be used.
    * 
    * NOTE: This is called after `_handleActions_common()`.
    * 
    * @return string
    * @see getDefaultDisplayMode()
    */
    abstract public function resolveDisplayMode() : string;
    
    abstract public function getDefaultDisplayMode() : string;
    
    protected function _handleActions_common() : void {}
    protected function _handleBreadcrumb_common() : void {}
    protected function _handleSubnavigation_common() : void {}
    protected function _handleTabs_common() : void {}
    
    protected function _handleActions() : void
    {
        $this->_handleActions_common();
        
        $method = $this->resolveModeMethod('handleActions');
        
        if(method_exists($this, $method))
        {
            $this->$method();
        }
    }
    
    protected function _handleSubnavigation() : void
    {
        $this->_handleSubnavigation_common();
        
        $method = $this->resolveModeMethod('handleSubnavigation');
        
        if(method_exists($this, $method))
        {
            $this->$method();
        }
    }
    protected function _handleTabs() : void
    {
        $this->_handleTabs_common();
        
        $method = $this->resolveModeMethod('handleTabs');
        
        if(method_exists($this, $method))
        {
            $this->$method();
        }
    }
    
    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        $method = $this->resolveModeMethod('renderContent');
        
        if(method_exists($this, $method))
        {
            return $this->$method();
        }
        
        return $this->renderer;
    }
    
    protected function _handleHelp() : void
    {
        $method = $this->resolveModeMethod('handleHelp');
        
        if(method_exists($this, $method))
        {
            $this->$method();
        }
    }
    
    protected function _handleSidebar() : void
    {
        $method = $this->resolveModeMethod('handleSidebar');
        
        if(method_exists($this, $method))
        {
            $this->$method();
        }
    }
    
    protected function _handleBreadcrumb() : void
    {
        $this->_handleBreadcrumb_common();
        
        $method = $this->resolveModeMethod('handleBreadcrumb');
        
        if(method_exists($this, $method))
        {
            $this->$method();
        }
    }
    
    protected function resolveModeMethod(string $baseName) : string
    {
        $mode = $this->resolveDisplayMode();
        
        if(empty($mode))
        {
            $mode = $this->getDefaultDisplayMode();
        }
        
        return '_'.$baseName.'_'.$mode;
    }
}
