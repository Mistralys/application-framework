# Admin — Rights & Locking

## Access Control

### Screen Rights Registry

Access to each admin screen is governed by a **right string** mapped to the screen class. The mapping is managed through:

- **`ScreenRightsInterface`** — Contract for querying a screen's required right.
- **`BaseScreenRights`** — Abstract implementation that stores a class-string → right map.
- **`ScreenRightsContainerInterface` / `ScreenRightsContainerTrait`** — Provides lazy access to the rights registry from any holder class.

Applications create a concrete subclass of `BaseScreenRights`, register all screen → right pairs in `_registerRights()`, and expose it through a `ScreenRightsContainerInterface` implementor.

### Enforcement

The `Skeleton` base class calls `isUserAllowed()` (via the screen trait) before rendering. If the user lacks the required right, the screen is hidden from navigation and access is denied.

If no explicit right is registered for a screen, `BaseScreenRights::getByScreen()` falls back to `Application_User::RIGHT_DEVELOPER` (most restrictive).

## Concurrent-Editing Locks

### Lock Modes

Screens that support locking declare it via the `isLockable()` method. Two modes exist:

| Mode | Constant | Behavior |
|------|----------|----------|
| Primaryless | `LOCK_MODE_PRIMARYLESS` | Lock is bound to the screen URL path only |
| Primary-based | `LOCK_MODE_PRIMARYBASED` | Lock is bound to a specific record (returned by `getLockManagerPrimary()`) |

### Lifecycle

1. `Skeleton::startLocking()` checks `isLockable()` and `LockManager::isEnabled()`.
2. A `LockManager` instance is created and bound to the screen.
3. In primary-based mode, the primary record is resolved and set on the manager.
4. `lock()` is called — if another user holds the lock, the attempt fails silently and the screen adapts (read-only mode).
5. On normal page completion, the lock is released automatically.

### Label Resolution

`getLockLabel()` provides a human-readable label for the locked item. When the primary is a `LockableRecord_Interface`, its `getLabel()` is used automatically; otherwise the screen must override `getLockLabel()`.
