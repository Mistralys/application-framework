<?php

declare(strict_types=1);

namespace Application\Sets\Admin\Traits;

use Application\Sets\Admin\Screens\ApplicationSetsMode;

trait AppSetSubmodeTrait
{
    public function getDefaultAction(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    public function getParentScreenClass(): string
    {
        return ApplicationSetsMode::class;
    }
}
