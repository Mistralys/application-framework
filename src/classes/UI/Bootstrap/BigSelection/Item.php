<?php

declare(strict_types=1);

/**
 * @property UI_Bootstrap_BigSelection $parent
 */
abstract class UI_Bootstrap_BigSelection_Item extends UI_Bootstrap implements Application_Interfaces_Iconizable
{
    const CLASS_NAME_ENTRY = 'bigselection-entry';

    use Application_Traits_Iconizable;

    protected $referenceID = '';

    abstract protected function resolveSearchWords() : string;

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
    public function setReferenceID(string $referenceID)
    {
        $this->referenceID = $referenceID;
        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceID() : string
    {
        return $this->referenceID;
    }
}
