<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\KeywordGlossary;

use Application\Composer\ModulesOverview\ModuleInfo;

/**
 * Builds a deduplicated, sorted list of {@see KeywordEntry} objects
 * from a collection of {@see ModuleInfo} value objects.
 *
 * Encapsulates the keyword collection, conflict detection, deduplication,
 * and alphabetical sorting that was previously duplicated across
 * {@see KeywordGlossaryGenerator} and `ModuleJsonExportGenerator`.
 *
 * @package Application
 * @subpackage Composer
 */
final class KeywordGlossaryBuilder
{
    /**
     * @var ModuleInfo[]
     */
    private array $modules;

    /** @var callable|null */
    private $onProgress;

    /**
     * @param ModuleInfo[]  $modules    Parsed module info objects to build the glossary from.
     * @param callable|null $onProgress Optional progress callback receiving a string message.
     */
    public function __construct(array $modules, ?callable $onProgress = null)
    {
        $this->modules    = $modules;
        $this->onProgress = $onProgress;
    }

    /**
     * Builds the deduplicated and alphabetically sorted keyword entry list.
     *
     * Keywords are keyed by their lowercase form; only the first-seen casing is
     * preserved. When the same keyword appears in multiple modules, the module
     * IDs are merged. A conflict warning is issued via the progress callback when
     * the same keyword carries different context strings across modules.
     *
     * @return KeywordEntry[]
     */
    public function build() : array
    {
        /** @var array<string, KeywordEntry> $map */
        $map = array();

        foreach($this->modules as $module)
        {
            $moduleId = $module->getId();

            foreach($module->getKeywords() as $rawKeyword)
            {
                $parsed = KeywordParser::parse($rawKeyword);

                if($parsed['keyword'] === '')
                {
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

        return $entries;
    }
}
