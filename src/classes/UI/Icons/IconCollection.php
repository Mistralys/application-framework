<?php
/**
 * @package UI
 * @subpackage Icons
 */

declare(strict_types=1);

namespace UI\Icons;

use AppUtils\FileHelper\JSONFile;

/**
 * Singleton registry of all available icons — both framework standard icons
 * and application custom icons. On first access the collection loads and
 * merges the two JSON sources, normalises IDs (hyphens/spaces → underscores),
 * and sorts the result alphabetically by icon ID.
 *
 * Custom icons with the same ID as a standard icon replace the standard entry,
 * allowing applications to override framework icons.
 *
 * @package UI
 * @subpackage Icons
 * @see IconInfo
 */
class IconCollection
{
    private static ?IconCollection $instance = null;

    /**
     * @var array<string,IconInfo>
     */
    private array $icons = array();

    private function __construct()
    {
        $this->loadIcons();
    }

    public static function getInstance() : self
    {
        if(self::$instance === null)
        {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Resets the singleton instance to null.
     *
     * @internal For use in tests only — allows each test to start with a
     *           fresh collection instance and prevents state leaking between
     *           test cases.
     * @return void
     */
    public static function resetInstance() : void
    {
        self::$instance = null;
    }

    /**
     * Returns all available icons sorted alphabetically by ID.
     *
     * @return IconInfo[]
     */
    public function getAll() : array
    {
        return array_values($this->icons);
    }

    /**
     * Returns only the framework standard icons, sorted alphabetically by ID.
     *
     * @return IconInfo[]
     */
    public function getStandardIcons() : array
    {
        $result = array();

        foreach($this->icons as $icon)
        {
            if($icon->isStandard())
            {
                $result[] = $icon;
            }
        }

        return $result;
    }

    /**
     * Returns only the application custom icons, sorted alphabetically by ID.
     *
     * @return IconInfo[]
     */
    public function getCustomIcons() : array
    {
        $result = array();

        foreach($this->icons as $icon)
        {
            if($icon->isCustom())
            {
                $result[] = $icon;
            }
        }

        return $result;
    }

    /**
     * Checks whether an icon with the given ID exists in the collection.
     *
     * @param string $iconID
     * @return bool
     */
    public function idExists(string $iconID) : bool
    {
        return isset($this->icons[$iconID]);
    }

    /**
     * Returns the {@see IconInfo} for the given icon ID.
     *
     * NOTE: The ID must be in its normalised form — hyphens and spaces
     * converted to underscores (e.g. `time_tracker`, not `time-tracker`).
     * To look up an icon using an un-normalised key, normalise it first via
     * {@see IconInfo::normaliseID()}. Use {@see self::idExists()}
     * to test existence before calling this method.
     *
     * @param string $iconID Normalised icon ID (underscores, no hyphens/spaces).
     * @return IconInfo
     * @throws \RuntimeException When no icon with the given ID exists.
     */
    public function getByID(string $iconID) : IconInfo
    {
        if(isset($this->icons[$iconID]))
        {
            return $this->icons[$iconID];
        }

        throw new \RuntimeException(sprintf(
            'No icon with ID [%s] found in the icon collection.',
            $iconID
        ));
    }

    /**
     * Returns the total number of icons in the collection.
     *
     * @return int
     */
    public function countIcons() : int
    {
        return count($this->icons);
    }

    /**
     * Loads icons from the framework JSON file and, if present, from the
     * application custom icons JSON file. Custom icons replace standard
     * icons with the same normalised ID.
     *
     * @return void
     */
    private function loadIcons() : void
    {
        $this->loadFromFile(
            APP_INSTALL_FOLDER . '/themes/default/icons.json',
            false
        );

        $customPath = APP_ROOT . '/themes/custom-icons.json';

        if(file_exists($customPath))
        {
            $this->loadFromFile($customPath, true);
        }

        ksort($this->icons);
    }

    /**
     * Parses a single icons JSON file and adds its entries to the collection.
     * Each icon ID is normalised by replacing hyphens and spaces with underscores.
     *
     * @param string $path     Absolute path to the JSON file.
     * @param bool   $isCustom Whether these icons are custom (application) icons.
     * @return void
     */
    private function loadFromFile(string $path, bool $isCustom) : void
    {
        $file = JSONFile::factory($path);

        if(!$file->exists())
        {
            return;
        }

        $data = $file->parse();

        foreach($data as $rawID => $iconDef)
        {
            $id = IconInfo::normaliseID((string)$rawID);

            $iconName = isset($iconDef['icon']) ? (string)$iconDef['icon'] : '';
            $prefix   = isset($iconDef['type']) ? (string)$iconDef['type'] : '';

            $this->icons[$id] = new IconInfo($id, $iconName, $prefix, $isCustom);
        }
    }

}
