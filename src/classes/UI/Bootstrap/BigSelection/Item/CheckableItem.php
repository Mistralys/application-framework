<?php

declare(strict_types=1);

namespace UI\Bootstrap\BigSelection\Item;

use AppUtils\OutputBuffering;
use UI;
use UI\Bootstrap\BigSelection\BaseItem;
use UI\Bootstrap\BigSelection\BigSelectionCSS;
use UI\Bootstrap\BigSelection\BigSelectionWidget;

/**
 * A checkable item for the BigSelection widget.
 *
 * Renders as a list entry with a hidden input that participates in
 * form submission when selected. The visual checkbox indicator consists
 * of two server-side-rendered FontAwesome icons ({@see BigSelectionCSS::CHECKBOX_ICON_UNCHECKED}
 * and {@see BigSelectionCSS::CHECKBOX_ICON_CHECKED}) inside the checkbox span.
 * CSS toggles which icon is visible based on the `active` class on the parent
 * `<li>`. JavaScript only toggles that `active` class and the hidden input's
 * `disabled` attribute on user click — it has no knowledge of icon elements.
 *
 * **Constraint:** `$this->parent` is only valid after the item has been
 * appended to a {@see BigSelectionWidget} via {@see BigSelectionWidget::addCheckable()}
 * or {@see BigSelectionWidget::prependCheckable()}. Do not call `_render()`
 * or access `$this->parent` before the item has been attached to a widget.
 *
 * @package Application
 * @subpackage User Interface
 *
 * @property BigSelectionWidget $parent
 *
 * @see BigSelectionWidget::addCheckable()
 * @see BigSelectionCSS::ITEM_CHECKABLE
 */
class CheckableItem extends BaseItem
{
    private string $label = '';
    private string $value = '';
    private string $description = '';
    private bool $selected = false;

    /**
     * Sets the form value submitted when this item is checked.
     *
     * @param string $value
     * @return $this
     */
    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Returns the form value for this item.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Sets the display label of the item.
     *
     * **The label is output as raw HTML markup** — it is not HTML-escaped before
     * rendering. This allows formatted labels (e.g. `<strong>`, `<em>`) but means
     * callers must never pass unescaped user-supplied content directly. Use
     * `htmlspecialchars()` or a suitable escaping helper if the label text
     * originates from user input.
     *
     * Note: this differs from {@see self::setDescription()}, whose value is
     * HTML-escaped with `htmlspecialchars()` before output.
     *
     * @param string|number|\UI_Renderable_Interface $label
     * @return $this
     */
    public function setLabel($label): self
    {
        $this->label = toString($label);
        return $this;
    }

    /**
     * Returns the display label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets an optional description shown below the label.
     *
     * @param string|number|\UI_Renderable_Interface $text
     * @return $this
     */
    public function setDescription($text): self
    {
        $this->description = toString($text);
        return $this;
    }

    /**
     * Returns the description text, or an empty string if none was set.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Marks the item as pre-selected (checked on initial render).
     *
     * Pre-selected items receive the `active` CSS class on the <li>
     * and their hidden input is rendered without the `disabled` attribute,
     * so the value is included in the form submission without any user
     * interaction.
     *
     * @return $this
     */
    public function makeSelected(): self
    {
        $this->selected = true;
        return $this;
    }

    /**
     * Returns whether the item is currently selected.
     *
     * @return bool
     */
    public function isSelected(): bool
    {
        return $this->selected;
    }

    /**
     * Returns the combined label and description text, stripped of HTML,
     * for use in the `data-terms` filtering attribute.
     *
     * The result is HTML-encoded so it can be safely embedded in a
     * double-quoted HTML attribute. JavaScript's `getAttribute()` decodes
     * the entities automatically, so the filtering behaviour is unaffected.
     *
     * @return string
     */
    protected function resolveSearchWords(): string
    {
        $words = strip_tags($this->label);

        if (!empty($this->description)) {
            $words .= ' ' . strip_tags($this->description);
        }

        return htmlspecialchars($words, ENT_QUOTES, 'UTF-8');
    }

    protected function _render(): string
    {
        $this->addClass(self::CLASS_NAME_ENTRY);
        $this->addClass(BigSelectionCSS::ITEM_CHECKABLE);

        if ($this->selected) {
            $this->addClass(BigSelectionCSS::STATE_ACTIVE);
        }

        $searchAtt = '';
        if ($this->parent->isFilteringInUse()) {
            $searchAtt = ' data-terms="' . $this->resolveSearchWords() . '"';
        }

        $disabledAttr = $this->selected ? '' : ' disabled';
        $formName = htmlspecialchars($this->parent->getFormName(), ENT_QUOTES, 'UTF-8');
        $value = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');

        $labelHtml = $this->renderIconLabel($this->label);

        OutputBuffering::start();

        ?>
        <li class="<?php echo implode(' ', $this->classes) ?>"<?php echo $searchAtt ?>>
            <input type="hidden" name="<?php echo $formName ?>[]" value="<?php echo $value ?>"<?php echo $disabledAttr ?>>
            <a href="javascript:void(0)" class="<?php echo BigSelectionCSS::ANCHOR ?>">
                <span class="<?php echo BigSelectionCSS::CHECKBOX_ICON ?>">
                    <?php echo UI::icon()->itemInactive()->addClass(BigSelectionCSS::CHECKBOX_ICON_UNCHECKED) ?>
                    <?php echo UI::icon()->itemActive()->addClass(BigSelectionCSS::CHECKBOX_ICON_CHECKED) ?>
                </span>
                <span class="<?php echo BigSelectionCSS::LABEL ?>">
                    <?php echo $labelHtml ?>
                </span>
                <?php if (!empty($this->description)) { ?>
                <span class="<?php echo BigSelectionCSS::DESCRIPTION ?>">
                    <?php echo htmlspecialchars($this->description, ENT_QUOTES, 'UTF-8') ?>
                </span>
                <?php } ?>
            </a>
        </li>
        <?php

        return OutputBuffering::get();
    }
}
