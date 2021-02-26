<?php
/**
 * File containing the {@link UI_ClientResource_Javascript} class.
 * @package UserInterface
 * @subpackage ClientResources
 * @see UI_ClientResource_Javascript
 */

declare(strict_types=1);

/**
 * Javascript include file. 
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see UI_ResourceManager
 */
class UI_ClientResource_Javascript extends UI_ClientResource
{
    private $defer = false;
    
    protected function _getFileType() : string
    {
        return UI_Themes_Theme::FILE_TYPE_JAVASCRIPT;
    }
    
    public function setDefer(bool $defer=true) : UI_ClientResource_Javascript
    {
        $this->defer = $defer;
        
        return $this;
    }
    
    public function renderTag() : string
    {
        $atts = array(
            'src' => $this->getURL(),
            'data-loadkey' => $this->getKey()
        );
        
        if($this->defer) 
        {
            $atts['defer'] = 'defer';
        }
        
        return '<script'.compileAttributes($atts).'></script>';
    }
}
