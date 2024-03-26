<?php

declare(strict_types=1);

use AppUtils\Interfaces\OptionableInterface;
use AppUtils\NumberInfo;
use AppUtils\Traits\OptionableTrait;
use UI\AdminURLs\AdminURL;
use function AppUtils\parseNumber;

/**
 * @package Application
 * @subpackage User Interface
 *
 * @property UI_Bootstrap_BigSelection_Item[] $children
 * @see template_default_ui_bootstrap_big_selection
 */
class UI_Bootstrap_BigSelection extends UI_Bootstrap implements OptionableInterface
{
    use OptionableTrait;

    public const OPTION_FILTERING_THRESHOLD = 'filteringThreshold';
    public const OPTION_FILTERING_ENABLED = 'filteringEnabled';
    public const OPTION_EMPTY_MESSAGE = 'emptyMessage';
    public const OPTION_HEIGHT_LIMITED = 'heightLimited';

    public function getDefaultOptions() : array
    {
        return array(
            self::OPTION_EMPTY_MESSAGE => '',
            self::OPTION_HEIGHT_LIMITED => null,
            self::OPTION_FILTERING_ENABLED => false,
            self::OPTION_FILTERING_THRESHOLD => 10
        );
    }
    
    protected function _render() : string
    {
        if(empty($this->children)) 
        {
            return $this->ui->createMessage($this->getEmptyMessage())
                ->enableIcon()
                ->makeInfo()
                ->makeNotDismissable()
                ->render();
        }
        
        return $this->ui->createTemplate('ui/bootstrap/big-selection')
            ->setVar('selection', $this)
            ->render();
    }
    
   /**
    * Makes the list scroll if it becomes too long.
    *
    * @param string|int|float|NULL $maxHeight Height value parsable by {@see NumberInfo}. Set to NULL to disable.
    * @return UI_Bootstrap_BigSelection
    * @see UI_Bootstrap_BigSelection::isHeightLimited()
    */
    public function makeHeightLimited($maxHeight) : UI_Bootstrap_BigSelection
    {
        return $this->setOption(self::OPTION_HEIGHT_LIMITED, $maxHeight);
    }

    public function getMaxHeight() : ?NumberInfo
    {
        $maxHeight = parseNumber($this->getOption(self::OPTION_HEIGHT_LIMITED));

        if(!$maxHeight->isZeroOrEmpty()) {
            return $maxHeight;
        }

        return null;
    }
    
   /**
    * Whether the list is limited in height.
    * 
    * @return bool
    * @see UI_Bootstrap_BigSelection::makeHeightLimited()
    */
    public function isHeightLimited() : bool
    {
        return $this->getOption(self::OPTION_HEIGHT_LIMITED) !== null;
    }
    
   /**
    * Sets the message text to show when the list is empty.
    * 
    * @param string|number|UI_Renderable_Interface $message
    * @return UI_Bootstrap_BigSelection
    */
    public function setEmptyMessage($message) : UI_Bootstrap_BigSelection
    {
        return $this->setOption(self::OPTION_EMPTY_MESSAGE, toString($message));
    }

    /**
     * Adds controls to filter the list by search terms.
     *
     * @param bool $enable
     * @return UI_Bootstrap_BigSelection
     */
    public function enableFiltering(bool $enable=true) : UI_Bootstrap_BigSelection
    {
        return $this->setOption(self::OPTION_FILTERING_ENABLED, $enable);
    }
    
   /**
    * Whether the filtering widget should be shown (it also
    * depends on the filtering threshold, the minimum number
    * of items to display it).
    * 
    * @return bool
    * @see UI_Bootstrap_BigSelection::setFilteringThreshold()
    */
    public function isFilteringEnabled() : bool
    {
        return $this->getBoolOption(self::OPTION_FILTERING_ENABLED);
    }
    
   /**
    * Whether filtering is enabled, and there are enough
    * items to actually display the filtering widget.
    * 
    * @return bool
    */
    public function isFilteringInUse() : bool
    {
        return $this->isFilteringEnabled() && $this->countItems() >= $this->getFilteringThreshold();
    }
    
   /**
    * Counts the number of items in the selection.
    * 
    * @return int
    */
    public function countItems() : int
    {
        return count($this->children);
    }
    
    public function getFilteringThreshold() : int
    {
        return $this->getIntOption(self::OPTION_FILTERING_THRESHOLD);
    }
    
   /**
    * Sets the number of items from which the filtering
    * widget is displayed if filtering is enabled.
    * 
    * @param int $amount
    * @return UI_Bootstrap_BigSelection
    */
    public function setFilteringThreshold(int $amount) : UI_Bootstrap_BigSelection
    {
        return $this->setOption(self::OPTION_FILTERING_THRESHOLD, $amount);
    }
    
    public function getEmptyMessage() : string
    {
        $message = $this->getStringOption(self::OPTION_EMPTY_MESSAGE);
        
        if(!empty($message)) {
            return $message;
        }
        
        return t('No items found.');
    }
    
   /**
    * Makes the items smaller.
    * 
    * @return UI_Bootstrap_BigSelection
    */
    public function makeSmall() : UI_Bootstrap_BigSelection
    {
        $this->addClass('size-small');
        return $this;
    }

    // region: Adding items

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @return UI_Bootstrap_BigSelection_Item_Regular
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function prependItem($label) : UI_Bootstrap_BigSelection_Item_Regular
    {
        $item = $this->createRegularItem($label);

        $this->prependChild($item);

        return $item;
    }

    /**
     * @param string|number|UI_Renderable_Interface $title
     * @return UI_Bootstrap_BigSelection_Item_Header
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function prependHeader($title) : UI_Bootstrap_BigSelection_Item_Header
    {
        $item = $this->createHeaderItem($title);

        $this->prependChild($item);

        return $item;
    }

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @param string $url
     * @return UI_Bootstrap_BigSelection_Item_Regular
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function prependLink($label, string $url) : UI_Bootstrap_BigSelection_Item_Regular
    {
        return $this->prependItem($label)->makeLinked($url);
    }

   /**
    * Adds a link to the list. Shortcut for adding the item and setting the link.
    * 
    * @param string|number|UI_Renderable_Interface $label
    * @param string|AdminURL $url
    * @return UI_Bootstrap_BigSelection_Item_Regular
    */
    public function addLink($label, $url) : UI_Bootstrap_BigSelection_Item_Regular
    {
        return $this->addItem($label)->makeLinked($url);
    }

    /**
     * Adds an item to the list.
     * Can be further configured via the returned instance.
     *
     * @param string|number|UI_Renderable_Interface $label
     * @return UI_Bootstrap_BigSelection_Item_Regular
     * @throws Application_Exception
     * @throws UI_Exception
     */
    public function addItem($label) : UI_Bootstrap_BigSelection_Item_Regular
    {
        $item = $this->createRegularItem($label);

        $this->appendChild($item);
        
        return $item;
    }

    /**
     * @param string|number|UI_Renderable_Interface $title
     * @return UI_Bootstrap_BigSelection_Item_Header
     * @throws Application_Exception
     */
    public function addHeader($title) : UI_Bootstrap_BigSelection_Item_Header
    {
        $item = $this->createHeaderItem($title);

        $this->appendChild($item);

        return $item;
    }

    // endregion
    
   /**
    * Retrieves all items that have been added.
    * 
    * @return UI_Bootstrap_BigSelection_Item[]
    */
    public function getItems() : array
    {
        return $this->children;
    }

    /**
     * @param string|number|UI_Renderable_Interface $label
     * @return UI_Bootstrap_BigSelection_Item_Regular
     * @throws UI_Exception
     */
    private function createRegularItem($label) : UI_Bootstrap_BigSelection_Item_Regular
    {
        $item = new UI_Bootstrap_BigSelection_Item_Regular($this->ui);
        $item->setLabel($label);
        return $item;
    }

    /**
     * @param string|number|UI_Renderable_Interface $title
     * @return UI_Bootstrap_BigSelection_Item_Header
     * @throws UI_Exception
     */
    private function createHeaderItem($title) : UI_Bootstrap_BigSelection_Item_Header
    {
        $item = new UI_Bootstrap_BigSelection_Item_Header($this->ui);
        $item->setTitle($title);
        return $item;
    }
}
