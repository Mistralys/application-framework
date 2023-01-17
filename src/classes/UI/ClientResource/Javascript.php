<?php
/**
 * File containing the {@link UI_ClientResource_Javascript} class.
 * @package UserInterface
 * @subpackage ClientResources
 * @see UI_ClientResource_Javascript
 */

declare(strict_types=1);

use AppUtils\HTMLTag;

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
    private bool $defer = false;
    private HTMLTag $tag;

    protected function init() : void
    {
        $this->tag = HTMLTag::create('script')
            ->setEmptyAllowed();
    }

    protected function _getFileType() : string
    {
        return UI_Themes_Theme::FILE_TYPE_JAVASCRIPT;
    }

    public function attr(string $name, string $value) : self
    {
        $this->tag->attr($name, $value);
        return $this;
    }

    public function setIntegrity(string $key) : self
    {
        return $this->attr('integrity', $key);
    }

    public function setTypeModule() : self
    {
        return $this->attr('type', 'module');
    }

    public function setCrossOriginAnonymous() : self
    {
        return $this->attr('crossorigin', 'anonymous');
    }

    public function setReferrerPolicyNone() : self
    {
        return $this->attr('referrerpolicy', 'no-referrer');
    }

    public function setDefer(bool $defer=true) : self
    {
        $this->defer = $defer;
        
        return $this;
    }
    
    public function renderTag() : string
    {
        $this->tag
            ->attr('src', $this->getURL())
            ->attr('data-loadkey', (string)$this->getKey());

        if($this->defer)
        {
            $this->tag->attr('defer', 'defer');
        }
        
        return (string)$this->tag;
    }
}
