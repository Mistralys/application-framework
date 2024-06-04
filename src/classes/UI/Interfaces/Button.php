<?php

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Interfaces\StringableInterface;
use UI\AdminURLs\AdminURLInterface;

interface UI_Interfaces_Button
    extends
    Application_Interfaces_Iconizable,
    ClassableInterface,
    Application_LockableItem_Interface,
    UI_Interfaces_ClientConfirmable,
    UI_Interfaces_Conditional
{
    /**
     * @param string|number|UI_Renderable_Interface|NULL $reason
     * @return $this
     */
    public function disable($reason='') : self;

    public function isDisabled() : bool;

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
     * @param string|AdminURLInterface $url
     * @param string $target
     * @return $this
     */
    public function link($url, string $target='') : self;

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
     * @param string|number|StringableInterface|NULL $label
     * @return $this
     */
    public function setLabel($label) : self;

    public function getLabel() : string;

    public function getID() : string;

    public function getTooltip() : string;
}