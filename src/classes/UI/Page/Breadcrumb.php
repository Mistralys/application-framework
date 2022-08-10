<?php

declare(strict_types=1);

class UI_Page_Breadcrumb implements UI_Renderable_Interface
{
    use UI_Traits_RenderableGeneric;

    protected string $id;
    protected UI_Page $page;

    /**
     * @var UI_Page_Breadcrumb_Item[]
     */
    protected array $items = array();

    public function __construct(UI_Page $page, string $id)
    {
        $this->id = $id;
        $this->page = $page;
    }

    public function getUI() : UI
    {
        return $this->page->getUI();
    }

    /**
     * Appends an item to the breadcrumb navigation. Returns the
     * item instance, use this to configure where it should be
     * linked.
     *
     * @param string|number|UI_Renderable_Interface|NULL $label
     * @return UI_Page_Breadcrumb_Item
     * @throws UI_Exception
     */
    public function appendItem($label) : UI_Page_Breadcrumb_Item
    {
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
     * @param array<string,mixed> $params
     * @return UI_Page_Breadcrumb_Item
     * @throws UI_Exception
     */
    public function appendArea(Application_Admin_Area $area, array $params = array()) : UI_Page_Breadcrumb_Item
    {
        return $this->appendItem($area->getNavigationTitle())
            ->makeLinkedFromArea($area, $params);
    }

    public function render() : string
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
    public function getItems() : array
    {
        $this->updateItems();

        return $this->items;
    }

    /**
     * Updates all items in the current collection, so they
     * know in which position they are.
     */
    protected function updateItems() : void
    {
        $total = count($this->items);

        foreach ($this->items as $i => $item)
        {
            $item->reset();

            if ($i === 0) {
                $item->setFirst();
            }

            if ($i === ($total - 1)) {
                $item->setLast();
            }
        }
    }

    public function display() : void
    {
        echo $this->render();
    }

    public function getPage() : UI_Page
    {
        return $this->page;
    }
    
   /**
    * Retrieves the last item in the breadcrumb, or null
    * if there are no items in the breadcrumb.
    *  
    * @return UI_Page_Breadcrumb_Item|NULL
    */
    public function getLastItem() : ?UI_Page_Breadcrumb_Item
    {
        return array_value_get_last($this->items);
    }
}
