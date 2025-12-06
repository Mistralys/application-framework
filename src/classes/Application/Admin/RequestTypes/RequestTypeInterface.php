<?php
/**
 * @package Admin
 * @subpackage Request Types
 */

declare(strict_types=1);

namespace Application\Admin\RequestTypes;

use Application\ApplicationException;
use Application\Interfaces\Admin\MissingRecordInterface;

/**
 * Interface for request types that deal with fetching records
 * from the current request. A base implementation is provided
 * by {@see BaseRequestType}.
 *
 * @package Admin
 * @subpackage Request Types
 *
 * @template T of object
 *
 * @see BaseRequestType
 */
interface RequestTypeInterface extends MissingRecordInterface
{
    /**
     * Gets the record specified in the request, or null
     * if none has been specified, or no such record exists
     * (depending on the implementing logic).
     *
     * @return T|null
     */
    public function getRecord();

    /**
     * Gets the record specified in the request, or redirects
     * to the appropriate URL if no such record exists
     * (as specified in {@see self::getRecordMissingURL()}).
     *
     * @return T
     */
    public function getRecordOrRedirect();

    /**
     * Gets the record specified in the request, or throws an exception
     * if no such record exists.
     *
     * @return T
     * @throws ApplicationException If no record has been specified in the request.
     */
    public function requireRecord();
}
