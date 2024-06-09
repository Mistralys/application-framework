<?php

declare(strict_types=1);

use AppUtils\AttributeCollection;
use UI\AdminURLs\AdminURLInterface;
use UI\Page\Navigation\LinkItemBase;

class UI_Page_Navigation_Item_ExternalLink extends LinkItemBase
{
    protected string $url;

    /**
     * @param UI_Page_Navigation $nav
     * @param string $id
     * @param string|AdminURLInterface $url
     * @param string|number|UI_Renderable_Interface $title
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function __construct(UI_Page_Navigation $nav, string $id, $url, $title)
    {
        parent::__construct($nav, $id);
        $this->url = (string)$url;

        $this->setTitle($title);
    }
    
    public function getType() : string
    {
        return 'externallink';
    }
    
    public function getURL() : string
    {
        return $this->url;
    }

    public function render(array $attributes = array()) : string
    {
        if(!$this->isValid())
        {
            return '';
        }

        $attribs = AttributeCollection::create($attributes)
            ->href($this->getURL())
            ->addClasses($this->classes)
            ->attr('target', $this->target);

        if(isset($this->tooltipInfo))
        {
            $this->tooltipInfo->injectAttributes($attribs);
        }

        return sprintf(
            '<a%s>%s</a>',
            $attribs,
            $this->renderIconLabel($this->getTitle())
        );
    }
}
