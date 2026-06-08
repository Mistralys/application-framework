# Admin - Screen Lifecycle Events
<INSTRUCTION>
# Admin Module — Screen Lifecycle Events

Events dispatched during the admin screen rendering lifecycle
(before/after actions, breadcrumb, sidebar, content).

</INSTRUCTION>
------------------------------------------------------------
_SOURCE: Screen lifecycle events_
# Screen lifecycle events
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Admin/
                └── Area/
                    ├── Events/
                    │   └── UIHandlingCompleteEvent.php
                └── Screens/
                    └── Events/
                        └── ActionsHandledEvent.php
                        └── BaseScreenEvent.php
                        └── BeforeActionsHandledEvent.php
                        └── BeforeBreadcrumbHandledEvent.php
                        └── BeforeContentRenderedEvent.php
                        └── BeforeSidebarHandledEvent.php
                        └── BreadcrumbHandledEvent.php
                        └── ContentRenderedEvent.php
                        └── SidebarHandledEvent.php

```
###  Path: `/src/classes/Application/Admin/Area/Events/UIHandlingCompleteEvent.php`

```php
namespace Application\Admin\Area\Events;

use Application\EventHandler\Event\BaseEvent as BaseEvent;
use Application\Interfaces\Admin\AdminAreaInterface as AdminAreaInterface;

class UIHandlingCompleteEvent extends BaseEvent
{
	public const EVENT_NAME = 'UIHandlingComplete';

	public function getName(): string
	{
		/* ... */
	}


	public function getArea(): AdminAreaInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/ActionsHandledEvent.php`

```php
namespace Application\Admin\Screens\Events;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onActionsHandled()
 * @see \Application_Traits_Admin_Screen::handleActions()
 */
class ActionsHandledEvent extends BaseScreenEvent
{
	public const EVENT_NAME = 'ActionsHandled';

	public function getName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/BaseScreenEvent.php`

```php
namespace Application\Admin\Screens\Events;

use Application\EventHandler\Eventables\BaseEventableEvent as BaseEventableEvent;
use Application\Interfaces\Admin\AdminScreenInterface as AdminScreenInterface;

/**
 * Abstract base class for admin screen events.
 *
 * @package Application
 * @subpackage Admin Screens - Events
 */
abstract class BaseScreenEvent extends BaseEventableEvent
{
	public function getScreen(): AdminScreenInterface
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/BeforeActionsHandledEvent.php`

```php
namespace Application\Admin\Screens\Events;

use TestDriver\Area\TestingScreen\CancelHandleActionsScreen as CancelHandleActionsScreen;

/**
 * NOTE: This event is cancellable, which causes the
 * screen's {@see \Application_Traits_Admin_Screen::_handleActions()}
 * method to be skipped entirely.
 *
 * Example: {@see CancelHandleActionsScreen}.
 *
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onBeforeActionsHandled()
 * @see \Application_Traits_Admin_Screen::handleActions()
 */
class BeforeActionsHandledEvent extends BaseScreenEvent
{
	public const EVENT_NAME = 'BeforeActionsHandled';

	public function getName(): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/BeforeBreadcrumbHandledEvent.php`

```php
namespace Application\Admin\Screens\Events;

use UI_Page_Breadcrumb as UI_Page_Breadcrumb;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onBeforeBreadcrumbHandled()
 * @see \Application_Traits_Admin_Screen::handleBreadcrumb()
 */
class BeforeBreadcrumbHandledEvent extends BaseScreenEvent
{
	public const EVENT_NAME = 'BeforeBreadcrumbHandled';

	public function getName(): string
	{
		/* ... */
	}


	public function getBreadcrumb(): UI_Page_Breadcrumb
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/BeforeContentRenderedEvent.php`

```php
namespace Application\Admin\Screens\Events;

use AppUtils\Interfaces\StringableInterface as StringableInterface;
use TestDriver\Area\TestingScreen\ReplaceContentScreen as ReplaceContentScreen;
use UI_Exception as UI_Exception;
use UI_Themes_Theme_ContentRenderer as UI_Themes_Theme_ContentRenderer;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onBeforeContentRendered()
 * @see \Application_Traits_Admin_Screen::renderContent()
 */
class BeforeContentRenderedEvent extends BaseScreenEvent
{
	public const EVENT_NAME = 'BeforeContentRendered';

	public function getName(): string
	{
		/* ... */
	}


	public function getRenderer(): UI_Themes_Theme_ContentRenderer
	{
		/* ... */
	}


	/**
	 * Replaces the screen's content with the provided content.
	 *
	 * **WARNING:** Handle with care. If the screen has sub-screens,
	 * this will effectively hide them. Sub-screen rendering is ignored
	 * if the screen itself has non-empty content.
	 *
	 * Example: See {@see ReplaceContentScreen}
	 *
	 * @param string|number|StringableInterface $content
	 * @return $this
	 * @throws UI_Exception
	 */
	public function replaceScreenContentWith($content): self
	{
		/* ... */
	}


	/**
	 * Whether this event defines content to replace the
	 * screen's content with.
	 *
	 * @return bool
	 */
	public function replacesContent(): bool
	{
		/* ... */
	}


	public function getContent(): string
	{
		/* ... */
	}


	public function isCancellable(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/BeforeSidebarHandledEvent.php`

```php
namespace Application\Admin\Screens\Events;

use UI_Page_Sidebar as UI_Page_Sidebar;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onBeforeSidebarHandled()
 * @see \Application_Traits_Admin_Screen::handleSidebar()
 */
class BeforeSidebarHandledEvent extends BaseScreenEvent
{
	public const EVENT_NAME = 'BeforeSidebarHandled';

	public function getName(): string
	{
		/* ... */
	}


	public function getSidebar(): UI_Page_Sidebar
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/BreadcrumbHandledEvent.php`

```php
namespace Application\Admin\Screens\Events;

use UI_Page_Breadcrumb as UI_Page_Breadcrumb;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onBreadcrumbHandled()
 * @see \Application_Traits_Admin_Screen::handleBreadcrumb()
 */
class BreadcrumbHandledEvent extends BaseScreenEvent
{
	public const EVENT_NAME = 'BreadcrumbHandled';

	public function getName(): string
	{
		/* ... */
	}


	public function getBreadcrumb(): UI_Page_Breadcrumb
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/ContentRenderedEvent.php`

```php
namespace Application\Admin\Screens\Events;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onContentRendered()
 * @see \Application_Traits_Admin_Screen::renderContent()
 */
class ContentRenderedEvent extends BaseScreenEvent
{
	public const EVENT_NAME = 'ContentRendered';

	public function getName(): string
	{
		/* ... */
	}


	/**
	 * Whether the screen rendered any content.
	 *
	 * NOTE: If no content has been rendered, the rendering
	 * is passed on to the sub-screens, if any.
	 *
	 * @return bool
	 */
	public function hasRenderedContent(): bool
	{
		/* ... */
	}


	public function isCancellable(): bool
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Admin/Screens/Events/SidebarHandledEvent.php`

```php
namespace Application\Admin\Screens\Events;

use UI_Page_Sidebar as UI_Page_Sidebar;

/**
 * @package Application
 * @subpackage Admin Screens - Events
 *
 * @see \Application_Traits_Admin_Screen::onSidebarHandled()
 * @see \Application_Traits_Admin_Screen::handleSidebar()
 */
class SidebarHandledEvent extends BaseScreenEvent
{
	public const EVENT_NAME = 'SidebarHandled';

	public function getName(): string
	{
		/* ... */
	}


	public function getSidebar(): UI_Page_Sidebar
	{
		/* ... */
	}
}


```