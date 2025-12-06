<?php
/**
 * @package API
 * @subpackage Versioning
 */

declare(strict_types=1);

namespace Application\API\Versioning;

use Application\API\APIMethodInterface;
use Application\API\Utilities\KeyPath;
use Application\API\Utilities\KeyReplacement;
use AppUtils\Interfaces\StringPrimaryRecordInterface;

/**
 * Interface for a specific version of an API method.
 * A base implementation is provided by {@see BaseAPIVersion}.
 *
 * @package API
 * @subpackage Versioning
 */
interface APIVersionInterface extends StringPrimaryRecordInterface
{
    public function getMethod() : APIMethodInterface;
    public function getVersion() : string;

    /**
     * Markdown-formatted changelog of this version.
     * @return string
     */
    public function getChangelog() : string;

    /**
     * List of keys (use dot notation for paths) that are deprecated in this version,
     * with replacement key or NULL if no replacement exists.
     *
     * @return array<int,KeyPath|KeyReplacement>
     */
    public function getDeprecatedKeys() : array;

    /**
     * List of keys (use dot notation for paths) that are removed in this version.
     *
     * > NOTE: These keys should have been marked as deprecated in a previous version,
     * > which is why there is no replacement key here.
     *
     * @return array<int,KeyPath|KeyReplacement>
     */
    public function getRemovedKeys() : array;

}
