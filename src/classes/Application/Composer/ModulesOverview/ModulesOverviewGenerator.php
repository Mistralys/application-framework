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

    private FolderInfo $rootFolder;
    private ModuleInfoParser $parser;

    public function __construct(FolderInfo $rootFolder)
    {
        $this->rootFolder = $rootFolder;
        $this->parser     = new ModuleInfoParser($rootFolder);
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
            $moduleInfo = $this->parser->parseFile($file);
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

}
