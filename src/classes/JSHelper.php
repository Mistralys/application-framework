<?php

class JSHelper extends \AppUtils\JSHelper
{
   /**
    * Adds a tooltip for the selected DOM element ID. For this to 
    * work, the element has to have the <code>title</code> attribute,
    * which is used for the tooltip.
    * 
    * @param string $elementID
    * @param string $placement
    */
    public static function tooltipify($elementID, $placement='top')
    {
        if(!in_array($placement, array('top', 'left', 'right', 'bottom'))) {
            $placement = 'top';
        }
        
        $ui = UI::getInstance();
        
        $ui->addJavascriptOnload(sprintf(
            "UI.MakeTooltip('#%s', null, '%s')",
            $elementID,
            $placement
        ), true);
    }
}
