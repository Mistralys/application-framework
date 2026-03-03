<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary;

use Application\Composer\BuildMessages;
use Application\Composer\KeywordGlossary\Events\DecorateGlossaryEvent;
use Application\Composer\ModulesOverview\ModuleContextFileFinder;
use Application\EventHandler\OfflineEvents\OfflineEventsManager;
use AppUtils\FileHelper\FileInfo;
use AppUtils\FileHelper\FolderInfo;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Orchestrates the keyword-glossary generation workflow.
 *
 * Discovers all `module-context.yaml` files via {@see ModuleContextFileFinder},
 * extracts `moduleMetaData.id` and `moduleMetaData.keywords` from each,
 * builds a de-duplicated {@see KeywordEntry} map, fires
 * {@see DecorateGlossaryEvent} via the offline events manager to collect
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

        // Keys are lowercase keyword strings; first-seen casing is preserved.
        /** @var array<string, KeywordEntry> $map */
        $map = array();

        foreach($files as $file)
        {
            try
            {
                $data = Yaml::parseFile($file->getPath());
            }
            catch(ParseException $e)
            {
                BuildMessages::addMessage(
                    'KeywordGlossaryGenerator',
                    BuildMessages::LEVEL_WARNING,
                    'Failed to parse ' . $file->getPath() . ' — ' . $e->getMessage()
                );

                if($this->onProgress !== null) {
                    ($this->onProgress)('WARNING: Failed to parse ' . $file->getPath() . ' — ' . $e->getMessage());
                }

                continue;
            }

            if(!is_array($data) || !isset($data['moduleMetaData']) || !is_array($data['moduleMetaData']))
            {
                continue;
            }

            $meta = $data['moduleMetaData'];

            if(!isset($meta['id']))
            {
                continue;
            }

            $moduleId = (string)$meta['id'];

            $keywords = array();

            if(isset($meta['keywords']) && is_array($meta['keywords']))
            {
                $keywords = array_map('strval', $meta['keywords']);
            }

            foreach($keywords as $rawKeyword)
            {
                $parsed = KeywordParser::parse($rawKeyword);

                if($parsed['keyword'] === '') {
                    continue;
                }

                $lowerKey = strtolower($parsed['keyword']);

                if(isset($map[$lowerKey]))
                {
                    if(
                        $this->onProgress !== null
                        && strcasecmp($parsed['context'], $map[$lowerKey]->getContext()) !== 0
                    ) {
                        ($this->onProgress)(
                            'WARNING: Keyword conflict for "' . $parsed['keyword'] . '": '
                            . '"' . $map[$lowerKey]->getContext() . '" (module: ' . implode(', ', $map[$lowerKey]->getModuleIds()) . ') '
                            . 'vs "' . $parsed['context'] . '" (module: ' . $moduleId . ')'
                        );
                    }

                    $map[$lowerKey] = $map[$lowerKey]->addModuleId($moduleId);
                }
                else
                {
                    $map[$lowerKey] = new KeywordEntry($parsed['keyword'], $parsed['context'], array($moduleId));
                }
            }
        }

        $entries = array_values($map);

        usort($entries, static function(KeywordEntry $a, KeywordEntry $b) : int {
            return strcasecmp($a->getKeyword(), $b->getKeyword());
        });

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
