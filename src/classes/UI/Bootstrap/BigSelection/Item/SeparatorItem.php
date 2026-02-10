<?php

declare(strict_types=1);

namespace UI\Bootstrap\BigSelection\Item;

use UI\Bootstrap\BigSelection\BaseItem;
use UI\Bootstrap\BigSelection\BigSelectionCSS;

class SeparatorItem extends BaseItem
{
    protected function resolveSearchWords(): string
    {
        return '';
    }

    protected function _render(): string
    {
        return '<li class="' . BigSelectionCSS::ITEM_SEPARATOR . '"><hr></li>';
    }
}
