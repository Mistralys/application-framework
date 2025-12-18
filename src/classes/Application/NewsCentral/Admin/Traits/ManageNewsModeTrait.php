<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Traits;

use Application\NewsCentral\Admin\Screens\ManageNewsArea;

trait ManageNewsModeTrait
{
    public function getParentScreenClass() : string
    {
        return ManageNewsArea::class;
    }
}