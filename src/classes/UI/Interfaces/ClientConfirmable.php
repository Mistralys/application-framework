<?php

declare(strict_types=1);

interface UI_Interfaces_ClientConfirmable
{
    /**
     * Adds a confirmation message to the element, as a dialog that is shown
     * before the action is executed.
     *
     * @param scalar|UI_Renderable_Interface $message
     * @param boolean $withInput Whether to have the user confirm the operation by typing a confirm string.
     * @return $this
     */
    public function makeConfirm($message, bool $withInput=false);

    public function getConfirmMessage() : UI_ClientConfirmable_Message;

    public function getURL() : string;

    public function isClickable() : bool;

    public function isLinked() : bool;

    public function getJavascript() : string;

    public function getUI() : UI;

    public function isConfirm() : bool;

    public function isDangerous() : bool;
}
