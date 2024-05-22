<?php

declare(strict_types=1);

namespace Application\Interfaces\Allowable;

use Application\Interfaces\AllowableInterface;
use Application_User;

interface DeveloperAllowedInterface extends AllowableInterface
{
    public const REQUIRED_RIGHT = Application_User::RIGHT_DEVELOPER;
}
