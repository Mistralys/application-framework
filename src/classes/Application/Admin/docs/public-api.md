# Admin — Public API

## Entry Points

### `Application_Admin_Skeleton`

Abstract base class for all administration screens. Provides the rendering lifecycle (actions → breadcrumb → sidebar → subnavigation → content), redirect helpers, locking integration, and simulation mode. Every Area, Mode, Submode, and Action inherits from this class.

### `Application\Admin\BaseArea`

Namespaced abstract base for new admin areas. Extends the legacy `Application_Admin_Area` with the `AllowableMigrationInterface` and `ClassLoaderScreenInterface`. New areas should extend this class.

### `Application\Admin\Area\BaseMode`

Namespaced abstract base for mode screens within an area.

### `Application\Admin\Area\Mode\BaseSubmode`

Namespaced abstract base for submode screens within a mode.

### `Application\Admin\Area\Mode\Submode\BaseAction`

Namespaced abstract base for action screens (deepest level of the hierarchy).

### `Application\Admin\Wizard\BaseWizardMode`

Abstract base for wizard-style multi-step screens (extends the legacy `Application_Admin_Wizard`).

### `Application\Admin\Index\AdminScreenIndex`

Singleton that provides runtime access to the indexed admin screen sitemap. Used to resolve URL paths to screen classes and to retrieve the full screen tree.

### `Application\Admin\Index\AdminScreenIndexer`

Build-time class-loader-based indexer. Discovers all admin screen classes in the application, instantiates them in stub mode, and produces the sitemap file consumed by `AdminScreenIndex`.

### `Application\Admin\BaseScreenRights`

Abstract utility for registering per-screen access rights. Applications define a subclass, register screen → right mappings, and pass it to the rights container.

### `Application\Admin\ScreenRightsInterface`

Contract for querying which right is required for a given screen class.

### `Application\Admin\ScreenRightsContainerInterface` / `ScreenRightsContainerTrait`

Interface + trait for any class that provides access to the application's admin screen rights registry.

### `Application\Admin\RequestTypes\RequestTypeInterface`

Generic contract for typed request-parameter handlers that resolve a record from the current HTTP request (with redirect-on-missing or exception variants).

### `Application\Admin\ClassLoaderScreenInterface`

Contract for screens that participate in the class-loader-based navigation tree (defines default subscreen class and parent screen class).

### `Application\Admin\AdminScreenStubInterface`

Marker interface: screens implementing this are ignored by the indexer (used for test stubs and internal placeholders).

### `Application\Admin\ScreenException`

Specialized exception that auto-collects screen class and URL path in its developer details.

### `Application\Admin\Traits\DevelModeInterface`

Interface for screens that should only appear in developer mode, grouped by a dev category.
