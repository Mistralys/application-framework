<?php

declare(strict_types=1);

namespace Application\Tags\Admin\Traits;

use Application\AppFactory;
use Application\Tags\Admin\Screens\Area\TagsArea;
use Application\Tags\TagCollection;

trait TagModeTrait
{
    public function createCollection(): TagCollection
    {
        return AppFactory::createTags();
    }

    public function getParentScreenClass() : string
    {
        return TagsArea::class;
    }

    public function getDefaultSubscreenClass(): ?string
    {
        return null;
    }
}
