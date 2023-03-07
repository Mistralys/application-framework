<?php

declare(strict_types=1);

namespace UI\Interfaces;

use UI_Message;

interface MessageWrapperInterface extends MessageLayoutInterface
{
    public function getMessage() : UI_Message;
}
