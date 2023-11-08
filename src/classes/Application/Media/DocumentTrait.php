<?php

declare(strict_types=1);

namespace Application\Media;

use Application;
use Application\AppFactory;
use Application_Exception;
use Application_Media_Delivery;
use Application_Media_Document;
use Application_Media_DocumentInterface;
use Application_Request;
use Application_User;
use AppUtils\ConvertHelper;
use AppUtils\FileHelper;
use AppUtils\FileHelper_Exception;
use AppUtils\ImageHelper;
use AppUtils\ImageHelper_Exception;

trait DocumentTrait
{
    public function getThumbnailSourceImage() : ImageHelper
    {
        return ImageHelper::createFromFile($this->getThumbnailSourcePath());
    }

    public function getMaxThumbnailSize(): int
    {
        return $this-$this->getThumbnailSourceImage()->getWidth();
    }

    public function thumbnailExists(?int $width = null, ?int $height = null) : bool
    {
        try
        {
            $path = $this->getThumbnailPath($width, $height);
        }
        catch (Application_Exception $e)
        {
            if ($e->getCode() === Application_Media_Document::ERROR_FILE_NOT_FOUND) {
                $e->disableLogging();
                return false;
            }

            throw $e;
        }

        return file_exists($path);
    }

    public function getThumbnailPath(?int $width = null, ?int $height = null) : string
    {
        $sourcePath = $this->getThumbnailSourcePath();
        $source = ImageHelper::createFromFile($sourcePath);

        if($source->isVector()) {
            return $sourcePath;
        }

        if($width <= 0) {$width = null;}
        if($height <= 0) {$height = null;}

        if ($width === null && $height === null) {
            return $sourcePath;
        }

        if ($width === $source->getWidth() && $height === $source->getHeight()) {
            return $sourcePath;
        }

        $folder = dirname($sourcePath);

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

    /**
     * Creates a thumbnail of the image for the specified dimensions.
     * Width and height can be omitted as needed to constrain resampling
     * to one or none of the sides.
     *
     * Returns the path to the thumbnail file when successful.
     *
     * @param int|null $width
     * @param int|null $height
     * @return string
     * @throws ImageHelper_Exception
     */
    public function createThumbnail(?int $width = null, ?int $height = null) : string
    {
        $sourcePath = $this->getThumbnailSourcePath();
        $source = $this->getThumbnailSourceImage();

        if($source->isVector()) {
            return $sourcePath;
        }

        $this->log(sprintf('Creating thumbnail for size [%sx%s].', $width, $height));

        $targetFile = $this->getThumbnailPath($width, $height);

        if (!file_exists($targetFile) || filemtime($targetFile) < filemtime($this->getPath())) {
            $source->resample($width, $height);
            $source->save($targetFile);
        }

        return $targetFile;
    }

    /**
     * @param bool $forceDownload
     * @return never
     */
    public function sendFile(bool $forceDownload=false)
    {
        FileHelper::sendFile(
            $this->getPath(),
            $this->getFilename(),
            $forceDownload
        );

        Application::exit('Send file to browser.');
    }

    /**
     * Renders a thumbnail image HTML tag for the document.
     *
     * @param int|null $preferredSize Will be used if the document allows this size. Use 0 or NULL to ignore.
     * @return string
     */
    public function renderThumbnail(?int $preferredSize=null) : string
    {
        return (new ThumbnailRenderer($this))
            ->setPreferredSize($preferredSize)
            ->render();
    }

    /**
     * Retrieves the default size for thumbnails of this document type.
     *
     * @param int|NULL $preferredSize Specify a preferred size, which will be used if possible. Use 0 or NULL to ignore.
     * @return int
     */
    public function getThumbnailDefaultSize(?int $preferredSize=null): int
    {
        $preferredSize = (int)$preferredSize;
        if($preferredSize <= 0) {
            $preferredSize = Application_Media_DocumentInterface::DEFAULT_THUMBNAIL_SIZE;
        }

        if($this->isVector()) {
            return $preferredSize;
        }

        $size = $this->getMaxThumbnailSize();

        if($size < $preferredSize) {
            return $size;
        }

        return $preferredSize;
    }

    /**
     * Retrieves the size of the media file on disk, in bytes.
     * @return int
     */
    public function getFilesize() : int
    {
        $size = filesize($this->getPath());
        if($size !== false) {
            return $size;
        }

        return 0;
    }

    /**
     * @return bool
     * @deprecated Use {@see self::isVector()} instead.
     */
    public function isTypeSVG() : bool
    {
        return $this->isVector();
    }

    public function isVector() : bool
    {
        return strtolower($this->getExtension()) === 'svg';
    }

    /**
     * Retrieves the user that created the upload.
     * @return Application_User
     * @throws Application_Exception
     */
    public function getUser() : Application_User
    {
        return Application::createUser($this->getUserID());
    }

    /**
     * Retrieves the size of the media file in a human-readable format,
     * e.g. 15 Kb.
     *
     * @return string
     */
    public function getFilesizeReadable() : string
    {
        return ConvertHelper::bytes2readable($this->getFilesize());
    }

    public function getFilename() : string
    {
        return ConvertHelper::transliterate($this->getName()).'.'.$this->getExtension();
    }

    /**
     * Retrieves the full URL to the media script to display a thumbnail
     * of the media file. The width and height parameters can be set as
     * needed to resample the thumbnail to the target size.
     *
     * @param integer|NULL $width
     * @param integer|NULL $height
     * @return string
     */
    public function getThumbnailURL(?int $width = null, ?int $height = null) : string
    {
        return APP_URL . '/media.php?' . http_build_query(array(
                'source' => $this->getMediaSourceID(),
                $this->getMediaPrimaryName() => $this->id,
                'width' => $width,
                'height' => $height
            ));
    }

    /**
     * @param Application_Media_Delivery $delivery
     * @param Application_Request $request
     * @return never
     * @throws FileHelper_Exception
     */
    public function serveFromRequest(Application_Media_Delivery $delivery, Application_Request $request)
    {
        $width = (int)$request->getParam('width', null);
        $height = (int)$request->getParam('height', null);

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

        FileHelper::sendFileAuto(
            $this->createThumbnail($width, $height),
            $this->getFilename()
        );

        Application::exit($this->getLogIdentifier().' | Sent image contents to stdout.');
    }

    public function getTypeIconURL() : string
    {
        return sprintf(
            '%s/documents/%s.png',
            AppFactory::createDriver()->getTheme()->getDefaultImagesURL(),
            $this->getTypeIconID()
        );
    }

    /**
     * @var array<string,string>
     */
    private static array $typeIconIDs = array();

    protected function getTypeIconID() : string
    {
        $extension = $this->getExtension();

        if(isset(self::$typeIconIDs[$extension])) {
            return self::$typeIconIDs[$extension];
        }

        self::$typeIconIDs[$extension] = 'generic';

        $specific = sprintf(
            '%s/documents/%s.png',
            AppFactory::createDriver()->getTheme()->getDefaultImagesPath(),
            $extension
        );

        if(file_exists($specific)) {
            self::$typeIconIDs[$extension] = $extension;
        }

        return self::$typeIconIDs[$extension];
    }

    public function getTypeIconPath() : string
    {
        return sprintf(
            '%s/documents/%s.png',
            AppFactory::createDriver()->getTheme()->getDefaultImagesPath(),
            $this->getTypeIconID()
        );
    }
}
