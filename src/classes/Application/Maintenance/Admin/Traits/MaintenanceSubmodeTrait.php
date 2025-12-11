<?php

declare(strict_types=1);

namespace Application\Maintenance\Admin\Traits;

use Application\Maintenance\Admin\Screens\MaintenanceMode;

trait MaintenanceSubmodeTrait
{
    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    public function getParentScreenClass(): string
    {
        return MaintenanceMode::class;
    }

    public function getDefaultAction(): string
    {
        return '';
    }
}
