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
- **Response Payload:** `ResponsePayload` and `ErrorResponsePayload` encapsulate
  successful and error responses. `ErrorResponse` is the builder for error payloads.
- **Connector:** `AppAPIConnector` and `AppAPIMethod` provide an HTTP client for
  consuming another framework application's API remotely.
- **Documentation:** `APIDocumentation` and `MethodDocumentation` generate the
  interactive API documentation UI. Examples are supported via `JSONMethodExample`.
- **User Rights:** `APIRightsInterface` / `APIRightsTrait` define the right to access
  the API admin area.
- **Authorization:** API key methods can declare a required application right via
  `getRequiredRight()`. The pipeline enforces this after authentication and before
  execution. Two dedicated error codes signal denial: `ERROR_METHOD_NOT_GRANTED`
  (183005) when the key has no method grant, and `ERROR_INSUFFICIENT_RIGHTS` (183006)
  when the key has method access but the user lacks the required right. Both produce
  HTTP 403 via `ErrorResponse::makeForbidden()`. `updateLastUsed()` is called after
  successful authorization. Both denial paths emit a log message that includes the
  API key ID and method name; the insufficient-rights path also includes the pseudo-user
  ID and the required right name.
  > **Design invariant:** `authorize()` is `private` — it is called unconditionally
  > by `_process()` and cannot be bypassed or overridden by subclasses. This makes
  > authorization mandatory for every request through the pipeline.
  >
  > **Tier 2 rights:** API methods may add further fine-grained right checks inside
  > `handleXxx()` handler methods using `$user->hasRight()`. On denial, send
  > `errorResponse(MailAPIInterface::ERROR_INSUFFICIENT_RIGHTS)->makeForbidden()->send()`.
  > See `SetMailingStateAPI::handleFinalize()` in the HCP Editor for a concrete example.
- **Events:** `RegisterAPIIndexCacheListener` and `RegisterAPIResponseCacheListener`
  integrate the method index and response caches with the framework's CacheControl system.

---

## Folder Structure

| Directory | Contents |
|---|---|
| `BaseMethods/` | `BaseAPIMethod` — abstract base class implementing the full method lifecycle. |
| `Collection/` | `APIMethodCollection`, `APIMethodIndex`, `APICacheLocation` — method discovery, indexing, and cache control integration. |
| `Connector/` | `AppAPIConnector`, `AppAPIMethod` — HTTP client for remote API consumption. |
| `Documentation/` | `APIDocumentation`, `MethodDocumentation`, `JSONMethodExample` — documentation rendering. |
| `Events/` | Cache control event listeners for method index and response caches. |
| `Groups/` | `APIGroupInterface`, `FrameworkAPIGroup`, `GenericAPIGroup` — method grouping. |
| `Response/` | `ResponseInterface`, `JSONInfoSerializer` — response formatting utilities. |
| `Traits/` | Mix-in interfaces and traits for JSON request/response, dry-run, and example rendering. |
| `User/` | `APIRightsInterface`, `APIRightsTrait` — admin area access rights. |
| `Utilities/` | `KeyDescription`, `KeyPath`, `KeyPathInterface`, `KeyReplacement` — response key documentation utilities. |
| `Versioning/` | `VersionedAPIInterface`, `VersionedAPITrait`, `BaseAPIVersion`, `VersionCollection`, `APIVersionInterface` — multi-version support. |
| `Admin/` | Admin UI screens and URL classes for API client management (see submodule `api-clients`). |
| `Cache/` | File-based response caching (see submodule `api-cache`). |
| `Clients/` | Collection/Record for API client and key management (see submodule `api-clients`). |
| `OpenAPI/` | OpenAPI 3.1 spec generation (see submodule `api-openapi`). |
| `Parameters/` | Parameter type system, validation, and rules (see submodule `api-parameters`). |

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
