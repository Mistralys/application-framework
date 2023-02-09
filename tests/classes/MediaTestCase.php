<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use Application;
use Application\AppFactory;
use Application_Media;

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

        $this->startTransaction();
    }

    protected function getExampleImagePath() : string
    {
        $file = $this->storageFolder . '/example-image.png';

        $this->assertFileExists($file);

        return $file;
    }
}
