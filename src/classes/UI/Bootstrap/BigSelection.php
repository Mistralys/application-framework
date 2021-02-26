<?php

use AppUtils\Traits_Optionable;
use AppUtils\Interface_Optionable;

/**
 * 
 * @property UI_Bootstrap_BigSelection_Item[] $children
 *
 */
class UI_Bootstrap_BigSelection extends UI_Bootstrap implements Interface_Optionable
{
    use Traits_Optionable;
    
    public function getDefaultOptions() : array
    {
        return array(
            'emptyMessage' => '',
            'heightLimited' => false,
            'filteringEnabled' => false,
            'filteringThreshold' => 10
        );
    }
    
    protected function _render()
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
    * @param bool $limited
    * @return UI_Bootstrap_BigSelection
    * @see UI_Bootstrap_BigSelection::isHeightLimited()
    */
    public function makeHeightLimited(bool $limited=true) : UI_Bootstrap_BigSelection
    {
        return $this->setOption('heightLimited', $limited);
    }
    
   /**
    * Whether the list is limited in height.
    * 
    * @return bool
    * @see UI_Bootstrap_BigSelection::makeHeightLimited()
    */
    public function isHeightLimited() : bool
    {
        return $this->getBoolOption('heightLimited');
    }
    
   /**
    * Sets the message text to show when the list is empty.
    * 
    * @param string|number|UI_Renderable_Interface $message
    * @return UI_Bootstrap_BigSelection
    */
    public function setEmptyMessage($message) : UI_Bootstrap_BigSelection
    {
        return $this->setOption('emptyMessage', toString($message));
    }
    
   /**
    * Adds controls to filter the list by search terms.
    * 
    * @return UI_Bootstrap_BigSelection
    */
    public function enableFiltering(bool $enable=true) : UI_Bootstrap_BigSelection
    {
        return $this->setOption('filteringEnabled', $enable);
    }
    
   /**
    * Whethe the filtering widget should be shown (it also
    * depends on the filtering threshold, the minimum amount
    * of items to display it).
    * 
    * @return bool
    * @see UI_Bootstrap_BigSelection::setFilteringThreshold()
    */
    public function isFilteringEnabled() : bool
    {
        return $this->getBoolOption('filteringEnabled');
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
    * Counts the amount of items in the selection.
    * 
    * @return int
    */
    public function countItems() : int
    {
        return count($this->children);
    }
    
    public function getFilteringThreshold() : int
    {
        return $this->getIntOption('filteringThreshold');
    }
    
   /**
    * Sets the amount of items from which the filtering
    * widget is displayed, if filtering is enabled.
    * 
    * @param int $amount
    * @return UI_Bootstrap_BigSelection
    */
    public function setFilteringThreshold(int $amount) : UI_Bootstrap_BigSelection
    {
        return $this->setOption('filteringThreshold', $amount);
    }
    
    public function getEmptyMessage() : string
    {
        $message = $this->getStringOption('emptyMessage');
        
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
    
   /**
    * Adds a link to the list. Shortcut for adding the item and setting the link.
    * 
    * @param string|number|UI_Renderable_Interface $label
    * @param string $url
    * @return UI_Bootstrap_BigSelection_Item
    */
    public function addLink($label, string $url)
    {
        return $this->addItem($label)->makeLinked($url);
    }
    
   /**
    * Adds an item to the list. Can be further configured
    * via the returned instance.
    * 
    * @param string|number|UI_Renderable_Interface $label
    * @return UI_Bootstrap_BigSelection_Item
    */
    public function addItem($label) : UI_Bootstrap_BigSelection_Item
    {
        $item = new UI_Bootstrap_BigSelection_Item($this->ui);
        $item->setLabel($label);
        
        $this->appendChild($item);
        
        return $item;
    }
    
   /**
    * Retrieves all items that have been added.
    * 
    * @return UI_Bootstrap_BigSelection_Item[]
    */
    public function getItems()
    {
        return $this->children;
    }
}
