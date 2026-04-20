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
     * @param string $message
     * @return void
     */
    private function progress(string $message) : void
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

        $moduleData = array();

        foreach($modules as $module)
        {
            $sourcePath  = rtrim($this->rootFolder->getPath(), '/') . '/' . $module->getSourcePath();
            $brief       = $this->resolveModuleBrief($module, $sourcePath);

            if(!$includeAll && $brief === null)
            {
                continue;
            }

            $readmePath  = rtrim($sourcePath, '/') . '/README.md';
            $description = ReadmeOverviewParser::extractOverview($readmePath);
            $source      = $this->resolveModuleSource($module);

            $moduleData[] = array(
                'id'             => $module->getId(),
                'label'          => $module->getLabel(),
                'summary'        => $module->getDescription(),
                'source'         => $source,
                'description'    => $description,
                'relatedModules' => $module->getRelatedModules(),
                'brief'          => $brief,
            );
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
