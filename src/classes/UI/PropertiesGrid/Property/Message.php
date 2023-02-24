<?php

declare(strict_types=1);

use UI\Interfaces\MessageWrapperInterface;
use UI\Traits\MessageWrapperTrait;

class UI_PropertiesGrid_Property_Message extends UI_PropertiesGrid_Property_Merged
    implements MessageWrapperInterface
{
    use MessageWrapperTrait;

    private UI_Message $message;

    protected function init() : void
    {
        $this->addClass('prop-message');

        $this->message = $this->grid->getUI()->createMessage('')
            ->makeNotDismissable()
            ->makeInfo();
    }

    public function getMessage() : UI_Message
    {
        return $this->message;
    }

    protected function filterValue($value) : UI_StringBuilder
    {
        $this->message->setMessage($this->text);

        return sb()->add((string)$this->message);
    }
}
