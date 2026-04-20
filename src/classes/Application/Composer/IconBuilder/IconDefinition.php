<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\IconBuilder;

/**
 * Value object for a single icon definition parsed from an icons JSON file.
 * Holds the icon's ID, FA icon name, and FA icon type (prefix), and provides
 * derived name forms used by the language renderers during code generation.
 *
 * @package Application
 * @subpackage Composer
 * @see IconsReader
 */
class IconDefinition
{
    private string $id;
    private string $iconName;
    private string $iconType;

    public function __construct(string $id, string $iconName, string $iconType)
    {
        $this->id = $id;
        $this->iconName = $iconName;
        $this->iconType = $iconType;
    }

    /**
     * Returns the icon's ID as defined in the JSON source file
     * (e.g. `attention_required` or `apiClients`).
     *
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * Returns the FontAwesome icon name (e.g. `exclamation-triangle`).
     *
     * @return string
     */
    public function getIconName() : string
    {
        return $this->iconName;
    }

    /**
     * Returns the FontAwesome icon type / prefix (e.g. `far`, `fas`).
     * Empty string when the default prefix applies.
     *
     * @return string
     */
    public function getIconType() : string
    {
        return $this->iconType;
    }

    /**
     * Returns the full icon identifier in the form `type:name` when a type is
     * present, or just the icon name when the type is empty.
     *
     * Examples: `far:sun`, `rocket`.
     *
     * @return string
     */
    public function getFullIconName() : string
    {
        if(!empty($this->iconType))
        {
            return $this->iconType . ':' . $this->iconName;
        }

        return $this->iconName;
    }

    /**
     * Returns the icon ID in UPPER_SNAKE_CASE form suitable for use as a
     * PHP class constant name.
     *
     * Examples: `attention_required` → `ATTENTION_REQUIRED`,
     * `apiClients` → `API_CLIENTS`, `actioncode` → `ACTIONCODE`.
     *
     * @return string
     */
    public function getConstantName() : string
    {
        $name = (string)preg_replace('/(?<!^)(?=[A-Z])/', '_', $this->id);
        return strtoupper($name);
    }
}
