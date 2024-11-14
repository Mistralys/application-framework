<?php

declare(strict_types=1);

namespace testsuites\Media;

use AppUtils\FileHelper\FileInfo;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

final class CreateUploadTests extends MediaTestCase
{
    public function test_createFromFile(): void
    {
        $file = $this->getExamplePNGPath();

        $document = $this->media->createFromFile('example-image', FileInfo::factory($file));
        $documentPath = $document->getPath();

        $this->assertFileExists($documentPath);

        $document->delete();

        $this->assertFileDoesNotExist($documentPath);
    }
}