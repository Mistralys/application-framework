<?php

declare(strict_types=1);

namespace Application\Traits\Allowable;

use Application\Interfaces\Allowable\DeveloperAllowedInterface;

trait DeveloperAllowedTrait
{
    public function getRequiredRights(): array
    {
        return DeveloperAllowedInterface::REQUIRED_RIGHTS;
    }

    public function getFeatureRights(): array
    {
        return array();
    }
}
