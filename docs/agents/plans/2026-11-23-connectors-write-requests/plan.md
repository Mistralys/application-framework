# Plan

## Plan Audit Cycles
- Audits: none — Plan Auditor v1.13.1
- Architectural Reviews: none — Plan Architect Reviewer v2.5.2

## Prior Project Context

- **Why now.** CoMa will expose its communication type rename as `PATCH /communicationTypes/{ct}`, with the operation selected by a vendor `Content-Type`. This follows the review of Olaf Böcker, CoMa's developer (`hcp-coma/docs/agents/plans/2026-11-19-bundle-transfer-and-rename/plan.md`, 2026-11-23). It is the first `PATCH` on the platform, and the HCP Editor will send it through this framework.
- **What the check found.** On the framework's curl adapter, a `PATCH` with a body hangs until timeout. The body is announced but never sent. This was reproduced against a local echo server (research brief, *HTTP transport*). `Connectors_Request` also refuses `PATCH` outright.
- **The wider gap.** The Editor's CoMa writes (`hcp-editor/assets/classes/Coma/Connector/BaseComaWriteMethod.php`) do not use this framework's request flow. They build `HTTP_Request2` requests themselves, and their class notes give the reasons: simulation always fabricates a response, `getData()` leaks raw PEAR transport exceptions, and an exact success status cannot be required. The CoMa GET base works around the same transport leak. The user ruled that the framework closes these gaps together with `PATCH`, so the Editor's writes can move onto the framework.
- **Repository vision.** Mid-term goals include test coverage, namespaces and stricter typing. Every new class here is namespaced and typed. Legacy classes are extended in place, not converted (AGENTS.md §4).
- **Release precedent.** v7.6.0 and v7.7.0 shipped as minor versions marked "Breaking-XS", with a *Breaking Changes* section in `changelog.md`. This plan follows that format.
- **Knowledge base.** No stored insight covers the Connectors module, and none is overtaken by this plan.

## Summary

The Connectors module gains complete support for write requests:

- `PATCH` as a first-class method.
- A curl transport that delivers request bodies for every method.
- Exact expected response codes, per method class or per request.
- Sending live during simulation when a request asks for it.
- Transport failures as `ConnectorException`.
- Faithful serialization of requests that carry a body.

The per-method facts are currently spread over three hand-maintained lists in `Connectors_Request`. They move into one method registry. With this in place, the HCP Editor can implement CoMa's `PATCH` rename and migrate its existing CoMa writes onto the framework flow instead of bypassing it. That migration is separate Editor work.

## Architectural Context

- **`Connectors_Request`** (`src/classes/Connectors/Request.php`, legacy class) holds the configuration of a request and sends it in `getData()`:
  - URL, method, headers, body, POST/GET data, timeout, cache;
  - the per-method rules: allowed methods (`$validHTTPMethods`, L79), valid response codes (`$codesByMethod`, L814), and which methods carry a body (`isPUT() || isPOST()`, L746);
  - `getData()` (L514) checks the cache, builds an `HTTP_Request2` (`createRequest()`, L714), then either fabricates a response in simulation (`simulateResponse()`, L563) or calls `send()` directly. It then validates the status (`createResponse()`, L627).
- **Subclasses:** `Connectors_Request_URL` (`src/classes/Connectors/Request/URL.php`) and `Connectors_Request_Method` (`src/classes/Connectors/Request/Method.php`, which adds an API method name and its query variable).
- **Transport:** PEAR `HTTP_Request2` v2.8.0. `ADAPTER_CLASSES` (L673) maps `curl` (the default) and `socket` to PEAR's adapters.
- **Method classes:** `Connectors\Connector\BaseConnectorMethod` (`src/classes/Connectors/Connector/BaseConnectorMethod.php`) builds requests for one endpoint (`createRequest()`, `createMethodRequest()`, `executeRequest()`). Legacy per-method bases sit in `src/classes/Connectors/Connector/Method/` (`Delete.php`, `Get.php`, `Post.php`, `Put.php`).
- **Simulation:** `Connectors\Connector\BaseConnector::isLiveRequestsEnabled()` (L48) is true outside simulation, and otherwise follows the `live-requests` request parameter.
- **Persistence:**
  - `Connectors\Request\RequestSerializer` (`src/classes/Connectors/Request/RequestSerializer.php`) serializes requests.
  - `Connectors\Response\ResponseSerializer` embeds the serialized request.
  - `Connectors_Request_Cache` (`src/classes/Connectors/Request/Cache.php`) stores serialized responses keyed by `getCacheHash()`.
- **Errors:** `Connectors\Connector\ConnectorException` carries the request and the HTTP response. `Connectors_Request` codes are in the `3390xx` range.

## Approach / Architecture

### 1. One method registry: `Connectors\Request\HTTPMethod`

New class `src/classes/Connectors/Request/HTTPMethod.php`. It is a constants and type class, because the project prohibits PHP enums (`constraints.md` L12). It is the single source for the per-method facts:

| Method | Constant | Body allowed | Default success codes |
|---|---|---|---|
| GET | `HTTPMethod::GET` | no | 200 |
| POST | `HTTPMethod::POST` | yes | 200, 201, 202 |
| PUT | `HTTPMethod::PUT` | yes | 200, 201, 202, 204 |
| PATCH | `HTTPMethod::PATCH` (new; PEAR has no constant) | yes | 200, 202, 204 |
| DELETE | `HTTPMethod::DELETE` | yes | 200, 204 |

API:
- `getAll(): array`
- `isSupported(string $method): bool`
- `allowsBody(string $method): bool`
- `getDefaultSuccessCodes(string $method): array` (`int[]`)

The constant values equal PEAR's method strings, so existing callers passing `HTTP_Request2::METHOD_*` keep working.

Changes in `Connectors_Request`:
- `$validHTTPMethods` is removed. `setHTTPMethod()` checks `HTTPMethod::isSupported()`, and its error lists the supported methods instead of the stale "POST or GET" text.
- The body gate in `createRequest()` becomes `HTTPMethod::allowsBody()`, for both POST data and the body. A `PATCH` and a `DELETE` now send a configured body. `GET` never does.
- `$codesByMethod` stays as a protected override seam, empty by default. `getCodesByMethod()` returns the override when the method is set there, and `HTTPMethod::getDefaultSuccessCodes()` otherwise. Application subclasses that override the property keep working.
- New helpers next to the existing ones: `makePUT()`, `makePATCH()`, `makeDELETE()`, `isPATCH()` and `isDELETE()`. The log lines in `createRequest()` become method-neutral ("body", not "POST body").

### 2. A curl transport that sends bodies: `Connectors\Request\Adapter\CurlAdapter`

New class `src/classes/Connectors/Request/Adapter/CurlAdapter.php`, extending `HTTP_Request2_Adapter_Curl`.

It overrides `createCurlHandle()`. After `parent::createCurlHandle()`, for every method except GET, HEAD and POST that has a non-empty request body, it sets:
- `CURLOPT_UPLOAD` = true;
- `CURLOPT_CUSTOMREQUEST` = the method;
- `CURLOPT_INFILESIZE` = the computed content length.

All other requests are untouched.

`Connectors_Request::ADAPTER_CLASSES[ADAPTER_CURL]` points to it, so every request using the default adapter gets it. The socket adapter is unchanged.

This was verified experimentally (research brief, *Experimental results*):
- `PATCH` and `DELETE` with a body arrive with method, `Content-Type`, `Content-Length` and body intact. This fixes the `PATCH` hang.
- `PUT` with a body stops sending `Transfer-Encoding: chunked` alongside `Content-Length`. Sending both is invalid HTTP and is upstream's second defect.

### 3. Expected response codes per method class and per request

- `Connectors_Request::setExpectedResponseCodes(int ...$codes)` replaces the method defaults for this request. Calling it with no codes restores the defaults. `getExpectedResponseCodes(): array` returns the effective list.
- `isValidResponseCode()` and `simulateResponse()` use the effective list. A simulated response therefore answers with the first expected code, for example 201 for a CoMa `POST`.
- `BaseConnectorMethod::getExpectedResponseCodes(): array` is a new protected hook that returns `array()` by default (meaning "method defaults"). `createRequest()` and `createMethodRequest()` apply it.
- The untyped, uncalled `Connectors_Connector_Method_Get::getValidResponseCodes()` is removed. The base hook replaces it.
- A response outside the expected codes still throws `ConnectorException::ERROR_REQUEST_FAILED` (`339002`) with the HTTP response attached, as today. Callers parse problem details from it.

### 4. Live sends during simulation

- `Connectors_Request::setLiveInSimulation(bool $enabled = true)` marks a request to be sent live even when its connector is in simulation mode.
- `isLiveRequest(): bool` is `liveInSimulation || connector->isLiveRequestsEnabled()`, and `getData()` branches on it. `createRequest()` logs the flag next to the existing simulation lines.
- The opposite policy, not sending at all in simulation, remains the method's own decision through `isLiveRequestsEnabled()`, as the Editor's configuration writers do today. Neither the framework nor `getData()` needs a third mode for it.

### 5. Transport failures as `ConnectorException`

- `getData()` no longer calls `$request->send()` inline. A new protected seam, `executeHTTPRequest(HTTP_Request2 $request): HTTP_Request2_Response` (default: `return $request->send();`), is called inside a private `sendLive()`.
- `sendLive()` converts `HTTP_Request2_ConnectionException` and `HTTP_Request2_MessageException` into `ConnectorException` with:
  - new code `Connectors_Request::ERROR_TRANSPORT_FAILURE` (`339009`);
  - the PEAR exception as `previous`;
  - the URL, method and timeout in the developer details;
  - `setRequest($this)`.
- `HTTP_Request2_LogicException`, `HTTP_Request2_NotImplementedException` and every other throwable propagate unchanged, because they are programming errors. This is the boundary the Editor's `BaseComaGetMethod` already draws (research brief).
- The seam is the overridable-factory test pattern from `testing.md`.

### 6. Requests serialize and hash with everything that identifies them

- `Connectors_Request::getConfiguredBody(): string` returns the body set with `setBody()`/`setBodyJSON()`. The existing `getBody()` keeps returning the body of the sent `HTTP_Request2`.
- `getHashData()` adds the configured body and the expected codes. Two requests that differ only in body no longer share a cache entry.
- `RequestSerializer` adds:
  - `KEY_BODY`;
  - `KEY_EXPECTED_CODES`;
  - for method requests, `KEY_METHOD_NAME` and `KEY_METHOD_VAR`, via a new `Connectors_Request_Method::getMethodVar()`.
- `unserialize()` restores a method request with its real API method name instead of the HTTP method. It also restores the body, the expected codes and `methodVar`.
- A method-request payload without `KEY_METHOD_NAME` comes from an older version and was always restored wrongly. It returns `null` (cache treated as obsolete), as payloads without a request type already do. URL-request payloads without the new keys restore with defaults.

### 7. `PATCH` method base class

New `src/classes/Connectors/Connector/Method/BasePatchMethod.php`: `Connectors\Connector\Method\BasePatchMethod extends BaseConnectorMethod`, with `getHTTPMethod()` returning `HTTPMethod::PATCH`. It is the sibling of the four legacy per-method bases, so a `PATCH` endpoint is declared the same way as the others.

## Rationale

- **The bypass is the symptom; the framework gaps are the cause.** The Editor's direct `HTTP_Request2` code duplicates transport, logging and error mapping. Each of its stated reasons maps to one missing capability: live send in simulation, transport exceptions, and exact status codes. `PATCH` adds a fourth, the transport. Closing all four lets a consumer use one request flow for reads and writes.
- **One registry instead of three lists.** `PATCH` would otherwise need three coordinated edits (allowed list, codes, body gate). Missing any of them fails silently: a 200-only fallback, or a body that is dropped. A registry makes the next method a one-row change. The current consumer is every `Connectors_Request`. The anticipated growth is further methods such as `HEAD` or `OPTIONS` as services require them.
- **Fix the transport in the framework, not per call.** Upstream v2.8.0 is the latest release and has the same code, so there is nothing to upgrade to. A per-method switch to sockets (option A in the user discussion) would leave the default adapter broken for the next `PATCH`, `DELETE` or `PUT` with a body. The subclass changes behaviour only for requests that have a body and use a method curl does not upload by itself. GET and POST, the bulk of traffic, are unaffected.
- **`DELETE` with a body is sent, not dropped.** Silently discarding a configured body is a defect, not a policy. No current Editor `DELETE` sets a body (research brief), so the change affects no existing call, and it is listed as a breaking change for external consumers.
- **Simulation stays in `getData()`.** One flag on the request keeps a single send path for simulation and live. This follows the global insight to route side effects through one implementation rather than copies, and avoids a framework enum of simulation modes that would only encode what a method can already decide.
- **Wrap only transport exceptions.** A caller handling connector failures should see `ConnectorException` for anything the network did. Programming errors must stay loud. The Editor already made this distinction once, and the framework adopts it so consumers can drop their copies.
- **Serializer fix in the same pass.** The serializer has to learn the body and `PATCH` anyway. Leaving the method-name bug would keep cached method requests restoring with the HTTP method as their API method name.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Where per-method rules live | `Connectors\Request\HTTPMethod` registry | (a) Add `PATCH` to the three existing lists; (b) PHP `enum` | (a) keeps three lists that must agree and fail silently when they do not. (b) is prohibited by `constraints.md` (autoloading failures on live). |
| Curl `PATCH` body defect | Framework `CurlAdapter` subclass, default for `curl` | (a) Force the socket adapter for `PATCH`; (b) wait for or patch upstream; (c) make sockets the default adapter | (a) leaves `DELETE`/`PUT` bodies and future custom methods broken on the default adapter. (b) v2.8.0 is current and unchanged upstream, and vendor patches are lost on update. (c) changes the transport, proxy and TLS behaviour of every existing request. |
| Which methods carry a body | POST, PUT, PATCH, DELETE | Only POST, PUT, PATCH | Dropping a configured `DELETE` body silently is a defect; no current consumer sets one. |
| Exact success codes | `setExpectedResponseCodes()` per request plus a method-class hook | (a) Callers check `getStatusCode()` after success; (b) override `$codesByMethod` in a request subclass | (a) lets a wrong-but-accepted status (200 instead of 201) through the framework as success, and the response is then built without error. (b) needs a request subclass per expectation. |
| Live send in simulation | Per-request `setLiveInSimulation()` | (a) Connector-level flag; (b) three-mode simulation policy constant | (a) is too coarse: the same CoMa connector has methods that must send and methods that must not. (b) adds a "skip" mode the method already decides with `isLiveRequestsEnabled()`; no consumer needs it in the framework. |
| Transport failures | Wrap `ConnectionException` and `MessageException` only, in `getData()` | (a) Wrap every `HTTP_Request2_Exception`; (b) leave unwrapped and document | (a) hides programming errors (`LogicException`) behind a connector failure. (b) keeps forcing every consumer to translate, which is the duplication this plan removes. |
| `PATCH` base class naming | Namespaced `Connectors\Connector\Method\BasePatchMethod` | Legacy `Connectors_Connector_Method_Patch` like its siblings | New classes must be namespaced (`constraints.md` L31–39); legacy classes are not renamed unless asked (AGENTS.md §4). |
| Old serialized method requests | Restore as `null` (obsolete cache) | Best-effort restore with the HTTP method as name | The old payload never contained the method name. A restored request would be wrong, which is today's bug. |

## Pattern Alignment

- **Follows:**
  - error codes as `public const int ERROR_*` in the class's own `3390xx` range (`src/classes/Connectors/Request.php` L33–37);
  - `ConnectorException` with request and response attached (`src/classes/Connectors/Connector/ConnectorException.php`);
  - per-method base classes next to the existing four (`src/classes/Connectors/Connector/Method/`);
  - live-HTTP tests in `#[Group('live-http')]` against `tests/application/request-tests/` endpoints (`tests/AppFrameworkTests/Connectors/RequestTest.php`);
  - the overridable protected seam for test doubles (`docs/agents/project-manifest/testing.md` §Reusable test patterns);
  - constants-and-type classes instead of enums, `array()` syntax, strict types (`docs/agents/project-manifest/constraints.md`);
  - "Breaking-XS" minor releases with a *Breaking Changes* section (`changelog.md` v7.7.0).
- **Departs:** `BasePatchMethod` is namespaced while its four siblings keep legacy names. Justified: new classes must be namespaced, and converting the siblings is a refactor of public base classes that the Editor extends (out of scope).
- **Departs:** a framework subclass of a vendor adapter. This is the first in the module. Justified: there is no upstream fix, and the subclass changes only the requests that are broken today.

## Structural Improvements

| Structure | Observation | Decision | Reason |
|-----------|-------------|----------|--------|
| `src/classes/Connectors/Request.php` `$validHTTPMethods`, `$codesByMethod`, body gate L746 | Three hand-maintained method lists that must agree | Promoted to step 1 | `PATCH` would otherwise be added three times; the registry is the durable shape. |
| `src/classes/Connectors/Request.php` `setHTTPMethod()` docs/message | Stale "POST or GET" | Promoted to step 2 | Same method is rewritten for the registry. |
| `src/classes/Connectors/Request.php` inline `send()` in `getData()` | No seam; transport exceptions leak; untestable without a network | Promoted to step 5 | Needed for wrapping and for tests. |
| `src/classes/Connectors/Connector/Method/Get.php` `getValidResponseCodes()` | Dead, untyped, never called | Promoted to step 4 (removed, replaced by the base hook) | The hook it hinted at is built in this plan. |
| `src/classes/Connectors/Request/RequestSerializer.php` method-request restore | HTTP method restored as API method name; method name, `methodVar` and body lost | Promoted to step 6 | The serializer changes anyway for body and `PATCH`; a restore bug in the same function cannot be left. |
| `src/classes/Connectors/Request.php` `getHashData()` | Body not part of the cache hash | Promoted to step 6 | Body-only requests would share cache entries once bodies are common. |
| `src/classes/Connectors/Request.php` `configureAdapter()` | `ssl_verify_peer`/`ssl_verify_host` are always false | Rejected | Pre-existing security debt. Turning verification on changes every consumer's TLS behaviour (internal CAs, self-signed stages) and needs its own decision and configuration knob. Recorded under Deferred Items. |
| `src/classes/Connectors/Request/Method.php` `getData()` hard-coded `de_DE` locale parameters | Unrelated to write requests | Rejected | Outside the write-request blast radius; changing it alters every method request's URL and cache hash. |
| Legacy method bases `Delete.php`/`Get.php`/`Post.php`/`Put.php` | Legacy naming | Rejected | Public classes the Editor extends; renaming is a refactor not requested (AGENTS.md §4). |
| RFC 9457 problem-detail parsing (Editor's `ComaErrorResponse`, `PigeonErrorResponse`) | Two parsers in the consumer with different shapes | Rejected for this plan | Not a gap that keeps writes outside the framework. A framework parser without migrating both Editor consumers would add a third. Recorded under Deferred Items. |

## Detailed Steps

1. **Method registry.** Create `src/classes/Connectors/Request/HTTPMethod.php` (namespace `Connectors\Request`) with the constants, `getAll()`, `isSupported()`, `allowsBody()` and `getDefaultSuccessCodes()` of Approach §1. Run `composer dump-autoload`.

2. **Request uses the registry** (`src/classes/Connectors/Request.php`).
   - Remove `$validHTTPMethods`. `setHTTPMethod()` validates with `HTTPMethod::isSupported()`. Rewrite its docblock and the developer message to list `HTTPMethod::getAll()`.
   - Add `makePUT()`, `makePATCH()`, `makeDELETE()`, `isPATCH()` and `isDELETE()`.
   - In `createRequest()`, replace `$this->isPUT() || $this->isPOST()` with `HTTPMethod::allowsBody($this->HTTPMethod)`, and make the log lines method-neutral.
   - Make `$codesByMethod` default `array()` (override seam). `getCodesByMethod()` falls back to `HTTPMethod::getDefaultSuccessCodes()`. Update its `@see` tags.

3. **Curl adapter.** Create `src/classes/Connectors/Request/Adapter/CurlAdapter.php` (namespace `Connectors\Request\Adapter`) per Approach §2. Document the two upstream defects, PEAR v2.8.0 and the conditions in the class docblock. Point `Connectors_Request::ADAPTER_CLASSES[self::ADAPTER_CURL]` to it. Run `composer dump-autoload`.

4. **Expected response codes.**
   - In `Connectors_Request`: add `setExpectedResponseCodes(int ...$codes)` and `getExpectedResponseCodes()`. `isValidResponseCode()`, `createResponseException()` (its "valid response codes" text) and `simulateResponse()` use the effective list.
   - In `BaseConnectorMethod`: add `protected function getExpectedResponseCodes(): array` returning `array()`. Apply it in `createRequest()` and `createMethodRequest()` when non-empty.
   - Delete `getValidResponseCodes()` from `src/classes/Connectors/Connector/Method/Get.php`.

5. **Simulation and transport** (`src/classes/Connectors/Request.php`).
   - Add `setLiveInSimulation()` and `isLiveRequest()`. `getData()` branches on `isLiveRequest()`, and `createRequest()` logs the flag.
   - Add the protected seam `executeHTTPRequest()` and the private `sendLive()` wrapper with `ERROR_TRANSPORT_FAILURE = 339009` (Approach §5). Record the time taken on failure as well (`handleRequestCompleted()` before rethrowing).
   - Update the `@throws` tags of `getData()`: `ConnectorException` for transport failures; `HTTP_Request2_LogicException` remains.

6. **Serialization and cache hash.**
   - `Connectors_Request`: add `getConfiguredBody()`. `getHashData()` includes the configured body and `getExpectedResponseCodes()`.
   - `src/classes/Connectors/Request/Method.php`: add `getMethodVar()`.
   - `src/classes/Connectors/Request/RequestSerializer.php`: add the four keys, serialize them, and restore them (Approach §6). Return `null` for a method-request payload without `KEY_METHOD_NAME`, logging why, as the missing-type branch does.

7. **`PATCH` base class.** Create `src/classes/Connectors/Connector/Method/BasePatchMethod.php` (Approach §7). Run `composer dump-autoload`.

8. **Test infrastructure.**
   - New endpoint `tests/application/request-tests/endpoint-echo.php`, modelled on `endpoint-json-200.php`. It answers JSON `{method, contentType, contentLength, transferEncoding, body}` from `$_SERVER` and `php://input`, with the HTTP status from an optional `status` query parameter (default 200; `204` sends no body).
   - New test double `tests/application/assets/classes/TestDriver/Connectors/RecordingRequest.php`: a `Connectors_Request_URL` subclass overriding `executeHTTPRequest()`. It records the `HTTP_Request2` it receives and returns a canned `HTTP_Request2_Response`, or throws a configured exception.
   - Run `composer dump-autoload`.

9. **Tests.** Implement the Test Plan below. Live tests use `setTimeout(10)`, so a regression of the hang fails instead of blocking the run.

10. **Documentation and changelog.** See Documentation Updates. Run `composer build-docs` locally to check the module context, and do not commit `.context/` (AGENTS.md §4).

11. **Verification.**
    - `composer test-filter -- Connectors`;
    - `composer test-group -- live-http` (needs the test web server at `APP_URL`);
    - `composer analyze` (no new PHPStan errors against `phpstan-result.txt`).

## Dependencies

- Step 1 → 2 → 4 → 5 → 6 (all edit `Request.php` in sequence; 4 relies on the registry defaults).
- Step 3 → 9 (live transport tests).
- Step 6 relies on step 4 (expected codes are serialized and hashed).
- Step 7 → 9.
- Step 8 → 9.
- Steps 10 and 11 come last.
- No dependency on any other repository. The Editor consumes the released version later (Out of Scope).

## Required Components

- **New:**
  - `src/classes/Connectors/Request/HTTPMethod.php`
  - `src/classes/Connectors/Request/Adapter/CurlAdapter.php`
  - `src/classes/Connectors/Connector/Method/BasePatchMethod.php`
  - `tests/application/request-tests/endpoint-echo.php`
  - `tests/application/assets/classes/TestDriver/Connectors/RecordingRequest.php`
  - `tests/AppFrameworkTests/Connectors/HTTPMethodTest.php`
  - `tests/AppFrameworkTests/Connectors/RequestSendingTest.php`
  - `tests/AppFrameworkTests/Connectors/RequestSerializerTest.php`
  - `tests/AppFrameworkTests/Connectors/RequestMethodsLiveHTTPTest.php`
  - `tests/AppFrameworkTests/Connectors/ConnectorMethodTest.php`
- **Modified:**
  - `src/classes/Connectors/Request.php`
  - `src/classes/Connectors/Request/Method.php`
  - `src/classes/Connectors/Request/RequestSerializer.php`
  - `src/classes/Connectors/Connector/BaseConnectorMethod.php`
  - `src/classes/Connectors/Connector/Method/Get.php`
  - `tests/AppFrameworkTests/Connectors/RequestTest.php`
  - `src/classes/Connectors/README.md`, `src/classes/Connectors/module-context.yaml`
  - `docs/agents/project-manifest/testing.md`, `docs/agents/project-manifest/constraints.md`
  - `changelog.md`

## Assumptions

- New error code `339009` continues `Connectors_Request`'s own range (`339001`–`339008`, with gaps). Codes `339003`, `339004` and `339007` are not reused, in case they were retired.
- The release is the next minor after the latest tag `7.8.0` (expected `7.9.0`). `changelog.md` currently tops out at `v7.7.0`; the missing `v7.8.0` entry is not part of this plan.
- The libcurl behind PHP in each environment honours `CURLOPT_UPLOAD` with `CURLOPT_CUSTOMREQUEST` as libcurl 8.23.0 did in the experiment. This has been libcurl's documented behaviour for many years.
- The test web server at `APP_URL` passes `PATCH` and `DELETE` bodies to PHP. PHP's built-in server and Apache do.

## Constraints

- No PHP enums, no `readonly`, `array()` syntax, `declare(strict_types=1)`, namespaced new classes, `composer dump-autoload` after adding classes (`docs/agents/project-manifest/constraints.md`, AGENTS.md §4).
- `.context/` is never committed from a feature branch.
- Live-HTTP tests keep `#[Group('live-http')]` and stay excluded from the default run.
- PHPStan via `composer analyze` only.

## Out of Scope

- **HCP Editor changes:**
  - upgrading its pinned framework `7.5.3` to this release, which crosses the v7.6.0–v7.8.0 changes;
  - implementing CoMa's `PATCH` rename;
  - moving `BaseComaWriteMethod` and its methods onto the framework flow;
  - dropping the transport translation in `BaseComaGetMethod`;
  - revisiting HubSpot's forced `useSockets()`.

  These belong to the Editor companion plan, `hcp-editor/docs/agents/plans/2026-11-19-coma-comtype-rename-move/`.
- Converting the legacy Connectors classes to namespaces.
- TLS verification in `configureAdapter()` (Deferred Items).
- A framework RFC 9457 problem-detail parser (Deferred Items).
- `HEAD`/`OPTIONS` support. The registry makes them one-row additions when a consumer needs them.

## Human Actions

| # | Action | When | Why an agent cannot do it |
|---|--------|------|---------------------------|
| 1 | Review the branch and release the framework (tag the next minor) | After the run | Releases are the user's (no agent tags or publishes) |
| 2 | Optionally report the two curl adapter defects (custom-method bodies not sent; `PUT` sending `Transfer-Encoding: chunked` with `Content-Length`) to `pear/HTTP_Request2`, using the research brief's reproduction | After the run | External account and upstream communication |

## Acceptance Criteria

- AC-01: `Connectors\Request\HTTPMethod` defines GET, POST, PUT, PATCH and DELETE with the body and default success-code table of Approach §1. `Connectors_Request` no longer contains its own list of allowed methods or body methods.
- AC-02: `setHTTPMethod('PATCH')` succeeds. An unsupported method throws `ERROR_INVALID_METHOD` (`339008`) with a message listing the supported methods. `makePUT()`, `makePATCH()`, `makeDELETE()`, `isPATCH()` and `isDELETE()` behave like the existing helpers.
- AC-03: A configured body and POST data are passed to the `HTTP_Request2` for POST, PUT, PATCH and DELETE, and never for GET.
- AC-04: Over real HTTP with the default (curl) adapter, a `PATCH`, `PUT` and `DELETE` with a JSON body and a vendor `Content-Type` arrive with the correct method, `Content-Type`, `Content-Length` and body, and without `Transfer-Encoding: chunked`. The same holds with the socket adapter, and POST is unchanged.
- AC-05: `ADAPTER_CLASSES[ADAPTER_CURL]` is `Connectors\Request\Adapter\CurlAdapter`. GET and POST requests through it behave as before (existing live tests pass).
- AC-06: Default success codes per method come from the registry. A subclass overriding `$codesByMethod` for a method still takes precedence.
- AC-07: `setExpectedResponseCodes(201)` makes a 200 response fail with `ERROR_REQUEST_FAILED` (HTTP response attached) and a 201 succeed. Calling it with no codes restores the defaults. A simulated response uses the first expected code.
- AC-08: A method class returning codes from `getExpectedResponseCodes()` gets them applied to requests made by `createRequest()` and `createMethodRequest()`. `Connectors_Connector_Method_Get::getValidResponseCodes()` no longer exists.
- AC-09: With the connector in simulation and live requests off, `getData()` does not call the transport and returns a simulated response. With `setLiveInSimulation()`, it calls the transport. With live requests on, behaviour is unchanged.
- AC-10: An `HTTP_Request2_ConnectionException` or `HTTP_Request2_MessageException` raised while sending surfaces as `ConnectorException` with code `339009`, the PEAR exception as previous and the request attached. An `HTTP_Request2_LogicException` propagates unchanged.
- AC-11: Over real HTTP, a request whose body is never sent (the pre-fix hang) can no longer occur. A request to a closed local port surfaces as `ConnectorException` `339009`, not a PEAR exception.
- AC-12: `RequestSerializer` round-trips URL and method requests with body, HTTP method (including PATCH), expected codes, API method name and `methodVar`. A legacy method-request payload without a method name restores as `null`.
- AC-13: Two requests that differ only in their body have different `getCacheHash()` values.
- AC-14: `Connectors\Connector\Method\BasePatchMethod` subclasses create requests whose HTTP method is `PATCH`.
- AC-15: `README.md`, `module-context.yaml`, `testing.md`, `constraints.md` and `changelog.md` describe `PATCH`, body methods, expected codes, live-in-simulation, transport failures, the curl adapter, the serializer change, and the breaking changes. `composer test-filter -- Connectors`, `composer test-group -- live-http` and `composer analyze` pass.

## Testing Strategy

- **Unit tests** with `StubConnector` and the `RecordingRequest` seam double cover every decision in `Connectors_Request` without a network: method validation, body gate, codes, simulation branch and exception wrapping.
- **Live-HTTP tests** against a new echo endpoint run every body-carrying method through both adapters. Only real HTTP can show the transport defect, and the original bug was invisible to anything mocked.
- **Serializer tests** round-trip both request types and the legacy payload.
- **Static analysis** with `composer analyze`.
- Group new test methods under `// region: AC-NN` comments (`testing.md` convention).

## Test Plan

- `tests/AppFrameworkTests/Connectors/HTTPMethodTest.php` (new) — supported set, `allowsBody()` per method, default success codes per method, unknown method rejected — AC-01
- `tests/AppFrameworkTests/Connectors/RequestTest.php` (extend):
  - asserts: `setHTTPMethod('PATCH')`; unsupported method → `339008` with the supported list in the details; new `make*`/`is*` helpers; `$codesByMethod` override precedence via an anonymous subclass; `ADAPTER_CLASSES` curl mapping; cache hash differs by body
  - covers: AC-02, AC-05, AC-06, AC-13
- `tests/AppFrameworkTests/Connectors/RequestSendingTest.php` (new, uses `RecordingRequest`):
  - asserts: body and POST data reach the recorded `HTTP_Request2` for POST, PUT, PATCH and DELETE but not GET; expected codes 201 → 200 fails with `339002` and response attached, 201 passes, reset restores defaults; simulated status equals the first expected code; simulation without live → transport not called; `setLiveInSimulation()` → called; live connector → called; `ConnectionException` and `MessageException` → `339009` with previous and request; `LogicException` propagates
  - covers: AC-03, AC-07, AC-09, AC-10
- `tests/AppFrameworkTests/Connectors/ConnectorMethodTest.php` (new):
  - asserts: a `BasePatchMethod` subclass (anonymous, `StubConnector`) creates `PATCH` requests; a method class overriding `getExpectedResponseCodes()` gets them on `createRequest()` and `createMethodRequest()`; `method_exists(Connectors_Connector_Method_Get::class, 'getValidResponseCodes')` is false
  - covers: AC-08, AC-14
- `tests/AppFrameworkTests/Connectors/RequestSerializerTest.php` (new):
  - asserts: URL and method requests round-trip body, `PATCH`, expected codes, method name and `methodVar`; legacy method payload without a method name → `null`; legacy URL payload restores with defaults
  - covers: AC-12
- `tests/AppFrameworkTests/Connectors/RequestMethodsLiveHTTPTest.php` (new, class-level `#[Group('live-http')]`):
  - asserts: data provider over adapters {curl, socket} × methods {POST, PUT, PATCH, DELETE} sending a JSON body with `Content-Type: application/vnd.test.patch-v1+json` to `endpoint-echo.php` → echoed method, content type, `Content-Length` = body length, empty `Transfer-Encoding`, identical body; `PATCH` with `status=204` succeeds; `setExpectedResponseCodes(201)` with `status=200` fails with `339002` carrying the response; a request to `http://127.0.0.1:1/` → `339009`. Each request has `setTimeout(10)`.
  - covers: AC-04, AC-11
- `tests/AppFrameworkTests/Connectors/RequestTest.php` existing `test_adapterSockets`/`test_adapterCURL` (unchanged, must pass) — AC-05
- `composer analyze` and the full `composer test-filter -- Connectors` run — AC-15
- Documentation reviewed against AC-15 in code review — AC-15

## Documentation Updates

- `src/classes/Connectors/README.md`:
  - "GET, POST, PUT, PATCH and DELETE"; `BasePatchMethod` in the method base list;
  - new sections: *Request bodies* (which methods send them, `setBodyJSON()`/`setContentType()` for vendor types), *Expected response codes* (defaults, `setExpectedResponseCodes()`, the method hook), *Simulation mode* (simulated responses, `setLiveInSimulation()`, skipping in the method via `isLiveRequestsEnabled()`), *Transport failures* (`ERROR_TRANSPORT_FAILURE`, what is and is not wrapped), *HTTP adapters* (curl default with the framework's `CurlAdapter`, sockets);
  - a *Writes with a vendor media type* example: a `BasePatchMethod` subclass sending JSON with a vendor `Content-Type` and requiring `200`.
- `src/classes/Connectors/module-context.yaml`: description and keyword "GET/POST/PUT/PATCH/DELETE".
- `docs/agents/project-manifest/testing.md` §Live-HTTP Tests: a table row for `RequestMethodsLiveHTTPTest` (all methods; proves body delivery per adapter and transport-failure wrapping), and a mention of `request-tests/endpoint-echo.php`. §Reusable test patterns: `RecordingRequest` as an instance of the seam pattern.
- `docs/agents/project-manifest/constraints.md`: a short *Connectors* section. Connector code sends through `Connectors_Request` and never builds `HTTP_Request2` directly. Per-method facts live in `Connectors\Request\HTTPMethod`. Transport failures arrive as `ConnectorException` `339009`.
- `changelog.md`: new top entry for the next minor version, marked "Breaking-XS":
  - Connectors: `PATCH` support and `BasePatchMethod`;
  - Connectors: request bodies for PATCH and DELETE;
  - Connectors: curl adapter delivers bodies for custom methods and no longer sends chunked `PUT`s with `Content-Length`;
  - Connectors: expected response codes per request and per method class;
  - Connectors: live requests in simulation;
  - Connectors: transport failures as `ConnectorException`;
  - Connectors: request serialization keeps the body and method name; the cache hash includes the body.

  *Breaking Changes*:
  - transport exceptions are now `ConnectorException` `339009`;
  - a configured `DELETE` body is now sent;
  - `Connectors_Connector_Method_Get::getValidResponseCodes()` is removed;
  - `ADAPTER_CLASSES['curl']` is the framework subclass;
  - cached method requests from older versions are discarded once.

## Deferred Items

| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---------------|--------|-----------------|-------|
| 1 | TLS verification in `Connectors_Request::configureAdapter()` (`ssl_verify_peer`/`ssl_verify_host` always false) | Research, 2026-11-23 | Changes every consumer's TLS behaviour; needs a configuration knob and environment CA decisions | Security debt; candidate for its own plan with a security-audit stage. |
| 2 | Framework RFC 9457 problem-detail type, replacing the Editor's `ComaErrorResponse` and `PigeonErrorResponse` | Research, 2026-11-23 | Only valuable together with migrating both Editor parsers | Reconsider in the Editor plan that moves CoMa writes onto the framework. |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **A consumer catches `HTTP_Request2_ConnectionException` from `getData()`** | Listed under Breaking Changes. In the workspace, only the Editor's `BaseComaGetMethod` does, and the Editor consumes this release only through its own upgrade plan. `MailsCheckURLs` builds its own `HTTP_Request2` and is unaffected. |
| **The curl subclass changes requests that worked before** | It acts only on non-GET/HEAD/POST requests with a body. `PUT` bodies are the only previously working case it touches, and the live tests compare `PUT` on both adapters. |
| **A different libcurl behaves differently** | The live tests run in each developer and CI environment that has the test web server. A failure there blocks the release. |
| **The hang regresses and blocks the test run** | Every live test sets a 10-second timeout, so the regression fails fast as `339009`. |
| **Old cache files fail to restore** | Method-request payloads without a method name restore as `null`, which the cache already treats as "fetch live". URL payloads restore with defaults. |
| **External framework consumers override `$codesByMethod`** | The property remains an override seam and wins over the registry (AC-06). |
| **The Editor upgrade from 7.5.3 is large** | Not part of this plan. The Editor plan sequences it, with a local symlink to this framework checkout while both are in development. |

## Recommended Workflow
- **Workflow:** ledger
- **Rationale:** A cross-cutting change to a shared module's public request contract, with breaking changes, a vendor-adapter subclass, simulation and exception semantics, serializer and cache changes, and live-HTTP verification, warrants separate QA, code review and release-engineering stages.
