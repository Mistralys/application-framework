<?php

declare(strict_types=1);

namespace AppFrameworkTests\Composer\ModulesOverview;

use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use Application\Composer\ModulesOverview\ModuleContextFileFinder;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Integration tests for {@see ModuleContextFileFinder}.
 * Runs against the framework's own module-context.yaml files.
 */
final class ModuleContextFileFinderTest extends ApplicationTestCase
{
    /**
     * Path to the framework root, relative to this test file's location.
     * tests/AppFrameworkTests/Composer/ModulesOverview/ → ../../../../ → framework root
     */
    private static string $frameworkRoot = '';

    private static function getFrameworkRoot() : FolderInfo
    {
        if(self::$frameworkRoot === '') {
            self::$frameworkRoot = realpath(__DIR__ . '/../../../../') ?: __DIR__ . '/../../../../';
        }

        return FolderInfo::factory(self::$frameworkRoot);
    }

    /**
     * @return FileInfo[]
     */
    private function runFindAll() : array
    {
        $finder = new ModuleContextFileFinder(self::getFrameworkRoot());
        return $finder->findAll();
    }

    public function test_findAll_returnsFiles() : void
    {
        $files = $this->runFindAll();

        $this->assertNotEmpty($files, 'findAll() should return at least one module-context.yaml file');
    }

    public function test_findAll_discoversConnectorsModule() : void
    {
        $files = $this->runFindAll();

        $paths = array_map(static function(FileInfo $f) : string {
            return $f->getPath();
        }, $files);

        $pathString = implode("\n", $paths);

        $this->assertStringContainsString(
            'Connectors/module-context.yaml',
            $pathString,
            'findAll() should discover the Connectors module context file'
        );
    }

    public function test_findAll_allFilesExist() : void
    {
        $files = $this->runFindAll();

        foreach($files as $file)
        {
            $this->assertFileExists(
                $file->getPath(),
                sprintf('File does not exist: %s', $file->getPath())
            );
        }
    }

    public function test_findAll_returnsNoDuplicates() : void
    {
        $files = $this->runFindAll();
        $paths = array();

        foreach($files as $file)
        {
            $path = $file->getPath();
            $this->assertArrayNotHasKey(
                $path,
                $paths,
                sprintf('Duplicate file found: %s', $path)
            );
            $paths[$path] = true;
        }
    }
}
