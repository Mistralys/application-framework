<?php
/**
 * File containing the {@link UI_StringBuilder} class.
 *
 * @package UI
 * @subpackage StringBuilder
 * @see UI_StringBuilder
 */

use AppUtils\AttributeCollection;
use AppUtils\StringBuilder;
use UI\AdminURLs\AdminURLInterface;

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
class UI_StringBuilder extends StringBuilder implements UI_Renderable_Interface, UI_Interfaces_Conditional
{
    use UI_Traits_RenderableGeneric;
    use UI_Traits_Conditional;

    public const CLASS_BTN_CLIPBOARD_COPY = 'btn-clipboard-copy';

    /**
     * Delay, in seconds, after which to hide the status
     * text saying that the text has been copied.
     */
    public const FADE_OUT_DELAY = 2.1;

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
     * @throws UI_Exception
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

    public function dangerXXL($string) : UI_StringBuilder
    {
        return $this->spanned($string, 'text-error-xxl');
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
     * Adds a success-styled text.
     *
     * @param string|number|UI_Renderable_Interface $string
     * @return $this
     */
    public function success($string) : UI_StringBuilder
    {
        return $this->spanned($string, 'text-success');
    }

    /**
     * Adds an inverted color styled text.
     *
     * @param string|number|UI_Renderable_Interface $string
     * @return $this
     */
    public function inverted($string) : UI_StringBuilder
    {
        return $this->spanned($string, 'text-inverted');
    }

    /**
     * Adds a secondary-styled text, which is slightly more
     * marked than muted text.
     *
     * @param string|number|UI_Renderable_Interface $string
     * @return $this
     */
    public function secondary($string) : UI_StringBuilder
    {
        return $this->spanned($string, 'text-secondary');
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
     * @param string|AdminURLInterface $url
     * @param string $right
     * @param bool $newTab
     * @return UI_StringBuilder
     * @throws Application_Exception
     */
    public function linkRight(string $label, $url, string $right='', bool $newTab=false) : UI_StringBuilder
    {
        if(!empty($right) && !Application::getUser()->can($right))
        {
            return sb()->add($label);
        }

        return $this->link($label, (string)$url, $newTab);
    }

    /**
     * @param string $label
     * @param string|AdminURLInterface $url
     * @param bool $newTab
     * @param AttributeCollection|null $attributes
     * @return self
     */
    public function adminLink(string $label, $url, bool $newTab=false, ?AttributeCollection $attributes=null) : self
    {
        return $this->link($label, $url, $newTab, $attributes);
    }

    /**
     * Adds a tooltip to the text. Includes styling to mark
     * the text as having a tooltip.
     *
     * NOTE: This will only work correctly with text content.
     * Markup may require adding styling exceptions, see the
     * `ui-core.css` file, and the `text-tooltip` class.
     *
     * @param string|number|UI_Renderable_Interface $string
     * @param string|number|UI_Renderable_Interface $tooltip
     * @return $this
     * @throws UI_Exception
     */
    public function tooltip($string, $tooltip)
    {
        $jsID = nextJSID();
        JSHelper::tooltipify($jsID);

        return $this->sf(
            '<span title="%s" id="%s" class="text-tooltip">%s</span>',
            $tooltip,
            $jsID,
            toString($string)
        );
    }

    /**
     * @param string|number|UI_Renderable_Interface $string
     * @param string|number|UI_Renderable_Interface $author
     * @return $this
     * @throws UI_Exception
     */
    public function blockquote($string, $author='') : UI_StringBuilder
    {
        $author = toString($author);

        if(empty($author))
        {
            return $this->sf(
                '<blockquote>&#8220;%s&#8221;</blockquote>',
                toString($string)
            );
        }

        return $this->sf(
            '<blockquote>&#8220;%s&#8221;</blockquote>'.
            '<p class="blockquote-author">- %s</p>',
            toString($string),
            toString($author)
        );
    }

    /**
     * @param string|number|UI_Renderable_Interface $string
     * @return UI_StringBuilder
     * @throws UI_Exception
     */
    public function parentheses($string) : UI_StringBuilder
    {
        return $this->sf('(%s)', toString($string));
    }

    /**
     * Renders a text clickable, with an optional tooltip.
     *
     * @param string|number|UI_Renderable_Interface $string
     * @param string $statement The JavaScript statement to execute on click.
     *                          Warning: must not include any double quotes, since
     *                          It is inserted in an HTML attribute.
     * @param string|number|UI_Renderable_Interface $tooltip
     * @return UI_StringBuilder
     * @throws UI_Exception
     */
    public function clickable($string, string $statement, $tooltip='') : UI_StringBuilder
    {
        $result = sb()->sf(
            '<span class="clickable" onclick="%s">%s</span>',
            $statement,
            toString($string)
        );

        if(empty($tooltip))
        {
            return $this->add($result);
        }

        return $this->tooltip($result, $tooltip);
    }

    /**
     * Formats a text as code, and adds a button beside it to
     * copy the text to the clipboard.
     *
     * @param string|number|UI_Renderable_Interface $string
     * @return UI_StringBuilder
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function codeCopy($string) : UI_StringBuilder
    {
        $jsID = nextJSID();
        $ui = $this->getUI();

        $this->code($string);

        // Setting display:none or even visibility:hidden causes the
        // text not to be copied, which is why we use the opacity.
        $this->sf(
            '<textarea id="%s" style="position: absolute;top: 0;left: 0;width: 0;height: 0;overflow: hidden;opacity: 0">%s</textarea>',
            $jsID,
            toString($string)
        );

        // Load the required client side libraries
        $ui->addJavascript('ui/clipboard-handler.js');
        $ui->addVendorJavascript('zenorocha/clipboardjs', 'dist/clipboard.js');

        // Initialize the clipboard handler
        $ui->addJavascriptOnload(sprintf(
            "new ClipboardHandler('.%s', %s)",
            self::CLASS_BTN_CLIPBOARD_COPY,
            self::FADE_OUT_DELAY
        ));

        return $this
            ->button(UI::button()
                ->addClass(self::CLASS_BTN_CLIPBOARD_COPY)
                ->makeMini()
                ->addDataAttribute('clipboard-target', '#'.$jsID)
                ->setTooltip(t('Copies the text to the clipboard.'))
                ->setIcon(UI::icon()->copy())
            )
            ->sf(
                '<span class="text-success" id="%s" style="display: none">%s</span>',
                $jsID.'-status',
                t('Text copied successfully.')
            );
    }

    public function render() : string
    {
        if($this->isValid()) {
            return parent::render();
        }

        return '';
    }
}
