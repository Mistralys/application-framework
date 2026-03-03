<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\ModulesOverview;

use AppUtils\FileHelper;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Discovers all `module-context.yaml` files in the project by
 * following the import chain defined in the project's `context.yaml`.
 *
 * Handles two import styles:
 * - Glob patterns ending in `module-context.yaml` (e.g. `assets/classes/ ** /module-context.yaml`)
 * - References to other `context.yaml` files (recursed into)
 *
 * @package Application
 * @subpackage Composer
 */
final class ModuleContextFileFinder
{
    private FolderInfo $rootFolder;

    /**
     * Tracks discovered absolute paths to prevent duplicates.
     *
     * @var array<string, bool>
     */
    private array $seen = array();

    public function __construct(FolderInfo $rootFolder)
    {
        $this->rootFolder = $rootFolder;
    }

    /**
     * Parses the project's `context.yaml` and recursively follows
     * all `import` entries to collect every `module-context.yaml` file.
     *
     * @return FileInfo[]
     */
    public function findAll() : array
    {
        $this->seen = array();

        $rootContextFile = $this->rootFolder->getPath() . '/context.yaml';

        return $this->resolveImports($rootContextFile);
    }

    /**
     * Parses a single `context.yaml` file and resolves its import entries.
     *
     * @param string $contextFilePath Absolute path to the context.yaml to parse.
     * @return FileInfo[]
     */
    private function resolveImports(string $contextFilePath) : array
    {
        if(!file_exists($contextFilePath)) {
            return array();
        }

        $data = Yaml::parseFile($contextFilePath);

        if(!isset($data['import']) || !is_array($data['import'])) {
            return array();
        }

        $baseDir = dirname($contextFilePath);
        $result = array();

        foreach($data['import'] as $entry) {
            if(!is_array($entry) || !isset($entry['path']) || !is_string($entry['path'])) {
                continue;
            }

            $importPath = $entry['path'];

            if($this->isModuleContextGlob($importPath)) {
                $found = $this->resolveGlob($baseDir, $importPath);
                foreach($found as $file) {
                    $result[] = $file;
                }
            } elseif($this->isContextYaml($importPath)) {
                $nestedContextFile = $baseDir . '/' . $importPath;
                $nestedContextFile = $this->normalizePath($nestedContextFile);
                $found = $this->resolveImports($nestedContextFile);
                foreach($found as $file) {
                    $result[] = $file;
                }
            }
        }

        return $result;
    }

    /**
     * Checks whether an import path is a glob that targets `module-context.yaml` files.
     *
     * @param string $importPath The import path value from context.yaml.
     * @return bool
     */
    private function isModuleContextGlob(string $importPath) : bool
    {
        return str_contains($importPath, '*') && str_ends_with($importPath, 'module-context.yaml');
    }

    /**
     * Checks whether an import path references another `context.yaml` file.
     *
     * @param string $importPath The import path value from context.yaml.
     * @return bool
     */
    private function isContextYaml(string $importPath) : bool
    {
        return str_ends_with($importPath, 'context.yaml') && !str_contains($importPath, '*');
    }

    /**
     * Resolves a glob pattern by extracting the base directory (before any wildcard)
     * and recursively searching it for files named `module-context.yaml`.
     *
     * @param string $baseDir    Absolute base directory of the context.yaml that contains the import.
     * @param string $globPattern The import path glob (e.g. `assets/classes/ ** /module-context.yaml`).
     * @return FileInfo[]
     */
    private function resolveGlob(string $baseDir, string $globPattern) : array
    {
        $targetFile = basename($globPattern);

        // Extract the static directory prefix before any wildcard characters
        $patternDir = dirname($globPattern);
        $wildcardPos = strpos($patternDir, '*');

        if($wildcardPos !== false) {
            $patternDir = substr($patternDir, 0, $wildcardPos);
        }

        $patternDir = rtrim($patternDir, '/');
        $searchDir  = $baseDir . '/' . $patternDir;
        $searchDir  = $this->normalizePath($searchDir);

        if(!is_dir($searchDir)) {
            return array();
        }

        $allPaths = FileHelper::createFileFinder($searchDir)
            ->includeExtension('yaml')
            ->makeRecursive()
            ->setPathmodeAbsolute()
            ->getAll();

        $result = array();

        foreach($allPaths as $path) {
            if(basename($path) !== $targetFile) {
                continue;
            }

            $normalized = $this->normalizePath($path);

            if(isset($this->seen[$normalized])) {
                continue;
            }

            $this->seen[$normalized] = true;
            $result[] = FileInfo::factory($normalized);
        }

        return $result;
    }

    /**
     * Normalizes a file system path by resolving `..` and `.` segments.
     *
     * @param string $path
     * @return string
     */
    private function normalizePath(string $path) : string
    {
        $real = realpath($path);
        return $real !== false ? $real : $path;
    }
}
