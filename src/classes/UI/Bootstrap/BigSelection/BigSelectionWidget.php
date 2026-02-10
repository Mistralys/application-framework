<?php

declare(strict_types=1);

namespace UI\Bootstrap\BigSelection;

use Application_Exception;
use AppUtils\Interfaces\OptionableInterface;
use AppUtils\Interfaces\StringableInterface;
use AppUtils\NumberInfo;
use AppUtils\Traits\OptionableTrait;
use template_default_ui_bootstrap_big_selection;
use UI\AdminURLs\AdminURLInterface;
use UI\Bootstrap\BigSelection\Item\HeaderItem;
use UI\Bootstrap\BigSelection\Item\RegularItem;
use UI\Bootstrap\BigSelection\Item\SeparatorItem;
use UI_Bootstrap;
use UI_Exception;
use UI_Renderable_Interface;
use function AppUtils\parseNumber;

/**
 * @package Application
 * @subpackage User Interface
 *
 * @property BaseItem[] $children
 *
 * @see BigSelectionCSS All CSS classes used by the widget and theme resource files.
 * @see template_default_ui_bootstrap_big_selection Template that renders the widget.
 */
class BigSelectionWidget extends UI_Bootstrap implements OptionableInterface
{
    use OptionableTrait;

    public const string OPTION_FILTERING_THRESHOLD = 'filteringThreshold';
    public const string OPTION_FILTERING_ENABLED = 'filteringEnabled';
    public const string OPTION_EMPTY_MESSAGE = 'emptyMessage';
    public const string OPTION_HEIGHT_LIMITED = 'heightLimited';

    public function getDefaultOptions(): array
    {
        return array(
            self::OPTION_EMPTY_MESSAGE => '',
            self::OPTION_HEIGHT_LIMITED => null,
            self::OPTION_FILTERING_ENABLED => false,
            self::OPTION_FILTERING_THRESHOLD => 10
        );
    }

    protected function _render(): string
    {
        if (empty($this->children)) {
            return $this->ui->createMessage($this->getEmptyMessage())
                ->enableIcon()
                ->makeInfo()
                ->makeNotDismissable()
                ->render();
        }

        return $this->ui->createTemplate(template_default_ui_bootstrap_big_selection::class)
            ->setVar('selection', $this)
            ->render();
    }

    /**
     * Makes the list scroll if it becomes too long.
     *
     * @param string|int|float|NULL $maxHeight Height value parsable by {@see NumberInfo}. Set to NULL to disable.
     * @return $this
     * @see BigSelectionWidget::isHeightLimited()
     */
    public function makeHeightLimited($maxHeight): self
    {
        return $this->setOption(self::OPTION_HEIGHT_LIMITED, $maxHeight);
    }

    public function getMaxHeight(): ?NumberInfo
    {
        $maxHeight = parseNumber($this->getOption(self::OPTION_HEIGHT_LIMITED));

        if (!$maxHeight->isZeroOrEmpty()) {
            return $maxHeight;
        }

        return null;
    }

    /**
     * Whether the list is limited in height.
     *
     * @return bool
     * @see BigSelectionWidget::makeHeightLimited()
     */
    public function isHeightLimited(): bool
    {
        return $this->getOption(self::OPTION_HEIGHT_LIMITED) !== null;
    }

    /**
     * Sets the message text to show when the list is empty.
     *
     * @param string|number|UI_Renderable_Interface $message
     * @return BigSelectionWidget
     */
    public function setEmptyMessage($message): BigSelectionWidget
    {
        return $this->setOption(self::OPTION_EMPTY_MESSAGE, toString($message));
    }

    /**
     * Adds controls to filter the list by search terms.
     *
     * @param bool $enable
     * @return BigSelectionWidget
     */
    public function enableFiltering(bool $enable = true): BigSelectionWidget
    {
        return $this->setOption(self::OPTION_FILTERING_ENABLED, $enable);
    }

    /**
     * Whether the filtering widget should be shown (it also
     * depends on the filtering threshold, the minimum number
     * of items to display it).
     *
     * @return bool
     * @see BigSelectionWidget::setFilteringThreshold()
     */
    public function isFilteringEnabled(): bool
    {
        return $this->getBoolOption(self::OPTION_FILTERING_ENABLED);
    }

    /**
     * Whether filtering is enabled, and there are enough
     * items to actually display the filtering widget.
     *
     * @return bool
     */
    public function isFilteringInUse(): bool
    {
        return $this->isFilteringEnabled() && $this->countItems() >= $this->getFilteringThreshold();
    }

    /**
     * Counts the number of items in the selection.
     *
     * @return int
     */
    public function countItems(): int
    {
        return count($this->children);
    }

    public function getFilteringThreshold(): int
    {
        return $this->getIntOption(self::OPTION_FILTERING_THRESHOLD);
    }

    /**
     * Sets the number of items from which the filtering
     * widget is displayed if filtering is enabled.
     *
     * @param int $amount
     * @return BigSelectionWidget
     */
    public function setFilteringThreshold(int $amount): BigSelectionWidget
    {
        return $this->setOption(self::OPTION_FILTERING_THRESHOLD, $amount);
    }

    public function getEmptyMessage(): string
    {
        $message = $this->getStringOption(self::OPTION_EMPTY_MESSAGE);

        if (!empty($message)) {
            return $message;
        }

        return t('No items found.');
    }

    /**
     * Makes the items smaller.
     *
     * @return BigSelectionWidget
     */
    public function makeSmall(): BigSelectionWidget
    {
        $this->addClass(BigSelectionCSS::WIDGET_SIZE_SMALL);
        return $this;
    }

    // region: Adding items

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @return RegularItem
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function prependItem($label): RegularItem
    {
        $item = $this->createRegularItem($label);

        $this->prependChild($item);

        return $item;
    }

    /**
     * @param string|number|UI_Renderable_Interface $title
     * @return HeaderItem
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function prependHeader($title): HeaderItem
    {
        $item = $this->createHeaderItem($title);

        $this->prependChild($item);

        return $item;
    }

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @param string $url
     * @return RegularItem
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function prependLink($label, string $url): RegularItem
    {
        return $this->prependItem($label)->makeLinked($url);
    }

    /**
     * Adds a link to the list. Shortcut for adding the item and setting the link.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @param string|AdminURLInterface $url
     * @return RegularItem
     */
    public function addLink($label, $url): RegularItem
    {
        return $this->addItem($label)->makeLinked($url);
    }

    /**
     * Adds an item to the list.
     * Can be further configured via the returned instance.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @return RegularItem
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function addItem($label): RegularItem
    {
        $item = $this->createRegularItem($label);

        $this->appendChild($item);

        return $item;
    }

    /**
     * @param string|int|float|StringableInterface $title
     * @return HeaderItem
     * @throws Application_Exception
     */
    public function addHeader(string|int|float|StringableInterface $title): HeaderItem
    {
        $item = $this->createHeaderItem($title);

        $this->appendChild($item);

        return $item;
    }

    /**
     * Adds a separator line to the list.
     *
     * @return SeparatorItem
     * @throws UI_Exception
     */
    public function addSeparator(): SeparatorItem
    {
        $item = new SeparatorItem($this->ui);

        $this->appendChild($item);

        return $item;
    }

    /**
     * Prepends a separator line to the list.
     *
     * @return SeparatorItem
     * @throws UI_Exception
     */
    public function prependSeparator(): SeparatorItem
    {
        $item = new SeparatorItem($this->ui);

        $this->prependChild($item);

        return $item;
    }

    // endregion

    /**
     * Retrieves all items that have been added.
     *
     * @return BaseItem[]
     */
    public function getItems(): array
    {
        return $this->children;
    }

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @return RegularItem
     * @throws UI_Exception
     */
    private function createRegularItem($label): RegularItem
    {
        $item = new RegularItem($this->ui);
        $item->setLabel($label);
        return $item;
    }

    /**
     * @param string|int|float|StringableInterface $title
     * @return HeaderItem
     * @throws UI_Exception
     */
    private function createHeaderItem(string|int|float|StringableInterface $title): HeaderItem
    {
        $item = new HeaderItem($this->ui);
        $item->setTitle($title);
        return $item;
    }
}
