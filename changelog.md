# Application Framework Changelog

## v7.7.0 - Regenerated Cache Artefacts & Example Pagination (Breaking-XS)

**Generated cache artefacts — the API method index, admin screen sitemap, and offline-events index —
are now regenerated in place instead of being deleted on cache clear.** Changelog entries are now
attributed to the transaction's actual owner rather than the session user, and a namespace mismatch
on the API cache location has been corrected. Paginated API methods can also register an example-mode
page size, so their example responses stay internally consistent with the list they queried.

- Cache: Generated artefacts registered as cache locations are regenerated in place, never deleted.
- Cache: Admin screen sitemap and offline-events indexes also regenerate in place.
- Cache: The admin cache screen follows the same rule.
- Cache: Regeneration failures are reported after all locations are processed; Composer warns instead of failing.
- Changelog: Committed entries are now attributed to the transaction's actual owner instead of the session user.
- AdminScreenIndex: Fixed indexing leaving the active locale switched afterwards.
- API: The method index survives `composer clear-caches` and `dump-autoload`.
- API: Paginated methods can register an example page size; examples report the page they queried.
- API: Corrected the method index cache location's namespace to match its actual location.
- Docs: The media module's relations to the API, API parameters and DBHelper modules are now declared in both directions, so module builds no longer report asymmetric relations.

### Breaking Changes

`PaginatedAPIInterface::registerPaginationParams()` gained an optional third parameter. This only
matters for implementers that do not use `PaginatedAPITrait`, which already accounts for it.

The API method index cache location's namespace moved from `Application\AppFactory` to
`Application\API\Collection`. Anyone referencing `Application\AppFactory\APICacheLocation` directly
must update to `Application\API\Collection\APICacheLocation`.

## v7.6.0 - Media Document APIs & Removed-Parameter Declaration (Breaking-XS)

**Media documents can now be added, fetched, listed, updated and deleted through the API.**
File content travels as base64 inside a JSON request body, with the upload validated before anything
is stored. Served media files are now hardened against inline-script attacks, and oversized API
requests are rejected cleanly with HTTP 413. API methods can also declare a request parameter as
removed, and callers still sending it get a clear error naming its replacement.

- Media: Added API methods to add, get, list, update and delete media documents.
- Media: Uploads are validated by document type, and failures leave files and records consistent.
- Media: Updates require an actual change and reject a file replacement that changes the extension.
- Media: Dry runs never write or delete anything; delete returns the document's pre-deletion state.
- Media: Served files now send hardening headers, and SVGs are sandboxed against inline scripts.
- Media: Fixed the thumbnail maximum size default; document types must now declare it.
- API: Methods can declare removed parameters; sending one is rejected with a dedicated error code.
- API: Removed parameters are also detected inside JSON request bodies.
- API: Registered parameters of JSON-body methods now resolve from the body, then the query string.
- API: Added a base64 file parameter with a configurable size limit (default 10 MiB).
- API: Oversized requests answer with HTTP 413, respect the server upload limit and are logged.
- API: Added pagination (limit, offset, total count) for list methods.
- API: Unknown record IDs now answer with HTTP 404.
- API: Error responses now expose the HTTP status code they were sent with.
- API: Development-mode error responses truncate long values from the echoed request body.
- API: Callback validation of parameters now passes extra arguments to the callback.
- Docs: Documented removed-parameter declaration and the rule to declare, not hand-write, guards.

### Breaking Changes

The thumbnail maximum size method on media documents no longer has a default implementation, because
the old default returned wrong values. Custom media document classes that use the shared document
trait must now implement it. Built-in document types are unaffected.

## v7.5.3 - Revisionable Settings Form Fixes (Breaking-XS)

**Settings forms for revisionable records now show the record's actual values when editing, and can be saved.**
Fields such as the label and custom values like an alias are now pre-filled. Saving these forms previously 
failed outright. Settings can now declare how their value is applied on save.

- Revisionable: Edit forms now pre-fill the record's label and custom values.
- Formable: Fixed saving settings forms for revisionable records failing.
- Formable: Added apply callbacks, letting a setting control how its value is saved.

### Breaking Changes

Form values for revisionable records now include the label and custom values in addition to the
revision data. On a name collision the label wins over custom values, which win over revision data.
If your code expects only revision data, account for the extra entries. Settings managers for
revisionable records must now register an apply callback for each saved setting, otherwise saving still fails.

## v7.5.2 - API Error Handling & Key Usage Fix

**Fixed a bug causing every key-authenticated API call to fail when usage tracking ran outside a transaction.**
Unexpected failures inside API methods now return a stable JSON error instead of leaking the
framework's HTML error page, and error responses no longer expose raw request data outside
development environments. Exception details also now include the underlying MySQL error code.

- API: Fixed key-authenticated API calls failing when usage tracking ran outside a transaction.
- API: Unexpected method failures now return a stable JSON error instead of an HTML page.
- API: Error responses no longer expose raw request data outside development environments.
- DBHelper: Exception details now include the MySQL error code alongside the SQL error message.
- Docs: Documented the new API error-handling and error-response redaction behavior.

## v7.5.1 - API Key Method Grant Management (Breaking-S)

**Invalid API keys now return a distinct unauthorized error instead of a generic bad-request error.**
Validation errors are now structured for easier programmatic handling. Administrators can
manage per-method API key grants and review right-to-method mappings from two new screens.

- API: Invalid API keys now return HTTP 401 instead of a generic error.
- API: Added a structured `validationErrors` array to error responses for programmatic access.
- API: Added a Methods Selection screen for managing API key method grants.
- API: Added a Rights Overview screen showing which methods each right grants access to.
- API: Added a permission guard preventing unauthorized users from saving method grants.
- API: Method index cache is now versioned and validates declared rights at build time.
- Docs: Documented the new HTTP 401 response and validation error format in the API docs.
- Tests: Documented that rights-gated API methods aren't supported by the success-assertion helper.

### Breaking Changes

Classes implementing `APIKeyMethodInterface` via `APIKeyMethodTrait` must now explicitly
implement `getRequiredRight()` — the trait no longer provides a default `null` implementation.
Return a right name to require authorization, or `null` to opt out. The method index JSON
schema is now versioned (v2); stale index files are rebuilt automatically on first access.

## v7.5.0 - API Method Authorization Enforcement (Breaking-XS)

**API keys are now authorized per-method and per-right, returning HTTP 403 when denied.**
Methods can require a specific right via `getRequiredRight()`, documented in the API README.

- API: Enforced method-level grant checks for API keys, returning 403 when unauthorized.
- API: Added optional per-method required-right check before execution.
- API: Documented the new `ERROR_METHOD_NOT_GRANTED` and `ERROR_INSUFFICIENT_RIGHTS` error codes.
- Docs: Documented the HTTP 403 authorization responses in the API READMEs.

### Breaking Changes

`APIKeyMethodInterface` gained a new `getRequiredRight()` method. Classes using the
`APIKeyMethodTrait` are unaffected, since it provides a default `null` implementation.
Direct interface implementers must add this method.

## v7.4.0 - BigSelection Multi-Select

**The BigSelection widget now supports multi-select mode with checkable items.**

- BigSelection: Added multi-selection capability with checkable items.
- Docs: Added three appinterface examples for checkable, mixed, and preselected states.

---

Older versions are archived in [docs/changelog-history](docs/changelog-history).
Most recent archive: [v7.3](docs/changelog-history/v7/changelog-v7.3.md).
