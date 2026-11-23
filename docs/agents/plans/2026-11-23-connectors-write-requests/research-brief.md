# Research Brief

Context: CoMa's communication type rename becomes `PATCH /communicationTypes/{ct}` with a vendor `Content-Type` (`hcp-coma/docs/agents/plans/2026-11-19-bundle-transfer-and-rename/plan.md`, recommended by Olaf Böcker, CoMa developer, 2026-11-23). The HCP Editor is the caller. The Editor's CoMa write methods build `HTTP_Request2` requests themselves instead of using this framework's `Connectors_Request` flow. The user asked for the framework to support `PATCH` properly, and for every gap that keeps the Editor's writes outside the framework to be closed in the same plan.

All paths are relative to the `application-framework` repository root unless prefixed with another repository folder. `{conn}` = `src/classes/Connectors`.

## Scope Sketch

- Request model — `{conn}/Request.php` — modification (methods, body, response codes, simulation, transport failures)
- HTTP transport — `{conn}/Request.php` adapter selection; new curl adapter class under `{conn}/Request/` — new code + modification
- Method base classes — `{conn}/Connector/BaseConnectorMethod.php`, `{conn}/Connector/Method/*.php` — modification + new `Patch` base
- Request persistence — `{conn}/Request/RequestSerializer.php`, `{conn}/Request/Cache.php` (via `getHashData()`), `{conn}/Response/ResponseSerializer.php` — modification
- Tests — `tests/AppFrameworkTests/Connectors/`, `tests/application/request-tests/`, `tests/application/assets/classes/TestDriver/Connectors/` — new + modification
- Documentation — `{conn}/README.md`, `{conn}/module-context.yaml`, `docs/agents/project-manifest/testing.md`, `changelog.md` — modification
- Consumer evidence (read only) — `hcp-editor/assets/classes/Coma/Connector/`, `hcp-editor/assets/classes/Hubspot/APIConnector/Method/PutTemplateMethod.php`, `hcp-editor/assets/classes/Pigeon/Connector/` — integration (not changed by this plan)

## Area: Why the Editor's CoMa writes bypass the framework

### Verified References
- `hcp-editor/assets/classes/Coma/Connector/BaseComaWriteMethod.php` L47 `abstract class BaseComaWriteMethod extends BaseConnectorMethod`; L57–66 `createRequestHelper()` does `new HTTP_Request2($url, $this->getHTTPMethod())`, `setAdapter('curl')`, `follow_redirects`, no SSL verification. Its class doc (L17–21) points to `PostTemplateMethod` for "why that flow is unsuitable for a CoMa write". `sendWriteRequest()` (L80–87) catches every `HTTP_Request2_Exception` and converts it.
- `hcp-editor/assets/classes/Coma/Connector/Method/PostTemplateMethod.php` class doc: the framework flow "automatically short-circuits into a simulated response while in simulation mode, whereas this method deliberately logs and sends regardless". Expects exactly `201` (`SUCCESS_STATUS_CODE`).
- `hcp-editor/assets/classes/Coma/Connector/Method/PostConfigurationMethod.php`: same base; under connector simulation it logs and returns without building a request; expects `201`.
- `hcp-editor/assets/classes/Coma/Connector/Method/DeleteConfigurationMethod.php` L79–81 `HTTP_Request2::METHOD_DELETE`; same skip-in-simulation policy; expects `204`.
- `hcp-editor/assets/classes/Coma/Connector/BaseComaGetMethod.php` (extends `Connectors_Connector_Method_Get`): class doc states that `Connectors_Request::getData()` "calls `HTTP_Request2::send()` unwrapped, so without this translation the raw PEAR exception would bypass the `catch (ConnectorException)`". It translates transport failures into its own `ERROR_TRANSPORT_FAILURE`, but deliberately leaves `HTTP_Request2_LogicException` and `HTTP_Request2_NotImplementedException` untranslated (programming errors). L104–113 `isReadPossible()`: "Simulated requests do not skip the call - `Connectors_Request::getData()` fabricates a simulated response instead - so every CoMa GET method must gate itself".
- `hcp-editor/assets/classes/Coma/Connector/ComaErrorResponse.php` and `hcp-editor/assets/classes/Pigeon/Connector/PigeonErrorResponse.php`: two independent RFC 9457 problem-detail parsers (`type`/`title`/`detail`), one standalone, one extending `Connectors_Response`, both with service-specific type slugs.
- `hcp-editor/assets/classes/Hubspot/APIConnector/Method/PutTemplateMethod.php` L39–41: the HubSpot `PUT` forces `->useSockets()` without a stated reason; history only shows a move (`262329285`, 2025-09-02).
- `hcp-editor/assets/classes/Maileditor/AjaxMethods/MailsCheckURLs.php`: the only other direct `new HTTP_Request2`, catching `HTTP_Request2_ConnectionException`; not a connector, unaffected by this plan.
- Editor DELETE methods on the framework flow (`Pigeon/.../DeleteTransmissionMethod.php`, `DeleteDictionaryWordMethod.php`, `Hubspot/.../DeleteTemplateMethod.php`) set no body or POST data.
- `hcp-editor/composer.json` L142: `"mistralys/application_framework": "7.5.3"` (exact pin). `hcp-editor/vendor/mistralys/application_framework` is a regular directory, not a symlink.

### Constraints
- The gaps that force the bypass are framework facts: (1) no per-request way to send live during simulation; (2) raw transport exceptions from `getData()`; (3) no `PATCH`; (4) no per-request expected status code (CoMa requires exactly 201/204). Skip-in-simulation (configuration writers) is method policy and already expressible with `ConnectorInterface::isLiveRequestsEnabled()`.
- Moving the Editor's methods onto the framework flow is Editor work and needs an Editor framework upgrade from 7.5.3.

## Area: Request model (`Connectors_Request`)

### Verified References
- `{conn}/Request.php` (1,030 lines), legacy class `abstract class Connectors_Request` (L29).
- L33–37 error codes: `339001` `ERROR_INVALID_AUTH_SCHEME`, `339002` `ERROR_REQUEST_FAILED`, `339005` `ERROR_NO_REQUEST_SENT_YET`, `339006` `ERROR_INVALID_DATA_VALUE`, `339008` `ERROR_INVALID_METHOD`.
- L43 `protected string $HTTPMethod = HTTP_Request2::METHOD_POST;` L79–84 `private static array $validHTTPMethods` = POST, GET, DELETE, PUT.
- L317–335 `setHTTPMethod()` throws `ERROR_INVALID_METHOD` for anything else; docblock and developer message still say "either POST or GET".
- L341–373: `makeGET()`, `makePOST()`, `isPOST()`, `isPUT()`, `isGET()`, `isHTTPMethod()`; no PATCH or DELETE helpers.
- L404–409 `setBody()`, L418–429 `setBodyJSON()`, L435 `setContentType()`, L492 `setHeader()`.
- L514–553 `getData()`: cache check → `createRequest()` → `if (!$this->connector->isLiveRequestsEnabled()) simulateResponse() else $request->send()` (L538–543, unwrapped) → `createResponse()` → cache store.
- L563–578 `simulateResponse()` fabricates `HTTP/1.0 {first valid code} Simulation OK`.
- L627–665 `createResponse()` throws `ConnectorException` `ERROR_REQUEST_FAILED` with `setResponse($response)` and `setRequest($this)` when the status is not valid for the method.
- L667–676 `ADAPTER_CURL`/`ADAPTER_SOCKETS`, `public const array ADAPTER_CLASSES` mapping to `HTTP_Request2_Adapter_Curl` / `_Socket`; L678 curl is the default.
- L714–797 `createRequest()`: L746 `if ($this->isPUT() || $this->isPOST())` gates both POST parameters and the body. Any other method drops the body silently.
- L799–808 `configureAdapter()`: adapter class, `follow_redirects`, no SSL verification.
- L814–824 `protected array $codesByMethod`: GET 200; DELETE 200, 204; PUT 200, 201, 202, 204; POST 200, 201, 202. L850–857 `getCodesByMethod()` falls back to `array(200)`.
- L971–978 `getBody()` returns the body of the sent `HTTP_Request2`, '' before sending. No accessor for the configured body.
- L1008–1018 `getHashData()`: payload, HTTP method, headers, URL. The body is not part of the cache hash.
- L175–180 request IDs use `Application::getUser()`.

### Established Patterns
- Error codes as `public const int ERROR_*` in the class's own range (`3390xx`) — `{conn}/Request.php` L33–37; `docs/agents/project-manifest/constraints.md` §Exception Handling.
- `ConnectorException` carries request and HTTP response (`setRequest()`, `setResponse()`) — `{conn}/Connector/ConnectorException.php` L46–95.
- Overridable protected factory/seam for test doubles — `docs/agents/project-manifest/testing.md` §Reusable test patterns.

### Structural Observations
- Three hand-maintained method lists must agree: `$validHTTPMethods` (L79), `$codesByMethod` (L814) and the `isPUT() || isPOST()` body gate (L746). Adding `PATCH` means touching all three, and missing one fails silently (body dropped, 200-only fallback).
- `setHTTPMethod()` documentation and message are stale ("POST or GET").
- `send()` is called inline in `getData()`; there is no seam to test transport failures without a network.
- The configured body is neither serialized nor hashed.

### Constraints
- `codesByMethod` is `protected`; an application subclass may override it. No Editor class does (`grep` for `codesByMethod`/`isValidResponseCode` in `hcp-editor/assets`: none).

## Area: HTTP transport (PEAR HTTP_Request2)

### Verified References
- `composer.json` L94 `"pear/http_request2": "^v2.5.1"`; `composer.lock` resolves `v2.8.0`, the latest release (2026-09-04, Packagist). Upstream trunk `HTTP/Request2/Adapter/Curl.php` has the same method switch.
- `vendor/pear/http_request2/HTTP/Request2.php` L52–59: method constants OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT (no PATCH); L305 `setMethod()` accepts any valid token.
- `vendor/pear/http_request2/HTTP/Request2/Adapter/Curl.php` L304–319: GET → `CURLOPT_HTTPGET`, POST → `CURLOPT_POST`, HEAD → `CURLOPT_NOBODY`, PUT → `CURLOPT_UPLOAD`, everything else → `CURLOPT_CUSTOMREQUEST`. L399–402 `calculateRequestLength()` then `workaroundPhpBug47204()`, which for non-POST methods installs `CURLOPT_READFUNCTION` and keeps the `Content-Length` header. L241 `protected function createCurlHandle()`.
- `Adapter.php` L47 `$bodyDisallowed = ['TRACE']`; protected `$requestBody` and `$contentLength` are available to subclasses.

### Experimental results (2026-11-23, PHP 8.5.12, libcurl 8.23.0, local `php -S` echo server, scripts in the session scratchpad)
- Stock curl adapter: POST and PUT deliver the body; **PATCH with a body hangs** — `Content-Length` is announced, the body is never sent, the server logs "Invalid request (Unexpected EOF)", the client fails with `HTTP_Request2_MessageException: Curl error: Operation timed out`.
- Stock curl adapter, PUT with a body: the request carries **both** `Transfer-Encoding: chunked` and `Content-Length` (invalid per RFC 9112 §6.3). PUT without a body also goes out chunked.
- Socket adapter (`HTTP_Request2_Adapter_Socket`): POST, PATCH and DELETE with a body all arrive intact. Without an explicit `Content-Type` it sends `application/x-www-form-urlencoded`.
- Curl adapter subclass overriding `createCurlHandle()` that, for every method except GET, HEAD and POST with a non-empty body, sets `CURLOPT_UPLOAD` + `CURLOPT_CUSTOMREQUEST` (method) + `CURLOPT_INFILESIZE` (content length): PATCH, DELETE and PUT with a body arrive with method, `Content-Type`, `Content-Length` and body intact and **no** chunked header; PATCH/DELETE without a body and POST are unchanged.

### Constraints
- No upstream fix is available; the fix must live in the framework.

## Area: Method base classes

### Verified References
- `{conn}/Connector/BaseConnectorMethod.php` L51 `abstract public function getHTTPMethod(): string;` L60–70 `createRequest(string $endpoint = '')` builds `Connectors_Request_URL` with `getURL() . '/' . $endpoint` and `setHTTPMethod()`; L72–83 `createMethodRequest()`; L93–99 `executeRequest()` stores active request/response; L117–125 log identifier.
- `{conn}/Connector/Method/Delete.php`, `Get.php`, `Post.php`, `Put.php`: legacy classes `Connectors_Connector_Method_{Name}` returning the matching `HTTP_Request2::METHOD_*`. `Get.php` L26 `public function getValidResponseCodes()` returns `array(200)`, untyped, with no caller anywhere (framework `src`/`tests`, Editor `assets`).
- `{conn}/Connector/BaseConnector.php` L48–55 `isLiveRequestsEnabled()`: true outside simulation, else the `live-requests` request parameter.

### Structural Observations
- `Get::getValidResponseCodes()` is a dead, untyped hook — the intent of per-method expected codes exists but is not wired.
- No `Patch` base class.

## Area: Request persistence (serializer and cache)

### Verified References
- `{conn}/Request/RequestSerializer.php` L48–60 `serialize()`: type, connector ID, URL, ID, POST data, GET data, headers, timeout, HTTP method. No body, no API method name, no method variable.
- Same file, `unserialize()`: for `REQUEST_TYPE_METHOD` it calls `new Connectors_Request_Method($connector, url, $data->getString(KEY_HTTP_METHOD), ...)` — the **HTTP method is passed as the API method name**; the real method name and `methodVar` are lost. It ends with `setHTTPMethod(stored method)`, which throws for any method outside the allowed list.
- `{conn}/Request/Method.php`: `__construct(..., string $method, ...)`, `getMethod()`, `setMethodVar()`, `_getHashData()` returns method and methodVar.
- `{conn}/Response/ResponseSerializer.php` L41 embeds `$response->getRequest()->serialize()`; L56 restores via `Connectors_Request::unserialize()`. `{conn}/Request/Cache.php` L124 saves `$response->serialize()`.

### Structural Observations
- A cached `Connectors_Request_Method` is restored with the wrong method name; a cached request with a body is restored without it; two body-only requests to the same URL share one cache hash.

## Area: Tests and documentation

### Verified References
- `tests/AppFrameworkTests/Connectors/RequestTest.php`: `BASE_ENDPOINT_URL = APP_URL.'/request-tests/endpoint-json-200.php'`; `#[Group('live-http')]` on `test_adapterSockets`/`test_adapterCURL`; unit tests for response codes, serialization (`test_serialization` covers responses only), JSON handling.
- `tests/application/request-tests/endpoint-json-200.php`: echoes the decoded JSON body as `data`, always 200.
- `tests/AppFrameworkTests/Connectors/GeneralConnectorTest.php`; `tests/AppFrameworkTestClasses/Traits/ConnectorTestTrait.php`; test connectors in `tests/application/assets/classes/TestDriver/Connectors/`; `Connectors\Connector\StubConnector` (`{conn}/Connector/StubConnector.php`).
- `docs/agents/project-manifest/testing.md` L400–431 §Live-HTTP Tests: table of live-http test methods to keep current; `composer test-group -- live-http`.
- `{conn}/README.md` (148 lines): "GET, POST, PUT and DELETE", one base class per method listed; `{conn}/module-context.yaml` description and keyword list the same four methods; CTX output in `.context/modules/connectors/` (generated; committed on `main` only).
- `changelog.md` top entry `v7.7.0`; latest git tag `7.8.0` (2026-11-20). `VERSION` is generated (`61ef9a51 Removed; Now automatically generated.`).
- `docs/agents/project-manifest/constraints.md`: no PHP enums (L12), no `readonly` (L13), `array()` syntax (L65–72), namespaces for new classes (L31–39), classmap autoload → `composer dump-autoload` (L48–50), error-code format (L161–170).

### Constraints
- Live-HTTP tests need a web server at `APP_URL` and stay excluded from the default run.

## Strategic Context

- Repository vision (mid-term): improve test coverage, refactor towards namespaces, stricter typing. This plan adds namespaced classes and typed seams in a legacy module without converting the legacy classes.
- Prior projects (v7.6.0, v7.7.0) shipped "Breaking-XS" minor versions with an explicit Breaking Changes section in `changelog.md`.
- `ledger_search_insights` (connectors, HTTP_Request2, curl, PATCH, simulation; live-http, error codes, release): no connector-specific insight. The global "route writes through one choke-point / one implementation for preview" insights support keeping simulation inside `getData()` instead of per-method copies. No stored insight is overtaken by this plan.
