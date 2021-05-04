<?php

use AppUtils\Interface_Classable;

interface UI_Interfaces_Button extends UI_Renderable_Interface, Application_Interfaces_Iconizable, Interface_Classable, Application_LockableItem_Interface
{
    /**
     * Styles the button as a button for a dangerous operation, like deleting records.
     *
     * @return UI_Interfaces_Button
     */
    public function makeDangerous();

    /**
     * @return UI_Interfaces_Button
     */
    public function makePrimary();

    /**
     * @return UI_Interfaces_Button
     */
    public function makeSuccess();

    /**
     * @return UI_Interfaces_Button
     */
    public function makeDeveloper();

    /**
     * @return UI_Interfaces_Button
     */
    public function makeWarning();

    /**
     * @return UI_Interfaces_Button
     */
    public function makeInfo();

    /**
     * @return UI_Interfaces_Button
     */
    public function makeInverse();

    /**
     * @param string $statement
     * @return UI_Interfaces_Button
     */
    public function click(string $statement);

    /**
     * @param string $url
     * @param string $target
     * @return UI_Interfaces_Button
     */
    public function link(string $url, string $target='');

    /**
     * @param string|number|UI_Renderable_Interface $tooltip
     * @return UI_Interfaces_Button
     */
    public function setTooltip($tooltip);

    /**
     * @param string|number|UI_Renderable_Interface $text
     * @return UI_Interfaces_Button
     */
    public function setLoadingText($text);

    /**
     * @param string $id
     * @return UI_Interfaces_Button
     */
    public function setID(string $id);

    /**
     * @param string $label
     * @return UI_Interfaces_Button
     */
    public function setLabel(string $label);

    public function getLabel() : string;

    public function getID() : string;

    public function getTooltip() : string;
}