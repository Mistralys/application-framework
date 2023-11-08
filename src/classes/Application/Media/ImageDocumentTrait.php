<?php

declare(strict_types=1);

namespace Application\Media;

trait ImageDocumentTrait
{
    public function getThumbnailSourcePath() : string
    {
        return $this->getPath();
    }

    public function getMaxThumbnailSize(): int
    {
        return $this->getWidth();
    }
}
