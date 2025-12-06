<?php

declare(strict_types=1);

namespace Application\Interfaces;

interface AllowableMigrationInterface extends AllowableInterface
{
    /**
     * Returns the name of the user right required to access this resource.
     * @return string|NULL The right name, or `NULL` if no right is required.
     */
    public function getRequiredRight() : ?string;

    /**
     * Returns a list of (optional) additional rights required
     * to access specific features available in the resource.
     *
     * @return array<string, string> List of right name > feature description pairs
     */
    public function getFeatureRights() : array;
}
