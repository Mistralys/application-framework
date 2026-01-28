<?php

declare(strict_types=1);

namespace UI\Event;

use UI\BaseUIEvent;
use UI_Form;

class FormCreatedEvent extends BaseUIEvent
{
    public const string EVENT_NAME = 'FormCreated';

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getForm(): UI_Form
    {
        return $this->getArgumentObject(1, UI_Form::class);
    }
}
