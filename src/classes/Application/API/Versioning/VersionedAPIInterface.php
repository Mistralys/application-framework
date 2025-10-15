<?php
/**
 * @package API
 * @subpackage Versioning
 */

declare(strict_types=1);

namespace Application\API\Versioning;

use Application\API\APIMethodInterface;
use AppUtils\FileHelper\FolderInfo;

/**
 * Interface for an API method that uses version handling using
 * separate version classes. This allows more granular control
 * over the response data for each version.
 *
 * Can be added to an existing API method with the trait {@see VersionedAPITrait}.
 *
 * ## Usage
 *
 * 1. Implement this interface in your API method class.
 * 2. Use the {@see VersionedAPITrait} trait in your API method class.
 * 3. Create a folder for the API's version classes.
 * 4. Return the folder in {@see self::getVersionFolder()}.
 * 5. Create a class for each version, extending {@see BaseAPIVersion}.
 *
 * > Typically, you would create an abstract base class for your API's versions,
 * > which builds the base response, and then extend that class for each version,
 * > adding or removing fields as needed.
 *
 * @package API
 * @subpackage Versioning
 * @see VersionedAPITrait
 */
interface VersionedAPIInterface extends APIMethodInterface
{
    public function getVersionFolder() : FolderInfo;
    public function getVersionCollection() : VersionCollection;
}
