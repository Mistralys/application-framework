<?php

declare(strict_types=1);

use Application\Media\Collection\MediaCollection;
use Application\Media\ImageDocumentInterface;
use Application\Media\ImageDocumentTrait;
use Application\Media\MediaException;
use AppUtils\BaseException;
use AppUtils\FileHelper\FileInfo;
use AppUtils\ImageHelper;
use AppUtils\ImageHelper\ImageFormats\Formats\GIFImage;
use AppUtils\ImageHelper_Size;

class Application_Media_Document_Image extends Application_Media_Document
    implements ImageDocumentInterface
{
    use ImageDocumentTrait;

    public const array IMAGE_EXTENSIONS = array(
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg'
    );

    /**
     * Maps a raster extension to the `IMAGETYPE_*` constant
     * {@see getimagesizefromstring()} reports for its detected format.
     * SVG is excluded: it is checked separately, through its root element,
     * since `getimagesizefromstring()` does not reliably detect it.
     *
     * @var array<string,int>
     */
    private const array RASTER_EXTENSION_TYPES = array(
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
        'gif' => IMAGETYPE_GIF,
    );

    public static function getLabel() : string
    {
        return t('Image');
    }

    public static function getIcon(): UI_Icon
    {
        return UI::icon()->image();
    }

    public static function getExtensions() : array
    {
        return self::IMAGE_EXTENSIONS;
    }

    /**
     * Checks that the content is a genuine image file matching the given
     * extension's family: a raster extension (jpg/jpeg/png/gif) must decode
     * to the matching `IMAGETYPE_*` via {@see getimagesizefromstring()}; the
     * `svg` extension must be a well-formed XML document whose first
     * element is `svg`. This is a file-type check only, not a content
     * policy — e.g. SVG scripts and external references are not inspected
     * here.
     *
     * @param string $content
     * @param string $extension
     * @return bool
     */
    public static function isContentValid(string $content, string $extension): bool
    {
        $extension = strtolower($extension);

        if($extension === 'svg') {
            return self::isValidSVG($content);
        }

        return self::isValidRasterImage($content, $extension);
    }

    private static function isValidRasterImage(string $content, string $extension) : bool
    {
        if(!isset(self::RASTER_EXTENSION_TYPES[$extension])) {
            return false;
        }

        $info = @getimagesizefromstring($content);

        if($info === false) {
            return false;
        }

        return $info[2] === self::RASTER_EXTENSION_TYPES[$extension];
    }

    /**
     * Checks that the content is a well-formed XML document whose first
     * element is `svg`, using a streaming reader so a large or malformed
     * payload is never fully loaded into a DOM tree. `LIBXML_NONET`
     * disallows network access for any external reference, and entity
     * substitution is never enabled (the reader's default), so a declared
     * external entity is parsed but never resolved.
     *
     * @param string $content
     * @return bool
     */
    private static function isValidSVG(string $content) : bool
    {
        if($content === '') {
            return false;
        }

        $reader = @XMLReader::XML($content, null, LIBXML_NONET);

        if($reader === false) {
            return false;
        }

        try
        {
            while(@$reader->read())
            {
                if($reader->nodeType === XMLReader::ELEMENT) {
                    return $reader->localName === 'svg';
                }
            }
        }
        finally
        {
            $reader->close();
        }

        return false;
    }

   /**
    * Retrieves an instance of the image helper for this image
    * that can be used to do any number of operations on the
    * source image.
    * 
    * @return ImageHelper
    */
    public function getImageHelper() : ImageHelper
    {
        return $this->getThumbnailSourceImage();
    }
    
   /**
    * Calculates the image dimensions for the target width,
    * keeping the aspect ratio.
    * 
    * @param integer $width
    * @return ImageHelper_Size
    */
    public function getSizeByWidth(int $width) : ImageHelper_Size
    {
        return $this->getImageHelper()->getSizeByWidth($width);
    }

    public function exists() : bool
    {
        return file_exists($this->getPath());
    }

    public function getMediaSourceID(): string
    {
        return MediaCollection::MEDIA_TYPE;
    }

    public function getMediaPrimaryName(): string
    {
        return MediaCollection::PRIMARY_NAME;
    }

    /**
     * @return int
     * @throws BaseException
     * @throws MediaException
     */
    public function getWidth() : int
    {
        $dimensions = $this->getDimensions();

        return $dimensions[0];
    }

    /**
     * @return int
     * @throws BaseException
     * @throws MediaException
     */
    public function getHeight() : int
    {
        $dimensions = $this->getDimensions();

        return $dimensions[1];
    }

    protected ?ImageHelper_Size $dimensions = null;

    /**
     * @return ImageHelper_Size
     * @throws MediaException
     * @throws BaseException
     */
    public function getDimensions() : ImageHelper_Size
    {
        if (isset($this->dimensions)) {
            return $this->dimensions;
        }

        $this->dimensions = new ImageHelper_Size(array(0, 0));

        $path = $this->getPath();
        if(file_exists($path)) {
            $this->dimensions = ImageHelper::getImageSize($path);
        }
        
        return $this->dimensions;
    }

    public function injectMetadata(UI_PropertiesGrid $grid) : void
    {
        $dimensions = $this->getDimensions();
        $format = $this->getImageFormat();

        $gFormat = $grid->add(t('Image format'), strtoupper($format->getID()));
        if($this->isAnimatedGIF()) {
            $gFormat->setComment(t('Animated: will never be resampled to preserve the animation.'));
        }

        $grid->add(t('Image size'), $dimensions->toReadableString());
    }

    public function isAnimatedGIF() : bool
    {
        $format = $this->getImageFormat();

        return
            $format instanceof GIFImage
            &&
            $format->fileHasAnimation(FileInfo::factory($this->getPath()));
    }
}
