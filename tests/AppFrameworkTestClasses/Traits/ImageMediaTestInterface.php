<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use AppFrameworkTestClasses\ApplicationTestCaseInterface;
use AppUtils\ImageHelper\ImageFormats\Formats\GIFImage;
use AppUtils\ImageHelper\ImageFormats\Formats\JPEGImage;
use AppUtils\ImageHelper\ImageFormats\Formats\PNGImage;
use AppUtils\ImageHelper\ImageFormats\Formats\SVGImage;

interface ImageMediaTestInterface extends ApplicationTestCaseInterface
{
    public const EXAMPLE_IMAGES = array(
        PNGImage::FORMAT_ID => 'example-image.png',
        JPEGImage::FORMAT_ID => 'example-image.jpg',
        SVGImage::FORMAT_ID => 'example-image.svg',
        GIFImage::FORMAT_ID . '-non-animated' => 'example-gif-non-animated.gif',
        GIFImage::FORMAT_ID . '-animated' => 'example-gif-animated.gif',
    );
}
