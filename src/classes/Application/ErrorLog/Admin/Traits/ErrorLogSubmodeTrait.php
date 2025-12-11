<?php

declare(strict_types=1);

namespace Application\ErrorLog\Admin\Traits;

use Application\ErrorLog\Admin\Screens\ErrorLogMode;

trait ErrorLogSubmodeTrait
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
        return ErrorLogMode::class;
    }
}
