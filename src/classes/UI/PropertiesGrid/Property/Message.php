<?php

class UI_PropertiesGrid_Property_Message extends UI_PropertiesGrid_Property_Merged
{
    /**
     * @var UI_Message
     */
    private $message;

    protected $classes = array('prop-message');

    protected function init()
    {
        $this->message = $this->grid->getUI()->createMessage('')
            ->makeNotDismissable()
            ->makeInfo();
    }

    public function getMessage() : UI_Message
    {
        return $this->message;
    }

    public function render() : string
    {
        $this->message->setMessage($this->label);
        $this->label = $this->message->render();

        return parent::render();
    }

    protected function filterValue($text) : UI_StringBuilder
    {
        return sb();
    }
}
