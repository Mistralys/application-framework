<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Traits;

use Application\NewsCentral\Admin\Screens\ReadNewsArea;

trait ReadNewsModeTrait
{
    public function getParentScreenClass() : string
    {
        return ReadNewsArea::class;
    }

    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }
}
