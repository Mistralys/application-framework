<?php

declare(strict_types=1);

namespace Application\Media\Admin\Traits;

use Application\AppFactory;
use Application\Media\Admin\Screens\Mode\ViewMode;
use Application\Media\Collection\MediaCollection;

trait MediaViewTrait
{
    public function getDefaultAction(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): null
    {
        return null;
    }

    public function createCollection() : MediaCollection
    {
        return AppFactory::createMediaCollection();
    }

    public function getParentScreenClass() : string
    {
        return ViewMode::class;
    }
}
