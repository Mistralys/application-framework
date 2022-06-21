<?php

declare(strict_types=1);

namespace UI\Page\Navigation;

use UI_Page_Navigation_Item;

abstract class LinkItemBase extends UI_Page_Navigation_Item
{
    protected string $target = '';

    public function setTarget(string $target) : self
    {
        $this->target = $target;
        return $this;
    }

    public function makeNewTab() : self
    {
        return $this->setTarget('_blank');
    }
}
