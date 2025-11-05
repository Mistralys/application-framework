<?php
/**
 * @package User Interface
 * @subpackage Traits
 */

declare(strict_types=1);

namespace UI\Traits;

use UI;
use UI\AdminURLs\AdminURLInterface;
use UI_Button;
use UI_ClientConfirmable_Message;
use UI_Exception;
use UI_Icon;
use UI_Interfaces_Button;
use UI_Page;
use UI_Renderable_Interface;
use UI_Themes_Theme;
use UI_Themes_Theme_ContentRenderer;

/**
 * Trait that can be used to add all button interface methods,
 * without extending the {@see UI_Button} class. Instead, all
 * methods are wrapper methods around a button instance.
 *
 * ## Usage
 *
 * - Use this trait.
 * - Implement the matching interface {@see ButtonDecoratorInterface}.
 * - Implement the {@see self::_getButtonInstance()} method.
 * - When rendering, use the button instance.
 *
 * @package User Interface
 * @subpackage Traits
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see ButtonDecoratorInterface
 */
trait ButtonDecoratorTrait
{
    private ?UI_Button $buttonInstance = null;

    final public function getButtonInstance(): UI_Button
    {
        if (!isset($this->buttonInstance)) {
            $this->buttonInstance = $this->_getButtonInstance();
        }

        return $this->buttonInstance;
    }

    abstract protected function _getButtonInstance(): UI_Button;

    public function getLabel(): string
    {
        return $this->getButtonInstance()->getLabel();
    }

    /**
     * Turns the button into a submit button.
     *
     * @param string $name
     * @param string|int|float|UI_Renderable_Interface $value
     * @return $this
     */
    public function makeSubmit(string $name, $value): self
    {
        $this->getButtonInstance()->makeSubmit($name, $value);
        return $this;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $title
     * @return $this
     * @throws UI_Exception
     */
    public function setTitle($title): self
    {
        $this->getButtonInstance()->setTitle($title);
        return $this;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $tooltip
     * @return $this
     * @throws UI_Exception
     */
    public function setTooltip($tooltip): self
    {
        $this->getButtonInstance()->setTooltip($tooltip);
        return $this;
    }

    public function makeDangerous(): self
    {
        $this->getButtonInstance()->makeDangerous();
        return $this;
    }

    public function makePrimary(): self
    {
        $this->getButtonInstance()->makePrimary();
        return $this;
    }

    public function makeSuccess(): self
    {
        $this->getButtonInstance()->makeSuccess();
        return $this;
    }

    public function makeDeveloper(): self
    {
        $this->getButtonInstance()->makeDeveloper();
        return $this;
    }

    public function makeWarning(): self
    {
        $this->getButtonInstance()->makeWarning();
        return $this;
    }

    public function makeInfo(): self
    {
        $this->getButtonInstance()->makeInfo();
        return $this;
    }

    public function makeInverse(): self
    {
        $this->getButtonInstance()->makeInverse();
        return $this;
    }

    public function makeLayout(string $layoutID): self
    {
        $this->getButtonInstance()->makeLayout($layoutID);
        return $this;
    }

    public function makeActiveLayout(string $layoutID): self
    {
        $this->getButtonInstance()->makeActiveLayout($layoutID);
        return $this;
    }

    public function setID(string $id): self
    {
        $this->getButtonInstance()->setID($id);
        return $this;
    }

    public function setLabel($label): self
    {
        $this->getButtonInstance()->setLabel($label);
        return $this;
    }

    public function makeActive(bool $active = true): self
    {
        $this->getButtonInstance()->makeActive($active);
        return $this;
    }

    public function isActive(): bool
    {
        return $this->getButtonInstance()->isActive();
    }

    public function disable($reason = ''): UI_Interfaces_Button
    {
        $this->getButtonInstance()->disable($reason);
        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->getButtonInstance()->isDisabled();
    }

    public function getID(): string
    {
        return $this->getButtonInstance()->getID();
    }

    public function isSubmittable(): bool
    {
        return $this->getButtonInstance()->isSubmittable();
    }

    /**
     * @param string $statement
     * @return $this
     */
    public function click(string $statement): self
    {
        $this->getButtonInstance()->click($statement);
        return $this;
    }

    /**
     * @param string|AdminURLInterface $url
     * @param string $target
     * @return $this
     */
    public function link($url, string $target = ''): self
    {
        $this->getButtonInstance()->link($url, $target);
        return $this;
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $text
     * @return $this
     */
    public function setLoadingText($text): self
    {
        $this->getButtonInstance()->setLoadingText($text);
        return $this;
    }

    public function getTooltip(): string
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
    public function hasClass(string $name): bool
    {
        return $this->getButtonInstance()->hasClass($name);
    }

    public function hasClasses(): bool
    {
        return $this->getButtonInstance()->hasClasses();
    }

    public function addClasses(array $names): self
    {
        $this->getButtonInstance()->addClasses($names);
        return $this;
    }

    public function getClasses(): array
    {
        return $this->getButtonInstance()->getClasses();
    }

    public function addClass($name): self
    {
        $this->getButtonInstance()->addClass($name);
        return $this;
    }

    public function removeClass(string $name): self
    {
        $this->getButtonInstance()->removeClass($name);
        return $this;
    }

    public function classesToString(): string
    {
        return $this->getButtonInstance()->classesToString();
    }

    public function classesToAttribute(): string
    {
        return $this->getButtonInstance()->classesToAttribute();
    }

    /**
     * @param string|number|UI_Renderable_Interface|NULL $message
     * @param bool $withInput
     * @return $this
     * @throws UI_Exception
     */
    public function makeConfirm($message, bool $withInput = false): self
    {
        $this->getButtonInstance()->makeConfirm($message, $withInput);
        return $this;
    }

    public function getConfirmMessage(): UI_ClientConfirmable_Message
    {
        return $this->getButtonInstance()->getConfirmMessage();
    }

    public function getURL(): string
    {
        return $this->getButtonInstance()->getURL();
    }

    public function isClickable(): bool
    {
        return $this->getButtonInstance()->isClickable();
    }

    public function isLinked(): bool
    {
        return $this->getButtonInstance()->isLinked();
    }

    public function getJavascript(): string
    {
        return $this->getButtonInstance()->getJavascript();
    }

    public function isConfirm(): bool
    {
        return $this->getButtonInstance()->isConfirm();
    }

    public function isDangerous(): bool
    {
        return $this->getButtonInstance()->isDangerous();
    }

    public function setIcon(?UI_Icon $icon): self
    {
        $this->getButtonInstance()->setIcon($icon);
        return $this;
    }

    public function hasIcon(): bool
    {
        return $this->getButtonInstance()->hasIcon();
    }

    public function getIcon(): ?UI_Icon
    {
        return $this->getButtonInstance()->getIcon();
    }

    public function isLocked(): bool
    {
        return $this->getButtonInstance()->isLocked();
    }

    public function getLockReason(): string
    {
        return $this->getButtonInstance()->getLockReason();
    }

    public function makeLockable($lockable = true): self
    {
        $this->getButtonInstance()->makeLockable($lockable);
        return $this;
    }

    public function isLockable(): bool
    {
        return $this->getButtonInstance()->isLockable();
    }

    public function lock($reason): self
    {
        $this->getButtonInstance()->lock($reason);
        return $this;
    }

    public function unlock(): self
    {
        $this->getButtonInstance()->unlock();
        return $this;
    }

    public function getPage(): UI_Page
    {
        return $this->getButtonInstance()->getPage();
    }

    public function getTheme(): UI_Themes_Theme
    {
        return $this->getButtonInstance()->getTheme();
    }

    public function getUI(): UI
    {
        return $this->getButtonInstance()->getUI();
    }

    public function getInstanceID(): string
    {
        return $this->getButtonInstance()->getInstanceID();
    }

    public function getRenderer(): UI_Themes_Theme_ContentRenderer
    {
        return $this->getButtonInstance()->getRenderer();
    }
}
