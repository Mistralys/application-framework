<?php

declare(strict_types=1);

namespace Application\Interfaces;

use Application\Traits\AllowableMigrationTrait;
use Application_User;

/**
 * @see AllowableMigrationTrait
 */
interface AllowableInterface
{
    /**
     * Whether the user is allowed to access the resource.
     * @return bool
     */
    public function isUserAllowed() : bool;

    /**
     * @return Application_User
     */
    public function getUser() : Application_User;
}
