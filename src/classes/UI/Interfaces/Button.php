<?php

use AppUtils\Interfaces\ClassableInterface;
use AppUtils\Interfaces\StringableInterface;
use UI\AdminURLs\AdminURLInterface;
use UI\Interfaces\ButtonLayoutInterface;

interface UI_Interfaces_Button
    extends
    Application_Interfaces_Iconizable,
    ClassableInterface,
    Application_LockableItem_Interface,
    UI_Interfaces_ClientConfirmable,
    UI_Interfaces_Conditional,
    ButtonLayoutInterface
{
    /**
     * @param string|number|UI_Renderable_Interface|NULL $reason
     * @return $this
     */
    public function disable($reason='') : self;

    public function isDisabled() : bool;

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