<?php

declare(strict_types=1);

namespace Application\Media\Admin\Traits;

use Application\AppFactory;
use Application\Media\Admin\Screens\MediaLibraryArea;
use Application\Media\Collection\MediaCollection;

trait MediaModeTrait
{
    public function getDefaultSubmode(): string
    {
        return '';
    }

    public function getDefaultSubscreenClass(): ?string
    {
        return null;
    }

    public function createCollection() : MediaCollection
    {
        return AppFactory::createMediaCollection();
    }

    public function getParentScreenClass() : string
    {
        return MediaLibraryArea::class;
    }
}
