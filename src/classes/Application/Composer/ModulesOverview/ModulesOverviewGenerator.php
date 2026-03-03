<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\ModulesOverview;

use Application\Composer\BuildMessages;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use AppUtils\FileHelper\JSONFile;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Orchestrates the module overview generation workflow.
 *
 * Discovers all `module-context.yaml` files via {@see ModuleContextFileFinder},
 * parses each into a {@see ModuleInfo} value object, renders the resulting
 * Markdown document via {@see ModulesOverviewRenderer}, and writes it to
 * `docs/agents/project-manifest/modules-overview.md`.
 *
 * @package Application
 * @subpackage Composer
 */
final class ModulesOverviewGenerator
{
    private const OUTPUT_RELATIVE_PATH = '/docs/agents/project-manifest/modules-overview.md';
    private const FALLBACK_PACKAGE_NAME = 'unknown/package';

    private FolderInfo $rootFolder;

    /**
     * Cache of resolved Composer package names, keyed by the resolved `composer.json`
     * absolute file path. This ensures one cache entry per `composer.json`, regardless
     * of how many sibling module directories share the same Composer root.
     *
     * @var array<string, string>
     */
    private array $packageNameCache = array();

    public function __construct(FolderInfo $rootFolder)
    {
        $this->rootFolder = $rootFolder;
    }

    /**
     * Runs the full generation workflow and writes the output file.
     *
     * @return void
     */
    public function generate() : void
    {
        echo 'ModulesOverviewGenerator: Discovering module-context.yaml files...' . PHP_EOL;

        $finder = new ModuleContextFileFinder($this->rootFolder);
        $files  = $finder->findAll();

        echo sprintf('ModulesOverviewGenerator: Discovered %d module-context.yaml file(s).', count($files)) . PHP_EOL;

        $modules = array();

        foreach($files as $file) {
            $moduleInfo = $this->parseModuleFile($file);
            if($moduleInfo === null) {
                continue;
            }
            $modules[] = $moduleInfo;
        }

        echo sprintf('ModulesOverviewGenerator: Parsed %d module(s).', count($modules)) . PHP_EOL;

        $this->validateRelations($modules);

        $renderer = new ModulesOverviewRenderer($modules);
        $markdown = $renderer->render();

        $outputPath = rtrim($this->rootFolder->getPath(), '/') . self::OUTPUT_RELATIVE_PATH;

        FileInfo::factory($outputPath)->putContents($markdown);

        echo sprintf('ModulesOverviewGenerator: Output written to %s', $outputPath) . PHP_EOL;
        echo sprintf(
            'ModulesOverviewGenerator: %d modules from %d package(s) — done.',
            count($modules),
            count(array_unique(array_map(static function(ModuleInfo $m) : string {
                return $m->getComposerPackage();
            }, $modules)))
        ) . PHP_EOL;
    }

    /**
     * Validates that all `relatedModules` references are bidirectional.
     *
     * For every module A that lists module B as related, module B must also list
     * module A back — **but only when both modules belong to the same Composer
     * package**. Cross-package references (e.g. an application module linking to
     * a framework module) are intentionally one-directional and are not flagged.
     *
     * References to module IDs that do not exist in any discovered package are
     * still reported as errors, as they are most likely typos.
     *
     * @param ModuleInfo[] $modules
     */
    private function validateRelations(array $modules) : void
    {
        /** @var array<string, ModuleInfo> $idMap */
        $idMap = array();
        foreach($modules as $module) {
            $idMap[$module->getId()] = $module;
        }

        // Errors are grouped by the Composer package of the source module so that
        // framework issues are clearly separated from application issues.
        /** @var array<string, string[]> $errorsByPackage */
        $errorsByPackage = array();

        foreach($modules as $module) {
            foreach($module->getRelatedModules() as $relatedId) {
                $package = $module->getComposerPackage();

                if(!isset($idMap[$relatedId])) {
                    $errorsByPackage[$package][] = sprintf(
                        "  - Module '%s' references unknown module '%s'.",
                        $module->getId(),
                        $relatedId
                    );
                    continue;
                }

                // Only enforce bidirectionality within the same Composer package.
                // Cross-package relations (e.g. app → framework) are one-directional by design.
                $related = $idMap[$relatedId];
                if(
                    $related->getComposerPackage() === $package &&
                    !in_array($module->getId(), $related->getRelatedModules(), true)
                ) {
                    $errorsByPackage[$package][] = sprintf(
                        "  - Module '%s' lists '%s' as related, but '%s' does not list '%s' back.",
                        $module->getId(),
                        $relatedId,
                        $relatedId,
                        $module->getId()
                    );
                }
            }
        }

        if(empty($errorsByPackage)) {
            return;
        }

        foreach($errorsByPackage as $package => $errors) {
            BuildMessages::addError(
                'ModulesOverviewGenerator (' . $package . ')',
                'Asymmetric `relatedModules` entries detected — fix the affected ' .
                '`module-context.yaml` files:' . PHP_EOL .
                implode(PHP_EOL, $errors)
            );
        }
    }

    /**
     * Parses a single `module-context.yaml` file and returns the corresponding
     * {@see ModuleInfo}. Returns `null` if the file lacks a valid `moduleMetaData`
     * section (with a warning echoed to stdout).
     *
     * @param FileInfo $file
     * @return ModuleInfo|null
     */
    private function parseModuleFile(FileInfo $file) : ?ModuleInfo
    {
        try {
            $data = Yaml::parseFile($file->getPath());
        } catch (ParseException $e) {
            echo sprintf(
                'ERROR: Failed to parse %s — %s',
                $file->getPath(),
                $e->getMessage()
            ) . PHP_EOL;
            throw $e;
        }

        if(!is_array($data) || !isset($data['moduleMetaData']) || !is_array($data['moduleMetaData'])) {
            echo sprintf('WARNING: No moduleMetaData section found in %s — skipping.', $file->getPath()) . PHP_EOL;
            return null;
        }

        $meta = $data['moduleMetaData'];

        if(!isset($meta['id'], $meta['label'], $meta['description'])) {
            echo sprintf('WARNING: Incomplete moduleMetaData in %s — skipping.', $file->getPath()) . PHP_EOL;
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
        $moduleDir  = dirname($file->getPath());
        $resolved   = realpath($this->rootFolder->getPath());
        $rootPath   = rtrim($resolved !== false ? $resolved : $this->rootFolder->getPath(), '/');
        $relative   = ltrim(str_replace($rootPath, '', $moduleDir), '/');

        return $relative . '/';
    }

    /**
     * Walks up the directory tree from the YAML file to find the nearest
     * `composer.json` and returns its `name` field. Results are cached per directory.
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
