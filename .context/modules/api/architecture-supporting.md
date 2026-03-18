# API - Groups, Documentation, Response, Connector, Utilities (Public API)
_SOURCE: APIGroupInterface, FrameworkAPIGroup, GenericAPIGroup, APIDocumentation, MethodDocumentation, JSONMethodExample, ResponseInterface, JSONInfoSerializer, AppAPIConnector, AppAPIMethod, KeyDescription, KeyPath, KeyPathInterface, KeyReplacement, APIRightsInterface, APIRightsTrait_
# APIGroupInterface, FrameworkAPIGroup, GenericAPIGroup, APIDocumentation, MethodDocumentation, JSONMethodExample, ResponseInterface, JSONInfoSerializer, AppAPIConnector, AppAPIMethod, KeyDescription, KeyPath, KeyPathInterface, KeyReplacement, APIRightsInterface, APIRightsTrait
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── API/
                └── Collection/
                    ├── APICacheLocation.php
                    ├── APIMethodCollection.php
                    ├── APIMethodIndex.php
                └── Connector/
                    ├── AppAPIConnector.php
                    ├── AppAPIMethod.php
                └── Documentation/
                    ├── APIDocumentation.php
                    ├── BaseAPIDocumentation.php
                    ├── Examples/
                    │   ├── JSONMethodExample.php
                    ├── MethodDocumentation.php
                └── Events/
                    ├── RegisterAPIIndexCacheListener.php
                    ├── RegisterAPIResponseCacheListener.php
                └── Groups/
                    ├── APIGroupInterface.php
                    ├── FrameworkAPIGroup.php
                    ├── GenericAPIGroup.php
                └── Response/
                    ├── JSONInfoSerializer.php
                    ├── ResponseInterface.php
                └── User/
                    ├── APIRightsInterface.php
                    ├── APIRightsTrait.php
                └── Utilities/
                    └── KeyDescription.php
                    └── KeyPath.php
                    └── KeyPathInterface.php
                    └── KeyReplacement.php

```
###  Path: `/src/classes/Application/API/Collection/APICacheLocation.php`

```php
namespace Application\AppFactory;

use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\API\Collection\APIMethodIndex as APIMethodIndex;
use Application\CacheControl\BaseCacheLocation as BaseCacheLocation;

/**
 * Cache location description class for the API method index,
 * so it can be handled via the cache control system.
 *
 * @package API
 * @subpackage Method Collection
 */
class APICacheLocation extends BaseCacheLocation
{
	public const CACHE_ID = 'APIMethodIndex';

	public function getID(): string
	{
		/* ... */
	}


	public function getByteSize(): int
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function clear(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Collection/APIMethodCollection.php`

```php
namespace Application\API\Collection;

use AppUtils\Collections\BaseClassLoaderCollectionMulti as BaseClassLoaderCollectionMulti;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use Application\API\APIManager as APIManager;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\AppFactory as AppFactory;

/**
 * Collection of all available API methods in the application.
 * This includes APIs from the application framework as well
 * as those provided by the application itself.
 *
 * @package API
 * @subpackage Method Collection
 *
 * @method APIMethodInterface[] getAll()
 * @method APIMethodInterface getByID(string $id)
 */
class APIMethodCollection extends BaseClassLoaderCollectionMulti
{
	public function getInstanceOfClassName(): ?string
	{
		/* ... */
	}


	public function getClassFolders(): array
	{
		/* ... */
	}


	public function isRecursive(): bool
	{
		/* ... */
	}


	public function getDefaultID(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Collection/APIMethodIndex.php`

```php
namespace Application\API\Collection;

use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\JSONFile as JSONFile;
use Application\API\APIException as APIException;
use Application\API\APIManager as APIManager;
use Application\API\APIMethodInterface as APIMethodInterface;
use Application\AppFactory\APICacheLocation as APICacheLocation;
use Application\Application as Application;
use Application_Interfaces_Loggable as Application_Interfaces_Loggable;
use Application_Traits_Loggable as Application_Traits_Loggable;

/**
 * API method indexing module: Creates a cache file on disk
 * that is used at runtime to look up whether a method exists,
 * and to fetch its class name without having to use the
 * {@see APIMethodCollection} to find it.
 *
 * ## Usage
 *
 * Use {@see APIManager::getMethodIndex} to get an instance
 * of this class, and then call {@see methodExists()} to check
 * if a method exists, or {@see getMethodClass()} to get the
 * class name of a method.
 *
 * @package API
 * @subpackage Method Collection
 */
class APIMethodIndex implements Application_Interfaces_Loggable
{
	use Application_Traits_Loggable;

	public function getLogIdentifier(): string
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getMethodNames(): array
	{
		/* ... */
	}


	public function methodExists(string $methodName): bool
	{
		/* ... */
	}


	/**
	 * @param class-string<APIMethodInterface> $methodName
	 * @return string
	 * @throws APIException
	 */
	public function getMethodClass(string $methodName): string
	{
		/* ... */
	}


	public function build(): self
	{
		/* ... */
	}


	public function getDataFile(): JSONFile
	{
		/* ... */
	}


	public function getCacheLocation(): APICacheLocation
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Connector/AppAPIConnector.php`

```php
namespace Application\API\Connector;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use Application\Bootstrap\Screen\APIBootstrap as APIBootstrap;
use Connectors as Connectors;
use Connectors\Connector\BaseConnector as BaseConnector;
use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors\Headers\HTTPHeadersBasket as HTTPHeadersBasket;

class AppAPIConnector extends BaseConnector
{
	public static function create(string $appURL): self
	{
		/* ... */
	}


	public function getURL(): string
	{
		/* ... */
	}


	/**
	 * @param string $methodName
	 * @param array<string,mixed>|ArrayDataCollection $params
	 * @param HTTPHeadersBasket|null $headers Optional headers to include in the request
	 * @return ArrayDataCollection
	 * @throws ConnectorException
	 */
	public function fetchMethodData(
		string $methodName,
		array|ArrayDataCollection $params = [],
		?HTTPHeadersBasket $headers = null,
	): ArrayDataCollection
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Connector/AppAPIMethod.php`

```php
namespace Application\API\Connector;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use Application\API\APIMethodInterface as APIMethodInterface;
use Connectors\Connector\ConnectorException as ConnectorException;
use Connectors\Headers\HTTPHeadersBasket as HTTPHeadersBasket;
use Connectors_Connector_Method_Post as Connectors_Connector_Method_Post;
use Throwable as Throwable;

class AppAPIMethod extends Connectors_Connector_Method_Post
{
	/**
	 * @param string $methodName
	 * @param ArrayDataCollection $params
	 * @param HTTPHeadersBasket|NULL $headers
	 * @return ArrayDataCollection
	 * @throws ConnectorException
	 */
	public function fetchJSON(
		string $methodName,
		ArrayDataCollection $params,
		?HTTPHeadersBasket $headers = null,
	): ArrayDataCollection
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Documentation/APIDocumentation.php`

```php
namespace Application\API\Documentation;

use Application\API\APIManager as APIManager;
use Application\Themes\DefaultTemplate\API\APIMethodsOverviewTmpl as APIMethodsOverviewTmpl;
use UI_Page_Template as UI_Page_Template;

class APIDocumentation extends BaseAPIDocumentation
{
}


```
###  Path: `/src/classes/Application/API/Documentation/BaseAPIDocumentation.php`

```php
namespace Application\API\Documentation;

use UI_Page as UI_Page;
use UI_Page_Template as UI_Page_Template;
use UI_Renderable_Interface as UI_Renderable_Interface;
use UI_Traits_RenderableGeneric as UI_Traits_RenderableGeneric;

abstract class BaseAPIDocumentation implements UI_Renderable_Interface
{
	use UI_Traits_RenderableGeneric;

	public function render(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Documentation/Examples/JSONMethodExample.php`

```php
namespace Application\API\Documentation\Examples;

use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\Highlighter as Highlighter;
use Application\API\Traits\JSONResponseInterface as JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use UI_Renderable as UI_Renderable;

/**
 * Renders an example JSON response for a given API method implementing {@see JSONResponseInterface},
 * for use in API documentation. It is used by {@see JSONResponseTrait::renderExample()} to fetch
 * the JSON to use.
 *
 * @package API
 * @subpackage Documentation
 */
class JSONMethodExample extends UI_Renderable
{
}


```
###  Path: `/src/classes/Application/API/Documentation/MethodDocumentation.php`

```php
namespace Application\API\Documentation;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\Themes\DefaultTemplate\API\APIMethodDetailTmpl as APIMethodDetailTmpl;
use UI_Page_Template as UI_Page_Template;

class MethodDocumentation extends BaseAPIDocumentation
{
}


```
###  Path: `/src/classes/Application/API/Events/RegisterAPIIndexCacheListener.php`

```php
namespace Application\API\Events;

use Application\API\APIManager as APIManager;
use Application\API\Collection\APIMethodIndex as APIMethodIndex;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener as BaseRegisterCacheLocationsListener;

/**
 * Registers the API method index cache location.
 *
 * @package Application
 * @subpackage CacheControl
 *
 * @see APIMethodIndex::getCacheLocation()
 */
class RegisterAPIIndexCacheListener extends BaseRegisterCacheLocationsListener
{
}


```
###  Path: `/src/classes/Application/API/Events/RegisterAPIResponseCacheListener.php`

```php
namespace Application\API\Events;

use Application\API\Cache\APIResponseCacheLocation as APIResponseCacheLocation;
use Application\CacheControl\Events\BaseRegisterCacheLocationsListener as BaseRegisterCacheLocationsListener;

/**
 * Registers the API response cache location with the CacheControl system.
 * This listener is discovered automatically — no manual registration required.
 *
 * @package Application
 * @subpackage CacheControl
 * @see APIResponseCacheLocation
 */
class RegisterAPIResponseCacheListener extends BaseRegisterCacheLocationsListener
{
}


```
###  Path: `/src/classes/Application/API/Groups/APIGroupInterface.php`

```php
namespace Application\API\Groups;

interface APIGroupInterface
{
	public function getID(): string;


	public function getLabel(): string;


	public function getDescription(): string;
}


```
###  Path: `/src/classes/Application/API/Groups/FrameworkAPIGroup.php`

```php
namespace Application\API\Groups;

class FrameworkAPIGroup extends GenericAPIGroup
{
	public static function create(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Groups/GenericAPIGroup.php`

```php
namespace Application\API\Groups;

class GenericAPIGroup implements APIGroupInterface
{
	public function getID(): string
	{
		/* ... */
	}


	public function getLabel(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Response/JSONInfoSerializer.php`

```php
namespace Application\API\Response;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;

/**
 * Helper class that serializes the information on an API method
 * to an array that is included in JSON responses.
 *
 * @package API
 * @subpackage Response
 * @see JSONResponseTrait::_sendJSONData()
 */
class JSONInfoSerializer
{
	public const KEY_REQUEST_MIME = 'requestMime';
	public const KEY_SELECTED_VERSION = 'selectedVersion';
	public const KEY_METHOD_NAME = 'methodName';
	public const KEY_REQUEST_TIME = 'requestTime';
	public const KEY_RESPONSE_MIME = 'responseMime';
	public const KEY_DESCRIPTION = 'description';
	public const KEY_AVAILABLE_VERSIONS = 'availableVersions';
	public const KEY_DOCUMENTATION_URL = 'documentationURL';

	public function toArray(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Response/ResponseInterface.php`

```php
namespace Application\API\Response;

use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * Base interface for all API responses.
 *
 * @package API
 * @subpackage Response
 */
interface ResponseInterface
{
	public function getMethod(): APIMethodInterface;
}


```
###  Path: `/src/classes/Application/API/User/APIRightsInterface.php`

```php
namespace Application\API\User;

interface APIRightsInterface
{
	public const GROUP_API = 'API';
	public const RIGHT_VIEW_API_CLIENTS = 'ViewAPIClients';
	public const RIGHT_EDIT_API_CLIENTS = 'EditAPIClients';
	public const RIGHT_DELETE_API_CLIENTS = 'DeleteAPIClients';
	public const RIGHT_CREATE_API_CLIENTS = 'CreateAPIClients';
}


```
###  Path: `/src/classes/Application/API/User/APIRightsTrait.php`

```php
namespace Application\API\User;

use Application_User_Rights as Application_User_Rights;
use Application_User_Rights_Group as Application_User_Rights_Group;

/**
 * Trait used to implement the rights for the API clients module.
 *
 * @package API
 * @subpackage User
 *
 * @see APIRightsInterface
 */
trait APIRightsTrait
{
	public function canEditAPIClients(): bool
	{
		/* ... */
	}


	public function canViewAPIClients(): bool
	{
		/* ... */
	}


	public function canDeleteAPIClients(): bool
	{
		/* ... */
	}


	public function canCreateAPIClients(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Utilities/KeyDescription.php`

```php
namespace Application\API\Utilities;

use Application\API\APIManager as APIManager;
use Application\MarkdownRenderer\MarkdownRenderer as MarkdownRenderer;

/**
 * Utility class used to describe an API response key with its path and a description.
 *
 * @package API
 * @subpackage Utilities
 */
class KeyDescription implements KeyPathInterface
{
	public KeyPath $path;
	public string $description;


	/**
	 * @param string|KeyPath $path
	 * @param string $description Markdown-enabled description of the key.
	 * @param mixed ...$args Optional arguments to be used with sprintf to format the description.
	 * @return self
	 */
	public static function create(string|KeyPath $path, string $description, ...$args): self
	{
		/* ... */
	}


	public function getPath(): string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function renderDescription(): string
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Utilities/KeyPath.php`

```php
namespace Application\API\Utilities;

class KeyPath implements KeyPathInterface
{
	public static function create(string|KeyPath $component): self
	{
		/* ... */
	}


	public function add(string $component): self
	{
		/* ... */
	}


	public function getPath(): string
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/API/Utilities/KeyPathInterface.php`

```php
namespace Application\API\Utilities;

use AppUtils\Interfaces\StringableInterface as StringableInterface;

interface KeyPathInterface extends StringableInterface
{
	public function getPath(): string;
}


```
###  Path: `/src/classes/Application/API/Utilities/KeyReplacement.php`

```php
namespace Application\API\Utilities;

class KeyReplacement implements KeyPathInterface
{
	public function getPath(): string
	{
		/* ... */
	}


	public function getOldKey(): string
	{
		/* ... */
	}


	public function getNewKey(): string
	{
		/* ... */
	}


	public static function create(string|KeyPath $oldKey, string|KeyPath $newKey): self
	{
		/* ... */
	}


	public function __toString(): string
	{
		/* ... */
	}
}


```