<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\ModulesOverview;

use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;
use Application\Composer\BuildMessages;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses individual `module-context.yaml` files into {@see ModuleInfo}
 * value objects.
 *
 * Encapsulates YAML parsing, source-path resolution, Composer-package
 * resolution, and CTX output-folder resolution so that every generator
 * that consumes module metadata ({@see ModulesOverviewGenerator},
 * application-level `ModuleJsonExportGenerator`, etc.) shares a single,
 * authoritative implementation.
 *
 * @package Application
 * @subpackage Composer
 */
final class ModuleInfoParser
{
    private const FALLBACK_PACKAGE_NAME = 'unknown/package';
    private const string SOURCE = 'ModuleInfoParser';

    private FolderInfo $rootFolder;

    /**
     * Cache of resolved Composer package names, keyed by absolute path
     * to the found composer.json.
     *
     * @var array<string, string>
     */
    private array $packageNameCache = array();

    public function __construct(FolderInfo $rootFolder)
    {
        $this->rootFolder = $rootFolder;
    }

    /**
     * Parses a single `module-context.yaml` file and returns the corresponding
     * {@see ModuleInfo}. Returns `null` if the file cannot be parsed or lacks a
     * valid `moduleMetaData` section; diagnostics are registered via {@see BuildMessages}.
     *
     * @param FileInfo $file
     * @return ModuleInfo|null
     */
    public function parseFile(FileInfo $file) : ?ModuleInfo
    {
        try {
            $data = Yaml::parseFile($file->getPath());
        } catch(ParseException $e) {
            BuildMessages::addError(
                self::SOURCE,
                sprintf('Failed to parse %s — %s', $file->getPath(), $e->getMessage())
            );
            return null;
        }

        if(!is_array($data) || !isset($data['moduleMetaData']) || !is_array($data['moduleMetaData'])) {
            BuildMessages::addWarning(
                self::SOURCE,
                sprintf('No moduleMetaData section found in %s — skipping.', $file->getPath())
            );
            return null;
        }

        $meta = $data['moduleMetaData'];

        if(!isset($meta['id'], $meta['label'], $meta['description'])) {
            BuildMessages::addWarning(
                self::SOURCE,
                sprintf('Incomplete moduleMetaData in %s — skipping.', $file->getPath())
            );
            return null;
        }

        $relatedModules = array();
        if(isset($meta['relatedModules']) && is_array($meta['relatedModules'])) {
            $relatedModules = $meta['relatedModules'];
        }

        $keywords = array();
        if(isset($meta['keywords']) && is_array($meta['keywords'])) {
            $keywords = array_map('strval', $meta['keywords']);
        }

        $sourcePath          = $this->resolveSourcePath($file);
        $composerPackage     = $this->resolveComposerPackageName($file);
        $contextOutputFolder = $this->resolveContextOutputFolder($data);

        return new ModuleInfo(
            (string)$meta['id'],
            (string)$meta['label'],
            (string)$meta['description'],
            $relatedModules,
            $sourcePath,
            $contextOutputFolder,
            $composerPackage,
            $keywords
        );
    }

    /**
     * Returns the source path (relative to project root) of the module directory
     * that contains the given YAML file.
     *
     * @param FileInfo $file
     * @return string E.g. `assets/classes/Variables/`
     */
    private function resolveSourcePath(FileInfo $file) : string
    {
        $moduleDir = dirname($file->getPath());
        $resolved  = realpath($this->rootFolder->getPath());
        $rootPath  = rtrim($resolved !== false ? $resolved : $this->rootFolder->getPath(), '/');
        $relative  = ltrim(str_replace($rootPath, '', $moduleDir), '/');

        return $relative . '/';
    }

    /**
     * Walks up the directory tree from the YAML file to find the nearest
     * `composer.json` and returns its `name` field. Results are cached per
     * directory.
     *
     * @param FileInfo $file
     * @return string
     */
    private function resolveComposerPackageName(FileInfo $file) : string
    {
        $current  = dirname($file->getPath());
        $resolved = realpath($this->rootFolder->getPath());
        $rootPath = $resolved !== false ? $resolved : $this->rootFolder->getPath();

        while(true) {
            $composerFile = $current . '/composer.json';

            if(file_exists($composerFile)) {
                if(isset($this->packageNameCache[$composerFile])) {
                    return $this->packageNameCache[$composerFile];
                }

                $json = JSONFile::factory($composerFile)->parse();
                if(isset($json['name']) && is_string($json['name'])) {
                    $this->packageNameCache[$composerFile] = $json['name'];
                    return $json['name'];
                }

                // composer.json found but no valid name — cache fallback and stop
                $this->packageNameCache[$composerFile] = self::FALLBACK_PACKAGE_NAME;
                return self::FALLBACK_PACKAGE_NAME;
            }

            // Stop when we reach the project root or the filesystem root
            if($current === $rootPath || $current === dirname($current)) {
                break;
            }

            $current = dirname($current);
        }

        // No composer.json found in the directory tree — return fallback
        return self::FALLBACK_PACKAGE_NAME;
    }

    /**
     * Extracts the CTX output folder from the first document's `outputPath` key.
     * For example, `modules/variables/overview.md` yields `.context/modules/variables/`.
     *
     * @param array<string, mixed> $yamlData The fully parsed YAML data.
     * @return string
     */
    private function resolveContextOutputFolder(array $yamlData) : string
    {
        if(
            !isset($yamlData['documents']) ||
            !is_array($yamlData['documents']) ||
            empty($yamlData['documents'])
        ) {
            return '.context/';
        }

        $firstDoc = $yamlData['documents'][0];

        if(!isset($firstDoc['outputPath']) || !is_string($firstDoc['outputPath'])) {
            return '.context/';
        }

        $outputDir = dirname($firstDoc['outputPath']);

        // dirname() returns '.' for a bare filename — normalise to empty string
        if($outputDir === '.') {
            return '.context/';
        }

        return '.context/' . trim($outputDir, '/') . '/';
    }
}
