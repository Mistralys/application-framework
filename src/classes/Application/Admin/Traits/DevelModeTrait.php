<?php

declare(strict_types=1);

namespace Application\Admin\Traits;

use Application\Development\Admin\Screens\DevelArea;

trait DevelModeTrait
{
    /**
     * @return class-string<DevelArea>
     */
    public function getParentScreenClass(): string
    {
        return DevelArea::class;
    }

    public function getDefaultSubmode() : string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): ?string
    {
        return null;
    }
}