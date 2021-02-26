<?php

class UI_QuickSelector_Group extends UI_QuickSelector_Container
{
    public function render(): string
    {
        $html =
        '<optgroup label="'.$this->getLabel().'">';

        $items = $this->getItems();

        if($this->selector->isSortingEnabled()) {
            usort($items, array($this, 'handle_sortItems'));
        }

        foreach($items as $item)
        {
            $html .= $item->render();
        }

        $html .=
        '</optgroup>';

        return $html;
    }
}