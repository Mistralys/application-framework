# Plan

## Plan Audit Cycles
- Audits: 4 (Cycle 1 — Critical: 1, Major: 1, Minor: 0 — resolved; Cycle 2 — Critical: 0, Major: 1, Minor: 1 — resolved; Cycle 3 — Critical: 1, Major: 0, Minor: 0 — addressed; Cycle 4 — Critical: 0, Major: 0, Minor: 0 — PASS, converged) — Plan Auditor v1.7.0
- Architectural Reviews: 1 — Plan Architect Reviewer v2.2.0

## Prior Project Context

- `2026-07-21-api-method-rights-enforcement` built the `authorize()` gate, the error codes `183005`/`183006`, and `getRequiredRight()` **with a null default in `APIKeyMethodTrait`** — explicitly chosen at the time for backward compatibility. This plan deliberately reverses that decision (Decision 9), so the changelog must frame it as follow-up hardening rather than a defect fix.
- `2026-07-21-api-method-rights-enforcement-rework-1` closed a missing Tier 2 check in HCP Editor's `SetMailingStateAPI::handleFinalize()`. That call site is the canonical Tier 2 example cited in `src/classes/Application/API/README.md` and must be re-pointed at `APIKeyRights::satisfies()`.
- Repository mid-term goals (test coverage, namespaces, stricter typing) are all served: every new class is namespaced and fully typed, and the test plan materially expands API-key authorization coverage.
- Global insight `30d67edf` (multi-tenant write APIs rely on the auth layer for scoping) reinforces the hard requirement that the rights layer must never grant API keys **broader** authority than a human user with the same rights, and that an unresolvable right must fail closed.

## Summary

Close the authorization gap where an API key's pseudo user can never satisfy a method's `getRequiredRight()` declaration, because pseudo users bypass session unpack and therefore hold no rights. Instead of granting rights to the pseudo user, introduce a new `APIKeyRights` object owned by `APIKeyRecord` that answers a **method-scoped** question — "does this key satisfy right X for method Y?" — by reading the method's declared right from the API method index and expanding it one level through the existing grant graph. The authorization gate swaps a single line; the pseudo user remains identity-only and is never mutated. Supporting work: the method index gains typed entries with a schema version and build-time right validation, `getRequiredRight()` becomes a mandatory declaration, and two new admin screens are added to the API key detail view (a diff-based Methods Selection grid and an informational Rights Overview).

## Architectural Context

**Authorization pipeline.** `BaseAPIMethod::_process()` (`src/classes/Application/API/BaseMethods/BaseAPIMethod.php`) calls `validate()` → `authorize()` → `updateLastUsed()`. `authorize()` is `private` and unconditionally invoked, so it cannot be bypassed or overridden. It performs three checks: key resolution (`ERROR_API_KEY_INVALID`, 401), method grant (`ERROR_METHOD_NOT_GRANTED`, 403), and required right (`ERROR_INSUFFICIENT_RIGHTS`, 403).

**Right declaration.** `APIKeyMethodInterface` (`src/classes/Application/API/Clients/API/APIKeyMethodInterface.php`) declares `getRequiredRight() : ?string`. `APIKeyMethodTrait` supplies both `manageParamAPIKey()` (final) and a permissive `getRequiredRight()` default returning `null`, and carries the override contract docblock. No framework class implements the interface — the five framework consumers (`BaseAPIMethod`, `OpenAPI\MethodConverter`, `OpenAPI\ResponseConverter`, `Clients\API\Params\APIKeyParam`, `themes/default/templates/api/APIMethodDetailTmpl.php`) all test with `instanceof` only.

**Method grants.** `APIKeyMethods` (`src/classes/Application/API/Clients/Keys/APIKeyMethods.php`) stores grants in `api_key_methods` and exposes `addMethods()`, `removeMethods()`, `setMethods()` (destructive clear-and-reinsert), `hasMethod()`, and `getAvailableMethods()` (process-static, sourced from the method index). Grant-all is a flag on the key record that also clears individual rows.

**Method index.** `APIMethodIndex` (`src/classes/Application/API/Collection/APIMethodIndex.php`) writes `storage/api/method-index.json` as a flat `methodName => class-string` map. `getMethodClass()` returns the raw string. Five call sites consume it: `APIManager` (L88/L113/L137/L177), `APIKeyMethods` (L97), `APIMethodParameter` (L20), `RegisterAPIIndexCacheListener` (L27), `ComposerScripts` (L184). Built by `composer build` → `ComposerScripts::apiMethodIndex()`; there is **no** standalone rebuild script in the framework (HCP Editor has one).

**Rights system.** `Application_User_Rights` (`src/classes/Application/User/Rights.php`) is the registry: `getRightByID()` **throws** `ERROR_UNKNOWN_RIGHT` (70701) for unregistered IDs; `rightIDExists()` is the non-throwing probe. `Application_User_Rights_Right` (`src/classes/Application/User/Rights/Right.php`) exposes two resolution depths: `getGrants()` / `hasGrant()` (one level, used by `User::hasRightGrant()` → `User::can()`) and `resolveGrants()` (fully recursive, used only by `toArray()` and `Container::resolveAllRights()`). `User::hasRight()` is a flat `in_array()`; `User::can()` returns `true` unconditionally when authentication is disabled. `Application::createUser()` memoizes instances in a process-global `$knownUsers` map.

**Admin GUI.** API key screens live in `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/` and are wired via `APIKeyActionRecordTrait::_handleTabs()` (Status, Key Settings) with URLs from `APIKeyURLs`. Screen rights are flat constants in `APIScreenRights` mapped onto `APIRightsInterface` rights. Screens are auto-discovered through the admin screen index driven by `getParentScreenClass()`. Hand-built multi-select grids on non-list screens follow `CacheControlMode` (`src/classes/Application/CacheControl/Admin/Screens/CacheControlMode.php`): `createDataGrid()` + `collectEntries()` + a `setCallback()` handler reading `$action->getSelectedValues()`. `RightsOverviewDevelMode` (`src/classes/Application/Users/Admin/Screens/RightsOverviewDevelMode.php`) is the reference for rendering rights with explicit-vs-effective grant distinction.

## Approach / Architecture

Five coordinated changes:

**1. `APIKeyRights` — the authority object.** A new final class at `src/classes/Application/API/Clients/Keys/APIKeyRights.php`, constructed with an `APIKeyRecord` and exposed via a new lazily-initialised `APIKeyRecord::getRights()` accessor (following the existing `getMethods()` shape). Its only public authorization method is:

```php
public function satisfies(string $methodName, string $rightID) : bool
```

The `$methodName` parameter is load-bearing: because there is no API through which a caller can obtain the union of rights across all granted methods, per-method scoping is enforced structurally rather than by convention.

**2. Gate swap.** `BaseAPIMethod::authorize()` replaces `$key->getPseudoUser()->hasRight($requiredRight)` with `$key->getRights()->satisfies($this->getMethodName(), $requiredRight)`. Checks 1 and 2, all error codes, HTTP statuses, and the existing log message contents remain unchanged, except that the insufficient-rights log line no longer names the pseudo-user ID (it is no longer the deciding authority) — it names the key ID, the method, and the required right.

**3. Typed method index with schema version.** `APIMethodIndexEntry` (new, `src/classes/Application/API/Collection/APIMethodIndexEntry.php`) carries method name, class name, declared right (nullable), and group ID. `method-index.json` becomes a versioned document: a `schema_version` key plus a `methods` map of entries. On read, a mismatched or absent version triggers an automatic rebuild — staleness is never inferred heuristically and a stale index is never treated as "no rights required". Only **declared** data is cached; grant expansion is resolved per request against the in-memory rights manager, because the grant graph is assembled at boot from PHP `grantRight()` / `grantGroupAll()` calls and a baked expansion would silently go stale. `APIMethodIndex` also gains a `clearIndexCache()` reset accessor: its hydrated document is held in a per-instance property with no invalidation path, and that single instance is held for the life of the process by the `APIManager::getInstance()` singleton, so tests and tooling need an explicit way to force the next read to hit disk — the same class of test-support reset that `Application_Countries::clearRecordCache()` / `Application_Locales::clearLocaleCache()` already provide for other process-wide singleton caches (see `ApplicationTestCase::tearDown()`).

**4. Mandatory right declaration.** The default `getRequiredRight()` implementation is deleted from `APIKeyMethodTrait` (which keeps `manageParamAPIKey()`), and the override contract docblock moves to the interface. Public methods must now return `null` explicitly — a visible, reviewable decision instead of a fail-open default.

**5. Two admin screens.** A **Methods Selection** screen with a multi-select grid of all available methods (columns: method name, required right, API group, granted state) whose save handler is diff-based — it reconciles only the methods present in the submitted view, making pagination and filtering safe by construction. And a **Rights Overview** screen presenting the reverse mapping (right → granted methods requiring it), marking each right as *Declared* or *Granted via {right}*. Both register in `APIKeyActionRecordTrait::_handleTabs()` alongside Status and Key Settings.

## Rationale

- **The key, not the user, answers the question.** The key knows its method grants; each method declares its right; therefore the key can answer the rights question directly without any rights-bearing user object existing. This avoids mutating `Application::$knownUsers`, which is process-global — mutating the memoized pseudo user would overwrite the logged-in admin's session rights when the IDs collide, and two keys pointing at the same pseudo user would clobber each other within one request.
- **Per-method scoping prevents privilege bleed.** A per-key union would let a Tier 2 check inside `MethodA` pass for a right that exists only because unrelated `MethodB` is granted to the same key. Scoping `satisfies()` to one method makes the union unobtainable rather than merely discouraged.
- **One-level grant expansion matches the human path exactly.** `User::can()` expands one level via `hasRightGrant()` → `Right::hasGrant()`. Matching that depth keeps API-key authority identical to a human holding the same rights; using `resolveGrants()` would make keys strictly more powerful, and routing through `can()` is unusable because it returns `true` unconditionally when authentication is disabled.
- **Caching declarations but not expansions** is the only stale-safe split: declarations change only when source changes (and the build regenerates the index), whereas the grant graph is assembled at runtime with nothing to invalidate a baked copy.
- **Failing the build on an unknown declared right** turns a renamed-right regression into a `composer build` failure instead of a live HTTP 500. Because the registered rights set varies per application set and environment, the runtime path still degrades to a **denial** (403), logged distinctly.
- **Diff-based save** fixes the destructive-save trap at the root. Banning pagination would be a latent trap that a future filter feature springs.
- **The abstraction earns its place despite being tautological today.** For the gate, steps 1–4 of the resolution algorithm always succeed, so the rights layer currently adds no independent constraint. It pays for itself in two ways: Tier 2 callers get a bleed-free way to ask rights questions, and there is exactly one place to change when the two layers are separated later. The architectural review (Plan Architect Reviewer v2.2.0) confirmed this trade-off with a `Reconsider` verdict on Decision 1 — not a request to remove the object, since both named alternatives (folding `satisfies()` onto `APIKeyMethods`, or mutating the pseudo user) are demonstrably worse — but a flag to revisit whether `satisfies()` has earned its keep once the HCP Editor Tier 2 migration (see Out of Scope) actually lands.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Where the rights check is answered | New `APIKeyRights` object owned by `APIKeyRecord` | (a) `getPseudoUser()` calls `setRights()` with a derived set before returning; (b) new `createAuthorizedUser()` returning a fresh rights-bearing user | (a) mutates a process-global memoized instance — can overwrite a live admin session and lets two keys clobber each other; (b) reintroduces the union, because a user's rights array cannot be scoped to one method. Neither is acceptable. |
| Derivation scope | Per invoked method | Per-key union materialised once | The union causes privilege bleed in Tier 2 checks: a method would pass a check for a right owed entirely to an unrelated method grant. Per-method scoping makes the union structurally unobtainable. |
| Grant resolution depth | One level via `Right::hasGrant()` | (a) Fully recursive `resolveGrants()`; (b) route through `Application_User::can()` | (a) gives API keys broader effective authority than a human with identical rights; (b) `can()` returns `true` unconditionally when authentication is disabled, silently disabling API authorization in those environments. |
| Rights per method | Keep singular `getRequiredRight() : ?string` | Widen to `getRequiredRights() : string[]` | One right per method is sufficient for every current and near-term case; widening the interface is a breaking change with no consumer asking for it. |
| Right declaration default | Mandatory — delete the trait default | Keep the `null` default | The default is fail-open: every new method is unprotected unless the author remembers to override. Since the project exists to close an authorization gap, a fail-open default is self-defeating. Cost is a downstream migration pass. |
| Index cache contents | Declared right + group only; version the schema | (a) Also cache the expanded grant set; (b) leave the index unchanged and instantiate the method class to read its declaration | (a) goes stale after any grant-chain edit with nothing to detect it — reintroducing fail-open; (b) instantiating a method class per authorization request is needless cost when the declaration is static. |
| Unknown right ID handling | Throw at index build time; deny (403) at request time | (a) Throw at request time only; (b) treat unknown as "no right required" | (a) surfaces as a live HTTP 500 instead of a build failure; (b) fails open — exactly the class of bug this project closes. The registered rights set varies per app set/environment, so both guards are needed. |
| Methods Selection save | Diff-based add/remove scoped to the submitted view | (a) `setMethods()` clear-and-reinsert; (b) forbid pagination and filtering on the grid | (a) silently revokes every method not on the current page; (b) is a latent trap that a future filter feature springs. Diffing makes pagination safe by construction. |
| Shown-method payload transport | Explicit serialised hidden field listing the shown methods | Derive "shown" server-side by recomputing the grid query on submit | Recomputation can drift if filters, limits, or the available-method set change between render and submit, producing wrong removals. An explicit submitted set makes the diff exactly reflect what the admin saw. |
| Union computation | Permitted only on the Rights Overview screen | Expose a public union accessor on `APIKeyRights` | A public accessor would be reachable from an authorization path. Keeping the union local to the presentation screen preserves the structural guarantee. |

## Pattern Alignment

- **Follows** the lazy-init accessor pattern on `APIKeyRecord` — `getRights()` mirrors `getMethods()` (`src/classes/Application/API/Clients/Keys/APIKeyRecord.php`).
- **Follows** the flat screen-rights constant convention in `src/classes/Application/API/Admin/APIScreenRights.php`, reusing existing `APIRightsInterface` rights — no new user rights are introduced.
- **Follows** the existing key-screen shape: `APIClientRequestTrait` + `APIKeyActionTrait` + `APIKeyActionRecordTrait`, `APIKeyActionRecordInterface`, `URL_NAME`, `getRequiredRight()`, `getCurrentScreenURL()` (`src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyStatusAction.php`).
- **Follows** the hand-built multi-select grid pattern from `src/classes/Application/CacheControl/Admin/Screens/CacheControlMode.php` (grid construction + entry collection + `setCallback()` handler reading `$action->getSelectedValues()`).
- **Follows** the confirm-action pattern for grid actions that mutate state — `addConfirmAction()` + `setCallback()` (`src/classes/Application/ErrorLog/Admin/Screens/ListSubmode.php`) — adopted for the Methods Selection save per the architectural review's recommendation (Decision 7).
- **Follows** the rights-presentation approach of `src/classes/Application/Users/Admin/Screens/RightsOverviewDevelMode.php` (grid rendering with an explicit-versus-effective grant distinction and muted cross-group annotation).
- **Follows** the URL-builder pattern in `src/classes/Application/API/Admin/APIKeyURLs.php` — two new methods composing from `base()`.
- **Follows** the framework's `array()`-only, no-`readonly`, no-enum, `declare(strict_types=1)` conventions (`docs/agents/project-manifest/constraints.md`).
- **Departs** by introducing a versioned JSON cache document where the framework's other index caches are unversioned. Justified: the index is now consulted for an authorization decision, so silently reading an old-format file must be impossible. The version key is the minimum mechanism that makes staleness detectable rather than inferred.
- **Departs** by making an interface method mandatory where the paired trait previously supplied a default. Justified by Decision 9 — the default was fail-open, and the framework itself has zero implementors, so the departure costs the framework nothing and buys an explicit, reviewable declaration everywhere.

## Detailed Steps

### Phase 1 — Method index foundation

1. **Add `APIMethodIndexEntry`.** Create `src/classes/Application/API/Collection/APIMethodIndexEntry.php` (namespaced `Application\API\Collection`, `declare(strict_types=1)`, `final`). Constructor takes method name, class name, nullable declared right, and group ID. Expose `getMethodName()`, `getClassName() : class-string<APIMethodInterface>`, `getRequiredRight() : ?string`, `getGroupID() : string`, plus `toArray()` and a static `fromArray()` for JSON round-tripping. Define the array keys as class constants so reader and writer cannot drift.

2. **Version the index and store declarations.** In `src/classes/Application/API/Collection/APIMethodIndex.php`:
   - Add `public const int SCHEMA_VERSION = 2;` and key constants for the document (`schema_version`, `methods`).
   - Rewrite `build()` to emit the versioned document. For each method from `getMethodCollection()->getAll()`: record `get_class($method)` and `$method->getGroup()->getID()`; record the declared right as `$method->getRequiredRight()` when the method `instanceof APIKeyMethodInterface`, else `null`. Keep the existing `$method->getVersions()` side-effect call and the existing log lines.
   - Rewrite `getIndex()` to read the file, and rebuild automatically when the file is missing **or** when `schema_version` is absent or `!== SCHEMA_VERSION`. Log the rebuild reason. After a rebuild, re-read from disk; if the version is still wrong, throw a new `APIException` code rather than proceeding with an unknown shape.
   - Hydrate into an `array<string,APIMethodIndexEntry>` keyed by method name.
   - Add `getEntry(string $methodName) : APIMethodIndexEntry` carrying the existing not-in-index exception text and `APIException::ERROR_METHOD_NOT_IN_INDEX`.
   - Reimplement `getMethodClass()` as `return $this->getEntry($methodName)->getClassName();` so its public contract and error behaviour are unchanged.
   - Leave `getMethodNames()`, `methodExists()`, `getDataFile()`, and `getCacheLocation()` behaviour unchanged (`getMethodNames()` still returns the method-name keys).
   - Add `public function clearIndexCache() : self` that sets the in-memory `$index` property back to `null` (no other state change, no disk write) and returns `$this`. The class currently exposes no reset path at all; this is required so a test (or a future admin cache-control action) can force the next `getIndex()` call to re-read `getDataFile()` from disk instead of returning the already-hydrated document.

3. **Validate declared rights at build time.** In `APIMethodIndex::build()`, for every non-null declared right, probe the rights registry with `Application::createSystemUser()->getRightsManager()->rightIDExists($rightID)` (the non-throwing probe) and collect every failure. After the loop, if any failures were collected, throw a new `APIException` code listing every offending method/right pair, and do **not** write the file. Collecting before throwing means one build run reports all misconfigurations rather than only the first.

4. **Add the new exception codes.** In `src/classes/Application/API/APIException.php`, add two constants continuing the existing `59213xxx` series: one for "unknown right declared by an API method" (build-time) and one for "method index schema version mismatch after rebuild".

### Phase 2 — Authorization

5. **Add `APIKeyRights`.** Create `src/classes/Application/API/Clients/Keys/APIKeyRights.php` (namespaced `Application\API\Clients\Keys`, `declare(strict_types=1)`, `final`, implements `Application_Interfaces_Loggable` using `Application_Traits_Loggable` so denials can be logged distinctly). Constructor takes `APIKeyRecord $key`. Implement `satisfies(string $methodName, string $rightID) : bool` exactly as:
   1. If `!$this->key->getMethods()->hasMethod($methodName)` → `false`. (Redundant for the gate, essential for Tier 2 callers.)
   2. Read the declared right from `APIManager::getInstance()->getMethodIndex()->getEntry($methodName)->getRequiredRight()`.
   3. If the declared right is `null` → `false`. A method declaring no right confers no authority.
   4. If the declared right `=== $rightID` → `true`.
   5. Otherwise resolve the declared right via the rights manager and return `$right->hasGrant($rightID)` — one level, mirroring `Application_User::hasRightGrant()`.
   6. Otherwise → `false`.
   In step 5, guard the lookup with `rightIDExists()` before `getRightByID()`. When the declared right is not registered, log a **distinct** message identifying it as an unresolvable-right denial (including key ID, method name, declared right, and requested right) and return `false`. Never let the registry exception escape into the HTTP response. Reach the rights manager through `Application::createSystemUser()->getRightsManager()` (the manager is a process-wide static, so no user state is read or written).

6. **Expose `getRights()` on `APIKeyRecord`.** Add a `private ?APIKeyRights $rights = null;` property and a `getRights() : APIKeyRights` accessor using the `isset()` lazy-init guard, mirroring `getMethods()`. Leave `getPseudoUser()` untouched and add a docblock line stating it is an **identity accessor only** — never a source of authorization.

7. **Swap the gate check.** In `src/classes/Application/API/BaseMethods/BaseAPIMethod.php::authorize()`, replace the pseudo-user check with `!$key->getRights()->satisfies($this->getMethodName(), $requiredRight)`. Update the log line to name the key ID, method name, and required right (drop the pseudo-user ID). Leave the error code, `makeForbidden()`, and the user-facing message unchanged. Update the `authorize()` docblock to describe the new authority.

### Phase 3 — Mandatory declaration

8. **Harden the interface.** In `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php`, rewrite the `getRequiredRight()` docblock to state that the declaration is mandatory, that the right is satisfied from the key's method grants via `APIKeyRights::satisfies()` (not from the pseudo user), and to absorb the override contract from the trait (overrides may only strengthen; returning `null` where a parent returns non-null bypasses the check).

9. **Remove the trait default.** Delete the `getRequiredRight()` implementation from `src/classes/Application/API/Clients/API/APIKeyMethodTrait.php`, keeping `manageParamAPIKey()` and updating the trait docblock to point at the interface for the declaration contract.

10. **Fix framework test fixtures.** Add an explicit `getRequiredRight(): ?string { return null; }` to `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethod.php` (it currently relies on the removed default). `TestAPIKeyMethodWithRight.php` already declares its right and needs no change.

11. **Register the test right.** `TestAPIKeyMethodWithRight::TEST_RIGHT` (`'TestAPIMethodRight'`) is **not currently registered in any rights group** — under Step 3 it would fail the build, and under Step 5 it would take the unresolvable-right denial path. Register it in the test application: add a right group (or extend an existing registration) in `tests/application/assets/classes/TestDriver/User.php::registerRightGroups()` that registers `TestAPIMethodRight` and at least one second right that **grants** it, so grant-expansion behaviour is exercised by a real chain rather than only by the `APIRightsInterface` chain.

### Phase 4 — Admin GUI

12. **Add screen rights.** In `src/classes/Application/API/Admin/APIScreenRights.php`, add `SCREEN_API_KEYS_METHODS` and `SCREEN_API_KEYS_RIGHTS` mapped to `APIRightsInterface::RIGHT_VIEW_API_CLIENTS`, and `SCREEN_API_KEYS_METHODS_EDIT` mapped to `RIGHT_EDIT_API_CLIENTS`. No new user rights.

13. **Add URL builders.** In `src/classes/Application/API/Admin/APIKeyURLs.php`, add `methods()` and `rights()` composing from `base()->action(...)` with the new screens' `URL_NAME` constants, mirroring `status()` / `settings()`.

14. **Create the Methods Selection screen.** Add `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyMethodsAction.php` extending `DBHelper\Admin\Screens\Action\BaseRecordAction` (**not** the bare `Application\Admin\Area\Mode\Submode\BaseAction`) — `BaseRecordAction` mixes in `RecordScreenTrait`, which supplies the `getRecord()` / `getCollection()` methods that `APIKeyActionRecordTrait` calls via `parent::`; this mirrors the sibling screen `APIKeyStatusAction` (`extends BaseRecordStatusAction`, itself a subclass of `BaseRecordAction`) — the other sibling, `APIKeySettingsAction`, is not the precedent here: it extends `BaseRecordSettingsAction extends BaseAction` directly and obtains `getRecord()`/`getCollection()` from the separate `RecordEditScreenTrait`, not from `BaseRecordAction`/`RecordScreenTrait`. Implement `APIKeyActionRecordInterface`, using `APIClientRequestTrait` + `APIKeyActionTrait` + `APIKeyActionRecordTrait`. Define `URL_NAME = 'methods'`, `getURLName()`, `getTitle()`, `getNavigationTitle()`, `getRequiredRight()` → `SCREEN_API_KEYS_METHODS`, `getFeatureRights()` exposing the edit right, and `getCurrentScreenURL()` → `adminURL()->methods()`. Build a `UI_DataGrid` in `_handleActions()` following `CacheControlMode::createDataGrid()`:
    - Columns: method name (sortable string, checkbox label), required right, API group (sortable), granted state.
    - `enableMultiSelect()` on the method-name column; `enableLimitOptionsDefault()`.
    - Entries built from `APIManager::getInstance()->getMethodIndex()` — one row per available method, reading the declared right and group ID from the index entry, and granted state from `$key->getMethods()->hasMethod()`.
    - A single grid action added via `addConfirmAction()` (a confirmation dialog gates the save before any grant is mutated — defense-in-depth recommended by the architectural review, complementary to the diff mechanism in Decision 7) and wired with `setCallback()` performing the diff save.
    - `executeCallbacks()` so the action runs before rendering.

15. **Implement the diff-based save.** In the save callback:
    - Read the selected method names from `$action->getSelectedValues()`.
    - Read the **shown** method set from a dedicated hidden field added to the grid via `addHiddenVar()`. Because `addHiddenVar()` coerces its value with `toString()` and cannot carry an array, serialise the shown set as a delimiter-joined string and split it on read; validate every parsed name against `getAvailableMethods()` and discard unknown entries before diffing.
    - Compute `toAdd = selected ∩ shown − currentlyGranted` and `toRemove = (shown − selected) ∩ currentlyGranted`. Intersecting `selected` with `shown` prevents a forged submission from granting a method the admin never saw.
    - Apply via `addMethods()` / `removeMethods()`. **Never** call `setMethods()`.
    - Redirect with a success message reporting the counts added and removed (use the sidebar/redirect message conventions already used by the key screens).

16. **Handle grant-all mode.** When `$key->getMethods()->areAllGranted()`, render an info alert above the grid stating that grant-all is enabled, call `disableMultiSelect()` on the grid, and make the save handler a no-op guard (return early with an info message) so a crafted POST cannot mutate grants while grant-all is on.

17. **Add the save button.** In `_handleSidebar()`, add a primary save button gated with `requireRight(APIScreenRights::SCREEN_API_KEYS_METHODS_EDIT)`, made clickable with the grid action's submit statement from `$grid->clientCommands()->submitAction(...)`. Suppress or disable it in grant-all mode.

18. **Create the Rights Overview screen.** Add `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyRightsAction.php`, extending the same `DBHelper\Admin\Screens\Action\BaseRecordAction` base as Step 14 (for the same `RecordScreenTrait` reason), with the same trait/interface composition, `URL_NAME = 'rights'`, `getRequiredRight()` → `SCREEN_API_KEYS_RIGHTS`, `getCurrentScreenURL()` → `adminURL()->rights()`. Compute the reverse mapping **locally in the screen** (this is the only permitted place for the union — see Decision 3):
    - For each granted method, read its declared right from the index entry; skip `null` declarations.
    - Record the right as **Declared**, listing the granted methods that declare it.
    - Expand each declared right one level via `Right::getGrants()` and record every granted-through right as **Granted via {declaring right}**, naming the granting right.
    - Skip and note (do not throw) any declared right that is not registered, so a misconfigured environment renders a warning row rather than a 500.
    - Render as a grid: right ID, origin (Declared / Granted via), the naming right where applicable, and the granted methods requiring it. Reuse the presentation idioms from `RightsOverviewDevelMode` (action icons, muted cross-group annotation).
    - Add an abstract/note stating the screen is informational, that nothing is persisted, and that it reports **declared** rights only — Tier 2 rights exercised inside handlers are not represented.

19. **Register both tabs.** In `src/classes/Application/API/Admin/Traits/APIKeyActionRecordTrait::_handleTabs()`, append a `Methods` tab and a `Rights` tab between the existing entries and `selectByAction()`, using the new URL builders and appropriate icons from `UI::icon()`.

20. **Rebuild indices.** Run `composer dump-autoload` (classmap autoloading — mandatory for the new classes) and `composer build` (rebuilds the admin screen index so the new screens resolve, and rebuilds the method index in the new schema).

### Phase 5 — Docblocks, tests, docs

21. **Document the grant-depth divergence.** Add a docblock note to both `Right::getGrants()` and `Right::resolveGrants()` in `src/classes/Application/User/Rights/Right.php` recording which depth the authorization path uses (one level, `getGrants()` via `hasGrant()`, shared by `User::can()` and `APIKeyRights::satisfies()`) so the pre-existing divergence is not silently widened. Do not change behaviour.

22. **Re-ground the existing authorization tests.** In `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php`, three cases currently authorize by calling `setRights()` on the pseudo user and will no longer be meaningful:
    - `test_methodAccessGrantedAll()` — must rely on grant-all satisfying the declared right, with no `setRights()` call.
    - `test_userRightsGranted()` — must rely on the method grant plus the declared right.
    - `test_updateLastUsedAfterAuthorization()` — drop the `setRights()` call.
    - `test_userRightsDenied()` currently passes only because the rights array is empty. It cannot be re-grounded on "a granted method whose declared right neither equals nor grants the right being asserted": `BaseAPIMethod::authorize()` always calls `satisfies($this->getMethodName(), $this->getRequiredRight())`, so the method name and the requested right are read from the same method instance being executed — by construction, the index's declared right for that method **is** the requested right, making a "differs but is granted" outcome unreachable at the gate (that comparison is already exercised directly against `APIKeyRights::satisfies()` in `KeyRightsTest`, independent of the gate). Re-ground the case instead on a denial the gate can actually produce: a method that is **not granted** to the key (the method-not-granted branch of `satisfies()`, step 1), asserting `ERROR_INSUFFICIENT_RIGHTS`. Add a second, separate re-grounded assertion — or extend the same test with a second scenario — covering a granted method whose declared right is **unresolvable/unregistered** (step 5's `rightIDExists()` guard), so both reachable gate-level denial paths (not-granted and unresolvable-right) are exercised end-to-end, preserving the coverage intent of AC-03 (not-granted branch) and AC-09 (unresolvable-right branch) without relying on the unreachable "differs but is granted" scenario.
    Constructing the unresolvable-right state is only feasible outside the rights-manager singleton, not inside it: Step 3's build-time probe and Step 5's runtime check both resolve through the identical `Application::createSystemUser()->getRightsManager()`, a process-wide static populated once via `registerRightGroups()` with no reset API, so any right real enough to pass Step 3 is necessarily still registered for Step 5 within the same process — an existing test-application right cannot be "unregistered" at test time. Instead, declare a dedicated fixture right whose ID is a sentinel string (e.g. `TestAPIMethodRightUnregistered`) that is deliberately never passed to `registerRight()`/`registerRightGroups()` anywhere in `tests/application/assets/classes/TestDriver/User.php`, and write that method's index entry directly to the cache file at `APIMethodIndex::getDataFile()`, keeping the current `SCHEMA_VERSION` so `getIndex()` reads the crafted entry as-is instead of triggering an automatic rebuild (which would re-run `build()`'s validation against the real fixture classes and overwrite the crafted entry with a valid one). Because this technique only edits the on-disk JSON cache — a plain data file, distinct from the rights-manager singleton — it needs no reset or mutation of `Application::createSystemUser()->getRightsManager()`: the singleton is simply never asked about a right it was never given, and the same crafted-entry approach serves both `KeyRightsTest`'s direct call to `satisfies()` and `KeyAuthorizationTest`'s gate-level assertion.
    Writing the file is not sufficient on its own: `APIMethodIndex::getIndex()` hydrates into a per-instance `$index` property guarded by `isset()` with no invalidation path, and that single `APIMethodIndex` instance is held for the life of the process by the `APIManager::getInstance()` singleton's `getMethodIndex()` lazy-init cache. Because PHPUnit runs the whole suite in one process (`processIsolation="false"`, `backupStaticProperties="false"`), other API tests earlier in the run (`Cache/*`, `ErrorResponseTest`, `KeyParamTest`, `APIKeyParameterTest`, and others that construct methods via `APIManager::getInstance()`) will very likely have already triggered a real index build, so the crafted on-disk entry would never be picked up without an explicit reset — the same class of problem `ApplicationTestCase::tearDown()` already solves for `Application_Countries`/`Application_Locales` via `clearRecordCache()`/`clearLocaleCache()`. Step 2's new `APIMethodIndex::clearIndexCache()` accessor exists for exactly this purpose: both crafted-entry cases must call `APIManager::getInstance()->getMethodIndex()->clearIndexCache()` immediately **after** writing the crafted file and **before** invoking `satisfies()` / triggering the gate, guaranteeing the next `getIndex()` call re-reads the file regardless of what earlier tests in the process already built. Restore the original file, or delete it to force a clean automatic rebuild, **and** call `clearIndexCache()` a second time, in `tearDown()`, so later tests inherit neither the crafted on-disk state nor the crafted entry lingering in memory.
    Add `Application::clearUserCache()` in `setUp()`/`tearDown()` per the documented gotcha if any case still touches user rights.

23. **Retire or repurpose the misleading test factory.** `APIClientTestTrait::createTestAPIKeyWithRights()` sets pseudo-user rights, which no longer influences authorization. Either remove it (updating all call sites) or rename and re-document it so no future test believes pseudo-user rights grant API authority.

24. **Add new tests** per the Test Plan below.

25. **Update the framework docs** per the Documentation Updates table below.

26. **Regenerate context docs.** Run `composer build` (or `composer build-docs`) and commit the updated `.context/` files.

## Dependencies

- Phase 2 depends on Phase 1 — `APIKeyRights::satisfies()` reads declared rights from the typed index.
- Phase 3 Step 11 (registering `TestAPIMethodRight`) must land **with or before** Phase 1 Step 3, otherwise `composer build` fails on the test application's own fixture.
- Phase 4 depends on Phase 1 — both screens read the declared right and group from index entries.
- Step 20 (`composer dump-autoload` + `composer build`) must run before any screen or index behaviour can be exercised manually or in tests.
- Phase 5 test re-grounding depends on Phases 1–3 being complete.
- The HCP Editor migration (adding explicit `getRequiredRight()` declarations to ~20 method classes, migrating Tier 2 `$user->hasRight()` call sites to `APIKeyRights::satisfies()`, and running `composer rebuild-api-method-index`) is a **separate follow-up project** and out of scope here.

## Required Components

**New files**

| File | Purpose |
|---|---|
| `src/classes/Application/API/Collection/APIMethodIndexEntry.php` | Typed index entry: method name, class name, declared right, group ID. |
| `src/classes/Application/API/Clients/Keys/APIKeyRights.php` | Answers `satisfies(string $methodName, string $rightID) : bool`. |
| `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyMethodsAction.php` | Methods Selection grid with diff-based save. |
| `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/APIKeyRightsAction.php` | Rights Overview (reverse mapping, declared/granted-via origin). |
| `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` | Unit coverage for `APIKeyRights::satisfies()`. |
| `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` | Coverage for the typed index and schema versioning. |

**Modified files**

| File | Change |
|---|---|
| `src/classes/Application/API/Collection/APIMethodIndex.php` | Versioned document, typed entries, `getEntry()`, build-time right validation, `clearIndexCache()` reset accessor. |
| `src/classes/Application/API/APIException.php` | Two new error codes. |
| `src/classes/Application/API/Clients/Keys/APIKeyRecord.php` | `getRights()` accessor; `getPseudoUser()` docblock. |
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` | Gate check swap, log line, `authorize()` docblock. |
| `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php` | Mandatory declaration + absorbed override contract. |
| `src/classes/Application/API/Clients/API/APIKeyMethodTrait.php` | Default `getRequiredRight()` removed. |
| `src/classes/Application/API/Admin/APIScreenRights.php` | Three new screen-right constants. |
| `src/classes/Application/API/Admin/APIKeyURLs.php` | `methods()` and `rights()` builders. |
| `src/classes/Application/API/Admin/Traits/APIKeyActionRecordTrait.php` | Two new tabs. |
| `src/classes/Application/User/Rights/Right.php` | Docblock notes on `getGrants()` / `resolveGrants()`. |
| `tests/application/assets/classes/TestDriver/API/TestAPIKeyMethod.php` | Explicit `getRequiredRight()` returning `null`. |
| `tests/application/assets/classes/TestDriver/User.php` | Register `TestAPIMethodRight` plus a granting right. |
| `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` | Four cases re-grounded. |
| `tests/AppFrameworkTestClasses/API/APIClientTestTrait.php` | Remove or repurpose `createTestAPIKeyWithRights()`. |

**Infrastructure**

- `storage/api/method-index.json` — regenerated in the new schema by `composer build`.
- Admin screen index — regenerated by `composer build` so the new screens resolve.

## Assumptions

- Every API method's `getGroup()->getID()` is stable and safe to cache in the index (it is a static identifier used for documentation and key administration).
- The rights manager is fully registered by the time an API request reaches `authorize()`; it is initialised on first user construction and held in a process-wide static.
- `Application::createSystemUser()` is safe to call from `APIKeyRights` and `APIMethodIndex` purely to reach `getRightsManager()`; no user state is read or written.
- The grid's available-method list is small enough that per-request index reads and one-level grant expansions are negligible; no additional caching is warranted.
- Downstream applications run their index rebuild as part of deployment, so no runtime migration shim is needed beyond the automatic schema-mismatch rebuild.

## Constraints

- PHP 8.4+, `declare(strict_types=1)` in every new file, `array()` syntax only, no PHP enums, no `readonly` properties.
- Classmap autoloading — `composer dump-autoload` after adding any class file.
- `authorize()` stays `private`; the gate must remain non-overridable.
- No user object may be mutated: `Application_User::setRights()` must not be called anywhere in this project, and `Application::$knownUsers` must never be written to.
- Grant-chain semantics are not changed; the `getGrants()` / `resolveGrants()` divergence is documented, not resolved.
- No new PHPStan `trait.unused` suppressions — if any trait ends up without a consumer, add a test-application consumer class.
- `phpstan-result.txt` must be regenerated in the same commit if the error count changes.
- `addHiddenVar()` cannot carry arrays; multi-value payloads must be serialised.
- Data grid IDs must be unique per request.

## Out of Scope

- HCP Editor adaptation: adding explicit `getRequiredRight()` declarations to its ~20 API method classes, migrating its Tier 2 `$user->hasRight()` call sites, and running `composer rebuild-api-method-index`. Separate follow-up project.
- Resolving the pre-existing `getGrants()` vs `resolveGrants()` divergence.
- Widening `getRequiredRight()` to multiple rights per method.
- Any persistence of derived rights, or any change to the `api_key_methods` schema.
- Representing Tier 2 rights on the Rights Overview screen — they are undeclared and cannot be derived.
- Introducing new user rights, or changing which rights gate the API admin screens.
- Adding a standalone framework Composer script for rebuilding the method index.

## Acceptance Criteria

- AC-01: An API key granted method `M` whose declaration is right `R` passes authorization for `M` without any rights being set on its pseudo user.
- AC-02: An API key with grant-all enabled passes authorization for any method with a declared right, without any rights being set on its pseudo user.
- AC-03: `APIKeyRights::satisfies()` returns `false` when the method is not granted to the key, when the method's declared right is `null`, and when the declared right neither equals nor grants (one level) the requested right.
- AC-04: `APIKeyRights::satisfies()` returns `true` when the requested right is reachable one level from the declared right via `Right::hasGrant()`, and `false` when it is only reachable through two or more levels.
- AC-05: `APIKeyRights` exposes no public API by which a caller can obtain the union of rights across all methods granted to a key.
- AC-06: `Application_User::setRights()` is not called anywhere in the changed framework source, and `Application::$knownUsers` is not written to by any code added in this project.
- AC-07: `APIKeyRecord::getPseudoUser()` returns a plain identity user with no rights derived from method grants.
- AC-08: An unregistered declared right causes `composer build` to fail with a message naming every offending method/right pair, and no index file is written.
- AC-09: At request time, an unresolvable declared right produces `ERROR_INSUFFICIENT_RIGHTS` (HTTP 403) and a log message distinct from an ordinary insufficient-rights denial; no exception escapes into the response.
- AC-10: `method-index.json` contains a `schema_version` key; a file with a missing or mismatched version triggers an automatic rebuild, and a stale index is never interpreted as "no rights required".
- AC-11: `APIMethodIndex::getEntry()` returns method name, class name, declared right (nullable), and group ID; `getMethodClass()` and `methodExists()` retain their existing public contracts and error behaviour.
- AC-12: `APIKeyMethodTrait` no longer declares `getRequiredRight()`, and the override contract is documented on `APIKeyMethodInterface`.
- AC-13: Every framework and test-application class implementing `APIKeyMethodInterface` declares `getRequiredRight()` explicitly.
- AC-14: The Methods Selection screen lists every available method with its declared right, API group, and granted state, and is reachable from the API key tab bar.
- AC-15: Saving the Methods Selection screen adds only newly selected methods and removes only deselected methods that were part of the submitted view; methods outside the submitted view remain untouched.
- AC-16: With grant-all enabled, the Methods Selection grid's multi-select is disabled, an info alert is shown, and a submitted save mutates no grants.
- AC-17: The Rights Overview screen lists each right reachable from the key's granted methods together with the granted methods requiring it, marks each as Declared or Granted via a named right, is labelled as informational and declared-rights-only, and persists nothing.
- AC-18: Both new screens are gated on the new `APIScreenRights` constants and introduce no new user rights.
- AC-19: `composer analyze` reports no new PHPStan errors, and no `trait.unused` suppression is added.
- AC-20: All framework API-key test suites pass, including the re-grounded authorization cases.
- AC-21: Saving the Methods Selection grid requires passing an explicit `addConfirmAction()` confirmation dialog before any grant mutation is applied.

## Testing Strategy

Unit tests at three levels, all under `tests/AppFrameworkTests/`, using the existing `APIClientTestCase` base and the Framework Test Application's driver:

1. **`APIKeyRights::satisfies()` in isolation** — exercise each branch of the resolution algorithm against a key with known method grants, using both the real `APIRightsInterface` grant chain (`EditAPIClients` → `ViewAPIClients`) and the new test-application chain registered in Step 11. This is where grant depth, null declarations, ungranted methods, and the unresolvable-right denial are pinned.
2. **The gate end-to-end** — the existing `KeyAuthorizationTest` cases, re-grounded so authorization derives from method grants rather than pseudo-user rights, plus a new negative case proving that setting rights on the pseudo user does **not** grant API authority.
3. **The method index** — schema version round-trip, automatic rebuild on version mismatch, typed entry accessors, unchanged `getMethodClass()` / `methodExists()` contracts, and the build-time throw for an unregistered declared right.

The two new admin screens are verified manually (both are UI-only; the framework has no admin-screen rendering test harness), with the diff arithmetic itself covered indirectly through `APIKeyMethods` add/remove behaviour already tested in `KeyMethodsTest`. Manual verification also confirms the Methods Selection save is gated behind its `addConfirmAction()` confirmation dialog (AC-21).

Run with `composer test-filter -- KeyRights`, `composer test-filter -- KeyAuthorization`, `composer test-filter -- MethodIndex`, then `composer analyze` for the static-analysis gate.

## Test Plan

- `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` — `satisfies()` returns `true` when the declared right equals the requested right on a granted method — AC-01, AC-03
- `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` — `satisfies()` returns `false` for a method not granted to the key — AC-03
- `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` — `satisfies()` returns `false` when the granted method's declared right is `null` — AC-03
- `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` — `satisfies()` returns `true` for a right reachable one level from the declared right (via the `EditAPIClients` → `ViewAPIClients` chain) — AC-04
- `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` — `satisfies()` returns `false` for a right reachable only at depth ≥ 2, proving parity with `User::can()` — AC-04
- `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` — `satisfies()` returns `true` under grant-all without pseudo-user rights — AC-02
- `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` — `satisfies()` returns `false` and throws nothing when the declared right is unregistered, using a crafted method-index cache entry (declared right never passed to `registerRightGroups()`) written directly to `APIMethodIndex::getDataFile()` and made visible via `APIMethodIndex::clearIndexCache()` — AC-09
- `tests/AppFrameworkTests/API/Keys/KeyRightsTest.php` — the pseudo user returned by `getPseudoUser()` holds no rights after a successful `satisfies()` call — AC-06, AC-07
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` (re-grounded `test_userRightsGranted`) — a key granted a method with a declared right authorizes successfully with no `setRights()` call — AC-01
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` (re-grounded `test_methodAccessGrantedAll`) — grant-all authorizes with no `setRights()` call — AC-02
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` (re-grounded `test_userRightsDenied`) — a method **not granted** to the key yields `ERROR_INSUFFICIENT_RIGHTS` at the gate — AC-03
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` (re-grounded `test_userRightsDenied`, second scenario) — a granted method whose crafted index entry declares a right never registered with the shared rights manager yields `ERROR_INSUFFICIENT_RIGHTS` at the gate, distinctly logged, once `APIMethodIndex::clearIndexCache()` has forced the crafted entry to be read — AC-09
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` (re-grounded `test_updateLastUsedAfterAuthorization`) — usage count increments after authorization derived from method grants alone — AC-01
- `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` (new) — setting rights on the pseudo user does **not** authorize a method the key was not granted — AC-05, AC-06
- `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` — `getEntry()` returns the correct method name, class name, declared right, and group ID for a key-authenticated method and for a non-key method (`null` right) — AC-11
- `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` — `getMethodClass()` still returns the class string, and still throws `ERROR_METHOD_NOT_IN_INDEX` for an unknown method — AC-11
- `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` — `methodExists()` and `getMethodNames()` behave unchanged against the versioned document — AC-11
- `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` — the written document contains `schema_version`, and a file written with a wrong/absent version triggers an automatic rebuild rather than being read as-is — AC-10
- `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` — building with a method declaring an unregistered right throws the new `APIException` code naming the offending method, and leaves no index file — AC-08
- `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` — `clearIndexCache()` forces the next `getIndex()` call to re-read `getDataFile()` from disk, observable via a document edited directly on disk between two calls — AC-11

## Documentation Updates

- `src/classes/Application/API/README.md` — rewrite the **Authorization** bullet: the third check now calls `APIKeyRights::satisfies($methodName, $right)`, answered from the key's method grants with one-level grant expansion, not from the pseudo user; note that the insufficient-rights log line no longer includes the pseudo-user ID and that an unresolvable declared right is a distinct, logged 403. Rewrite the **Tier 2 rights** note to direct authors to `satisfies()` and away from `$user->hasRight()` on the pseudo user, and record the known limitation that a Tier 2 check can only assert rights reachable from the invoking method's own declaration.
- `src/classes/Application/API/Clients/README.md` — update the `getRequiredRight()` description: the declaration is now **mandatory**, there is no trait default, public methods return `null` explicitly, and the right is satisfied from method grants rather than pseudo-user rights.
- `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php` — absorb the override contract docblock from the trait; document mandatory declaration.
- `src/classes/Application/API/Clients/API/APIKeyMethodTrait.php` — trait docblock points at the interface for the declaration contract.
- `src/classes/Application/User/Rights/Right.php` — docblock notes on `getGrants()` and `resolveGrants()` recording that the authorization path (both `User::can()` and `APIKeyRights::satisfies()`) uses the one-level `getGrants()` depth.
- `changelog.md` — new entry under the WIP section: breaking change for the mandatory `getRequiredRight()` declaration (with a `### Breaking Changes` subsection describing the downstream migration pass), the method-index schema change requiring a rebuild, the new `APIKeyRights` authority object, and the two new admin screens.
- `docs/agents/project-manifest/constraints.md` — add two invariants (API section or Known Gotchas): rights questions for API keys go through `APIKeyRights` and are always method-scoped, never derived from a user object; and the API key pseudo user is identity-only and must never be granted rights.
- `docs/agents/project-manifest/testing.md` — note that API-key authorization tests must derive authority from method grants, and that setting pseudo-user rights no longer affects the gate.
- `.context/` — regenerate via `composer build` and commit the updated generated files.

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Removing the trait default breaks downstream applications at parse/analysis time.** ~20 HCP Editor method classes use the trait and several rely on the null default. | Framework itself has zero implementors, so nothing breaks in-repo. Flag as a breaking change in `changelog.md` with an explicit migration instruction, and scope the HCP Editor pass as a named follow-up project. |
| **Build-time right validation fails the build in an environment whose rights set differs.** The registered rights set varies per application set. | Collect all failures and report them together so one run fixes everything; keep the runtime path failing closed (403, distinctly logged) so a divergent environment degrades safely rather than opening up. |
| **The test application's own `TestAPIMethodRight` is unregistered and would fail the new build validation.** | Step 11 registers it (plus a granting right) before the validation lands; this is called out as an ordering dependency. |
| **Existing authorization tests pass for the wrong reason after the change.** `test_userRightsDenied` currently passes only because rights are empty and would become vacuous. | Step 22 re-grounds all four affected cases on genuine method-grant-derived outcomes and adds a negative case proving pseudo-user rights confer nothing. |
| **The crafted unresolvable-right index entry is nondeterministic.** `APIMethodIndex::getIndex()` caches its hydrated document in a per-instance property held by the process-wide `APIManager::getInstance()` singleton; other API tests earlier in the same PHPUnit process will likely have already triggered a real index build before the crafted entry is written, so the on-disk write would otherwise never be observed. | Step 2 adds `APIMethodIndex::clearIndexCache()`; Step 22 requires both crafted-entry test cases to call it immediately after writing the crafted file (forcing a fresh disk read) and again in `tearDown()` after restoring the original file (preventing the crafted entry from leaking into later tests) — mirroring the `clearRecordCache()`/`clearLocaleCache()` precedent in `ApplicationTestCase::tearDown()`. |
| **A stale index silently authorizes.** An old-format index read as-is could yield a `null` declared right everywhere. | Explicit `schema_version` with automatic rebuild on mismatch; a persistent mismatch after rebuild throws rather than proceeding. `null` declared right returns `false`, so the fallback is denial, not permission. |
| **The diff save is forgeable.** A crafted POST could submit a "shown" set containing methods the admin never saw. | Intersect `selected` with `shown`, validate every parsed name against `getAvailableMethods()`, and gate the save on `SCREEN_API_KEYS_METHODS_EDIT`. In grant-all mode the handler returns early without mutating anything. The save action also requires passing an `addConfirmAction()` confirmation dialog before the callback runs, as an additional defense-in-depth layer. |
| **`APIKeyRights` is currently tautological within this project's own scope** — its primary payoff (bleed-free Tier 2 checks) accrues only once the HCP Editor Tier 2 migration (Out of Scope) lands. | Keep the object: both named alternatives (folding `satisfies()` onto `APIKeyMethods`, or mutating the pseudo user) are worse. Flagged by the architectural review as a `Reconsider` — revisit whether `satisfies()` has earned its keep once the HCP Editor follow-up lands, or if it stalls indefinitely. |
| **`addHiddenVar()` cannot carry the shown-method array.** | Serialise as a delimiter-joined string and validate each parsed entry on read — explicitly specified in Step 15. |
| **The Rights Overview union leaks into an authorization path.** | The union is computed locally in the screen; `APIKeyRights` exposes no union accessor (AC-05). Documented in `constraints.md`. |
| **New screens do not appear until indices are rebuilt**, producing a confusing "screen not found" during development. | Step 20 makes `composer dump-autoload` + `composer build` an explicit, ordered step with its dependency stated. |
| **`APIKeyRights` reaching the rights manager via `createSystemUser()` looks like it touches user state.** | It only calls `getRightsManager()`, which returns a process-wide static; document this in the class docblock so a future reader does not "optimise" it into a user-rights read. |

## Recommended Workflow
- **Workflow:** ledger
- **Rationale:** The change spans authorization, a cached index format, a breaking interface contract, two new admin screens, and a test re-grounding pass — cross-cutting work with a real security surface that benefits from formal QA, security-audit, and review stages.
