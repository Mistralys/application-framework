<?php

declare(strict_types=1);

/**
 * @see UI_Interfaces_ClientConfirmable
 */
trait UI_Traits_ClientConfirmable
{
    /**
     * @var UI_ClientConfirmable_Message|NULL
     */
    protected $confirmMessage;

    /**
     * Adds a confirmation dialog with the specified message
     * before the button action is executed. Automatically
     * styles the confirmation dialog according to the button
     * style, e.g. if it's a danger button the dialog will be
     * a dangerous operation dialog.
     *
     * @param scalar|UI_Renderable_Interface $message Can contain HTML code.
     * @param boolean $withInput Whether to have the user confirm the operation by typing a confirm string.
     * @return $this
     * @throws UI_Exception
     */
    public function makeConfirm($message, bool $withInput=false)
    {
        $this->getConfirmMessage()
            ->setMessage($message)
            ->makeWithInput($withInput);

        return $this;
    }

    /**
     * Returns the confirm message instance to be able to configure it further.
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
