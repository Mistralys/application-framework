<?php
/**
 * File containing the class {@see HTML_QuickForm2_Element_UIButton}.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @see HTML_QuickForm2_Element_UIButton
 */

declare(strict_types=1);

/**
 * Twitter Bootstrap-based switch element that acts like a checkbox.
 *
 * @package User Interface
 * @subpackage Form Elements
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class HTML_QuickForm2_Element_UIButton extends HTML_QuickForm2_Element_Button
{
    /**
     * NOTE: We are not implementing the button interface
     * on purpose. The interface conflicts with methods from
     * the QuickForm classes (like hasClass() for example).
     * This way we are at least able to use the trait.
     *
     * @see UI_Interfaces_Button::hasClass()
     */
    // @TODO This has been removed because of PHP7 conflicts.
    // The trait overloading the QuickForm2 methods is not
    // compatible with PHP7, and causes a fatal error. Hence,
    // the trait has been removed, and the methods are now
    // implemented directly in this class.
    //
    //use UI_Traits_GenericButton;

    private UI_Button $button;

    protected function initNode() : void
    {
        parent::initNode();

        $this->button = UI::button();
        $this->button->makeSubmit($this->getName(), 'yes');
    }

    public function getButtonInstance() : UI_Button
    {
        return $this->button;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label) : self
    {
        $this->button->setLabel($label);
        return $this;
    }

    public function __toString() : string
    {
        return $this->button->render();
    }

    // region: Button trait methods

    public function getLabel() : string
    {
        return $this->getButtonInstance()->getLabel();
    }

    public function makeSubmit() : self
    {
        $this->getButtonInstance()->makeSubmit($this->getName(), 'yes');
        return $this;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @return $this
     * @throws UI_Exception
     */
    public function setTitle($title) : self
    {
        $this->getButtonInstance()->setTitle($title);
        return $this;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $tooltip
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($tooltip) : self
    {
        $this->getButtonInstance()->setTooltip($tooltip);
        return $this;
    }

    public function makeDangerous() : self
    {
        $this->getButtonInstance()->makeDangerous();
        return $this;
    }

    public function makePrimary() : self
    {
        $this->getButtonInstance()->makePrimary();
        return $this;
    }

    public function makeSuccess() : self
    {
        $this->getButtonInstance()->makeSuccess();
        return $this;
    }

    public function makeDeveloper() : self
    {
        $this->getButtonInstance()->makeDeveloper();
        return $this;
    }

    public function makeWarning() : self
    {
        $this->getButtonInstance()->makeWarning();
        return $this;
    }

    public function makeInfo() : self
    {
        $this->getButtonInstance()->makeInfo();
        return $this;
    }

    public function makeInverse() : self
    {
        $this->getButtonInstance()->makeInverse();
        return $this;
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function click(string $statement) : self
    {
        $this->getButtonInstance()->click($statement);
        return $this;
    }

    /**
     * @param string $url
     * @param string $target
     * @return $this
     */
    public function link(string $url, string $target = '') : self
    {
        $this->getButtonInstance()->link($url, $target);
        return $this;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $text
     * @return $this
     */
    public function setLoadingText($text) : self
    {
        $this->getButtonInstance()->setLoadingText($text);
        return $this;
    }

    public function getTooltip() : string
    {
        return $this->getButtonInstance()->getTooltip();
    }

    /**
     * NOTE: This is not type hinted on purpose
     * to stay compatible with the
     * `HTML_Common2::hasClass()` method.
     *
     * @param string $name
     * @return bool
     */
    public function hasClass(string $name) : bool
    {
        return $this->getButtonInstance()->hasClass($name);
    }

    public function hasClasses() : bool
    {
        return $this->getButtonInstance()->hasClasses();
    }

    public function addClasses(array $names) : self
    {
        $this->getButtonInstance()->addClasses($names);
        return $this;
    }

    public function getClasses() : array
    {
        return $this->getButtonInstance()->getClasses();
    }

    public function classesToString() : string
    {
        return $this->getButtonInstance()->classesToString();
    }

    public function classesToAttribute() : string
    {
        return $this->getButtonInstance()->classesToAttribute();
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $message
     * @param bool $withInput
     * @return $this
     * @throws UI_Exception
     */
    public function makeConfirm($message, bool $withInput = false) : self
    {
        $this->getButtonInstance()->makeConfirm($message, $withInput);
        return $this;
    }

    public function getConfirmMessage() : UI_ClientConfirmable_Message
    {
        return $this->getButtonInstance()->getConfirmMessage();
    }

    public function getURL() : string
    {
        return $this->getButtonInstance()->getURL();
    }

    public function isClickable() : bool
    {
        return $this->getButtonInstance()->isClickable();
    }

    public function isLinked() : bool
    {
        return $this->getButtonInstance()->isLinked();
    }

    public function getJavascript() : string
    {
        return $this->getButtonInstance()->getJavascript();
    }

    public function isConfirm() : bool
    {
        return $this->getButtonInstance()->isConfirm();
    }

    public function isDangerous() : bool
    {
        return $this->getButtonInstance()->isDangerous();
    }

    public function setIcon(?UI_Icon $icon) : self
    {
        $this->getButtonInstance()->setIcon($icon);
        return $this;
    }

    public function hasIcon() : bool
    {
        return $this->getButtonInstance()->hasIcon();
    }

    public function getIcon() : ?UI_Icon
    {
        return $this->getButtonInstance()->getIcon();
    }

    public function isLocked() : bool
    {
        return $this->getButtonInstance()->isLocked();
    }

    public function getLockReason() : string
    {
        return $this->getButtonInstance()->getLockReason();
    }

    public function makeLockable($lockable = true) : self
    {
        $this->getButtonInstance()->makeLockable($lockable);
        return $this;
    }

    public function isLockable() : bool
    {
        return $this->getButtonInstance()->isLockable();
    }

    public function lock($reason) : self
    {
        $this->getButtonInstance()->lock($reason);
        return $this;
    }

    public function unlock() : self
    {
        $this->getButtonInstance()->unlock();
        return $this;
    }

    public function getPage() : UI_Page
    {
        return $this->getButtonInstance()->getPage();
    }

    public function getTheme() : UI_Themes_Theme
    {
        return $this->getButtonInstance()->getTheme();
    }

    public function getUI() : UI
    {
        return $this->getButtonInstance()->getUI();
    }

    public function getInstanceID() : string
    {
        return $this->getButtonInstance()->getInstanceID();
    }

    public function getRenderer() : UI_Themes_Theme_ContentRenderer
    {
        return $this->getButtonInstance()->getRenderer();
    }

    // endregion
}
