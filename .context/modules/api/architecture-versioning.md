# API - Versioning (Public API)
_SOURCE: VersionedAPIInterface, VersionedAPITrait, BaseAPIVersion, VersionCollection, APIVersionInterface_
# VersionedAPIInterface, VersionedAPITrait, BaseAPIVersion, VersionCollection, APIVersionInterface
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Versioning/
                    └── APIVersionInterface.php
                    └── BaseAPIVersion.php
                    └── VersionCollection.php
                    └── VersionedAPIInterface.php
                    └── VersionedAPITrait.php

```
###  Path: `/src/classes/Application/API/Versioning/APIVersionInterface.php`

```php
namespace Application\API\Versioning;

use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Utilities\KeyPath as KeyPath;
use Application\API\Utilities\KeyReplacement as KeyReplacement;

/**
 * Interface for a specific version of an API method.
 * A base implementation is provided by {@see BaseAPIVersion}.
 *
 * @package API
 * @subpackage Versioning
 */
interface APIVersionInterface extends StringPrimaryRecordInterface
{
	public function getMethod(): APIMethodInterface;


	public function getVersion(): string;


	/**
	 * Markdown-formatted changelog of this version.
	 * @return string
	 */
	public function getChangelog(): string;


	/**
	 * List of keys (use dot notation for paths) that are deprecated in this version,
	 * with replacement key or NULL if no replacement exists.
	 *
	 * @return array<int,KeyPath|KeyReplacement>
	 */
	public function getDeprecatedKeys(): array;


	/**
	 * List of keys (use dot notation for paths) that are removed in this version.
	 *
	 * > NOTE: These keys should have been marked as deprecated in a previous version,
	 * > which is why there is no replacement key here.
	 *
	 * @return array<int,KeyPath|KeyReplacement>
	 */
	public function getRemovedKeys(): array;
}


```
###  Path: `/src/classes/Application/API/Versioning/BaseAPIVersion.php`

```php
namespace Application\API\Versioning;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Utilities\KeyPath as KeyPath;
use Application\API\Utilities\KeyPathInterface as KeyPathInterface;
use Application\API\Utilities\KeyReplacement as KeyReplacement;

/**
 * Abstract base class for API versions. Used by API methods that
 * use {@see VersionedAPIInterface} to implement their versioning.
 *
 * @package API
 * @subpackage Versioning
 */
abstract class BaseAPIVersion implements APIVersionInterface
{
	public function getID(): string
	{
		/* ... */
	}


	public function getMethod(): APIMethodInterface
	{
		/* ... */
	}


	public function getChangelog(): string
	{
		/* ... */
	}


	/**
	 * Markdown-formatted changelog of this version.
	 *
	 * Use {@see self::getRemovedKeys()} and {@see self::getDeprecatedKeys()} for changes
	 * that are automatically appended to the changelog.
	 *
	 * @return string
	 */
	abstract protected function _getChangelog(): string;
}


```
###  Path: `/src/classes/Application/API/Versioning/VersionCollection.php`

```php
namespace Application\API\Versioning;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollection as BaseClassLoaderCollection;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;

/**
 * Collection of all API versions available for a specific API method
 * that uses versioning via {@see VersionedAPITrait}. Uses ClassHelper
 * class loading to dynamically load all version classes in the
 * version folder of the method.
 *
 * @package API
 * @subpackage Versioning
 *
 * @method APIVersionInterface[] getAll()
 * @method APIVersionInterface getByID(string $id)
 * @method APIVersionInterface getDefault()
 */
class VersionCollection extends BaseClassLoaderCollection
{
	protected function createItemInstance(string $class): ?StringPrimaryRecordInterface
	{
		/* ... */
	}


	public function getInstanceOfClassName(): string
	{
		/* ... */
	}


	public function isRecursive(): bool
	{
		/* ... */
	}


	public function getClassesFolder(): FolderInfo
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Versioning/VersionedAPIInterface.php`

```php
namespace Application\API\Versioning;

use AppUtils\FileHelper\FolderInfo as FolderInfo;
use Application\API\APIMethodInterface as APIMethodInterface;

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
	public function getVersionFolder(): FolderInfo;


	public function getVersionCollection(): VersionCollection;
}


```
###  Path: `/src/classes/Application/API/Versioning/VersionedAPITrait.php`

```php
namespace Application\API\Versioning;

use AppUtils\ArrayDataCollection as ArrayDataCollection;

/**
 * Trait used to implement {@see VersionedAPIInterface} in an API method,
 * and add version handling using separate version classes.
 *
 * ----------------------------------------------------------------
 * For more documentation, see {@see VersionedAPIInterface}.
 * ----------------------------------------------------------------
 *
 * @package API
 * @subpackage Versioning
 * @see VersionedAPIInterface
 */
trait VersionedAPITrait
{
	final public function getVersionCollection(): VersionCollection
	{
		/* ... */
	}


	final public function getVersions(): array
	{
		/* ... */
	}


	final public function getChangelog(): array
	{
		/* ... */
	}


	final protected function collectResponseData(ArrayDataCollection $response, string $version): void
	{
		/* ... */
	}


	abstract protected function _collectResponseData(ArrayDataCollection $response, APIVersionInterface $version): void;
}


```
---
**File Statistics**
- **Size**: 6.74 KB
- **Lines**: 275
File: `modules/api/architecture-versioning.md`
