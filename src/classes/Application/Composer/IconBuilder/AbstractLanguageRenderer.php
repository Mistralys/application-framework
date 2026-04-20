<?php
/**
 * @package Application
 * @subpackage Composer
 */

declare(strict_types=1);

namespace Application\Composer\IconBuilder;

/**
 * Abstract base class for language-specific icon method renderers. Provides
 * the shared region-marker structure and method iteration loop; subclasses
 * implement only the single-method rendering logic.
 *
 * The rendered output of {@see self::render()} is the text that replaces the
 * content between the `START METHODS` and `END METHODS` marker comments
 * inside the target PHP or JS file.
 *
 * @package Application
 * @subpackage Composer
 * @see PHPRenderer
 * @see JSRenderer
 * @see IconsReader
 */
abstract class AbstractLanguageRenderer
{
    /**
     * The region label used in the `// region:` comment marker.
     * Both PHP and JS renderers share this unified label.
     */
    protected const string REGION_LABEL = 'Icon methods';

    private IconsReader $iconsReader;

    public function __construct(IconsReader $iconsReader)
    {
        $this->iconsReader = $iconsReader;
    }

    /**
     * Returns the icons reader instance providing the icon definitions
     * to render.
     *
     * @return IconsReader
     */
    public function getIconsReader() : IconsReader
    {
        return $this->iconsReader;
    }

    /**
     * Renders the code for a single icon definition. The returned string
     * must include all relevant lines for that method plus a trailing newline.
     *
     * @param IconDefinition $icon
     * @return string
     */
    abstract protected function renderMethod(IconDefinition $icon) : string;

    /**
     * Assembles and returns the complete replacement content that goes
     * between the `START METHODS` and `END METHODS` marker comments.
     *
     * The output includes the region label marker, all generated icon
     * method lines, and the endregion marker, surrounded by the blank
     * lines that preserve the original file structure.
     *
     * @return string
     */
    public function render() : string
    {
        $parts = array();

        $parts[] = PHP_EOL;
        $parts[] = PHP_EOL . '    // region: ' . self::REGION_LABEL . PHP_EOL;
        $parts[] = '    ' . PHP_EOL;

        foreach($this->iconsReader->getIcons() as $icon)
        {
            $parts[] = $this->renderMethod($icon);
        }

        $parts[] = PHP_EOL;
        $parts[] = '    // endregion' . PHP_EOL;
        $parts[] = PHP_EOL . '    ';

        return implode('', $parts);
    }

    /**
     * Converts an underscore-separated or camelCase icon ID to PascalCase.
     * Used by the JS renderer to produce JS method names.
     *
     * Examples:
     * - `actioncode`         → `Actioncode`
     * - `attention_required` → `AttentionRequired`
     * - `apiClients`         → `ApiClients`
     *
     * @param string $id The normalised icon ID (underscores as word separators).
     * @return string
     */
    protected function toPascalCase(string $id) : string
    {
        return implode('', array_map('ucfirst', explode('_', $id)));
    }

    /**
     * Renders the argument list for the `setType()` / `SetType()` call.
     *
     * When the icon has a non-empty type prefix, both the icon name and
     * the prefix are included as quoted string arguments. When the prefix
     * is empty, only the icon name is included.
     *
     * @param IconDefinition $icon
     * @return string
     */
    protected function renderSetTypeArgs(IconDefinition $icon) : string
    {
        if(!empty($icon->getIconType()))
        {
            return "'" . $icon->getIconName() . "', '" . $icon->getIconType() . "'";
        }

        return "'" . $icon->getIconName() . "'";
    }
}
