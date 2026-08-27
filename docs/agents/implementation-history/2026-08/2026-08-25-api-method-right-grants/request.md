# Project: API Method Right Grants

## The Problem

In the framework, we must add rights management to API keys. We already have the possibility to grant access to API methods, but they are currently disconnected from the user rights management.

The precise cause is narrower than "users get their rights at login". `Application::createUser()` constructs a user with an empty `$rights` array, and `Application_User::setRights()` is only ever called from one place: `Application\Session\Base::loadUserByID()` during session unpack. A pseudo user is never session-authenticated, so its rights array is permanently empty.

This means that even if an API key is cleared for an API method, the pseudo user behind it does not have the necessary rights for it.

> Do not "fix" this in the session layer. The session unpack path is correct for authenticated users; the gap is that pseudo users bypass it entirely.

### Current Enforcement

`BaseAPIMethod::authorize()` is the gate. It runs three checks in order:

1. The submitted key resolves to a known `APIKeyRecord` (else `ERROR_API_KEY_INVALID`, HTTP 401).
2. The key is granted the method (else `ERROR_METHOD_NOT_GRANTED`, HTTP 403).
3. If `getRequiredRight()` returns non-null, `$key->getPseudoUser()->hasRight($right)` must pass (else `ERROR_INSUFFICIENT_RIGHTS`, HTTP 403).

Check 3 can never pass today, because the pseudo user holds no rights.

Check 3 is the only line that changes: `$key->getPseudoUser()->hasRight($right)` becomes `$key->getRights()->satisfies($this->getMethodName(), $right)`. Checks 1 and 2, the error codes, the HTTP statuses, and the existing log messages all stay as they are.

## The Project

API methods declare the right they require via `APIKeyMethodInterface::getRequiredRight() : ?string`. This means the rights check can be satisfied from the API method grants held by the key, without the pseudo user ever holding those rights.

Example: An API key is cleared for the API method `CreateRecordAPI`. This method requires the right `CreateRecords`. When `CreateRecordAPI` is invoked with that key, the rights check must pass.

> The fact that deriving the rights is functionally indistinguishable from granting access to an API method without the rights layer is accepted: This is planned to evolve in the future to separate the two layers.

### The Authority Is the Key, Not the User

The check is answered by the **API key**, not by a rights-bearing user object. The key knows which methods it grants; each method declares its required right; therefore the key can answer "is right X satisfied?" directly.

This inverts the naive shape (force-grant rights onto the pseudo user, then ask the user). That shape is rejected — see Decision 2.

## Design Decisions

| # | Decision | Choice |
|---|---|---|
| 1 | Rights per method | Keep the singular `getRequiredRight() : ?string`. One right per method is sufficient. No interface widening. |
| 2 | Where the check is answered | A new `APIKeyRights` object owned by `APIKeyRecord`. No user object is mutated. `getPseudoUser()` stays a pure identity accessor. |
| 3 | Derivation scope | **Per invoked method**, not per key. The gate resolves the right declared by the method under authorization only. The full per-key set is a presentation concern for the Rights Overview screen. |
| 4 | Grant resolution depth | One level via `Right::hasGrant()` — identical to the `Application_User::can()` path, so API keys get no broader authority than human users. |
| 5 | Right lookup source | Extend the API method index cache to store the **declared** right and API group per method, behind a typed DTO and an explicit schema version. Grant expansion is **not** cached. |
| 6 | Grant-all semantics | Grant-all satisfies any single method's declared right by definition. No union set is materialised. |
| 7 | Project scope | Framework only. HCP Editor adaptation is a separate follow-up project. |
| 8 | Unknown rights | Throw at index build time. A method declaring an unregistered right is a configuration error. |
| 9 | Right declaration | `getRequiredRight()` becomes **mandatory** on `APIKeyMethodInterface`. The permissive default in `APIKeyMethodTrait` is removed. |
| 10 | Methods Selection save | Diff-based add/remove scoped to the submitted form. Pagination becomes safe by construction. |

### Decision 2 — No User Mutation

The naive shape — `APIKeyRecord::getPseudoUser()` calling `setRights()` with a derived set before returning the user — is **rejected**.

`Application::createUser()` memoizes instances in a static `$knownUsers[$userID]` map. Calling `setRights()` on the returned instance mutates a **shared, process-global** object. Two concrete failure modes:

- If the pseudo user ID is also the currently logged-in admin's user ID, their session rights are silently overwritten.
- Two API keys pointing at the same pseudo user with different method grants clobber each other's derived rights within a single request.

Beyond those, a memoized getter with a hidden mutating side effect is a latent hazard: a new call site added years later silently corrupts global state with no local indication that it does so.

**Resolution:** introduce `APIKeyRights`, owned by `APIKeyRecord`:

```php
final class APIKeyRights
{
    public function __construct(private readonly APIKeyRecord $key) {}

    public function satisfies(string $methodName, string $rightID) : bool;
}
```

`BaseAPIMethod::authorize()` calls `$key->getRights()->satisfies($this->getMethodName(), $requiredRight)` instead of `$key->getPseudoUser()->hasRight($requiredRight)`. `getPseudoUser()` is left untouched and continues to return the plain identity user.

#### The Method Name Parameter Is Load-Bearing

`satisfies()` is scoped to a single method, not to the key as a whole. This is what enforces Decision 3 structurally — there is no API surface on which a caller *can* obtain the union, so privilege bleed is impossible by construction rather than by convention.

#### Resolution Algorithm

`satisfies(string $methodName, string $rightID)` resolves as follows:

1. If `$methodName` is not granted to the key → return `false`. (Redundant with check 2 for the gate, but essential for Tier 2 callers.)
2. Read the **declared** right of `$methodName` from the method index.
3. If the declared right is `null` → return `false`. A method declaring no right confers no authority.
4. If the declared right equals `$rightID` → return `true`.
5. Otherwise resolve the declared right via `Application_User_Rights::getRightByID()` and test `Right::hasGrant($rightID)`. Hit → `true`.
6. No match → `false`.

Step 5 mirrors `Application_User::hasRightGrant()` exactly, which is what keeps API key authority aligned with human user authority (see Decision 4).

Steps 2–5 read from the in-memory rights manager and the cached index only — no database access beyond the existing method-grant load.

> **On the gate being tautological:** for the authorization gate, steps 1–4 will always succeed, because check 2 has already confirmed the method grant and step 2 reads the same declaration the gate is testing against. This is intentional and is the accepted consequence noted at the top of "The Project" — today the rights layer adds no independent constraint. The abstraction earns its place in two ways: it gives Tier 2 callers a bleed-free way to ask rights questions, and it isolates the single place that must change when the two layers are separated in future.

> An unresolvable right ID in step 5 must not abort with an exception leaking to the response. See the caveat under Decision 8.

#### Tier 2 Consumers

Tier 2 checks inside `handleXxx()` must route through `APIKeyRights` as well:

```php
if (!$key->getRights()->satisfies($this->getMethodName(), SomeRights::RIGHT_DO_THING)) {
    $this->errorResponse(APIMethodInterface::ERROR_INSUFFICIENT_RIGHTS)
        ->makeForbidden()
        ->send();
}
```

A rights-bearing `Application_User` is deliberately **not** offered. Handing out a user object would reintroduce the union — a user's rights array cannot be scoped to one method, so any Tier 2 `hasRight()` call against it would see rights derived from unrelated method grants. That is exactly the bleed Decision 3 exists to prevent.

Consequences:

- `getPseudoUser()` remains available for **identity** only (audit trails, ownership, `getName()`, cache scoping).
- No `createAuthorizedUser()` or equivalent is added. `Application::$knownUsers` is never written to by this project.
- Tier 2 sites in downstream applications that currently call `$user->hasRight()` on the pseudo user were already non-functional (the array was always empty). They must be migrated to `APIKeyRights::satisfies()`. The plan must call this out for the HCP Editor follow-up.

> **Known limitation:** a Tier 2 check can only assert rights reachable from the *invoking* method's own declaration. A method needing an authority its declaration does not cover has a modelling problem, not a missing accessor — the correct fix is to split it into two methods with distinct declared rights.

### Decision 3 — Per-Method Derivation, Not Per-Key Union

The gate needs exactly one right per request: the one declared by the method being invoked. Materialising the union of rights across every method the key grants creates **privilege bleed**.

Concretely: the framework documents a Tier 2 pattern where methods perform additional `hasRight()` checks inside their own handlers. Under a union set, `MethodA` would pass a Tier 2 check for a right that exists only because unrelated `MethodB` is also granted to the same key. The key would receive more authority than the sum of its individual method grants.

Therefore:

- **Authorization path:** resolve the right declared by the invoked method; check that one right. Never build a union.
- **Presentation path:** the Rights Overview screen may compute the full per-key set, because it is informational and never feeds an authorization decision. This is the only place the union is permitted.

This also collapses Decision 6: grant-all trivially satisfies any single method's declared right, so no system-wide god-user right set ever needs to exist.

### Decision 4 — Grant Resolution Depth

The gate uses `Application_User::hasRight()`, an exact `in_array()` check against the flat rights array. It does **not** resolve grant chains — that is `can()`, via `hasRightGrant()`.

The gate stays off `can()`. Switching to it is rejected: `can()` returns `true` unconditionally when authentication is disabled, which would silently disable API authorization in those environments.

So `APIKeyRights::satisfies()` must resolve grants itself. The depth must match what a logged-in user gets, otherwise an API key ends up with a **broader** effective right set than a human holding identical rights.

There are currently two resolution depths in the rights system:

| Method | Depth | Used by |
|---|---|---|
| `Right::getGrants()` | one level, non-recursive | `Right::hasGrant()` → `User::hasRightGrant()` → `User::can()` |
| `Right::resolveGrants()` | fully recursive | `Right::toArray()` only |

**Resolution:** `APIKeyRights::satisfies()` expands **one level**, via `Right::hasGrant()` — matching the `can()` path exactly. API keys and human users then have identical effective authority for the same declared rights.

> The divergence between `getGrants()` and `resolveGrants()` is pre-existing and out of scope here. The plan must add a docblock note on both methods recording which one the authorization path uses, so the discrepancy is not silently widened later.

### Decision 5 — Method Index Format Change

`storage/api/method-index.json` currently maps `methodName => className` and nothing else. It must be extended to also carry, per method:

- the **declared** required right (nullable)
- the API group

**Only declared data is cached.** Grant expansion is deliberately excluded: the grant graph is assembled at boot from `grantRight()` / `grantGroupAll()` calls in PHP, so baking an expanded set into the index would leave a stale index after any grant-chain edit — with nothing to detect it. That reintroduces exactly the fail-open failure this section warns about. Expansion against the in-memory rights manager is cheap; it happens per request.

#### Typed Entries

Index values change from a bare `class-string` to a structured entry. The implementation must introduce a DTO rather than passing raw arrays:

```php
final class APIMethodIndexEntry
{
    public function getMethodName() : string;
    public function getClassName() : string;
    public function getRequiredRight() : ?string;
    public function getGroupID() : string;
}
```

`APIMethodIndex::getMethodClass()` currently returns `$index[$methodName]` directly as a string. It must be updated to read through the DTO, and a `getEntry(string $methodName) : APIMethodIndexEntry` accessor added.

#### Schema Versioning

The file gains an explicit `schema_version` key. On read, a mismatched or absent version triggers an automatic rebuild. Staleness must never be inferred heuristically, and a stale index must **never** be silently treated as "no rights required" — that would fail open.

Downstream applications must re-run their index rebuild. In HCP Editor this is `composer rebuild-api-method-index`.

### Decision 8 — Where Unknown Rights Throw

`Application_User_Rights::getRightByID()` throws when a right ID is not registered. This happens after a right is renamed or removed while a method still declares the old name.

The throw must occur at **index build time**, not at request time. A renamed right then surfaces during `composer build` as a build failure, rather than as an HTTP 500 on a live API call.

> **Caveat the plan must cover:** the registered rights set can differ per application set and per environment. A build that validates cleanly in one environment does not guarantee validity in another. The runtime path must therefore still degrade safely — an unresolvable right ID at request time is a **denial** (HTTP 403), never a pass, and must be logged distinctly from an ordinary insufficient-rights denial.

### Decision 9 — Mandatory Right Declaration

`APIKeyMethodTrait` currently supplies a default `getRequiredRight()` returning `null`. Every new API-key method is therefore unprotected unless its author remembers to override it — the default is fail-open.

Since the purpose of this project is closing an authorization gap, the default is removed:

- `getRequiredRight() : ?string` stays on `APIKeyMethodInterface` and becomes a method every implementing class must define.
- Genuinely public methods return `null` **explicitly**, which is a visible, reviewable decision.
- The default implementation is deleted from `APIKeyMethodTrait`. The `manageParamAPIKey()` implementation stays.
- The existing **override contract** documented on the trait (overrides may only *strengthen* a declaration; returning `null` where a parent returns non-null bypasses the check) moves to the interface docblock, since the trait no longer carries an implementation to override.

**Blast radius:** no framework class implements `APIKeyMethodInterface` today — the interface and trait are infrastructure, and the only consumers are `BaseAPIMethod`, `MethodConverter`, `ResponseConverter`, `APIKeyParam` and `APIMethodDetailTmpl`, all of which test with `instanceof` and never call the trait default. So the framework itself needs no migration.

It is still a **breaking change for downstream applications**. HCP Editor API method classes using the trait must each add an explicit declaration. The plan must:

- Note this in the framework changelog as a breaking change requiring a downstream migration pass.
- Confirm whether test fixtures under `tests/` implement the interface and need updating.

### Decision 10 — Diff-Based Method Selection Save

`APIKeyMethods::setMethods()` clears all grants and re-inserts the submitted set. Combined with a paginated or server-side-filtered grid, a save would silently revoke every method not on the current page.

Banning pagination is rejected as a solution — it is a latent trap that a future filter feature will spring. The save handler is fixed at the root instead:

- The form submits both the set of methods **shown** and the subset **selected**.
- The handler computes `toAdd = selected − currentlyGranted` and `toRemove = (shown − selected) ∩ currentlyGranted`.
- It applies these via the existing `APIKeyMethods::addMethods()` and `removeMethods()`. `setMethods()` is not used.

Methods outside the submitted view are untouched, so pagination and filtering are safe by construction.

## Scope Boundaries

- **API-key methods only.** `getRequiredRight()` is declared on `APIKeyMethodInterface`, not on `APIMethodInterface`. Methods without API-key authentication have no right declaration and take no part in the derivation.
- **Tier 2 rights are a known blind spot.** The framework documents a Tier 2 pattern where methods perform additional fine-grained checks inside their own processing. Those rights are not declared anywhere and cannot be derived. The Rights Overview screen therefore reports *declared* rights, not *all rights actually exercised*. It must be labelled as such.
- **Nothing is persisted.** Rights are resolved on the fly from the method grants. Nothing is written to the pseudo user record or to any rights table.
- **No user object is mutated, and none is created.** `Application_User::setRights()` is not called anywhere in this project, `Application::$knownUsers` is never written to, and no rights-bearing user instance is handed out. See "Tier 2 Consumers" under Decision 2.
- **Grant chain semantics are not changed.** The pre-existing divergence between `Right::getGrants()` (one level) and `Right::resolveGrants()` (recursive) is documented but not resolved here. See Decision 4.

## GUI Changes

The API Key detail screen needs two additional screens. Both are reached from the existing API key tab bar (`APIKeyActionRecordTrait::_handleTabs()`), alongside Status and Key Settings.

### Screen Rights

Add new constants to `Application\API\Admin\APIScreenRights`, following the existing `SCREEN_API_KEYS_*` naming and reusing the existing underlying rights from `APIRightsInterface`:

| Constant | Underlying right |
|---|---|
| `SCREEN_API_KEYS_METHODS` | `RIGHT_VIEW_API_CLIENTS` |
| `SCREEN_API_KEYS_METHODS_EDIT` | `RIGHT_EDIT_API_CLIENTS` |
| `SCREEN_API_KEYS_RIGHTS` | `RIGHT_VIEW_API_CLIENTS` |

No new user rights are introduced.

### Methods Selection

This screen had been envisioned, but was never implemented: A datagrid of all available API methods, with multi-select capability to select which API methods to grant to the API key.

**Columns:** method name, required right, API group, granted state. The grid is groupable/filterable by API group.

A save button in the sidebar saves the API methods selection for the API key.

The save handler is **diff-based** (see Decision 10): it reconciles only the methods present in the submitted view, so pagination and server-side filtering are safe. The form must submit the shown method set alongside the selected subset for the diff to be computable.

> If the "Grant all" setting is enabled, an info alert is shown above the grid with a message saying that grant all mode is enabled, and the multi-select is disabled in the grid. No changes can be made to the selection.

### Rights Overview

This screen lists all user rights relevant to the API methods granted to the key.

Presentation is a **reverse mapping**: each right is listed together with the granted methods that require it, so it is immediately visible why a right is present.

Each right must be marked with **how** it is reached:

| Origin | Meaning |
|---|---|
| Declared | A granted method declares this right directly via `getRequiredRight()`. |
| Granted via | The right is reachable because a declared right grants it. The granting right must be named. |

Without this distinction the screen understates the blast radius of a method grant — an admin sees a short list of declared rights and cannot tell what else those rights unlock.

> This screen is informational only. No changes or maintenance is needed in regards to these rights. Nothing is persisted.

> This is the **only** place where the union of rights across all granted methods is computed. It must never feed an authorization decision. See Decision 3.

> The screen reports declared rights only. See "Tier 2 rights are a known blind spot" under Scope Boundaries.

## Key References

| Concern | Location |
|---|---|
| Authorization gate | `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` (`authorize()`) |
| Right declaration contract | `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php` |
| Default null implementation (to be removed) | `src/classes/Application/API/Clients/API/APIKeyMethodTrait.php` |
| Method grant storage | `src/classes/Application/API/Clients/Keys/APIKeyMethods.php` (table `api_key_methods`) |
| Pseudo user resolution | `src/classes/Application/API/Clients/Keys/APIKeyRecord.php` (`getPseudoUser()`) |
| Method index cache | `src/classes/Application/API/Collection/APIMethodIndex.php` |
| Method index cache control | `src/classes/Application/API/Collection/APICacheLocation.php` |
| User instance cache | `src/classes/Application/Application.php` (`createUser()`, `$knownUsers`, `clearUserCache()`) |
| User rights storage | `src/classes/Application/User/User.php` (`setRights()`, `hasRight()`, `can()`, `hasRightGrant()`) |
| Grant chains | `src/classes/Application/User/Rights/Right.php` (`getGrants()`, `hasGrant()`, `resolveGrants()`) |
| Right registry lookup | `src/classes/Application/User/Rights.php` (`getRightByID()`) |
| Screen rights constants | `src/classes/Application/API/Admin/APIScreenRights.php` |
| API key screens | `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/` |
| API authorization docs | `src/classes/Application/API/README.md`, `src/classes/Application/API/Clients/README.md` |

### New Components

| Component | Proposed Location | Purpose |
|---|---|---|
| `APIKeyRights` | `src/classes/Application/API/Clients/Keys/APIKeyRights.php` | Answers `satisfies(string $methodName, string $rightID) : bool` for a key. Owned by `APIKeyRecord`, exposed via a new `getRights()` accessor. |
| `APIMethodIndexEntry` | `src/classes/Application/API/Collection/APIMethodIndexEntry.php` | Typed index entry: method name, class name, declared right, group ID. |
| Methods Selection screen | `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/` | Datagrid of all API methods with diff-based save. |
| Rights Overview screen | `src/classes/Application/API/Admin/Screens/Mode/View/APIKeys/` | Reverse mapping of rights to granted methods, with declared/granted-via origin. |

Both screens use `APIKeyActionRecordTrait` and register in its `_handleTabs()`, alongside the existing Status and Key Settings tabs.

## Documentation Updates

| Artefact | Change |
|---|---|
| `src/classes/Application/API/README.md` | Update the **Authorization** section: the gate now calls `APIKeyRights::satisfies()`, not `$user->hasRight()`. Rewrite the **Tier 2 rights** note to direct authors to `satisfies()` and away from the pseudo user. |
| `src/classes/Application/API/Clients/README.md` | Update the `getRequiredRight()` description — the declaration is now mandatory, not optional. |
| `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php` | Absorb the override contract docblock from the trait. |
| `src/classes/Application/User/Rights/Right.php` | Docblock note on `getGrants()` / `resolveGrants()` recording which depth the authorization path uses (Decision 4). |
| `changelog.md` | Breaking change entry for the mandatory `getRequiredRight()` declaration, plus the method-index schema change requiring a rebuild. |
| `docs/agents/project-manifest/constraints.md` | Record two invariants: rights questions for API keys go through `APIKeyRights` and are always method-scoped; the pseudo user is identity-only and must never be granted rights. |
| `.context/` | Regenerate via `composer build` after implementation. |
