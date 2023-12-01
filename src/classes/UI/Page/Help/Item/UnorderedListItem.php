<?php

namespace UI\Page\Help\Item;

use AppUtils\Interfaces\StringableInterface;
use UI_Exception;
use UI_Page_Help_Item;

class UnorderedListItem extends UI_Page_Help_Item
{
    /**
     * @var string[]
     */
    private array $items = array();

    protected function _render() : string
    {
        if(empty($this->items)) {
            return '';
        }

        $this->addClass('help-list-unordered');

        return sprintf(
            '<ul class="%s">%s</ul>',
            $this->classesToString(),
            $this->renderItems()
        );
    }

    private function renderItems() : string
    {
        $list = array();

        foreach($this->items as $item) {
            $list[] = '<li class="help-list-item">'.$item.'</li>';
        }

        return implode('', $list);
    }

    /**
     * @param string|int|float|StringableInterface|NULL $item
     * @return $this
     * @throws UI_Exception
     */
    public function addItem($item) : self
    {
        $this->items[] = toString($item);
        return $this;
    }

    /**
     * @param array<int,string|int|float|StringableInterface|NULL>|string|int|float|StringableInterface|NULL ...$items
     * @return $this
     * @throws UI_Exception
     */
    public function addItems(...$items) : self
    {
        foreach($items as $item) {
            if(is_array($item)) {
                $this->addItems($item);
                continue;
            }

            $this->addItem($item);
        }

        return $this;
    }

    public function getDefaultOptions() : array
    {
        return array(
            'text' => ''
        );
    }
}
