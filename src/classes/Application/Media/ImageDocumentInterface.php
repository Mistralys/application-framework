<?php

declare(strict_types=1);

namespace Application\Media;

use Application_Media_DocumentInterface;
use AppUtils\ImageHelper_Size;

interface ImageDocumentInterface extends Application_Media_DocumentInterface
{
    public function getWidth() : int;
    public function getHeight() : int;
    public function getDimensions() : ImageHelper_Size;
}
