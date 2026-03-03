# DBHelper - API Methods
_SOURCE: API method class signatures_
# API method class signatures
```
// Structure of documents
└── src/
    └── classes/
        └── DBHelper/
            └── API/
                └── Methods/
                    └── DescribeCollectionsAPI.php

```
###  Path: `/src/classes/DBHelper/API/Methods/DescribeCollectionsAPI.php`

```php
namespace DBHelper\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\FileHelper as FileHelper;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Groups\FrameworkAPIGroup as FrameworkAPIGroup;
use Application\API\Traits\JSONResponseInterface as JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;

/**
 * API method that compiles information about all DBHelper collections
 * that are in use in the application.
 *
 * @package Application
 * @subpackage API
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
class DescribeCollectionsAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;

	public const METHOD_NAME = 'DescribeCollections';
	public const VERSION_1_0 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1_0;
	public const RESPONSE_KEY_COLLECTIONS = 'collections';

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getExampleJSONResponse(): array
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	public function getReponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 2.24 KB
- **Lines**: 108
File: `modules/db-helper/architecture-api-methods.md`
