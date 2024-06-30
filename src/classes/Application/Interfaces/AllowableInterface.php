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
     * Returns a list of all rights required to access the resource.
     *
     * @return string
     */
    //public function getRequiredRight() : string;

    /**
     * Whether the user is allowed to access the resource.
     * @return bool
     */
    public function isUserAllowed() : bool;

    /**
     * Returns a list of (optional) additional rights required
     * to access specific features available in the resource.
     *
     * @return array<string, string> List of right name > feature description pairs
     */
    //public function getFeatureRights() : array;

    /**
     * @return Application_User
     */
    public function getUser() : Application_User;
}
