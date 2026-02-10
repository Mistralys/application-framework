<?php
/**
 * @package Application
 * @subpackage UserInterface
 * @see \UI\TooltipInfo
 */

declare(strict_types=1);

namespace UI;

use Application_Interfaces_Loggable;
use Application_Traits_Loggable;
use AppUtils\AttributeCollection;
use AppUtils\Interfaces\StringableInterface;
use JSHelper;
use UI;
use UI_Exception;
use UI_Renderable_Interface;
use UI_Traits_RenderableGeneric;

/**
 * Helper class used to configure a tooltip.
 *
 * Use the method {@see UI::tooltip()} to create an instance.
 *
 * Usage for rendering:
 *
 * 1) Set the element ID to attach it to.
 *    Either use {@see TooltipInfo::attachToID()}, or
 *    {@see TooltipInfo::injectAttributes()} to use an
 *    existing `id` attribute (or create one automatically).
 * 2) Enable the tooltip. It will be automatically enabled
 *    if it is rendered to string, if {@see TooltipInfo::injectAttributes()}
 *    is called, or if {@see TooltipInfo::injectJS()} is called.
 *
 * @package Application
 * @subpackage UserInterface
 * @see UI::tooltip()
 */
class TooltipInfo
    implements
    UI_Renderable_Interface,
    Application_Interfaces_Loggable
{
    use UI_Traits_RenderableGeneric;
    use Application_Traits_Loggable;

    private string $content;
    private string $placement;
    private string $elementID = '';

    /**
     * @param string|number|StringableInterface|NULL $content
     * @param string $placement
     * @throws UI_Exception
     */
    public function __construct($content, string $placement=JSHelper::TOOLTIP_TOP)
    {
        $this->content = toString($content);
        $this->placement = $placement;
    }

    /**
     * @param string|number|StringableInterface|TooltipInfo|NULL $content
     * @return TooltipInfo
     * @throws UI_Exception
     */
    public static function create($content) : TooltipInfo
    {
        if($content instanceof TooltipInfo) {
            return $content;
        }

        return new TooltipInfo($content);
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

            return $this;
        }

        $this->logUI('WARNING | No element ID set for tooltip.');
        $this->logUI('Tooltip text: [%s]', $this->content);

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

    public function getContent() : string
    {
        return $this->content;
    }
}
