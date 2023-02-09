<?php

declare(strict_types=1);

namespace testsuites\Media;

use AppUtils\FileHelper\FileInfo;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

final class CreateDocumentTests extends MediaTestCase
{
    public function test_createFromFile() : void
    {
        $file = $this->getExampleImagePath();

        $document = $this->media->createFromFile('example-image', FileInfo::factory($file));
        $documentPath = $document->getPath();

        $this->assertFileExists($documentPath);

        $document->delete();

        $this->assertFileDoesNotExist($documentPath);
    }

    public function test_createImageFromFile() : void
    {
        $file = $this->getExampleImagePath();

        $document = $this->media->createImageFromFile('example-image', FileInfo::factory($file));
        $documentPath = $document->getPath();

        $this->assertFileExists($documentPath);

        $document->delete();

        $this->assertFileDoesNotExist($documentPath);
    }
}
