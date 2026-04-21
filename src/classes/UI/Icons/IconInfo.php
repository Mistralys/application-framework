<?php
/**
 * @package UI
 * @subpackage Icons
 */

declare(strict_types=1);

namespace UI\Icons;

use UI_Icon;

/**
 * Read-only value object for a single available icon. Holds the icon's
 * ID, FA icon name, FA prefix, and whether it is a custom (application)
 * icon or a standard (framework) icon. Provides a factory method to
 * create the matching {@see UI_Icon} instance.
 *
 * @package UI
 * @subpackage Icons
 * @see IconCollection
 */
class IconInfo
{
    private string $id;
    private string $iconName;
    private string $prefix;
    private bool $isCustom;

    public function __construct(string $id, string $iconName, string $prefix, bool $isCustom)
    {
        $this->id = $id;
        $this->iconName = $iconName;
        $this->prefix = $prefix;
        $this->isCustom = $isCustom;
    }

    /**
     * @return string
     */
    public function getID() : string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIconName() : string
    {
        return $this->iconName;
    }

    /**
     * @return string
     */
    public function getPrefix() : string
    {
        return $this->prefix;
    }

    /**
     * @return bool
     */
    public function isCustom() : bool
    {
        return $this->isCustom;
    }

    /**
     * @return bool
     */
    public function isStandard() : bool
    {
        return !$this->isCustom;
    }

    /**
     * Creates a UI_Icon instance with this icon's type pre-configured.
     *
     * When the prefix is empty, {@see UI_Icon::setType()} is called with
     * one argument only (matching the generated method convention). When a
     * prefix is present it is passed as the second argument.
     *
     * @return UI_Icon
     */
    public function createIcon() : UI_Icon
    {
        $icon = new UI_Icon();

        if(empty($this->prefix))
        {
            $icon->setType($this->iconName);
        }
        else
        {
            $icon->setType($this->iconName, $this->prefix);
        }

        return $icon;
    }

    /**
     * Normalises an icon ID by replacing hyphens and spaces with underscores.
     *
     * This is the canonical normalisation method used by both the runtime
     * registry ({@see IconCollection}) and the build-time code generator
     * ({@see \Application\Composer\IconBuilder\IconsReader}). Always delegate
     * to this method rather than repeating the inline formula.
     *
     * Example:
     * ```php
     * IconInfo::normaliseID('time-tracker');  // → 'time_tracker'
     * IconInfo::normaliseID('my icon name');  // → 'my_icon_name'
     * IconInfo::normaliseID('already_ok');    // → 'already_ok'
     * IconInfo::normaliseID('apiClients');    // → 'apiclients'
     * IconInfo::normaliseID('API_Keys');      // → 'api_keys'
     * ```
     *
     * @param string $id Raw icon ID (may contain hyphens or spaces).
     * @return string Normalised icon ID: lowercase, underscores only.
     * @since 1.0.0
     */
    public static function normaliseID(string $id) : string
    {
        return strtolower(str_replace(array('-', ' '), '_', $id));
    }

    /**
     * Returns the method name used in the icon classes, derived by
     * converting the underscore-separated ID to camelCase.
     *
     * Examples: `attention_required` → `attentionRequired`, `add` → `add`.
     *
     * @return string
     */
    public function getMethodName() : string
    {
        $parts = explode('_', $this->id);
        $methodName = array_shift($parts);

        foreach($parts as $part)
        {
            $methodName .= ucfirst($part);
        }

        return $methodName;
    }

    /**
     * Returns the full icon name including prefix, e.g. `far:sun`.
     * When the prefix is empty, only the icon name is returned, e.g. `rocket`.
     *
     * @return string
     */
    public function getFullIconName() : string
    {
        if(!empty($this->prefix))
        {
            return $this->prefix . ':' . $this->iconName;
        }

        return $this->iconName;
    }
}
