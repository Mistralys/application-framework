<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application;
use Application\AppFactory;
use Application_Media;
use Application_Media_Document;
use Application_Media_Document_Image;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;

abstract class MediaTestCase extends ApplicationTestCase
{
    protected Application_Media $media;
    protected string $storageFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->media = AppFactory::createMedia();
        $this->uploads = AppFactory::createUploads();
        $this->storageFolder = __DIR__.'/../files/Media';
        $this->testMedia = array();

        $this->startTransaction();
    }

    protected function getExampleImagePath() : string
    {
        $file = $this->storageFolder . '/example-image.png';

        $this->assertFileExists($file);

        return $file;
    }

    public function createTestImageMedia(string $name='example-image') : Application_Media_Document_Image
    {
        $file = $this->getExampleImagePath();

        $document = $this->media->createImageFromFile($name, FileInfo::factory($file));
        $documentPath = $document->getPath();

        $this->assertFileExists($documentPath);

        $this->testMedia[] = $document;

        return $document;
    }

    /**
     * @var Application_Media_Document[]
     */
    protected array $testMedia = array();


    protected function tearDown(): void
    {
        parent::tearDown();

        foreach($this->testMedia as $media)
        {
            FileHelper::deleteFile($media->getPath());
        }
    }
}
