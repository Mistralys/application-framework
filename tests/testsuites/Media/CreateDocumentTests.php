<?php

declare(strict_types=1);

namespace testsuites\Media;

use AppUtils\FileHelper\FileInfo;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

final class CreateDocumentTests extends MediaTestCase
{
    public function test_createFromFile() : void
    {
        $file = $this->storageFolder.'/example-image.png';

        $this->assertFileExists($file);

        $document = $this->media->createFromFile('example-image', FileInfo::factory($file));
        $documentPath = $document->getPath();

        $this->assertFileExists($documentPath);

        $document->delete();

        $this->assertFileDoesNotExist($documentPath);
    }
}
