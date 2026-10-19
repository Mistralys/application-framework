<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API;

use Application\API\APIMethodInterface;

/**
 * Interface for API methods that resolve a single media document from a
 * `mediaID` parameter (Get, Update, Delete).
 *
 * Use {@see MediaDocumentAPITrait} to implement this interface.
 *
 * @package Media
 * @subpackage API
 * @see MediaDocumentAPITrait
 */
interface MediaDocumentAPIInterface extends APIMethodInterface
{
    public const string PARAM_MEDIA_ID = 'mediaID';

    /**
     * The submitted (or missing) `mediaID` could not be resolved to an
     * existing media document. Sent with HTTP 404 via
     * {@see \Application\API\ErrorResponse::makeNotFound()} — never the
     * generic {@see APIMethodInterface::ERROR_NO_VALUE_AVAILABLE} (183004)
     * nor the exists-callback {@see \Application\API\Parameters\Validation\Type\ValueExistsCallbackValidation::VALIDATION_VALUE_NOT_EXISTS}
     * (183401).
     *
     * Allocated in the `68120x` sub-range.
     */
    public const int VALIDATION_UNKNOWN_MEDIA_ID = 681201;

    public function manageMediaDocumentParams() : MediaDocumentParamsContainer;
}
