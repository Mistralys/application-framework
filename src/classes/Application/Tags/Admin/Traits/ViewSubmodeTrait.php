<?php

declare(strict_types=1);

namespace Application\Tags\Admin\Traits;

use Application\AppFactory;
use Application\Tags\Admin\Screens\Mode\ViewMode;
use Application\Tags\TagCollection;

trait ViewSubmodeTrait
{
    public function createCollection(): TagCollection
    {
        return AppFactory::createTags();
    }

    public function getParentScreenClass() : string
    {
        return ViewMode::class;
    }
}
