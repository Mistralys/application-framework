<?php

declare(strict_types=1);

namespace Application\Validation;

trait ValidationLoggableTrait
{
    public function getValidatorLabel(): string
    {
        return $this->getLogIdentifier();
    }
}
