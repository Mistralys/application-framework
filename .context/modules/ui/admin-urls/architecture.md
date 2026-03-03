# UI AdminURLs - Architecture
_SOURCE: Public class and interface signatures_
# Public class and interface signatures
```
// Structure of documents
└── src/
    └── classes/
        └── UI/
            └── AdminURLs/
                └── AdminURL.php
                └── AdminURLException.php
                └── AdminURLInterface.php
                └── AdminURLsInterface.php

```
###  Path: `/src/classes/UI/AdminURLs/AdminURL.php`

```php
namespace UI\AdminURLs;

use AppUtils\URLBuilder\URLBuilder as URLBuilder;
use AppUtils\URLInfo as URLInfo;
use Application\Application as Application;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

/**
 * Helper class used to build admin screen URLs.
 *
 * To create an instance, use the {@see \UI::adminURL()} method,
 * or the {@see AdminURL::create()} method.
 *
 * @package User Interface
 * @subpackage Admin URLs
 * @see AdminURLInterface
 *
 * @method static AdminURL create(array $params = array())
 * @method static AdminURL createFromURL(string $url)
 * @method static AdminURL createFromURLInfo(URLInfo $info)
 */
class AdminURL extends URLBuilder implements AdminURLInterface
{
	/**
	 * Adds an admin area screen parameter.
	 * @param string $name
	 * @return $this
	 */
	public function area(string $name): self
	{
		/* ... */
	}


	/**
	 * Adds an admin mode screen parameter.
	 * @param string $name
	 * @return $this
	 */
	public function mode(string $name): self
	{
		/* ... */
	}


	/**
	 * Adds an admin submode screen parameter.
	 * @param string $name
	 * @return $this
	 */
	public function submode(string $name): self
	{
		/* ... */
	}


	/**
	 * Adds an admin action screen parameter.
	 * @param string $name
	 * @return $this
	 */
	public function action(string $name): self
	{
		/* ... */
	}


	/**
	 * Add the parameter to enable the application simulation mode.
	 * @param bool $enabled
	 * @return $this
	 */
	public function simulation(bool $enabled = true): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/UI/AdminURLs/AdminURLException.php`

```php
namespace UI\AdminURLs;

use Application_Exception as Application_Exception;

/**
 * @package User Interface
 * @subpackage Admin URLs
 */
class AdminURLException extends Application_Exception
{
	public const ERROR_INVALID_HOST = 169601;
}


```
###  Path: `/src/classes/UI/AdminURLs/AdminURLInterface.php`

```php
namespace UI\AdminURLs;

use AppUtils\ConvertHelper\JSONConverter\JSONConverterException as JSONConverterException;
use AppUtils\Interfaces\RenderableInterface as RenderableInterface;
use AppUtils\URLBuilder\URLBuilderInterface as URLBuilderInterface;

/**
 * Interface for admin URL instances.
 * See {@see AdminURL} for the implementation.
 *
 * @package User Interface
 * @subpackage Admin URLs
 * @see AdminURL
 */
interface AdminURLInterface extends URLBuilderInterface
{
	/**
	 * Adds an admin area screen parameter.
	 * @param string $name
	 * @return $this
	 */
	public function area(string $name): self;


	/**
	 * Adds an admin mode screen parameter.
	 * @param string $name
	 * @return $this
	 */
	public function mode(string $name): self;


	/**
	 * Adds an admin submode screen parameter.
	 * @param string $name
	 * @return $this
	 */
	public function submode(string $name): self;


	/**
	 * Adds an admin action screen parameter.
	 * @param string $name
	 * @return $this
	 */
	public function action(string $name): self;


	/**
	 * Add the parameter to enable the application simulation mode.
	 * @param bool $enabled
	 * @return $this
	 */
	public function simulation(bool $enabled = true): self;
}


```
###  Path: `/src/classes/UI/AdminURLs/AdminURLsInterface.php`

```php
namespace UI\AdminURLs;

/**
 * Interface for classes that give access to admin URLs,
 * typically for a specific entity.
 *
 * @package User Interface
 * @subpackage Admin URLs
 */
interface AdminURLsInterface
{
	/**
	 * Gets the base admin URL for the entity.
	 *
	 * This should be a meaningful entry point for the entity,
	 * typically a list, status or overview screen.
	 *
	 *
	 * @return AdminURLInterface
	 */
	public function base(): AdminURLInterface;
}


```
---
**File Statistics**
- **Size**: 4.2 KB
- **Lines**: 211
File: `modules/ui/admin-urls/architecture.md`
