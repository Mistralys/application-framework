<?php
/**
 * File containing the {@link UI_ClientResource_Stylesheet} class.
 * @package UserInterface
 * @subpackage ClientResources
 * @see UI_ClientResource_Stylesheet
 */

declare(strict_types=1);

/**
 * CSS Stylesheet include file.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see UI_ResourceManager
 */
class UI_ClientResource_Stylesheet extends UI_ClientResource
{
    private $media = 'all';
    
    protected function _getFileType() : string
    {
        return UI_Themes_Theme::FILE_TYPE_STYLESHEET;
    }

    protected function init() : void
    {
    }

    public function setMedia(string $media) : UI_ClientResource_Stylesheet
    {
        $this->media = $media;
        
        return $this;
    }
    
    public function getMedia() : string
    {
        return $this->media;
    }
    
    public function renderTag() : string
    {
        return sprintf(
            '<link rel="stylesheet" href="%s" media="%s" data-loadkey="%s"/>',
            $this->getURL(),
            $this->getMedia(),
            $this->getKey()
        );
    }
}
