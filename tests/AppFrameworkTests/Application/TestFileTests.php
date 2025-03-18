<?php

declare(strict_types=1);

namespace AppFrameworkTests\Application;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application;

final class TestFileTests extends ApplicationTestCase
{
    public function test_minimumArguments() : void
    {
        $file = $this->saveTestFile('File content');

        $this->assertStringContainsString('.'. Application::DEFAULT_TEST_FILE_EXTENSION, $file->getPath());
    }

    public function test_allArguments() : void
    {
        $file = $this->saveTestFile('File content', 'txt', 'filename');

        $this->assertStringContainsString('filename.txt', $file->getPath());
    }

    public function test_contentIsSaved() : void
    {
        $file = $this->saveTestFile('File content');

        $this->assertStringEqualsFile($file->getPath(), 'File content');
    }

    public function test_summarize() : void
    {
        $file = $this->saveTestFile('<!doctype html><html lang="en"><body>File content</body></html>', 'html');

        $this->addToAssertionCount(1);

        echo $file->summarize();
    }
}
