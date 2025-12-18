<?php

declare(strict_types=1);

namespace Application\DeploymentRegistry;

use Application\ApplicationException;

class DeploymentRegistryException extends ApplicationException
{

    public const int ERROR_VERSION_DOES_NOT_EXIST = 123901;
}
