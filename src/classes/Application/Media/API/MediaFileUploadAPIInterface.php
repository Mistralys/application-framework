<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

use Application\API\APIMethodInterface;
use Application\Media\Writer\MediaFileContent;

/**
 * Interface for API methods that accept a media file upload (`fileName` +
 * `fileContent`) — used by `AddMediaDocument` and `UpdateMediaDocument`.
 *
 * This piece owns no validation codes of its own: file content validation
 * failures are reported through {@see \Application\Media\Writer\MediaWriterException}'s
 * `6810xx` block (design review Decision 5), converted to a client error
 * through {@see self::sendWriterValidationError()}.
 *
 * Use {@see MediaFileUploadAPITrait} to implement this interface.
 *
 * @package Media
 * @subpackage API
 * @see MediaFileUploadAPITrait
 */
interface MediaFileUploadAPIInterface extends APIMethodInterface
{
    public const string PARAM_FILE_NAME = 'fileName';
    public const string PARAM_FILE_CONTENT = 'fileContent';

    /**
     * Registers the file upload parameters.
     *
     * @param bool $required `true` (`AddMediaDocument`) registers both
     *        parameters as required. `false` (`UpdateMediaDocument`)
     *        registers them as optional, plus a pair rule that enforces
     *        both are sent together or neither is.
     * @return $this
     */
    public function registerMediaFileParams(bool $required) : self;

    /**
     * Whether a file was submitted (both `fileName` and `fileContent`).
     */
    public function hasMediaFile() : bool;

    /**
     * Builds the submitted file content once, from `fileName` and the
     * decoded `fileContent`. `null` when no file was submitted.
     */
    public function getMediaFileContent() : ?MediaFileContent;

    /**
     * Validates the submitted file content. The single validation call used
     * identically by both dry and real runs, so neither can drift from the
     * other (design review Decision 6).
     *
     * @param string|null $requiredTypeID When given, the resolved type must
     *        match this type ID exactly.
     * @return string The resolved media type ID (e.g. `Image`, `PDF`).
     */
    public function validateMediaFile(?string $requiredTypeID=null) : string;
}
