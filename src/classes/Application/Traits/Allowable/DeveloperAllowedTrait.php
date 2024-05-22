<?php

declare(strict_types=1);

namespace Application\Traits\Allowable;

use Application\Interfaces\Allowable\DeveloperAllowedInterface;

/**
 * @see DeveloperAllowedInterface
 */
trait DeveloperAllowedTrait
{
    public function getRequiredRight(): string
    {
        return DeveloperAllowedInterface::REQUIRED_RIGHT;
    }
}
