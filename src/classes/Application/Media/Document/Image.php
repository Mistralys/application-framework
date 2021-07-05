<?php

use AppUtils\ImageHelper_Size;
use AppUtils\ImageHelper;

class Application_Media_Document_Image extends Application_Media_Document
{
    const ERROR_FILE_NOT_FOUND = 384970001;

    public static function getLabel()
    {
        return t('Image');
    }

    public static function getExtensions()
    {
        return array(
            'jpg',
            'jpeg',
            'png',
            'gif',
            'svg'
        );
    }

    public function getThumbnailPath($width = null, $height = null)
    {
        if($this->isTypeSVG()) {
            return $this->getPath();
        }
        
        if (empty($width) && empty($height)) {
            return $this->getPath();
        }

        if ($width == $this->getWidth() && $height == $this->getHeight()) {
            return $this->getPath();
        }
        
        $folder = dirname($this->getPath());

        if (empty($width)) {
            return sprintf(
                '%s/%s_h%s.%s',
                $folder,
                $this->id,
                $height,
                $this->getExtension()
            );
        }

        if (empty($height)) {
            return sprintf(
                '%s/%s_w%s.%s',
                $folder,
                $this->id,
                $width,
                $this->getExtension()
            );
        }

        return sprintf(
            '%s/%s_w%s_h%s.%s',
            $folder,
            $this->id,
            $width,
            $height,
            $this->getExtension()
        );
    }

    public function thumbnailExists($width = null, $height = null) 
    {
        try {
            $path = $this->getThumbnailPath($width, $height);
        } catch (Application_Exception $e) {
            if ($e->getCode() == self::ERROR_FILE_NOT_FOUND) {
                $e->disableLogging();

                return false;
            }

            throw $e;
        }

        return file_exists($path);
    }

    public function serveFromRequest(Application_Media_Delivery $delivery, Application_Request $request)
    {
        $width = $request->getParam('width', null);
        $height = $request->getParam('height', null);

        $this->log(sprintf(
            'Serving image [%s] in dimensions [%sx%s]', 
            $this->getFilename(),
            $width,
            $height
        ));

        $this->log(sprintf(
            'Source file is located at [%s].',
            $this->getPath()
        ));
        
        $targetFile = $this->createThumbnail($width, $height);

        ImageHelper::displayImage($targetFile);
        
        Application::exit($this->getLogIdentifier().' | Sent image contents to stdout.');
    }

    /**
     * Creates a thumbnail of the image for the specified dimensions.
     * Width and height can be omitted as needed to constrain resampling
     * to one or none of the sides.
     *
     * Returns the path to the thumbnail file when successful.
     *
     * @see Application_Media_Document::createThumbnail()
     * @throws Application_Exception
     * @return string
     */
    public function createThumbnail($width = null, $height = null)
    {
        if($this->isTypeSVG()) {
            return $this->getPath();
        }
        
        $this->log(sprintf('Creating thumbnail for size [%sx%s].', $width, $height));
        
        $targetFile = $this->getThumbnailPath($width, $height);

        if (!file_exists($targetFile)) {
            $helper = $this->getImageHelper();
            $helper->resample($width, $height);
            $helper->save($targetFile);
        }

        return $targetFile;
    }
    
   /**
    * Retrieves an instance of the image helper for this image
    * that can be used to do any number of operations on the
    * source image.
    * 
    * @return ImageHelper
    */
    public function getImageHelper()
    {
        return ImageHelper::createFromFile($this->getPath());
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
        $helper = $this->getImageHelper();
        return $helper->getSizeByWidth($width);
    }

    public function exists()
    {
        return file_exists($this->getPath());
    }
    
    public function getWidth()
    {
        $dimensions = $this->getDimensions();

        return $dimensions[0];
    }

    public function getHeight()
    {
        $dimensions = $this->getDimensions();

        return $dimensions[1];
    }

    protected $dimensions;

    public function getDimensions()
    {
        if (isset($this->dimensions)) {
            return $this->dimensions;
        }

        $path = $this->getPath();
        if (!file_exists($path)) {
            throw new Application_Exception(
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
}
