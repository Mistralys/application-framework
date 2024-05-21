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
     * @return string|string[] Single right name, or list of right names.
     */
    //public function getRequiredRights() : array;

    public function isUserAllowed() : bool;

    /**
     * Returns a list of (optional) additional rights required
     * to access specific features available in the resource.
     *
     * @return array<string, string> List of right name > feature description pairs
     */
    //public function getFeatureRights() : array;

    public function getUser() : Application_User;
}
