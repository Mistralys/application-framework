<?php
/**
 * @package Application
 * @subpackage UserInterface
 */

declare(strict_types=1);

namespace UI\Page\Section;

use UI\Event\PageRendered;
use UI_Page_Section;

/**
 * Global registry of sections created in the current request.
 * Offers methods to access the section instances to fetch
 * information about them.
 *
 * > NOTE: This makes the most sense to be used at the end of
 * > the request, when all sections are known. Look at the
 * > event {@see PageRendered} for example.
 *
 * @package Application
 * @subpackage UserInterface
 */
class SectionsRegistry
{
    /**
     * @var array<string,UI_Page_Section>
     */
    private static array $sections = array();

    /**
     * Registers a section instance.
     *
     * @param UI_Page_Section $section
     * @return void
     */
    public static function register(UI_Page_Section $section) : void
    {
        self::$sections[$section->getInstanceID()] = $section;
    }

    public static function getAll() : array
    {
        return array_values(self::$sections);
    }

    /**
     * @param string $group
     * @return UI_Page_Section[]
     */
    public static function getByGroup(string $group) : array
    {
        $result = array();

        foreach(self::$sections as $section)
        {
            if($section->getGroup() === $group)
            {
                $result[] = $section;
            }
        }

        return $result;
    }

    /**
     * Fetches all sections that have been rendered in the
     * target group.
     *
     * @param string $group
     * @return UI_Page_Section[]
     */
    public static function getRenderedByGroup(string $group) : array
    {
        $result = array();

        foreach(self::getByGroup($group) as $section)
        {
            if($section->hasBeenRendered())
            {
                $result[] = $section;
            }
        }

        return $result;
    }
}
