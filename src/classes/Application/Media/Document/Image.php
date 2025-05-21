<?php

declare(strict_types=1);

use Application\Media\Collection\MediaCollection;
use Application\Media\ImageDocumentInterface;
use Application\Media\ImageDocumentTrait;
use Application\Media\MediaException;
use AppUtils\BaseException;
use AppUtils\FileHelper\FileInfo;
use AppUtils\ImageHelper\ImageFormats\Formats\GIFImage;
use AppUtils\ImageHelper_Size;
use AppUtils\ImageHelper;

class Application_Media_Document_Image extends Application_Media_Document
    implements ImageDocumentInterface
{
    use ImageDocumentTrait;

    public const IMAGE_EXTENSIONS = array(
        'jpg',
        'jpeg',
        'png',
        'gif',
        'svg'
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

        $path = $this->getPath();
        if (!file_exists($path)) {
            throw new MediaException(
                'Image file does not exist',
                sprintf(
                    'Retrieving size of image [%1$s] from document [%2$s] failed, file not found.',
                    $path,
                    $this->id
                ),
                self::ERROR_FILE_NOT_FOUND
            );
        }

        $this->dimensions = ImageHelper::getImageSize($path);
        
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
