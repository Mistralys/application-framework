<?php
/**
 * @package User Interface
 * @subpackage Themes
 */

declare(strict_types=1);

namespace UI;

/**
 * This class contains a repository of CSS classes globally
 * available for applications, independently of the selected
 * theme.
 *
 * @package User Interface
 * @subpackage Themes
 */
class CSSClasses
{
    /**
     * Class for developer-only visual elements. Automatically
     * hidden for non-developer users.
     *
     * @see \UI_StringBuilder::developer()
     */
    public const string RIGHT_DEVELOPER = 'right-developer';

    /**
     * Class to highlight parts of a text that refer to concepts,
     * names or the like. Typically used for dynamically inserted
     * text in translatable texts.
     *
     * Example:
     *
     * ```php
     * $message = t(
     *   'The product %1$s has been updated successfully at %2$s.',
     *   sb()->reference($productName),
     *   sb()->time()
     * );
     * ```
     *
     * @see \UI_StringBuilder::reference()
     */
    public const string TEXT_REFERENCE = 'text-reference';


    /**
     * Marks any element as clickable by giving it the click cursor.
     *
     * **Note**: The functionality must be added separately.
     * The method {@see \UI_StringBuilder::clickable()} can help
     * with that.
     *
     * @see \UI_StringBuilder::clickable()
     */
    public const string CLICKABLE = 'clickable';

    /**
     * Styles text in a monospace font, without using
     * a `code` tag.
     *
     * @see \UI_StringBuilder::mono()
     */
    public const string TEXT_MONOSPACE = 'monospace';
    public const string TEXT_ERROR_XXL = 'text-error-xxl';
    public const string TEXT_SUCCESS = 'text-success';
    public const string TEXT_MUTED = 'muted';
    public const string TEXT_WARNING = 'text-warning';
    public const string TEXT_ERROR = 'text-error';
    public const string TEXT_SECONDARY = 'text-secondary';
    public const string TEXT_INVERTED = 'text-inverted';
    public const string TEXT_INFO = 'text-info';

    public const string INPUT_XLARGE = 'input-xlarge';
    public const string INPUT_LARGE = 'input-large';
    public const string INPUT_XXLARGE = 'input-xxlarge';
    public const string INPUT_XSMALL = 'input-mini';
    public const string INPUT_SMALL = 'input-small';
    public const string INPUT_MEDIUM = 'input-medium';
}
