<?php

declare(strict_types=1);

namespace UI\Bootstrap\BigSelection;

use Application_Interfaces_Iconizable;
use Application_Traits_Iconizable;
use UI_Bootstrap;

/**
 * @property BigSelectionWidget $parent
 */
abstract class BaseItem extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
    public const string CLASS_NAME_ENTRY = BigSelectionCSS::ITEM_ENTRY;

    use Application_Traits_Iconizable;

    protected string $referenceID = '';

    abstract protected function resolveSearchWords(): string;

    /**
     * Sets an optional reference ID for the item, which can be
     * used to uniquely identify it.
     *
     * For example, when creating a list of products, using the
     * product ID as reference ID will allow finding the item
     * again using the product ID.
     *
     * @param string $referenceID
     * @return $this
     */
    public function setReferenceID(string $referenceID) : self
    {
        $this->referenceID = $referenceID;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceID(): string
    {
        return $this->referenceID;
    }
}
