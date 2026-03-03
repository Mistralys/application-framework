# UI Client Resources - Architecture
_SOURCE: Public class signatures_
# Public class signatures
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── ClientResource.php
            └── ClientResource/
                ├── Javascript.php
                ├── Stylesheet.php
            └── ClientResourceCollection.php
            └── ResourceManager.php

```
###  Path: `/src/classes/UI/ClientResource.php`

```php
namespace ;

use AppUtils\FileHelper as FileHelper;

/**
 * Abstract base class for clientside resource files.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see UI_ResourceManager
 * @see UI_ClientResource_Javascript
 * @see UI_ClientResource_Stylesheet
 */
abstract class UI_ClientResource
{
	public function getFileOrURL(): string
	{
		/* ... */
	}


	public function getKey(): int
	{
		/* ... */
	}


	public function disable(): UI_ClientResource
	{
		/* ... */
	}


	public function isEnabled(): bool
	{
		/* ... */
	}


	/**
	 * Whether this resource has already been loaded in the current
	 * request, and does not have to be included again.
	 *
	 * @return bool
	 */
	public function isAvoidable(): bool
	{
		/* ... */
	}


	/**
	 * Whether the resource has been specified with an absolute
	 * `http` URL, meaning it is an external resource (like jQuery).
	 *
	 * @return bool
	 */
	public function isAbsoluteURL(): bool
	{
		/* ... */
	}


	/**
	 * File type identifier, e.g. "js".
	 *
	 * @return string
	 *
	 * @see UI_Themes_Theme::FILE_TYPE_STYLESHEET
	 * @see UI_Themes_Theme::FILE_TYPE_JAVASCRIPT
	 * @see UI_Themes_Theme::FILE_TYPE_TEMPLATE
	 * @see UI_Themes_Theme::FILE_TYPE_GRAPHIC
	 */
	public function getFileType(): string
	{
		/* ... */
	}


	/**
	 * Sets the priority with which it should be included
	 * in the page.
	 *
	 * @param int $priority
	 * @return UI_ClientResource
	 */
	public function setPriority(int $priority): UI_ClientResource
	{
		/* ... */
	}


	public function getPriority(): int
	{
		/* ... */
	}


	/**
	 * Retrieves the URL to include this resource.
	 *
	 * Automatically returns the minified version
	 * if enabled, and includes the application's
	 * build key parameter if present.
	 *
	 * @return string
	 */
	public function getURL(): string
	{
		/* ... */
	}


	/**
	 * If we want to use the minified versions of scripts,
	 * this will check if there is a file with the same name,
	 * but with `-min` appended. This is then used instead.
	 *
	 * Note: this will not be applied to absolute URLs.
	 *
	 * @return string
	 */
	public function getMinifiedFileName(): string
	{
		/* ... */
	}


	public function getPath(): string
	{
		/* ... */
	}


	/**
	 * Relative path to the resource file.
	 *
	 * @return string
	 */
	public function getRelativePath(): string
	{
		/* ... */
	}


	public function toArray(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ClientResource/Javascript.php`

```php
namespace ;

use AppUtils\HTMLTag as HTMLTag;

/**
 * Javascript include file.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see UI_ResourceManager
 */
class UI_ClientResource_Javascript extends UI_ClientResource
{
	public function attr(string $name, string $value): self
	{
		/* ... */
	}


	public function setIntegrity(string $key): self
	{
		/* ... */
	}


	public function setTypeModule(): self
	{
		/* ... */
	}


	public function setCrossOriginAnonymous(): self
	{
		/* ... */
	}


	public function setReferrerPolicyNone(): self
	{
		/* ... */
	}


	public function setDefer(bool $defer = true): self
	{
		/* ... */
	}


	public function renderTag(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ClientResource/Stylesheet.php`

```php
namespace ;

/**
 * CSS Stylesheet include file.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 *
 * @see UI_ResourceManager
 */
class UI_ClientResource_Stylesheet extends UI_ClientResource
{
	public function setMedia(string $media): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	public function getMedia(): string
	{
		/* ... */
	}


	public function renderTag(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ClientResourceCollection.php`

```php
namespace UI;

use UI as UI;
use UI_ClientResource as UI_ClientResource;
use UI_ClientResource_Javascript as UI_ClientResource_Javascript;
use UI_ClientResource_Stylesheet as UI_ClientResource_Stylesheet;
use UI_ResourceManager as UI_ResourceManager;

/**
 * Helper class that can be used to keep track of all client
 * resources that get added, and access the list afterwards.
 * Resources can be added just like the {@see UI} class.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class ClientResourceCollection
{
	public function getUI(): UI
	{
		/* ... */
	}


	/**
	 * @return UI_ClientResource[]
	 */
	public function getResources(): array
	{
		/* ... */
	}


	public function hasResources(): bool
	{
		/* ... */
	}


	public function addJavascript(
		string $fileOrUrl,
		int $priority = 0,
		bool $defer = false,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addVendorJavascript(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addVendorStylesheet(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	public function addStylesheet(
		string $fileOrUrl,
		string $media = 'all',
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/ResourceManager.php`

```php
namespace ;

use AppUtils\ClassHelper as ClassHelper;
use AppUtils\FileHelper as FileHelper;

/**
 * Handles the loading of clientside resources.
 *
 * Each stylesheet or javascript include file gets a
 * unique load key, which is automatically registered
 * clientside via the `application.registerLoadKey()`
 * method. This works also with dynamically loaded
 * contents.
 *
 * These load keys are then used to determine which
 * of the requested includes actually have to be
 * included in the current page. They are submitted
 * in every AJAX request via the `_loadkeys` parameter.
 *
 * @package UserInterface
 * @subpackage ClientResources
 * @author Sebastian Mordziol <s.mordziol@mistralys.com>
 */
class UI_ResourceManager
{
	public const ERROR_INVALID_RESOURCE_TYPE_PRIORITY = 62201;
	public const ERROR_UNKNOWN_RESOURCE_EXTENSION = 62202;
	const LOADKEYS_REQUEST_VARIABLE = '_loadkeys';

	public function getUI(): UI
	{
		/* ... */
	}


	public function addJavascript(
		string $fileOrURL,
		int $priority = 0,
		bool $defer = false,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addStylesheet(
		string $fileOrURL,
		string $media = 'all',
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	public function addResource(string $fileOrURL): UI_ClientResource
	{
		/* ... */
	}


	public function getVendorURL(): string
	{
		/* ... */
	}


	public function addVendorJavascript(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Javascript
	{
		/* ... */
	}


	public function addVendorStylesheet(
		string $packageName,
		string $file,
		int $priority = 0,
	): UI_ClientResource_Stylesheet
	{
		/* ... */
	}


	/**
	 * Retrieves the unique load key that identifies javascript
	 * or stylesheet includes.
	 *
	 * @param string $fileOrUrl The relative path to the file, e.g. <code>file.js</code> or <code>file.css</code>
	 * @return integer
	 */
	public function registerClientResource(string $fileOrUrl): int
	{
		/* ... */
	}


	/**
	 * Returns an indexed array with client resource keys
	 * that have been specified as already loaded in the
	 * request, using the <code>_loadkeys</code> parameter.
	 * This parameter is set automatically by an AJAX calls
	 * in the application, in order to avoid loading resources
	 * multiple times.
	 *
	 * @return integer[]
	 */
	public function getLoadedResourceKeys(): array
	{
		/* ... */
	}


	/**
	 * Clears all script load keys present in the current request,
	 * if any. Use this if you do not wish to avoid loading stylesheets
	 * and javascripts when using AJAX calls.
	 */
	public function clearLoadkeys()
	{
		/* ... */
	}


	/**
	 * @return UI_ClientResource_Javascript[]
	 */
	public function getJavascripts(): array
	{
		/* ... */
	}


	/**
	 * @return UI_ClientResource_Stylesheet[]
	 */
	public function getStylesheets(): array
	{
		/* ... */
	}


	public function renderIncludes(): string
	{
		/* ... */
	}
}


```
---
**File Statistics**
- **Size**: 8.74 KB
- **Lines**: 519
File: `modules/ui/client-resources/architecture.md`
