<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\IconBuilder;

use AppUtils\FileHelper\JSONFile;
use UI\Icons\IconInfo;

/**
 * Parses an icons JSON file and returns a sorted, filtered list of
 * {@see IconDefinition} instances for use by the language renderers
 * during icon code generation.
 *
 * IDs are normalised on load (hyphens and spaces become underscores).
 * The spinner icon is excluded from the result set because it has
 * special runtime behaviour and must not be overwritten by the builder.
 *
 * @package Application
 * @subpackage Composer
 * @see IconDefinition
 * @see IconBuilder
 */
class IconsReader
{
    public const string EXCLUDED_ICON_SPINNER = 'spinner';

    private string $jsonPath;

    /**
     * @var IconDefinition[]|null
     */
    private ?array $icons = null;

    public function __construct(string $jsonPath)
    {
        $this->jsonPath = $jsonPath;
    }

    /**
     * Returns the path to the icons JSON file.
     *
     * @return string
     */
    public function getPath() : string
    {
        return $this->jsonPath;
    }

    /**
     * Returns all parsed icon definitions, sorted alphabetically by ID
     * and with the spinner icon excluded.
     *
     * @return IconDefinition[]
     */
    public function getIcons() : array
    {
        if($this->icons === null)
        {
            $this->icons = $this->load();
        }

        return $this->icons;
    }

    /**
     * Returns the number of icon definitions in the parsed set.
     *
     * @return int
     */
    public function countIcons() : int
    {
        return count($this->getIcons());
    }

    /**
     * Loads and parses the JSON file, normalises IDs, excludes the spinner,
     * and sorts the resulting definitions alphabetically by ID.
     *
     * @return IconDefinition[]
     */
    private function load() : array
    {
        $file = JSONFile::factory($this->jsonPath);

        $rawData = array();

        if($file->exists())
        {
            $rawData = $file->parse();
        }

        $icons = array();

        foreach($rawData as $rawID => $iconDef)
        {
            $id = IconInfo::normaliseID((string)$rawID);

            if($id === self::EXCLUDED_ICON_SPINNER)
            {
                continue;
            }

            $iconName = isset($iconDef['icon']) ? (string)$iconDef['icon'] : '';
            $iconType = isset($iconDef['type']) ? (string)$iconDef['type'] : '';

            $icons[$id] = new IconDefinition($id, $iconName, $iconType);
        }

        ksort($icons);

        return array_values($icons);
    }

}
