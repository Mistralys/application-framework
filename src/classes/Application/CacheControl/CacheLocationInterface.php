<?php
/**
 * @package Application
 * @subpackage CacheControl
 */

declare(strict_types=1);

namespace Application\CacheControl;

use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Interface for cache locations.
 *
 * USAGE: Extend {@see BaseCacheLocation} to make use
 * of the default implementation.
 *
 * @package Application
 * @subpackage CacheControl
 */
interface CacheLocationInterface extends StringPrimaryRecordInterface
{
    /**
     * The size of the cache location in bytes.
     * @return int
     */
    public function getByteSize() : int;

    /**
     * Human-readable label for the cache location.
     * @return string
     */
    public function getLabel() : string;

    /**
     * Clear the cache location.
     */
    public function clear() : void;
}
