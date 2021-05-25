<?php
/**
 * File containing the {@link UI_StringBuilder} class.
 *
 * @package UI
 * @subpackage StringBuilder
 * @see UI_StringBuilder
 */

use AppUtils\StringBuilder;

/**
 * Extension to the app utils StringBuilder class, with
 * framework-specific methods.
 *
 * @package UI
 * @subpackage StringBuilder
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * 
 * @see AppUtils\StringBuilder
 * @see UI_Renderable_Interface
 * @see UI_Traits_RenderableGeneric
 */
class UI_StringBuilder extends StringBuilder implements UI_Renderable_Interface 
{
    use UI_Traits_RenderableGeneric;
    
   /**
    * Adds an icon.
    * 
    * @param UI_Icon $icon
    * @return $this
    */
    public function icon(UI_Icon $icon) : UI_StringBuilder
    {
        return $this->add((string)$icon);
    }
    
   /**
    * Adds an informational styled text.
    * 
    * @param string|number|UI_Renderable_Interface $string
    * @return $this
    */
    public function info($string) : UI_StringBuilder
    {
        return $this->sf(
            '<span class="text-info">%s</span>',
            toString($string)
        );
    }
    
   /**
    * Adds the danger-styled text "This cannot be undone, are you sure?".
    * @return $this
    */
    public function cannotBeUndone() : UI_StringBuilder
    {
        return $this->bold(sb()->danger(t('This cannot be undone, are you sure?')));
    }
    
   /**
    * Adds a muted text.
    * 
    * @param string|number|UI_Renderable_Interface $string
    * @return $this
    */
    public function muted($string) : UI_StringBuilder
    {
        return $this->spanned($string, 'muted');
    }
    
   /**
    * Adds a button.
    *
    * @param UI_Button $button
    * @return $this
    */
    public function button(UI_Button $button) : UI_StringBuilder
    {
        return $this->add($button);
    }

   /**
    * Adds a danger-styled text.
    * 
    * @param string|number|UI_Renderable_Interface $string
    * @return $this
    */
    public function danger($string) : UI_StringBuilder
    {
        return $this->spanned($string, 'text-error');
    }
    
   /**
    * Adds a warning-styled text.
    *
    * @param string|number|UI_Renderable_Interface $string
    * @return $this
    */
    public function warning($string) : UI_StringBuilder
    {
        return $this->spanned($string, 'text-warning');
    }
    
   /**
    * Adds a monospace-styled text.
    * 
    * @param string|number|UI_Renderable_Interface $string
    * @return $this
    */
    public function mono($string) : UI_StringBuilder
    {
        return $this->spanned($string, 'monospace');
    }

    /**
     * Renders an HTML link, but only if the user has the specified right.
     * Otherwise, the link label is used.
     *
     * @param string $label
     * @param string $url
     * @param string $right
     * @param bool $newTab
     * @return UI_StringBuilder
     * @throws Application_Exception
     */
    public function linkRight(string $label, string $url, string $right='', bool $newTab=false) : UI_StringBuilder
    {
        if(!empty($right) && !Application::getUser()->can($right))
        {
            return sb()->add($label);
        }

        return $this->link($label, $url, $newTab);
    }

    /**
     * @param string|number|UI_Renderable_Interface $string
     * @param string|number|UI_Renderable_Interface $tooltip
     * @return $this
     */
    public function tooltip($string, $tooltip)
    {
        $jsID = nextJSID();
        JSHelper::tooltipify($jsID);

        return $this->sf(
            '<span title="%s" id="%s" style="cursor: help">%s</span>',
            $tooltip,
            $jsID,
            toString($string)
        );
    }

    /**
     * @param string|number|UI_Renderable_Interface $string
     * @return $this
     */
    public function blockquote($string) : UI_StringBuilder
    {
        return $this->sf(
            '<blockquote>&#8220;%s&#8221;</blockquote>',
            toString($string)
        );
    }
}
