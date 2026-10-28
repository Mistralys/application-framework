<?php
/**
 * @package API
 * @subpackage Traits
 */

declare(strict_types=1);

namespace Application\API\Traits;

use Application\API\APIException;
use Application\API\APIMethodInterface;
use Application\API\Utilities\KeyDescription;
use Application_FilterCriteria;
use AppUtils\ArrayDataCollection;

/**
 * Interface for API methods that support paging a list result through a
 * `limit`/`offset` pair, with a `totalCount` response key independent of
 * paging — matching the existing HCP Editor response shape of
 * `GetMailingsAPI`/`GetComtypesAPI`, so those can adopt this piece later
 * without a breaking response change.
 *
 * An out-of-range `limit` or a negative `offset` is **rejected**, never
 * silently clamped — a client must never mistake a truncated page for a
 * complete one.
 *
 * > **Note:** This rejection only covers numeric out-of-range values. A
 * > non-numeric `limit`/`offset` (e.g. `abc`) is not rejected with
 * > {@see self::VALIDATION_INVALID_LIMIT}/{@see self::VALIDATION_INVALID_OFFSET} —
 * > it is treated as an `IntegerParameter` warning and silently falls back
 * > to the configured default via null-coalescing, inherited from
 * > `IntegerParameter`'s own non-numeric-value handling.
 *
 * **Example responses.** A method whose example response is produced while
 * {@see \Application\API\Traits\JSONResponseWithExampleInterface::isExampleResponse()}
 * returns `true` resolves its page through the optional `$exampleLimit`
 * registered via {@see self::registerPaginationParams()} instead of the
 * submitted request values: {@see self::getPaginationLimit()} returns
 * `$exampleLimit ?? $defaultLimit`, and {@see self::getPaginationOffset()}
 * always returns `0`. This is by design — an example response is not a
 * response to any actual request, so `limit`/`offset` request values are
 * ignored on purpose, keeping `applyPagination()`/`addPaginationResponseKeys()`
 * automatically consistent with the example page that was built.
 *
 * Use the trait {@see PaginatedAPITrait} to implement this interface.
 *
 * @package API
 * @subpackage Traits
 * @see PaginatedAPITrait
 */
interface PaginatedAPIInterface extends APIMethodInterface
{
    public const string PARAM_LIMIT = 'limit';
    public const string PARAM_OFFSET = 'offset';

    public const string KEY_LIMIT = 'limit';
    public const string KEY_OFFSET = 'offset';
    public const string KEY_TOTAL_COUNT = 'totalCount';

    /**
     * The submitted `limit` is outside the `1`-`$maxLimit` range configured
     * via {@see self::registerPaginationParams()}.
     */
    public const int VALIDATION_INVALID_LIMIT = 183701;

    /**
     * The submitted `offset` is negative.
     */
    public const int VALIDATION_INVALID_OFFSET = 183702;

    /**
     * The `$exampleLimit` passed to {@see self::registerPaginationParams()}
     * is outside the `1`-`$maxLimit` range. This is a developer error raised
     * at registration time, not a request validation failure.
     */
    public const int ERROR_INVALID_EXAMPLE_LIMIT = 183703;

    /**
     * Registers the `limit` and `offset` parameters.
     *
     * @param int $defaultLimit Used for {@see self::getPaginationLimit()} when `limit` is not submitted.
     * @param int $maxLimit The maximum value accepted for `limit`; a higher value is rejected with {@see self::VALIDATION_INVALID_LIMIT}.
     * @param int|null $exampleLimit Used for {@see self::getPaginationLimit()} instead of `$defaultLimit` while building an example response (see the "Example responses" note above). Must be between `1` and `$maxLimit`, or `APIException` with {@see self::ERROR_INVALID_EXAMPLE_LIMIT} is thrown.
     * @return $this
     * @throws APIException {@see self::ERROR_INVALID_EXAMPLE_LIMIT}
     */
    public function registerPaginationParams(int $defaultLimit, int $maxLimit, ?int $exampleLimit = null) : self;

    /**
     * The resolved `limit` value: the submitted value, or the configured
     * default when `limit` was not submitted.
     *
     * @return int
     */
    public function getPaginationLimit() : int;

    /**
     * The resolved `offset` value: the submitted value, or `0` when
     * `offset` was not submitted.
     *
     * @return int
     */
    public function getPaginationOffset() : int;

    /**
     * Applies the resolved `limit`/`offset` to the given filter criteria,
     * through its public {@see Application_FilterCriteria::setLimit()} signature only.
     *
     * @param Application_FilterCriteria $criteria
     * @return $this
     */
    public function applyPagination(Application_FilterCriteria $criteria) : self;

    /**
     * Writes the `limit`, `offset` and `totalCount` response keys.
     *
     * @param ArrayDataCollection $response
     * @param int $totalCount Total number of matching results, independent of paging.
     * @return $this
     */
    public function addPaginationResponseKeys(ArrayDataCollection $response, int $totalCount) : self;

    /**
     * Key descriptions for the `limit`, `offset` and `totalCount` response
     * keys, for a consuming method to merge into its own
     * `getResponseKeyDescriptions()`.
     *
     * @return KeyDescription[]
     */
    public function getPaginationKeyDescriptions() : array;
}
