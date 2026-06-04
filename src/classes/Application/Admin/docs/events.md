# Admin — Lifecycle Events

## Screen Events (`Application\Admin\Screens\Events\`)

These events are dispatched during the screen rendering lifecycle. All extend `BaseScreenEvent` and provide access to the originating screen via `getScreen()`.

| Event Class | Dispatched When |
|-------------|-----------------|
| `BeforeActionsHandledEvent` | Before `handleActions()` is called |
| `ActionsHandledEvent` | After `handleActions()` completes |
| `BeforeBreadcrumbHandledEvent` | Before `handleBreadcrumb()` is called |
| `BreadcrumbHandledEvent` | After `handleBreadcrumb()` completes |
| `BeforeSidebarHandledEvent` | Before `handleSidebar()` is called |
| `SidebarHandledEvent` | After `handleSidebar()` completes |
| `BeforeContentRenderedEvent` | Before `_renderContent()` is called |
| `ContentRenderedEvent` | After `_renderContent()` completes |

## Area Events (`Application\Admin\Area\Events\`)

| Event Class | Dispatched When |
|-------------|-----------------|
| `UIHandlingCompleteEvent` | After the area's full UI handling cycle has finished |

## Welcome Events (`Application\Admin\Welcome\Events\`)

| Event Class | Dispatched When |
|-------------|-----------------|
| `WelcomeQuickNavEvent` | When the Welcome area builds its quick-navigation links (allows listeners to inject custom items) |

## Listening to Events

All screen events are fired through the `EventableInterface` / `EventableTrait` mechanism. Register listeners via `EventableListener` or the offline event system (`AppFactory::createOfflineEvents()`).
