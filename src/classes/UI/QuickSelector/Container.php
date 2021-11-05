<?php

abstract class UI_QuickSelector_Container extends UI_QuickSelector_Base
{
    public const ERROR_ITEM_ID_ALREADY_EXISTS = 997581001;

   /**
    * @var UI_QuickSelector_Base[]
    */
    protected $items = array();
    
   /**
    * Adds a new item to the selectable items collection.
    *
    * @param string $id Can be any number or string used to identify the item.
    * @param string $label
    * @param string $url The URL to jump to if the user selects the item.
    * @return UI_QuickSelector_Container
    * @throws Application_Exception
    */
    public function addItem(string $id, string $label, string $url) : UI_QuickSelector_Container
    {
        if($this->hasItemID($id)) {
            throw new Application_Exception(
                'Duplicate item ID',
                sprintf(
                    'An item with the ID [%s] has already been added.',
                    $id
                ),
                self::ERROR_ITEM_ID_ALREADY_EXISTS
            );
        }
    
        $this->items[] = new UI_QuickSelector_Item(
            $this->selector,
            $id,
            $label,
            $url
        );
    
        return $this;
    }

    public function addGroup(string $label) : UI_QuickSelector_Group
    {
        $group = new UI_QuickSelector_Group($this->selector, 'group-'.nextJSID(), $label);
        $this->items[] = $group;
        return $group;
    }

    /**
     * Checks whether an item with the specified ID already exists.
     * @param string $id
     * @return boolean
     */
    public function hasItemID(string $id) : bool
    {
        $total = count($this->items);

        for($i=0; $i<$total; $i++)
        {
            $item = $this->items[$i];
            
            if($item instanceof UI_QuickSelector_Container) {
                if($item->hasItemID($id)) {
                    return true;
                }
                continue;
            }
            
            if($item->getID() === $id) {
                return true;
            }
        }
    
        return false;
    }
    
   /**
    * @return UI_QuickSelector_Base[]
    */
    public function getItems() : array
    {
        return $this->items;
    }
    
    public function countItems() : int
    {
        $amount = 0;
        $total = count($this->items);
        for($i=0; $i<$total; $i++)
        {
            $item = $this->items[$i];
            
            if($item instanceof UI_QuickSelector_Container) {
                $amount += $item->countItems();
                continue;
            }
            
            $amount++;
        }
        
        return $amount;
    }

    public function handle_sortItems(UI_QuickSelector_Base $a, UI_QuickSelector_Base $b)
    {
        return strnatcasecmp($a->getLabel(), $b->getLabel());
    }
}
