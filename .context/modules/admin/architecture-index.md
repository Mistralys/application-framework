# Admin - Index and Sitemap
<INSTRUCTION>
# Admin Module — Screen Index & Sitemap

Build-time screen discovery (AdminScreenIndexer) and the runtime
screen index (AdminScreenIndex) that powers navigation and API lookups.

</INSTRUCTION>
------------------------------------------------------------
_SOURCE: Index and sitemap classes_
# Index and sitemap classes
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── Index/
                    └── API/
                        ├── DescribeAdminAreasAPIInterface.php
                        ├── Methods/
                        │   └── DescribeAdminAreasAPI.php
                    └── AdminScreenIndex.php
                    └── AdminScreenIndexer.php
                    └── AdminScreenInfoCollector.php
                    └── ScreenDataInterface.php
                    └── Screens/
                        ├── SitemapMode.php
                    └── StubArea.php
                    └── StubMode.php
                    └── StubSubmode.php

```
###  Path: `/src/classes/Application/Admin/Index/API/DescribeAdminAreasAPIInterface.php`

```php
namespace Application\Admin\Index\API;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\Admin\Index\ScreenDataInterface as ScreenDataInterface;

interface DescribeAdminAreasAPIInterface extends APIMethodInterface, ScreenDataInterface
{
	public const KEY_ROOT_SCREENS = 'screens';
}


```
###  Path: `/src/classes/Application/Admin/Index/API/Methods/DescribeAdminAreasAPI.php`

```php
namespace Application\Admin\Index\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Groups\FrameworkAPIGroup as FrameworkAPIGroup;
use Application\API\Traits\JSONResponseInterface as JSONResponseInterface;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\Admin\Index\API\DescribeAdminAreasAPIInterface as DescribeAdminAreasAPIInterface;
use Application\Admin\Index\AdminScreenIndex as AdminScreenIndex;
use Override as Override;

/**
 * API method that compiles information about all administration areas
 * available in the application.
 *
 * @package Application
 * @subpackage API
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see BaseAPIMethod
 */
class DescribeAdminAreasAPI extends BaseAPIMethod implements JSONResponseInterface, RequestRequestInterface, DescribeAdminAreasAPIInterface
{
	use JSONResponseTrait;
	use RequestRequestTrait;

	public const METHOD_NAME = 'DescribeAdminAreas';
	public const VERSION_1_0 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1_0;

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


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
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
}


```
###  Path: `/src/classes/Application/Admin/Index/AdminScreenIndex.php`

```php
namespace Application\Admin\Index;

use AdminException as AdminException;
use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ConvertHelper\JSONConverter as JSONConverter;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\FileHelper\PHPFile as PHPFile;
use Application\Application as Application;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use UI\AdminURLs\AdminURLInterface as AdminURLInterface;

class AdminScreenIndex
{
	public static function getInstance(): AdminScreenIndex
	{
		/* ... */
	}


	public static function getAPIMethodsFolder(): FolderInfo
	{
		/* ... */
	}


	public static function getIndexFile(): PHPFile
	{
		/* ... */
	}


	public function urlPathExists(string $path): bool
	{
		/* ... */
	}


	public function getClassByURLPath(string $path): ?string
	{
		/* ... */
	}


	/**
	 * @param AdminScreenInterface|class-string<AdminScreenInterface> $subject
	 * @return array<string,string> Screen ID => URL Name pairs
	 */
	public function getSubscreenIDNames(AdminScreenInterface|string $subject): array
	{
		/* ... */
	}


	/**
	 * @param AdminScreenInterface|class-string<AdminScreenInterface> $subject
	 * @return class-string<AdminScreenInterface>[]
	 */
	public function getSubscreenClasses(AdminScreenInterface|string $subject): array
	{
		/* ... */
	}


	/**
	 * @param AdminScreenInterface|class-string<AdminScreenInterface> $subject
	 * @param string $idOrName
	 * @return class-string<AdminScreenInterface>
	 */
	public function getSubscreenClass(AdminScreenInterface|string $subject, string $idOrName): string
	{
		/* ... */
	}


	public function getTree(): array
	{
		/* ... */
	}


	public function countScreens(): int
	{
		/* ... */
	}


	/**
	 * @return array<string,class-string<AdminAreaInterface>>
	 */
	public function getAdminAreas(): array
	{
		/* ... */
	}


	/**
	 * @param string $name Screen ID, URL name or class name.
	 * @return class-string<AdminAreaInterface>|null
	 */
	public function getAreaByName(string $name): ?string
	{
		/* ... */
	}


	public function requireAreaByName(string $name): string
	{
		/* ... */
	}


	/**
	 * @param string|class-string<AdminAreaInterface> $name Screen ID, URL name or class name.
	 * @return bool
	 */
	public function areaExists(string $name): bool
	{
		/* ... */
	}


	/**
	 * @return string[]
	 */
	public function getAdminAreaURLNames(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Index/AdminScreenIndexer.php`

```php
namespace Application\Admin\Index;

use AppLocalize\Localization as Localization;
use AppUtils\ClassHelper as ClassHelper;
use AppUtils\Collections\BaseClassLoaderCollectionMulti as BaseClassLoaderCollectionMulti;
use Application\Admin\AdminScreenStubInterface as AdminScreenStubInterface;
use Application\Interfaces\Admin\AdminActionInterface as AdminActionInterface;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;
use Application\Interfaces\Admin\AdminModeInterface as AdminModeInterface;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Interfaces\Admin\AdminSubmodeInterface as AdminSubmodeInterface;
use Application_Admin_Wizard_Step as Application_Admin_Wizard_Step;
use Application_Driver as Application_Driver;
use Mistralys\AppFramework\AppFramework as AppFramework;
use ReflectionClass as ReflectionClass;

/**
 * @method AdminScreenInfoCollector[] getAll()
 */
class AdminScreenIndexer extends BaseClassLoaderCollectionMulti
{
	public function index(): self
	{
		/* ... */
	}


	public function countScreens(): int
	{
		/* ... */
	}


	public function countContentScreens(): int
	{
		/* ... */
	}


	public function serialize(): array
	{
		/* ... */
	}


	public function getInstanceOfClassName(): string
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
###  Path: `/src/classes/Application/Admin/Index/AdminScreenInfoCollector.php`

```php
namespace Application\Admin\Index;

use AdminException as AdminException;
use AppUtils\ClassHelper as ClassHelper;
use AppUtils\FileHelper as FileHelper;
use AppUtils\FileHelper\FolderInfo as FolderInfo;
use AppUtils\FileHelper\PHPFile as PHPFile;
use AppUtils\Interfaces\StringPrimaryRecordInterface as StringPrimaryRecordInterface;
use Application\Admin\ClassLoaderScreenInterface as ClassLoaderScreenInterface;
use Application\AppFactory as AppFactory;
use Application\Framework\AppFolder as AppFolder;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;
use Application\Interfaces\AllowableMigrationInterface as AllowableMigrationInterface;

class AdminScreenInfoCollector implements StringPrimaryRecordInterface
{
	public function getID(): string
	{
		/* ... */
	}


	public function getScreen(): AdminScreenInterface
	{
		/* ... */
	}


	/**
	 * @return class-string<AdminScreenInterface>
	 */
	public function getClass(): string
	{
		/* ... */
	}


	public function getClassPath(): PHPFile
	{
		/* ... */
	}


	public function getFolder(): FolderInfo
	{
		/* ... */
	}


	public function getSubscreensFolder(): FolderInfo
	{
		/* ... */
	}


	public function detectParentScreenClass(): ?string
	{
		/* ... */
	}


	public function registerSubscreen(AdminScreenInfoCollector $info): self
	{
		/* ... */
	}


	public function registerParentScreen(AdminScreenInfoCollector $parent): self
	{
		/* ... */
	}


	public function getURLName(): string
	{
		/* ... */
	}


	public function getURLPath(): string
	{
		/* ... */
	}


	/**
	 * @return class-string<AdminScreenInterface>[]
	 */
	public function getSubscreenClasses(): array
	{
		/* ... */
	}


	public function toArray(): array
	{
		/* ... */
	}


	public function toTreeArray(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Index/ScreenDataInterface.php`

```php
namespace Application\Admin\Index;

interface ScreenDataInterface
{
	public const KEY_SCREEN_NAVIGATION_TITLE = 'navigationTitle';
	public const KEY_SCREEN_REQUIRED_RIGHT = 'requiredRight';
	public const KEY_SCREEN_CLASS = 'class';
	public const KEY_SCREEN_PATH = 'path';
	public const KEY_SCREEN_ID = 'id';
	public const KEY_SCREEN_URL_PATH = 'urlPath';
	public const KEY_SCREEN_TITLE = 'title';
	public const KEY_SCREEN_FEATURE_RIGHTS = 'featureRights';
	public const KEY_SCREEN_URL_NAME = 'urlName';
	public const KEY_ROOT_URL_PATHS = 'urlPaths';
	public const KEY_ROOT_FLAT = 'flat';
	public const KEY_ROOT_TREE = 'tree';
	public const KEY_SCREEN_SUBSCREEN_CLASSES = 'subscreenClasses';
	public const KEY_SCREEN_SUBSCREENS = 'subscreens';
}


```
###  Path: `/src/classes/Application/Admin/Index/Screens/SitemapMode.php`

```php
namespace Application\Admin\Index\Screens;

use Application\Admin\Area\BaseMode as BaseMode;
use Application\Admin\Index\AdminScreenIndex as AdminScreenIndex;
use Application\Admin\Traits\DevelModeInterface as DevelModeInterface;
use Application\Admin\Traits\DevelModeTrait as DevelModeTrait;
use Application\Development\Admin\DevScreenRights as DevScreenRights;
use Application\Themes\DefaultTemplate\Admin\SitemapTmpl as SitemapTmpl;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

class SitemapMode extends BaseMode implements DevelModeInterface
{
	use DevelModeTrait;

	public const URL_NAME = 'sitemap';

	public function getURLName(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getDevCategory(): string
	{
		/* ... */
	}


	public function getRequiredRight(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Index/StubArea.php`

```php
namespace Application\Admin\Index;

use Application\Admin\AdminScreenStubInterface as AdminScreenStubInterface;
use Application\Admin\BaseArea as BaseArea;

class StubArea extends BaseArea implements AdminScreenStubInterface
{
	public function getURLName(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDefaultMode(): string
	{
		/* ... */
	}


	public function getDefaultSubscreenClass(): null
	{
		/* ... */
	}


	public function getNavigationGroup(): string
	{
		/* ... */
	}


	public function getDependencies(): array
	{
		/* ... */
	}


	public function isCore(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Index/StubMode.php`

```php
namespace Application\Admin\Index;

use Application\Admin\AdminScreenStubInterface as AdminScreenStubInterface;
use Application\Admin\Area\BaseMode as BaseMode;

class StubMode extends BaseMode implements AdminScreenStubInterface
{
	public function getURLName(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDefaultSubmode(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Index/StubSubmode.php`

```php
namespace Application\Admin\Index;

use Application\Admin\AdminScreenStubInterface as AdminScreenStubInterface;
use Application\Admin\Area\Mode\BaseSubmode as BaseSubmode;

class StubSubmode extends BaseSubmode implements AdminScreenStubInterface
{
	public function getURLName(): string
	{
		/* ... */
	}


	public function getNavigationTitle(): string
	{
		/* ... */
	}


	public function getTitle(): string
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDefaultAction(): string
	{
		/* ... */
	}
}


```