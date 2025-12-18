<?php

declare(strict_types=1);

namespace Application\Countries\Admin\Traits;

use Application\Countries\Admin\Screens\Mode\ViewScreen;

trait CountryViewTrait
{
    public function getParentScreenClass(): string
    {
        return ViewScreen::class;
    }
}
