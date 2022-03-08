<?php

use AppUtils\Interface_Classable;

interface UI_Interfaces_Button
    extends
    Application_Interfaces_Iconizable,
    Interface_Classable,
    Application_LockableItem_Interface,
    UI_Interfaces_ClientConfirmable
{
    /**
     * Styles the button as a button for a dangerous operation, like deleting records.
     *
     * @return $this
     */
    public function makeDangerous() : self;

    /**
     * @return $this
     */
    public function makePrimary() : self;

    /**
     * @return $this
     */
    public function makeSuccess() : self;

    /**
     * @return $this
     */
    public function makeDeveloper() : self;

    /**
     * @return $this
     */
    public function makeWarning() : self;

    /**
     * @return $this
     */
    public function makeInfo() : self;

    /**
     * @return $this
     */
    public function makeInverse() : self;

    /**
     * @param string $statement
     * @return $this
     */
    public function click(string $statement) : self;

    /**
     * @param string $url
     * @param string $target
     * @return $this
     */
    public function link(string $url, string $target='') : self;

    /**
     * @param string|number|UI_Renderable_Interface $tooltip
     * @return $this
     */
    public function setTooltip($tooltip) : self;

    /**
     * @param string|number|UI_Renderable_Interface $text
     * @return $this
     */
    public function setLoadingText($text) : self;

    /**
     * @param string $id
     * @return $this
     */
    public function setID(string $id) : self;

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel(string $label) : self;

    public function getLabel() : string;

    public function getID() : string;

    public function getTooltip() : string;
}