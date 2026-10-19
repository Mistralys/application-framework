<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

use Application\API\APIMethodInterface;

/**
 * Interface for API methods that accept media document metadata (`name`,
 * `description`, `keywords`) — used by `AddMediaDocument` and
 * `UpdateMediaDocument`.
 *
 * Use {@see MediaMetadataAPITrait} to implement this interface.
 *
 * @package Media
 * @subpackage API
 * @see MediaMetadataAPITrait
 */
interface MediaMetadataAPIInterface extends APIMethodInterface
{
    public const string PARAM_NAME = 'name';
    public const string PARAM_DESCRIPTION = 'description';
    public const string PARAM_KEYWORDS = 'keywords';

    public const int MAX_NAME_LENGTH = 160;
    public const int MAX_DESCRIPTION_LENGTH = 1200;
    public const int MAX_KEYWORDS_LENGTH = 500;

    /**
     * `description` or `keywords` contained HTML markup, which is never
     * accepted (the admin form enforces the same rule via `addRuleNoHTML()`).
     *
     * Allocated in the `68121x` sub-range.
     */
    public const int VALIDATION_HTML_NOT_ALLOWED = 681211;

    /**
     * Registers the metadata parameters.
     *
     * @param bool $clearable When `true` (`UpdateMediaDocument`), `description`
     *        and `keywords` are registered as clearable: absent means
     *        unchanged, an explicit empty value means clear. When `false`
     *        (`AddMediaDocument`), all three parameters are plain strings.
     * @return $this
     */
    public function registerMediaMetadataParams(bool $clearable) : self;

    /**
     * The submitted `name`, or `null` when it was not sent.
     */
    public function getMediaName() : ?string;

    /**
     * The submitted `description`, or `null` when it was not sent.
     */
    public function getMediaDescription() : ?string;

    /**
     * The submitted `keywords`, or `null` when it was not sent.
     */
    public function getMediaKeywords() : ?string;

    /**
     * Resolves the document name: the submitted `name`, or `$default` cut
     * to {@see self::MAX_NAME_LENGTH} characters (`mb_substr()`) when no
     * name was sent. A sent name is already length-checked by its own
     * parameter; the default is not, so this applies the same limit.
     *
     * @param string $default
     * @return string
     */
    public function resolveMediaName(string $default) : string;

    /**
     * Validates the HTML rule on `description`/`keywords`. The single home
     * of {@see self::VALIDATION_HTML_NOT_ALLOWED}, called identically by
     * dry and real runs so neither can drift from the other.
     *
     * @return void
     */
    public function validateMediaMetadata() : void;
}
