<?php

declare(strict_types=1);

namespace AppFrameworkTestClasses\Traits;

use Application\AppFactory;
use Application_Media;
use Application_Media_Document;
use Application_Media_Document_Image;
use Application_Uploads;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\ImageHelper\ImageFormats\Formats\GIFImage;
use AppUtils\ImageHelper\ImageFormats\FormatsCollection;
use AppUtils\ImageHelper\ImageFormats\ImageFormatInterface;
use DBHelper;

/**
 * @see ImageMediaTestInterface
 */
trait ImageMediaTestTrait
{
    public function createTestPNGImage() : Application_Media_Document_Image
    {
        return $this->createTestImage(FormatsCollection::getInstance()->getPNGFormat());
    }

    public function createTestJPGImage() : Application_Media_Document_Image
    {
        return $this->createTestImage(FormatsCollection::getInstance()->getJPGFormat());
    }

    public function createTestSVGImage() : Application_Media_Document_Image
    {
        return $this->createTestImage(FormatsCollection::getInstance()->getSVGFormat());
    }

    public function createTestGIFImage(bool $animated=false) : Application_Media_Document_Image
    {
        return $this->createTestImage(FormatsCollection::getInstance()->getGIFFormat(), $animated);
    }

    public function createTestImage(?ImageFormatInterface $format=null, bool $animated=false) : Application_Media_Document_Image
    {
        $file = $this->getExampleImagePath($format, $animated);

        $document = AppFactory::createMedia()->createImageFromFile('test-image-'.$this->getTestCounter(), FileInfo::factory($file));
        $documentPath = $document->getPath();

        $this->assertFileExists($documentPath);

        $this->testMedia[] = $document;

        return $document;
    }

    protected function getMediaStoragePath() : string
    {
        return __DIR__.'/../../files/Media';
    }

    protected function getExampleImagePath(?ImageFormatInterface $format=null, ?bool $animated=null) : string
    {
        if($format === null) {
            $format = FormatsCollection::getInstance()->getPNGFormat();
        }

        $suffix = '';
        if($format instanceof GIFImage && $animated !== null) {
            $suffix = '-non-animated';
            if($animated) {
                $suffix = '-animated';
            }
        }

        $key = $format->getID().$suffix;

        $this->assertArrayHasKey($key, ImageMediaTestInterface::EXAMPLE_IMAGES);

        $file = $this->getMediaStoragePath() . '/'. ImageMediaTestInterface::EXAMPLE_IMAGES[$key];

        $this->assertFileExists($file);

        return $file;
    }

    protected function getExamplePNGPath() : string
    {
        return $this->getExampleImagePath(FormatsCollection::getInstance()->getPNGFormat());
    }

    protected function getExampleJPGPath() : string
    {
        return $this->getExampleImagePath(FormatsCollection::getInstance()->getJPGFormat());
    }

    protected function getExampleSVGPath() : string
    {
        return $this->getExampleImagePath(FormatsCollection::getInstance()->getSVGFormat());
    }

    protected function getExampleGIFPath(bool $animated=false) : string
    {
        return $this->getExampleImagePath(FormatsCollection::getInstance()->getGIFFormat(), $animated);
    }

    protected Application_Media $media;
    protected Application_Uploads $uploads;
    protected string $storageFolder;

    /**
     * @var Application_Media_Document[]
     */
    protected array $testMedia = array();

    public function setUpImageTestCase(): void
    {
        $this->startTransaction();

        $this->media = AppFactory::createMedia();
        $this->uploads = AppFactory::createUploads();
        $this->storageFolder = $this->getMediaStoragePath();
        $this->testMedia = array();

        DBHelper::deleteRecords(Application_Media::TABLE_NAME);

        $this->media->clearCollection();
        $this->media->setRootTag(AppFactory::createTags()->createNewTag('Media'));
    }

    public function tearDownImageTestCase(): void
    {
        $this->clearTestMedia();
    }

    protected function clearTestMedia(): void
    {
        foreach($this->testMedia as $media)
        {
            FileHelper::deleteFile($media->getPath());
        }
    }
}