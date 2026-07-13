<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\ModulesOverview;

use Application\Composer\KeywordGlossary\Events\DecorateGlossaryEvent;
use Application\Composer\KeywordGlossary\KeywordGlossaryBuilder;
use Application\EventHandler\OfflineEvents\OfflineEventsManager;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;

/**
 * Generic, subclassable generator that encapsulates the
 * application-agnostic module JSON export workflow.
 *
 * Discovers and parses all `module-context.yaml` files via
 * {@see ModuleContextFileFinder} and {@see ModuleInfoParser}, resolves
 * README overviews via {@see ReadmeOverviewParser} and module briefs via
 * {@see resolveModuleBrief()}, builds the keyword glossary via
 * {@see KeywordGlossaryBuilder}, fires {@see DecorateGlossaryEvent} to
 * collect custom glossary sections, and writes a JSON document with
 * `generatedAt`, `modules`, `glossary`, and `glossarySections` keys.
 *
 * Applications can subclass this generator and override the hook methods
 * {@see resolveModuleSource()} and {@see resolveModuleBrief()} to customise
 * module source classification and brief resolution without duplicating the
 * core data-collection workflow.
 *
 * Progress output is routed through the optional `$onProgress` callable.
 * When `null`, no output is produced, which is suitable for automated or
 * test contexts.
 *
 * @package Application
 * @subpackage Composer
 */
class ModuleJsonExportGenerator
{
    private FolderInfo $rootFolder;
    private ModuleInfoParser $parser;

    /** @var callable|null */
    private $onProgress;

    /**
     * @param FolderInfo    $rootFolder Root folder to scan for `module-context.yaml` files.
     * @param callable|null $onProgress Optional progress callback receiving a string message.
     */
    public function __construct(FolderInfo $rootFolder, ?callable $onProgress = null)
    {
        $this->rootFolder = $rootFolder;
        $this->parser     = new ModuleInfoParser($rootFolder);
        $this->onProgress = $onProgress;
    }

    /**
     * Emits a progress message via the `$onProgress` callback when one is set.
     *
     * Declared `protected` so that subclasses overriding hook methods such as
     * {@see resolveAdditionalDocs()}, {@see resolveModuleBrief()}, or
     * {@see resolveModuleSource()} can emit progress messages without
     * re-implementing the callback invocation pattern.
     *
     * @param string $message
     * @return void
     */
    protected function progress(string $message) : void
    {
        if($this->onProgress !== null)
        {
            ($this->onProgress)($message);
        }
    }

    /**
     * Orchestrates the full workflow: discovers modules, resolves descriptions
     * and briefs, builds the glossary, collects glossary sections, and writes
     * the JSON output file.
     *
     * By default only modules that have a brief are included in the output.
     * Pass `true` for `$includeAll` to include modules without a brief.
     *
     * @param string $outputPath Absolute path to the JSON output file.
     * @param bool   $includeAll When true, modules without a brief are also included.
     * @return void
     */
    public function generate(string $outputPath, bool $includeAll = false) : void
    {
        $this->progress('ModuleJsonExportGenerator: Discovering module-context.yaml files...');

        $finder = new ModuleContextFileFinder($this->rootFolder);
        $files  = $finder->findAll();

        $this->progress(sprintf('ModuleJsonExportGenerator: Discovered %d module-context.yaml file(s).', count($files)));

        /** @var ModuleInfo[] $modules */
        $modules = array();

        foreach($files as $file)
        {
            $moduleInfo = $this->parser->parseFile($file);

            if($moduleInfo === null)
            {
                continue;
            }

            $modules[] = $moduleInfo;
        }

        $this->progress(sprintf('ModuleJsonExportGenerator: Parsed %d module(s).', count($modules)));

        $moduleData  = array();
        $totalDocs   = 0;
        $totalSkipped = 0;

        foreach($modules as $module)
        {
            $sourcePath  = rtrim($this->rootFolder->getPath(), '/') . '/' . $module->getSourcePath();
            $brief       = $this->resolveModuleBrief($module, $sourcePath);

            if(!$includeAll && $brief === null)
            {
                continue;
            }

            $readmePath     = rtrim($sourcePath, '/') . '/README.md';
            $description    = ReadmeOverviewParser::extractOverview($readmePath);
            $source         = $this->resolveModuleSource($module);
            $additionalDocs = $this->resolveAdditionalDocs($module, $sourcePath);

            $totalDocs    += count($additionalDocs);
            // $totalSkipped counts only resolveAdditionalDocs() skips (missing/unreadable/out-of-
            // bounds files). Parser-level skips (non-.md entries) are dropped before a module is
            // added to $modules, so they are not reflected here.
            $totalSkipped += count($module->getExportDocs()) - count($additionalDocs);

            $moduleData[] = array(
                'id'             => $module->getId(),
                'label'          => $module->getLabel(),
                'summary'        => $module->getDescription(),
                'source'         => $source,
                'description'    => $description,
                'relatedModules' => $module->getRelatedModules(),
                'brief'          => $brief,
                'additionalDocs' => $additionalDocs,
            );
        }

        if($totalDocs > 0 || $totalSkipped > 0)
        {
            $this->progress(sprintf(
                'ModuleJsonExportGenerator: Additional docs: %d loaded, %d skipped.',
                $totalDocs,
                $totalSkipped
            ));
        }

        $glossary         = $this->buildGlossary($modules);
        $glossarySections = $this->collectGlossarySections();

        $output = array(
            'generatedAt'      => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'modules'          => $moduleData,
            'glossary'         => $glossary,
            'glossarySections' => $glossarySections,
        );

        $json = json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if($json === false)
        {
            throw new \RuntimeException('ModuleJsonExportGenerator: Failed to encode output as JSON.');
        }

        FileInfo::factory($outputPath)->putContents($json);

        $this->progress(sprintf('ModuleJsonExportGenerator: Output written to %s', $outputPath));
    }

    /**
     * Resolves the source classification string for a module.
     *
     * The default implementation returns the module's Composer package name.
     * Applications can override this method to return a human-readable
     * source label (e.g. `'framework'` or `'hcp-editor'`).
     *
     * @param ModuleInfo $module The parsed module info.
     * @return string The source classification string.
     */
    protected function resolveModuleSource(ModuleInfo $module) : string
    {
        return $module->getComposerPackage();
    }

    /**
     * Resolves the brief content for a module.
     *
     * The default implementation looks for a `README-Brief.md` file in
     * the module's source directory and returns its full content, or `null`
     * if the file does not exist or cannot be read.
     *
     * Applications can override this method to use a different brief file
     * name, location, or resolution strategy.
     *
     * @param ModuleInfo $module     The parsed module info.
     * @param string     $sourcePath Absolute path to the module's source directory.
     * @return string|null The brief content, or null if not found.
     */
    protected function resolveModuleBrief(ModuleInfo $module, string $sourcePath) : ?string
    {
        $briefPath = rtrim($sourcePath, '/') . '/README-Brief.md';

        if(!file_exists($briefPath))
        {
            return null;
        }

        $content = file_get_contents($briefPath);

        if($content === false)
        {
            return null;
        }

        return $content;
    }

    /**
     * Resolves the additional documentation files declared in the module's
     * `moduleMetaData.exportDocs` list.
     *
     * Iterates each relative path, resolves it against `$sourcePath`, applies a
     * containment guard to prevent path traversal outside the module's source
     * directory, reads the file content, and returns an array of `{fileName, content}`
     * objects. Missing, unreadable, or out-of-bounds files are skipped with a
     * progress warning.
     *
     * **Containment guard — trailing-slash semantics:** `$sourceBase` is always
     * terminated with a `/` before the `str_starts_with()` comparison. This is
     * required to prevent sibling-directory prefix collisions: without the
     * trailing slash, a module at `/modules/foo` would incorrectly pass paths
     * under `/modules/foobar`, because `"foobar/..."` starts with `"foo"`.
     *
     * **Fallback path:** when `realpath($sourcePath)` returns `false` (e.g. the
     * source directory is a dangling symlink), the raw `$sourcePath` is used as
     * `$sourceBase` instead. Any file inside such a directory will also fail
     * `realpath()`, so the `$resolvedPath === false` clause in the guard catches
     * all entries before the prefix comparison runs.
     *
     * @see ModuleJsonExportGeneratorTest::test_generate_exportDocs_siblingDirectoryBoundaryCollision_isBlocked()
     *
     * Subclasses may override this method to customise resolution logic.
     *
     * @param ModuleInfo $module     The parsed module info.
     * @param string     $sourcePath Absolute path to the module's source directory.
     * @return array<int, array{fileName: string, content: string}>
     */
    protected function resolveAdditionalDocs(ModuleInfo $module, string $sourcePath) : array
    {
        $result          = array();
        $resolvedSource  = realpath(rtrim($sourcePath, '/'));
        // Fallback to the raw path if realpath() fails (e.g., the source dir is a dangling symlink).
        // In that case every file inside it will also fail realpath(), so the containment guard's
        // $resolvedPath === false clause will catch them all before the str_starts_with() comparison.
        $sourceBase      = rtrim($resolvedSource !== false ? $resolvedSource : rtrim($sourcePath, '/'), '/') . '/';

        foreach($module->getExportDocs() as $relPath)
        {
            $absolutePath = rtrim($sourcePath, '/') . '/' . $relPath;
            $resolvedPath = realpath($absolutePath);

            // Containment guard: path must resolve inside the module's source directory.
            // realpath() returns false for paths that do not exist or cannot be resolved,
            // so a false return already covers the "file not found" case.
            if($resolvedPath === false || !str_starts_with($resolvedPath, $sourceBase))
            {
                $this->progress(sprintf(
                    'ModuleJsonExportGenerator: exportDocs entry "%s" for module "%s" resolves outside the module directory or does not exist — skipping.',
                    $relPath,
                    $module->getId()
                ));
                continue;
            }

            $content = file_get_contents($resolvedPath);

            if($content === false)
            {
                $this->progress(sprintf(
                    'ModuleJsonExportGenerator: exportDocs file "%s" for module "%s" could not be read — skipping.',
                    $relPath,
                    $module->getId()
                ));
                continue;
            }

            $result[] = array(
                'fileName' => basename($resolvedPath),
                'content'  => $content,
            );
        }

        return $result;
    }

    /**
     * Builds the deduplicated, alphabetically sorted keyword glossary
     * from all parsed module keywords, delegating to {@see KeywordGlossaryBuilder}.
     *
     * @param ModuleInfo[] $modules
     * @return array<int, array{term: string, context: string, relatedModules: string[]}>
     */
    private function buildGlossary(array $modules) : array
    {
        $entries  = (new KeywordGlossaryBuilder($modules))->build();
        $glossary = array();

        foreach($entries as $entry)
        {
            $glossary[] = array(
                'term'           => $entry->getKeyword(),
                'context'        => $entry->getContext(),
                'relatedModules' => $entry->getModuleIds(),
            );
        }

        return $glossary;
    }

    /**
     * Fires the offline {@see DecorateGlossaryEvent} to collect custom
     * glossary sections from registered listeners, then serializes them
     * into a plain array structure suitable for JSON encoding.
     *
     * Returns an empty array when no listeners are registered or when the
     * offline event index has not yet been built.
     *
     * @return array<int, array{heading: string, columnHeaders: string[], entries: array<int, array{values: string[]}>}>
     */
    private function collectGlossarySections() : array
    {
        $manager   = new OfflineEventsManager();
        $container = $manager->triggerEvent(DecorateGlossaryEvent::EVENT_NAME);
        $event     = $container->getTriggeredEvent();

        if(!($event instanceof DecorateGlossaryEvent))
        {
            return array();
        }

        $result = array();

        foreach($event->getSections() as $section)
        {
            $entries = array();

            foreach($section->getEntries() as $entry)
            {
                $entries[] = array(
                    'values' => $entry->getValues(),
                );
            }

            $result[] = array(
                'heading'       => $section->getHeading(),
                'columnHeaders' => $section->getColumnHeaders(),
                'entries'       => $entries,
            );
        }

        return $result;
    }
}
