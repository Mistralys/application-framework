<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use Application;
use Application_Media;

abstract class MediaTestCase extends ApplicationTestCase
{
    protected Application_Media $media;
    protected string $storageFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->media = Application::createMedia();
        $this->storageFolder = __DIR__.'/../files/Media';

        $this->startTransaction();
    }
}
