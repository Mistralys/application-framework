<?php

class UI_Page_Breadcrumb
{
    protected $id;

    /**
     * @var UI_Page
     */
    protected $page;

    protected $items = array();

    public function __construct(UI_Page $page, $id)
    {
        $this->id = $id;
        $this->page = $page;
    }

    /**
     * Appends an item to the breadcrumb navigation. Returns the
     * item instance, use this to configure where it should be
     * linked.
     *
     * @param string $label
     * @return UI_Page_Breadcrumb_Item
     */
    public function appendItem($label)
    {
        require_once 'UI/Page/Breadcrumb/Item.php';

        $item = new UI_Page_Breadcrumb_Item($this, $label);
        $this->items[] = $item;

        return $item;
    }

    /**
     * Appends a breadcrumb item for the specified administration
     * area instance. The optional request parameters are added to
     * the generated URL.
     *
     * @param Application_Admin_Area $area
     * @param array $params
     */
    public function appendArea(Application_Admin_Area $area, $params = array())
    {
        $this->appendItem($area->getNavigationTitle())->makeLinkedFromArea($area, $params);
    }

    public function render()
    {
        // only render the breadcrumb when we have more than 1 item.
        $total = count($this->items);
        if ($total < 2) {
            return '';
        }

        return $this->page->renderTemplate(
            'frame.breadcrumb',
            array(
                'breadcrumb' => $this
            )
        );
    }

    /**
     * Retrieves an indexed array with all elements in the
     * breadcrumb, from first to last.
     *
     * @return UI_Page_Breadcrumb_Item[]
     */
    public function getItems()
    {
        $this->updateItems();

        return $this->items;
    }

    /**
     * Updates all items in the current collection so they
     * know in which position they are.
     */
    protected function updateItems()
    {
        /* @var $item UI_Page_Breadcrumb_Item */

        $total = count($this->items);
        for ($i = 0; $i < $total; $i++) {
            $item = $this->items[$i];
            $item->reset();

            if ($i == 0) {
                $item->setFirst();
            }

            if ($i == $total - 1) {
                $item->setLast();
            }
        }
    }

    public function display()
    {
        echo $this->render();
    }

    /**
     * @return UI_Page
     */
    public function getPage()
    {
        return $this->page;
    }
    
   /**
    * Retrieves the last item in the breadcrumb, or null
    * if there are no items in the breadcrumb.
    *  
    * @return UI_Page_Breadcrumb_Item|NULL
    */
    public function getLastItem()
    {
        $items = $this->items;
        return array_pop($items);
    }
}