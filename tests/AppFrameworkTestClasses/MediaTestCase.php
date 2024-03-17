<?php

declare(strict_types=1);

namespace Mistralys\AppFrameworkTests\TestClasses;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application;
use Application\AppFactory;
use Application_Media;
use Application_Media_Document;
use Application_Media_Document_Image;
use Application_Uploads;
use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use DBHelper;

abstract class MediaTestCase extends ApplicationTestCase
{
    protected Application_Media $media;
    protected Application_Uploads $uploads;
    protected string $storageFolder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->media = AppFactory::createMedia();
        $this->uploads = AppFactory::createUploads();
        $this->storageFolder = $this->getMediaStoragePath();

        DBHelper::deleteRecords(Application_Media::TABLE_NAME);

        $this->startTransaction();
    }
}
