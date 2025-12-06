<?php

declare(strict_types=1);

namespace AppFrameworkTests\ErrorLog;

use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\JSONFile;

final class FileTypeTest extends ApplicationTestCase
{
    /**
     * The `trace` extension used by the error log to store
     * stack traces is automatically registered with AppUtils
     * so it can be opened as a JSON file.
     *
     * @see \Application_Bootstrap::initFileTypes()
     */
    public function test_traceCanBeOpenedAsJSON() : void
    {
        $file = 'tests/logs/error.trace';

        $obj = FileInfo::factory($file);

        $this->assertInstanceOf(JSONFile::class, $obj);
    }
}
