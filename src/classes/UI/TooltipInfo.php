<?php

declare(strict_types=1);

namespace UI;

use Application_Traits_Loggable;
use AppUtils\AttributeCollection;
use JSHelper;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

class TooltipInfo
    implements
    UI_Renderable_Interface,
    \Application_Interfaces_Loggable
{
    use UI_Traits_RenderableGeneric;
    use Application_Traits_Loggable;

    private string $content;
    private string $placement;
    private string $elementID = '';

    public function __construct($content, string $placement=JSHelper::TOOLTIP_TOP)
    {
        $this->content = toString($content);
        $this->placement = $placement;
    }

    public function makeTop() : self
    {
        return $this->setPlacement(JSHelper::TOOLTIP_TOP);
    }

    public function makeBottom() : self
    {
        return $this->setPlacement(JSHelper::TOOLTIP_BOTTOM);
    }

    public function makeLeft() : self
    {
        return $this->setPlacement(JSHelper::TOOLTIP_LEFT);
    }

    public function makeRight() : self
    {
        return $this->setPlacement(JSHelper::TOOLTIP_RIGHT);
    }

    public function setPlacement(string $placement) : self
    {
        $this->placement = $placement;
        return $this;
    }

    public function attachToID(string $id) : self
    {
        $this->elementID = $id;
        return $this;
    }

    public function render() : string
    {
        $this->injectJS();
        return '';
    }

    public function injectJS() : self
    {
        if(!empty($this->elementID))
        {
            JSHelper::tooltipify(
                $this->elementID,
                $this->placement
            );
        }
        else
        {
            $this->logUI('WARNING | No element ID set for tooltip.');
            $this->logUI('Tooltip text: [%s]', $this->content);
        }

        return $this;
    }

    public function injectAttributes(AttributeCollection $attributes) : self
    {
        $attributes->attrQuotes('title', $this->content);

        if(!$attributes->hasAttribute('id'))
        {
            $attributes->attr('id', nextJSID());
        }

        $this->attachToID($attributes->getAttribute('id'));

        return $this->injectJS();
    }

    public function getLogIdentifier() : string
    {
        return 'TooltipInfo';
    }
}
