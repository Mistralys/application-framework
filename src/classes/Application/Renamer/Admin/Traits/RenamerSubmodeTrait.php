<?php

declare(strict_types=1);

namespace Application\Renamer\Admin\Traits;

use Application\Renamer\Admin\Screens\Mode\RenamerMode;

trait RenamerSubmodeTrait
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
        return RenamerMode::class;
    }
}
