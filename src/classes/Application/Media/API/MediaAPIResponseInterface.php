<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

/**
 * Response key constants for the media document API methods.
 *
 * Deliberately a constants-only interface: it is implemented by every media
 * API method so each one carries a compile-time reference to the shape of
 * the response it builds through {@see MediaDocumentSerializer}, without
 * pulling in any default method implementation (there is none to pull in).
 *
 * @package Media
 * @subpackage API
 * @see MediaDocumentSerializer
 */
interface MediaAPIResponseInterface
{
    /**
     * The single media document shape, as built by {@see MediaDocumentSerializer::serialize()}
     * / {@see MediaDocumentSerializer::serializePreview()}.
     */
    public const string KEY_MEDIA_DOCUMENT = 'mediaDocument';

    /**
     * A list of media document shapes, as returned by `GetMediaDocuments`.
     */
    public const string KEY_MEDIA_DOCUMENTS = 'mediaDocuments';

    public const string KEY_MEDIA_ID = 'mediaID';
    public const string KEY_NAME = 'name';
    public const string KEY_MEDIA_TYPE = 'mediaType';
    public const string KEY_MEDIA_TYPE_LABEL = 'mediaTypeLabel';
    public const string KEY_EXTENSION = 'extension';
    public const string KEY_FILE_NAME = 'fileName';
    public const string KEY_FILE_SIZE = 'fileSize';

    /**
     * ISO 8601 (`DateTime::ATOM`) timestamp of when the document was added.
     */
    public const string KEY_DATE_ADDED = 'dateAdded';

    public const string KEY_AUTHOR_ID = 'authorID';
    public const string KEY_DESCRIPTION = 'description';
    public const string KEY_KEYWORDS = 'keywords';

    /**
     * Image-specific sub-shape (`width`, `height`, `isVector`, `isAnimated`).
     * `null` for a non-image document.
     */
    public const string KEY_IMAGE = 'image';
    public const string KEY_IMAGE_WIDTH = 'width';
    public const string KEY_IMAGE_HEIGHT = 'height';
    public const string KEY_IMAGE_IS_VECTOR = 'isVector';
    public const string KEY_IMAGE_IS_ANIMATED = 'isAnimated';

    /**
     * Base64-encoded file content, included only when explicitly requested
     * (e.g. `GetMediaDocument`'s `includeContent` parameter).
     */
    public const string KEY_CONTENT = 'content';

    public const string KEY_MODIFIED = 'modified';
    public const string KEY_DELETED = 'deleted';
    public const string KEY_DRY_RUN = 'dryRun';
}
