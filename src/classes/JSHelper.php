<?php

class JSHelper extends \AppUtils\JSHelper
{
    public const TOOLTIP_TOP = 'top';
    public const TOOLTIP_BOTTOM = 'bottom';
    public const TOOLTIP_LEFT = 'left';
    public const TOOLTIP_RIGHT = 'right';

    /**
     * @var string[]
     */
    private static array $validTooltipPlacements = array(
        self::TOOLTIP_TOP,
        self::TOOLTIP_BOTTOM,
        self::TOOLTIP_LEFT,
        self::TOOLTIP_RIGHT
    );

    /**
     * Adds a tooltip for the selected DOM element ID. For this to
     * work, the element has to have the <code>title</code> attribute,
     * which is used for the tooltip.
     *
     * @param string $elementID
     * @param string $placement
     * @throws UI_Exception
     */
    public static function tooltipify(string $elementID, string $placement=self::TOOLTIP_TOP) : void
    {
        if(!in_array($placement, self::$validTooltipPlacements)) {
            $placement = self::TOOLTIP_TOP;
        }
        
        $ui = UI::getInstance();
        
        $ui->addJavascriptOnload(sprintf(
            "UI.MakeTooltip('#%s', null, '%s')",
            $elementID,
            $placement
        ), true);
    }
}
