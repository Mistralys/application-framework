<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary;

use Application\Composer\KeywordGlossary\Events\DecorateGlossaryEvent;
use Application\Composer\ModulesOverview\ModuleContextFileFinder;
use Application\Composer\ModulesOverview\ModuleInfoParser;
use Application\EventHandler\OfflineEvents\OfflineEventsManager;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;

/**
 * Orchestrates the keyword-glossary generation workflow.
 *
 * Discovers all `module-context.yaml` files via {@see ModuleContextFileFinder},
 * delegates parsing to {@see ModuleInfoParser} to obtain {@see ModuleInfo} value
 * objects from each (files lacking `id`, `label`, or `description` are skipped),
 * delegates keyword deduplication and sorting to {@see KeywordGlossaryBuilder},
 * fires {@see DecorateGlossaryEvent} via the offline events manager to collect
 * custom {@see GlossarySection} instances, renders the Markdown document
 * via {@see KeywordGlossaryRenderer}, and writes it to the specified output path.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordGlossaryGenerator
{
    /**
     * @var FolderInfo
     */
    private FolderInfo $rootFolder;

    /** @var callable|null */
    private $onProgress;

    /**
     * @param FolderInfo    $rootFolder Root folder to scan for `module-context.yaml` files.
     * @param callable|null $onProgress Optional progress callback receiving a string message.
     */
    public function __construct(FolderInfo $rootFolder, ?callable $onProgress = null)
    {
        $this->rootFolder = $rootFolder;
        $this->onProgress = $onProgress;
    }

    /**
     * Returns the root folder.
     *
     * @return FolderInfo
     */
    public function getRootFolder() : FolderInfo
    {
        return $this->rootFolder;
    }

    /**
     * Runs the full generation workflow and writes the output file.
     *
     * @param string $outputPath Absolute path to the Markdown output file.
     * @return void
     */
    public function generate(string $outputPath) : void
    {
        $finder = new ModuleContextFileFinder($this->rootFolder);
        $files  = $finder->findAll();
        $parser = new ModuleInfoParser($this->rootFolder);

        $modules = array();

        foreach($files as $file)
        {
            $info = $parser->parseFile($file);

            if($info !== null)
            {
                $modules[] = $info;
            }
        }

        $entries  = (new KeywordGlossaryBuilder($modules, $this->onProgress))->build();
        $sections = $this->collectSections();
        $markdown = (new KeywordGlossaryRenderer($entries, $sections))->render();

        FileInfo::factory($outputPath)->putContents($markdown);

        if($this->onProgress !== null) {
            ($this->onProgress)('KeywordGlossaryGenerator: Output written to ' . $outputPath);
        }
    }

    /**
     * Fires the offline {@see DecorateGlossaryEvent} to collect custom
     * glossary sections from registered listeners.
     *
     * Returns an empty array when no listeners are registered or when the
     * offline event index has not yet been built.
     *
     * @return GlossarySection[]
     */
    private function collectSections() : array
    {
        $manager   = new OfflineEventsManager();
        $container = $manager->triggerEvent(DecorateGlossaryEvent::EVENT_NAME);
        $event     = $container->getTriggeredEvent();

        if($event instanceof DecorateGlossaryEvent)
        {
            return $event->getSections();
        }

        return array();
    }
}
