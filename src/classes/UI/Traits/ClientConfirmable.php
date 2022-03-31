<?php

declare(strict_types=1);

/**
 * @see UI_Interfaces_ClientConfirmable
 */
trait UI_Traits_ClientConfirmable
{
    protected ?UI_ClientConfirmable_Message $confirmMessage = null;

    /**
     * Adds a confirmation dialog with the specified message
     * before the button action is executed. Automatically
     * styles the confirmation dialog according to the button
     * style, e.g. if it's a danger button the dialog will be
     * a dangerous operation dialog.
     *
     * @param string|number|UI_Renderable_Interface|NULL $message Can contain HTML code.
     * @param boolean $withInput Whether to have the user confirm the operation by typing a confirmation string.
     * @return $this
     * @throws UI_Exception
     */
    public function makeConfirm($message, bool $withInput=false) : self
    {
        $this->getConfirmMessage()
            ->setMessage($message)
            ->makeWithInput($withInput);

        return $this;
    }

    /**
     * Returns the confirmation message instance to be able to configure it further.
     * If none exists yet, it is created.
     *
     * @return UI_ClientConfirmable_Message
     */
    public function getConfirmMessage() : UI_ClientConfirmable_Message
    {
        if(isset($this->confirmMessage))
        {
            return $this->confirmMessage;
        }

        $this->confirmMessage = new UI_ClientConfirmable_Message($this);

        return $this->confirmMessage;
    }

    public function isConfirm() : bool
    {
        return isset($this->confirmMessage);
    }
}
