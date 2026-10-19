# Module: API

## Purpose

Central API subsystem of the Application Framework. Provides the full lifecycle for
JSON API endpoints: request dispatching, method registration, response envelope
construction, parameter handling, versioning, documentation, and client
authentication. Application projects register their API methods, which the framework
discovers, indexes, and exposes through a single dispatcher entry point.

---

## Key Concepts

- **API Method:** A named endpoint class implementing `APIMethodInterface`. Each method
  declares its parameters, versions, response format, and group affiliation. The base
  class `BaseAPIMethod` handles the lifecycle (validation → execution → response).
- **APIManager:** Singleton entry point. Processes incoming API requests by resolving
  the method name, instantiating the method class, and delegating to `process()`.
- **Method Index:** `APIMethodIndex` discovers and caches all available method classes
  across registered folders. Rebuilt on demand or via `composer build`.
- **Method Collection:** `APIMethodCollection` provides runtime access to method
  instances, creation, and iteration.
- **API Groups:** Methods belong to a group (`APIGroupInterface`) used for documentation
  organization and API key permissions. Built-in groups: `FrameworkAPIGroup`,
  `GenericAPIGroup`.
- **Versioning:** Methods can declare multiple version numbers and implement
  `VersionedAPIInterface` + `VersionedAPITrait` for version-specific response classes
  stored in per-method version folders.
- **Traits:** Mix-in interfaces and traits for common patterns:
  - `JSONResponseInterface` / `JSONResponseTrait` — standard JSON envelope responses.
  - `JSONRequestInterface` / `JSONRequestTrait` — JSON request body parsing.
  - `DryRunAPIInterface` / `DryRunAPITrait` — dry-run mode support.
  - `JSONResponseWithExampleInterface` / `JSONResponseWithExampleTrait` — example
    response rendering.
  - `RequestRequestInterface` / `RequestRequestTrait` — traditional `$_REQUEST` handling.
  - `PaginatedAPIInterface` / `PaginatedAPITrait` — `limit`/`offset`/`totalCount`
    paging for list methods, with an out-of-range `limit` or a negative `offset`
    rejected (never clamped) via `VALIDATION_INVALID_LIMIT`/`VALIDATION_INVALID_OFFSET`
    (183701/183702) — this numeric-range rejection does not cover a non-numeric
    `limit`/`offset`, which `IntegerParameter` treats as a warning and silently
    resolves to the configured default. `applyPagination()` applies the resolved
    values to an `Application_FilterCriteria` through its public `setLimit()`
    signature only.
- **Composing methods from pieces:** Shared, reusable method features are
  self-contained interface+trait pairs ("pieces"), never one catch-all trait.
  See the "API Trait Composition Model (Lego Rule)" section of
  `docs/agents/project-manifest/constraints.md` for the full rule set. The
  framework references are `Countries/API` (a single-record resolution
  piece) and `Media/API` (three domain pieces: document lookup, metadata,
  file upload), beside the generic `DryRunAPIInterface`/`DryRunAPITrait` and
  `PaginatedAPIInterface`/`PaginatedAPITrait` pieces above.
- **Response Payload:** `ResponsePayload` and `ErrorResponsePayload` encapsulate
  successful and error responses. `ErrorResponse` is the builder for error payloads.
  Its `getErrorData()` method includes a `validationErrors` array (in addition to
  the legacy `validationMessages` string array) with one `{param, code, message}`
  entry per validation error: `param` is the API-native parameter name (or `null`
  for rule-level, non-parameter errors), `code` is the numeric error code, and
  `message` is the human-readable text without the type/code prefix. This gives
  API consumers (such as the MCP server's `callApi()`) programmatic access to the
  failing parameter name without parsing free-text messages. `ErrorResponsePayload::getHttpStatusCode()`
  exposes the HTTP status code the response was (or would be) sent with — useful
  for code using `processReturn()`, where the real HTTP header is never sent.
- **Removed parameters:** A method can declare a request parameter name it no
  longer accepts via `addRemovedParam()` (mirrors `addParam()`), configuring an
  optional replacement name, `removedSince` version, and free-form note on the
  returned `RemovedParameter`. `BaseAPIMethod` rejects any request still sending
  a declared removed name — including an explicit empty value — with the
  standard `ERROR_REMOVED_PARAMETER_USED` (183009, HTTP 400) **before**
  `validate()` runs, so the stable code is never collapsed into
  `ERROR_INVALID_REQUEST_PARAMS` (183003) even when the replacement is itself a
  required parameter that was not submitted. The error message names the
  removed parameter and, when declared, the replacement, the removed-since
  version, and the note (used verbatim — never as a `sprintf()` format string).
  The response data's `removedParameters` key carries one structured entry per
  offending name (`name`/`replacement`/`removedSince`/`note`), never the
  submitted value. Declare removed parameters instead of hand-writing a
  `getParam()` guard in `collectRequestData()`.
- **JSON body parameters:** A method implementing `JSONRequestInterface` resolves
  every registered non-header, non-reserved parameter from the parsed JSON
  request body first, falling back to the request (query string/POST) for any
  key the body does not carry — the body value always wins when both are
  present. This is implemented by `APIParamManager::getValueSource()` (a
  `JSONBodyValueSource` for JSON methods, a `RequestValueSource` otherwise, see
  the `api-parameters` submodule), injected into every parameter at
  registration time except header and reserved (`method`/`apiVersion`) ones,
  which stay request-only. `JSONRequestTrait::getRequestData()` parses the body
  lazily, once per request: an empty or whitespace body is treated as an empty
  object, so query-only callers keep working unchanged, while invalid JSON or
  a top level that is not an object is rejected with `ERROR_FAILED_TO_READ_INPUT`
  (182801) in both `process()` and `processReturn()` modes. A re-entry guard
  (a provisional empty collection assigned before the body is read) prevents
  the development-mode error echo from recursing into a second parse attempt
  when the body itself is what failed to parse; in that case the echo carries
  `JSONRequest.unparseableBody` instead of the (empty) body data — the raw body
  verbatim when at most 512 characters, otherwise `[N characters truncated]` —
  and a subsequent `getRequestData()` call returns the empty collection without
  parsing again. Every string value echoed in a development-mode error
  response, at any nesting depth and whether or not its key is a registered
  parameter, is similarly truncated past 512 characters. Removed-parameter
  detection (`rejectRemovedParams()`) also sources its key list from the
  active value source, so a declared removed name is detected whether it is
  sent in the body or the query string.
- **Request body ceiling:** `BaseAPIMethod::getRequestBody()` reads the raw request
  body through a `RequestBodyReader` (see the `Request/` folder below), built by the
  overridable `createRequestBodyReader()` factory, which defaults to
  `RequestBodyReader::fromGlobals(RequestBodyReader::resolveEffectiveCeiling(AppConfig::getAPIMaxRequestBodySize(), ini_get('post_max_size')))` —
  the **effective ceiling**: the lower of the configured limit
  (`APP_API_MAX_REQUEST_BODY_SIZE`, derived from the file-content size limit when
  unset) and the server's own `post_max_size`, resolved by
  `RequestBodyReader::resolveEffectiveCeiling()`. `fromGlobals()` itself is
  unchanged — it still takes the ceiling it is given as-is; resolving the
  server-aware limit is the factory's job, not the reader's. A declared or actual
  body length over the effective ceiling yields
  `JSONRequestInterface::ERROR_REQUEST_BODY_TOO_LARGE` (182802) and HTTP 413 via
  `ErrorResponse::makePayloadTooLarge()`, with the existing "exceeds the maximum
  allowed size" message. A body PHP silently discarded because it exceeded
  `post_max_size` converts to the same code and HTTP status, but with its own
  distinct message naming the declared length and `post_max_size` explicitly —
  the two cases share a client action (send a smaller body) but are no longer
  worded identically. The response data's `maxRequestBodySize` key now carries
  the *effective* ceiling, so a client that shrinks its request to fit can
  actually succeed. A read failure still yields the existing
  `ERROR_FAILED_TO_READ_INPUT` (182801). Every rejection (too-large, discarded,
  or read-failure) is logged via `$this->log()` when logging is enabled — a
  diagnostic entry naming the method, the rejection reason, the declared length
  (or `'unknown'`), and the limit, never an audit trail, since no authenticated
  caller identity exists yet at body-read time. A `setRequestBody()` preset
  (used by in-process tests) bypasses the reader entirely, since the body is
  already in memory and was never subject to the ceiling a real request body is
  read under. Operators must still keep `post_max_size` at or above the
  framework's configured ceiling — the effective-ceiling resolution reports
  accurately when `post_max_size` is lower, but does not raise it.
- **Connector:** `AppAPIConnector` and `AppAPIMethod` provide an HTTP client for
  consuming another framework application's API remotely.
- **Documentation:** `APIDocumentation` and `MethodDocumentation` generate the
  interactive API documentation UI. Examples are supported via `JSONMethodExample`.
- **User Rights:** `APIRightsInterface` / `APIRightsTrait` define the right to access
  the API admin area.
- **Authorization:** API key methods can declare a required application right via
  `getRequiredRight()`. The pipeline enforces this after authentication and before
  execution. The third check uses `APIKeyRights::satisfies()` to derive authority
  from the key's method grants — not from pseudo-user rights. Three dedicated
  error codes signal denial: `ERROR_API_KEY_INVALID` (183007) when a value was
  submitted for the API key parameter but it does not match any known key,
  `ERROR_METHOD_NOT_GRANTED` (183005) when the key has no method grant, and
  `ERROR_INSUFFICIENT_RIGHTS` (183006) when the key has method access but
  `satisfies()` returns false (e.g. the declared right is unresolvable). The
  invalid-key case produces HTTP 401 via `ErrorResponse::makeUnauthorized()`;
  the other two produce HTTP 403 via `ErrorResponse::makeForbidden()`. A wholly
  missing API key value is instead rejected earlier by the generic
  required-parameter validation (`ERROR_INVALID_REQUEST_PARAMS`, 183003), since
  `authorize()` never runs in that case. `updateLastUsed()` is called after
  successful authorization. All denial paths emit a log message; the method-grant
  and insufficient-rights paths include the API key ID and method name.
  A record that cannot be resolved from a submitted ID (e.g. an unknown
  `mediaID`) uses a fifth status helper, `ErrorResponse::makeNotFound()`
  (HTTP 404) — distinct from a validation failure, so an HTTP client can tell
  "no such record" apart from "bad input" without parsing the error code. Its
  first caller is `Application\Media\API\MediaDocumentParamsContainer::requireValue()`.
  A sixth status helper, `ErrorResponse::makePayloadTooLarge()` (HTTP 413), is used
  when the request body exceeds the configured ceiling or was discarded by PHP —
  see "Request body ceiling" above. Its first caller is `BaseAPIMethod::getRequestBody()`.
  > **Design invariant:** `authorize()` is `private` — it is called unconditionally
  > by `_process()` and cannot be bypassed or overridden by subclasses. This makes
  > authorization mandatory for every request through the pipeline.
  >
  > **Tier 2 rights:** API methods may add further fine-grained right checks inside
  > `handleXxx()` handler methods. For method-scoped checks, use
  > `$key->getRights()->satisfies()`. On denial, send
  > `errorResponse(MailAPIInterface::ERROR_INSUFFICIENT_RIGHTS)->makeForbidden()->send()`.
  > See `SetMailingStateAPI::handleFinalize()` in the HCP Editor for a concrete example.
- **API key usage tracking:** `APIKeyRecord::updateLastUsed()` is transactional and
  exception-safe, and is always safe to call — with or without an ambient
  transaction already open. If no transaction is open, it starts, owns, and commits
  its own transaction around the write; on failure it rolls back only that
  transaction and rethrows, leaving none open. If a transaction is already open, it
  writes through it without committing or rolling back, leaving the caller's
  transaction entirely authoritative. This matters because the usage counter and
  `last_used` timestamp are an audit fact about the request, not part of the
  calling method's business operation: they are recorded even when a `dryRun=true`
  method call later rolls back its own transaction.
- **Events:** `RegisterAPIIndexCacheListener` and `RegisterAPIResponseCacheListener`
  integrate the method index and response caches with the framework's CacheControl system.
- **Unexpected throwable handling:** `BaseAPIMethod::process()` and `processReturn()`
  share one private execution boundary that catches every `Throwable` the method's own
  error handling does not account for — including a failure raised **before** either
  data-collector method runs (e.g. from `validate()`, `authorize()`, the
  `updateLastUsed()` call, or `getActiveVersion()`). Such a throwable is reported as
  `ERROR_UNEXPECTED_THROWABLE` (183008): HTTP 500 via `process()`, the corresponding
  `ErrorResponsePayload` via `processReturn()` — never an HTML page, never HTTP 200.
  The throwable's message and trace are never included in the response; only an opaque
  `errorLogReference` value is, generated by
  `Application_ErrorLog_Log_Entry_Exception::logThrowable()`. That adapter delegates to
  the legacy `Application_Exception::getLogID()` lifecycle for framework-native
  exceptions (no duplicate log entry), and logs the class, code, trace, and any
  `BaseException::getDetails()` (e.g. a `DBHelper_Exception`'s SQL error text) for
  every other throwable — in the log only, never in the returned reference.
  Building the error response envelope resolves the active version again
  (`JSONInfoSerializer::toArray()` unconditionally, `ErrorResponse::getErrorData()`
  outside a development environment), which would otherwise call a throwing
  `getActiveVersion()` override a second time, outside the shared execution boundary.
  Both call sites use `getSafeActiveVersion()` instead — it memoizes the first
  resolution and falls back to `getCurrentVersion()` (then a hardcoded placeholder) on
  failure, guaranteeing it never throws.
- **Error response redaction:** `ErrorResponse` keeps request-derived content
  (`$_REQUEST`, and the parsed JSON body a `JSONRequestTrait`-using method echoes) in a
  dedicated channel via `addRequestData()`, separate from method-owned domain data
  added via `addData()` (e.g. a validation payload). `getErrorData()` — the one
  chokepoint both response paths (live HTTP and `processReturn()`) read the error body
  through — rebuilds the `requestData` channel into a minimal method/API-version
  allowlist outside a development environment (`Application::isDevelEnvironment()`),
  and drops the parsed-body `JSONRequest` key entirely. `addData()` content always
  passes through unfiltered. An API key can never appear in either channel: it is a
  header-only parameter, never part of `$_REQUEST` or a request body.

---

## Folder Structure

| Directory | Contents |
|---|---|
| `BaseMethods/` | `BaseAPIMethod` — abstract base class implementing the full method lifecycle. |
| `Collection/` | `APIMethodCollection`, `APIMethodIndex`, `APIMethodIndexEntry`, `APICacheLocation` — method discovery, typed index entries, and cache control integration. |
| `Connector/` | `AppAPIConnector`, `AppAPIMethod` — HTTP client for remote API consumption. |
| `Documentation/` | `APIDocumentation`, `MethodDocumentation`, `JSONMethodExample` — documentation rendering. |
| `Events/` | Cache control event listeners for method index and response caches. |
| `Groups/` | `APIGroupInterface`, `FrameworkAPIGroup`, `GenericAPIGroup` — method grouping. |
| `Request/` | `RequestBodyReader`, `RequestBodyException` — reads the raw request body under a byte ceiling, detecting an over-ceiling body, a `post_max_size`-discarded body, and a read failure as distinct internal exceptions. `RequestBodyReader::resolveEffectiveCeiling()` resolves the lower of the configured ceiling and the server's `post_max_size`. Wired into ingress via `BaseAPIMethod::getRequestBody()`/`createRequestBodyReader()` — see "Request body ceiling" above. |
| `Response/` | `ResponseInterface`, `JSONInfoSerializer` — response formatting utilities. |
| `Traits/` | Mix-in interfaces and traits for JSON request/response, dry-run, and example rendering. |
| `User/` | `APIRightsInterface`, `APIRightsTrait` — admin area access rights. |
| `Utilities/` | `KeyDescription`, `KeyPath`, `KeyPathInterface`, `KeyReplacement` — response key documentation utilities. |
| `Versioning/` | `VersionedAPIInterface`, `VersionedAPITrait`, `BaseAPIVersion`, `VersionCollection`, `APIVersionInterface` — multi-version support. |
| `Admin/` | Admin UI screens and URL classes for API client management (see submodule `api-clients`). |
| `Cache/` | File-based response caching (see submodule `api-cache`). |
| `Clients/` | Collection/Record for API client and key management (see submodule `api-clients`). |
| `OpenAPI/` | OpenAPI 3.1 spec generation (see submodule `api-openapi`). |
| `Parameters/` | Parameter type system, validation, rules, and value sources (`ValueSource/`, see submodule `api-parameters`). |

---

## Integration Points

- **Application projects** register API method folders via the method index. The
  framework discovers all classes implementing `APIMethodInterface`.
- **Submodules** `api-parameters`, `api-cache`, `api-openapi`, and `api-clients` extend
  the core with parameter handling, caching, spec generation, and client management.
- **CacheControl** receives cache location registrations from `Events/`.
- **Connectors** module provides the HTTP transport layer used by `AppAPIConnector`.
- **UI module** provides the admin screens framework that `Admin/` builds on.
- **Composer build** pipeline triggers method index generation and OpenAPI spec output.
