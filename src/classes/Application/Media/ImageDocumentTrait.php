<?php

declare(strict_types=1);

namespace Application\Media;

use AppUtils\BaseException;
use AppUtils\Microtime_Exception;

trait ImageDocumentTrait
{
    /**
     * @return string
     * @throws Microtime_Exception
     */
    public function getThumbnailSourcePath() : string
    {
        return $this->getPath();
    }

    /**
     * @return int
     * @throws MediaException
     * @throws BaseException
     */
    public function getMaxThumbnailSize(): int
    {
        return $this->getWidth();
    }
}
