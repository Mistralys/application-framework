<?php
/**
 * @package Media
 * @subpackage Writer
 */

declare(strict_types=1);

namespace Application\Media\Writer;

use Application\Media\MediaException;

/**
 * Exception thrown by the media writer services ({@see MediaFileContent},
 * {@see MediaFileValidator}, {@see MediaDocumentWriter}).
 *
 * Its `VALIDATION_*` constants are the single source of truth for the
 * writer's client-facing validation codes; no other class re-declares them.
 *
 * @package Media
 * @subpackage Writer
 */
class MediaWriterException extends MediaException
{
    // region: Client-facing validation codes (6810xx)

    public const int VALIDATION_UNSUPPORTED_EXTENSION = 681001;
    public const int VALIDATION_TYPE_MISMATCH = 681002;
    public const int VALIDATION_CONTENT_MISMATCH = 681003;
    public const int VALIDATION_EMPTY_FILE = 681004;
    public const int VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED = 681005;
    public const int VALIDATION_INVALID_FILE_NAME = 681006;

    // 681007 was planned as VALIDATION_METADATA_NOT_SUPPORTED, for the
    // columns-absent path dropped in design review 2 (Decision 1). The
    // number is intentionally left unused rather than being reassigned.

    // endregion

    // region: Internal error codes (6811xx)

    public const int ERROR_NO_TRANSACTION = 681101;
    public const int ERROR_FILE_WRITE_FAILED = 681102;

    // endregion

    /**
     * Whether this exception's code is one of the client-facing validation
     * codes (`VALIDATION_*`, the `6810xx` block), as opposed to an internal
     * error code (`ERROR_*`, the `6811xx` block).
     *
     * @return bool
     */
    public function isValidationError() : bool
    {
        $code = $this->getCode();

        return $code >= self::VALIDATION_UNSUPPORTED_EXTENSION
            && $code <= self::VALIDATION_INVALID_FILE_NAME;
    }
}
