# Plan

## Plan Audit Cycles
- Audits: 3 (Sonnet 5.5 ×3) — Plan Auditor v1.12.0
- Architectural Reviews: 1 (Sonnet 5.5 ×1) — Plan Architect Reviewer v2.3.4

## Prior Project Context
- **Parent project.** `docs/agents/plans/2026-10-16-media-document-apis/` shipped the media document API in 8 work packages: JSON-body parameters, `Base64FileParameter`, `isContentValid()`, `MediaDocumentWriter`, `PaginatedAPI*`, three media pieces, five methods. It had zero Critical/High/Medium security findings. Its `synthesis.md` is the input of this rework.
- **Parent work is uncommitted.** The parent's implementation is still in the working tree, uncommitted. `changelog.md` heads with `v7.7.0 - **WIP; UNRELEASED**`. This rework builds on that tree and adds to the same unreleased v7.7.0 entry, with no version bump.
- **Already resolved.** The synthesis asked for a knowledge-curator pass on the two stale `ResponseKeySorter` insights, and that pass is marked DONE.
- **Insights applied:**
  - `dfd00d1a-ab39-4907-9ea3-51d7ac76809c`: `hasRawRequestValue()` / `getRawRequestValueUnprocessed()` are the seam for new parameter types. Step 1 consolidates their internals but keeps both names and their semantics.
  - `0ff1dbae-96be-4350-83c5-d43dd6bc3bad`: reject oversized input before the expensive step. Steps 3–4 apply the same rule one layer earlier: the body is rejected before it is read into memory and JSON-decoded.
  - `bddd007a-ef0d-493d-9154-b86c5edcd594` and `6da672ec-bab6-453a-becc-a0337ee41590`: fix a security issue once, at the shared source. Step 6 hardens the single serving seam (`DocumentTrait`) that media documents and uploads share.
  - `ea2df729-ec33-4517-b1d3-7c028edb974b`: dynamic numbers in error messages go in as `sprintf` arguments.
  - `af333a25-0c2e-4463-93d4-7afa13031704`: expected failures are converted to client errors; anything unexpected stays with the boundary.
- **Strategic vision.** Mid term: test coverage, stricter typing, namespaces. This rework is mostly coverage and hardening, and all new classes are namespaced.

## Summary
This rework addresses the actionable items of `docs/agents/plans/2026-10-16-media-document-apis/synthesis.md`. It does four things:
- Closes two security follow-ups:
  - an upstream request-body size ceiling ahead of the JSON parse, including a clear error when PHP itself discarded an oversized body
  - non-rendering delivery headers for SVG media
- Fixes the pre-existing `validateByCallback()` bug that drops its `...$args`, and consolidates the duplicated raw-value accessors in `BaseAPIParameter`.
- Closes the two flagged coverage gaps: committed runtime tests for the `TestMediaMetadataMethod`/`TestMediaFileUploadMethod` fixtures, and a live-HTTP test of the unparseable-JSON-body case through the real `process()` path.
- Corrects two documentation inaccuracies:
  - the Media API error-code table conflates top-level codes with nested parameter-validation codes
  - two hand-maintained module tables have drifted from the generated `modules-overview.md`

**One synthesis claim is corrected by research.** The synthesis says the inline SVG XSS risk "is not reachable today", because the API returns content only as base64. In fact the existing `media.php` delivery path (`Application_Media_Delivery` → `DocumentTrait::serveFromRequest()` → `FileHelper::sendFileAuto()`) already serves SVG media inline as `image/svg+xml` from the application origin. The item is therefore promoted from "deferred" to a plan step.

## Architectural Context
- **Parameters.**
  - `src/classes/Application/API/Parameters/BaseAPIParameter.php` holds per-parameter state. Raw-value access goes through an injected `ParamValueSourceInterface` (`ValueSource/RequestValueSource.php`, `ValueSource/JSONBodyValueSource.php`) via `getRawRequestValue()` (L115), `hasRawRequestValue()` (L143) and `getRawRequestValueUnprocessed()` (L168).
  - Callback validation is `validateByCallback()` (L222) → `Validation/Type/CallbackValidation.php`, whose constructor already accepts and forwards `...$args`.
- **Request ingress.**
  - `src/classes/Application/API/BaseMethods/BaseAPIMethod.php::getRequestBody()` (L492–L504) reads `setRequestBody()`'s preset or `php://input` in full, with no size check.
  - Its only caller is `src/classes/Application/API/Traits/JSONRequestTrait.php::getRequestData()` (L66–L93). That method sets a provisional empty collection before reading, which is the re-entry guard for error responses raised during the read. It treats an empty body as `{}`.
  - Error codes for the JSON request path live on `Traits/JSONRequestInterface.php` (`ERROR_FAILED_TO_READ_INPUT = 182801`). HTTP-status helpers live on `src/classes/Application/API/ErrorResponse.php` (`make{Status}()`, L251–L292).
- **Config.** An optional `APP_*` setting has four parts: a `BaseConfigRegistry` constant, a `Bootstrap::registerOptionalSetting()` default (L273), a `SetAppConfigSettingTrait` setter and an `AppConfig` getter. The model is `API_MAX_FILE_CONTENT_SIZE`.
- **Media delivery.**
  - `tests/application/media.php` → `Application_Bootstrap_Screen_Media` (`src/classes/Application/Bootstrap/Screen/Media.php`) → `Application_Media_Delivery::serveFromRequest()` (`src/classes/Application/Media/Delivery.php` L40) → `$document->serveFromRequest()`.
  - Media documents (`Media/Document.php`, `Media/Document/Image.php`) and uploads (`src/classes/Application/Uploads/Upload.php`) all inherit `src/classes/Application/Media/DocumentTrait.php`, whose `serveFromRequest()` (L304) and `sendFile()` (L169) hand off to `FileHelper`.
  - SVG is vector, so `createThumbnail()` returns the original file. `FileHelper_MimeTypes` marks `svg` as browser-displayable, so it is sent inline.
- **Tests.**
  - TestDriver API fixtures live in `tests/application/assets/classes/TestDriver/API/`. Media API tests extend `Mistralys\AppFrameworkTests\TestClasses\MediaTestCase` and drive methods through `processReturn()` (see `tests/AppFrameworkTests/Media/API/MediaDocumentParamsContainerTest.php`).
  - Live-HTTP tests carry `#[Group('live-http')]` and are excluded in `phpunit.xml`.
- **Manifest.** `AGENTS.md` §1 and `docs/agents/project-manifest/context-documentation.md` each hold a hand-maintained module table. `docs/agents/project-manifest/modules-overview.md` is generated by `composer build-docs`, lists every module with its Context Docs path, and already includes `media`.

## Approach / Architecture

### A. Parameter fixes (`BaseAPIParameter`)
- `validateByCallback(callable $callback, ...$args)` constructs `new CallbackValidation($callback, ...$args)`. No current caller passes args (research brief), so behaviour changes only for future callers. Those now receive the documented signature `($value, $result, $param, ...$args)`.
- **Raw-accessor consolidation.** `getRawRequestValueUnprocessed()` becomes the only method that reads from the injected value source. `getRawRequestValue()` becomes:
  - **no source injected:** `return $this->getRequestParam()->get();` (unchanged standalone fallback)
  - **source injected:** `return $this->getRawRequestValueUnprocessed();`

  The docblocks of both state the single remaining difference: the no-source fallback (empty strings dropped versus kept). Method names, visibility and observable behaviour are unchanged, which keeps insight `dfd00d1a` accurate.

### B. Request body ceiling (API module)
- **New config** `BaseConfigRegistry::API_MAX_REQUEST_BODY_SIZE = 'APP_API_MAX_REQUEST_BODY_SIZE'`:
  - default `0` through `Bootstrap::registerOptionalSetting()`
  - setter `setAPIMaxRequestBodySize(int $bytes)`
  - getter `AppConfig::getAPIMaxRequestBodySize() : int`, which returns `self::deriveAPIMaxRequestBodySize((int)boot_constant(API_MAX_REQUEST_BODY_SIZE), self::getAPIMaxFileContentSize())`
  - new pure helper `AppConfig::deriveAPIMaxRequestBodySize(int $configured, int $fileContentLimit) : int`. A configured value `> 0` is returned as is. `0` means "derive from the file limit": `intdiv($fileContentLimit * 4, 3) + self::API_REQUEST_BODY_OVERHEAD`, where the new `public const int API_REQUEST_BODY_OVERHEAD = 1048576` (1 MiB for the surrounding JSON). Boot settings cannot be changed at test time (`boot_constant()` → `Application_Bootstrap::getSetting()`), so the pure helper is what makes the derivation testable.
  - With the default 10 MiB file limit, the ceiling is 15,029,589 bytes. Raising the file limit raises the ceiling automatically, so the two limits cannot drift apart.
- **New** `src/classes/Application/API/Request/RequestBodyReader.php` (namespace `Application\API\Request`), a small I/O class that reads the raw request body under a byte ceiling:
  - `__construct(int $maxBytes, ?int $declaredLength=null, string $streamURI='php://input')`
  - `static fromGlobals(int $maxBytes) : self` reads `$_SERVER['CONTENT_LENGTH']`: a digit string becomes an `int`, anything else becomes `null`.
  - `read() : string`, in this order:
    1. If `declaredLength` is not `null` and `> maxBytes`, throw `ERROR_BODY_TOO_LARGE`. The stream is never opened.
    2. `$raw = file_get_contents($streamURI, false, null, 0, $maxBytes + 1)`, with the PHP warning suppressed so the failure surfaces only through the exception. If that returns `false`, throw `ERROR_BODY_READ_FAILED`.
    3. If `strlen($raw) > maxBytes`, throw `ERROR_BODY_TOO_LARGE`. This catches chunked bodies or an absent or understated `Content-Length`. At most `maxBytes + 1` bytes are ever buffered.
    4. If `$raw === ''` and `declaredLength > 0`, throw `ERROR_BODY_DISCARDED`. PHP discarded the body because it exceeded `post_max_size`.
    5. Return `$raw`, untrimmed. Trimming stays in `BaseAPIMethod`.
  - `getMaxBytes() : int` and `getDeclaredLength() : ?int`, used for the error message.
- **New** `src/classes/Application/API/Request/RequestBodyException.php` extends `Application\API\APIException`:
  - `ERROR_BODY_TOO_LARGE = 59213012`
  - `ERROR_BODY_DISCARDED = 59213013`
  - `ERROR_BODY_READ_FAILED = 59213014`

  These continue `APIException`'s `592130xx` block (59213001–59213011 are taken; grep-verified). The exception is internal: it never reaches a client, because `getRequestBody()` converts it.
- **Modified** `JSONRequestInterface`:
  - `ERROR_REQUEST_BODY_TOO_LARGE = 182802`
  - `RESPONSE_KEY_ERROR_MAX_REQUEST_BODY_SIZE = 'maxRequestBodySize'`
- **Modified** `ErrorResponse`: `makePayloadTooLarge() : self` sets `Connectors_ResponseCode::HTTP_REQUEST_ENTITY_TOO_LARGE` (413). Its docblock names `BaseAPIMethod::getRequestBody()` as the first caller, matching the existing helpers.
- **New** `BaseAPIMethod::createRequestBodyReader() : RequestBodyReader` (`protected`, overridable). The default returns `RequestBodyReader::fromGlobals(AppConfig::getAPIMaxRequestBodySize())`. Its current consumer is the in-process ceiling test, which overrides it with a small `maxBytes`, an explicit `declaredLength` and a temp-file `streamURI`, so the real `read()` path and all three conversion branches run without HTTP and without a 15 MB payload. Growth: a per-method ceiling, should a non-media method ever need one, overrides this factory instead of adding config.
- **Modified** `BaseAPIMethod::getRequestBody()`:
  - Uses the `setRequestBody()` preset as is when one exists. Presets are a test-only input and are not subject to the ceiling, so the production reader is not shaped around them.
  - Otherwise calls `$this->createRequestBodyReader()->read()`, and trims the result as today.
  - Catches only `RequestBodyException`:
    - `ERROR_BODY_TOO_LARGE` → `errorResponse(JSONRequestInterface::ERROR_REQUEST_BODY_TOO_LARGE)->makePayloadTooLarge()->setErrorMessage('The request body exceeds the maximum allowed size of %s bytes.', $max)->addData(array(RESPONSE_KEY_ERROR_MAX_REQUEST_BODY_SIZE => $max))->send()`
    - `ERROR_BODY_DISCARDED` → the same code and status, with the message `'The request body (%s bytes declared) was discarded by the server because it exceeds the server upload limit (post_max_size).'`
    - `ERROR_BODY_READ_FAILED` → the existing `ERROR_FAILED_TO_READ_INPUT` (182801) response, unchanged.

  `JSONRequestTrait`'s provisional-collection guard already covers errors sent from inside `getRequestBody()`, so no re-entry change is needed. The reading logic leaves `BaseAPIMethod` for its own unit-testable class.

### C. Live-HTTP coverage of the body path
`tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php` gains two tests against the existing `TestJSONBodyParamsMethod` fixture:
- an unparseable body over the real `process()` path: HTTP 400, JSON content type, top-level `code` 182801, and in this dev test environment `JSONRequest.unparseableBody` echoing the short malformed body
- an oversized body: HTTP 413 and code 182802. This holds whether the framework ceiling or the server's `post_max_size` trips first, since both map to 182802.

### D. SVG delivery hardening (Media module)
- **New** `src/classes/Application/Media/Delivery/MediaResponseHeaders.php` (namespace `Application\Media\Delivery`), the single response-header policy for served media files:
  - `public const array ACTIVE_CONTENT_EXTENSIONS = array('svg', 'svgz')`: formats a browser can execute script in when opened directly.
  - `public const string ACTIVE_CONTENT_CSP = "default-src 'none'; style-src 'unsafe-inline'; sandbox"`
  - `static forExtension(string $extension) : array<string,string>`:
    - every extension gets `X-Content-Type-Options: nosniff`
    - active-content extensions (case-insensitive) also get `Content-Security-Policy: <ACTIVE_CONTENT_CSP>`
  - `static send(string $extension) : bool` emits `forExtension()` through `header()` and returns `true`. When `headers_sent()`, it emits nothing and returns `false`. The class stays free of a logging dependency; the caller reports the skip.
- **Modified** `DocumentTrait::serveFromRequest()` and `DocumentTrait::sendFile()` call `MediaResponseHeaders::send()` immediately before handing off to `FileHelper`.
  - **Keyed on the file actually sent**, not the document: `serveFromRequest()` stores `createThumbnail($width, $height)` in a local `$path` and passes `FileHelper::getExtension($path)` (vendor `FileHelper.php` L306, lower-cased by default). `sendFile()` passes `FileHelper::getExtension($this->getPath())`. For SVG the thumbnail path and the original are the same file, so behaviour is identical today; keying on the sent path keeps the policy correct if a format ever has its thumbnail rendered to a different type.
  - **A skipped send is logged.** When `send()` returns `false`, the trait calls `$this->log()` (both users, `Application_Media_Document` and `Application_Uploads_Upload`, use `Application_Traits_Loggable`) with a message naming the file and stating that the security headers were not sent because output had already started. The only mitigation for the SVG issue therefore cannot disappear without a trace.
  - Inline display is kept, so `<img src>` usage and the admin UI work unchanged: browsers never run script inside `<img>`-embedded SVG.
  - A directly opened SVG URL is sandboxed: no script, no same-origin access.
- Content validation stays shape-only (`Image::isContentValid()`). Sanitising SVG content is explicitly not adopted (Considered Alternatives).

### E. Fixture runtime tests
Two new test classes in `tests/AppFrameworkTests/Media/API/` drive the existing TestDriver fixtures through `processReturn()`, following `MediaDocumentParamsContainerTest`:
- `MediaMetadataPieceTest`:
  - resolved name, description and keywords
  - the default name when `name` is omitted
  - HTML rejected with `MediaMetadataAPIInterface::VALIDATION_HTML_NOT_ALLOWED` (681211)
- `MediaFileUploadPieceTest`:
  - a valid PNG resolves to type `Image`, with file name and size echoed
  - an unsupported extension → 681001
  - a content/extension mismatch → 681003
  - an empty decoded file → 681004
  - invalid base64 is reported as top-level 183003 with `validationErrors[].code` 183508

### F. Documentation accuracy
- `src/classes/Application/Media/API/README.md` §"Error codes" splits into two tables:
  - **Top-level error codes** (the response `code`): 681001–681006, 681201, 681211, 681221, 182801, 182802 (new), 183003
  - **Parameter validation codes**, reported inside a 183003 response under `validationErrors[].code`: 183508, 183509, 183701, 183702
- The same README's §"SVG and duplicate uploads" states the delivery headers from §D. §"Size limits" documents the request-body ceiling, its derivation, 182802/HTTP 413, and the rule that `post_max_size` must be at least the effective ceiling.
- **Manifest:** both hand-maintained module tables are replaced by a pointer to the generated `modules-overview.md` (see Structural Improvements). The `AGENTS.md` §2 maintenance rule is updated to match.

## Rationale
- **Ceiling at ingress, not in the parameter.** `Base64FileParameter`'s gate runs only after the whole body has been buffered and JSON-decoded, which can hold several times the body size. Only a check before `php://input` is read bounds memory. Deriving the default from the file limit keeps one operator knob meaningful, while the explicit override covers installs with other large JSON methods.
- **Detecting PHP's silent discard.** Without step 4 of `read()`, an over-`post_max_size` upload becomes `{}`, and the client is told `fileContent` is missing. That cost an investigation for no reason. Mapping both causes to one code (182802, HTTP 413) gives clients one thing to handle.
- **A separate `RequestBodyReader`.** Its current consumer is `BaseAPIMethod::getRequestBody()`. Growth: any future non-JSON body ingress, such as raw uploads, and `AddJSErrorLog`'s direct `php://input` read (out of scope here). A separate class also makes the ceiling testable with a temp-file stream URI, without HTTP.
- **An overridable reader factory, not a preset check.** Test presets bypass the reader, and tests reach the real `read()` path by overriding `createRequestBodyReader()`. This keeps the production reader free of a test-only method and lets in-process tests cover the discard and read-failure conversions, which a preset check could never reach.
- **A skipped header send is logged.** A silent `headers_sent()` skip would drop the only SVG mitigation without a trace. `send()` returns a bool and the trait logs through its existing `log()`, so the policy class needs no logging dependency.
- **Headers, not sanitising.** A CSP `sandbox` on the response neutralises script in a directly opened SVG regardless of its content, and leaves the file byte-identical, so `isContentValid()` and the admin UI are unaffected. Sanitising would need a maintained SVG allow-list parser: new dependency surface, false positives on legitimate SVGs, and a bypass history in every known implementation. The user's earlier decision to accept SVG as the admin UI does stays intact.
- **`nosniff` for every served file.** It costs nothing and prevents content-type confusion for any upload type. Keeping it in the same policy class avoids a second header site.
- **One policy class, not inline `header()` calls.** Media documents and uploads share `DocumentTrait`, so one call site covers both. The class is the seam for future per-type headers, for example `Content-Disposition` for active document types, should any be added.
- **Tests named after the pieces.** The fixtures exist to isolate the pieces, and the test names say what is covered.
- **Pointer instead of tables.** Two hand-maintained copies of a generated list have already drifted by 6–7 modules each. A pointer cannot drift.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Where to enforce the body ceiling | `RequestBodyReader` used by `BaseAPIMethod::getRequestBody()` | (a) rely on `post_max_size` only; (b) check inside `JSONRequestTrait::getRequestData()` after reading; (c) web-server `LimitRequestBody` only | (a) gives no clear error and needs php.ini access. (b) has already buffered the body. (c) cannot be shipped by a library. Reading with a max length bounds memory in PHP itself and needs no environment change. |
| Default ceiling value | Derived from `APP_API_MAX_FILE_CONTENT_SIZE` (×4/3 + 1 MiB) when the setting is `0` | A fixed default (e.g. 16 MiB); a ceiling per method | A fixed default drifts once operators raise the file limit. A per-method ceiling has no consumer yet: only media methods carry large bodies, and they all share the file limit. |
| In-process test seam for the ceiling | Overridable `BaseAPIMethod::createRequestBodyReader()`; presets bypass the reader | (a) `RequestBodyReader::checkPreset()` applying the ceiling to presets; (b) no in-process seam, live-HTTP only | (a) shapes a production class around test input, needs a ~15 MB padded preset, and cannot reach the discard or read-failure conversions. (b) leaves the `getRequestBody()` conversion covered only by the slow, opt-in live-HTTP group. |
| Error code for a body discarded by PHP | Same 182802/413 as an over-ceiling body | A separate code (e.g. 182803) | The client action is identical: send less. The message still says which limit tripped. |
| SVG XSS mitigation | Response headers (`CSP: sandbox`, `nosniff`) at the shared serving seam | (a) sanitise SVG on upload; (b) reject SVG over the API; (c) force `Content-Disposition: attachment` for SVG | (a) brings parser risk and alters user files. (b) reverses a user decision. (c) breaks direct inline viewing in the admin UI without being safer than a sandbox CSP. |
| Header policy when output has already started | `send()` returns `false` and the `DocumentTrait` call site logs the skip | (a) silent no-op; (b) drop the guard and let PHP warn | (a) lets the only SVG mitigation vanish without a trace. (b) pollutes output with warnings. The log line costs one call and reuses the trait's existing logger. |
| Raw-accessor consolidation | `getRawRequestValue()` delegates to `getRawRequestValueUnprocessed()` when a source exists | (a) a lazily created `RequestValueSource` as a fallback for both; (b) leave as is | (a) changes standalone semantics (empty strings would stop being dropped) for every type's unit tests. (b) leaves the flagged duplicate. Delegation removes the duplicate with zero behaviour change. |
| Module tables in AGENTS.md / context-documentation.md | Replace with a pointer to the generated `modules-overview.md` | Add the missing rows by hand | Adding rows fixes today and drifts again with the next module. The generated file already carries the Context Docs column. |

## Pattern Alignment
- **Follows:**
  - the four-part optional-config pattern (`API_MAX_FILE_CONTENT_SIZE` in `BaseConfigRegistry.php` L300, `Bootstrap.php` L273, `SetAppConfigSettingTrait.php` L137, `AppConfig.php` L193)
  - the `ErrorResponse::make{Status}()` helper style (`ErrorResponse.php` L251–L292)
  - JSON-request error codes on `JSONRequestInterface` (`182801` → `182802`)
  - `APIException`'s `592130xx` internal-code block
  - the live-HTTP test conventions (`#[Group('live-http')]`, listed in `docs/agents/project-manifest/testing.md`)
  - the TestDriver-fixture test pattern (`MediaDocumentParamsContainerTest.php`)
  - namespaced sub-folders for new Media code (`Media/Writer/`, `Media/API/`)
- **New folder** `src/classes/Application/API/Request/`. No existing API sub-folder covers request ingress. `Utilities/` holds response-key helpers and `Response/` is response-side, so a separate folder keeps the module's request/response split readable.
- **No departure at the serving seam.** `DocumentTrait` is itself namespaced (`Application\Media`) and already imports `AppUtils\FileHelper`, so calling the namespaced `MediaResponseHeaders` from it follows the module's conventions. Its users, `Application_Media_Document` and `Application_Uploads_Upload`, are legacy global-namespace classes and are not edited. The legacy `Application_Media_Delivery` is not touched either (AGENTS.md §4).

## Structural Improvements

| Structure | Observation | Decision | Reason |
|-----------|-------------|----------|--------|
| `src/classes/Application/API/Parameters/BaseAPIParameter.php::validateByCallback()` | Drops `...$args` (pre-existing bug, WP-005) | Promoted to step 1 | One-line fix; the receiving `CallbackValidation` already supports args; no caller depends on the drop. |
| `BaseAPIParameter::getRawRequestValue()` / `getRawRequestValueUnprocessed()` | Byte-identical source branch (Reviewer, WP-008) | Promoted to step 1 | The file is already open in step 1; delegation removes the duplicate with no behaviour change. |
| `src/classes/Application/API/Traits/PaginatedAPITrait.php` callback closures | Could pass `$maxLimit` as a callback arg now that args work | Rejected | The closures with `use()` are correct and tested; rewriting them adds churn with no gain. |
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php::getRequestBody()` | Inline preset/stream/error handling with no testable seam | Promoted to steps 3–4 | Extracted into `RequestBodyReader` as part of adding the ceiling. |
| `src/classes/Application/AjaxMethods/AddJSErrorLog.php` direct `php://input` read | The same unbounded read pattern | Rejected | Outside this plan's blast radius (AJAX, not API). Recorded in Deferred Items. |
| `src/classes/Application/Media/DocumentTrait.php` serving methods | No response-header policy; SVG served inline on the app origin | Promoted to step 6 | Security finding; a single shared seam for media and uploads. |
| `src/classes/Application/Media/Delivery.php` (legacy) | Global-namespace class with an `Exception` catch-all and HTML error output | Rejected | Not needed for the header fix; AGENTS.md §4 forbids unrequested legacy refactors. |
| `src/classes/Application/Media/API/README.md` §"Error codes" | Flat table conflates top-level and nested codes (QA, WP-007) | Promoted to step 9 | The table is client-facing contract documentation. |
| `AGENTS.md` §1 module table and `docs/agents/project-manifest/context-documentation.md` §"Available Modules" | Hand-maintained copies of the generated `modules-overview.md`, already missing 6–7 modules | Promoted to step 10 | A pointer removes the drift permanently; the synthesis named this area. |
| `src/classes/Application/Media/module-context.yaml` | Media CTX entry point exists; root `context.yaml` imports it via glob | Rejected (no change needed) | The synthesis follow-up is already satisfied; step 10 only verifies the new `Delivery/` class appears in the generated docs. |

## Detailed Steps
1. **Parameter fixes.** In `src/classes/Application/API/Parameters/BaseAPIParameter.php`:
   - `validateByCallback()` passes `...$args` to `new CallbackValidation($callback, ...$args)`.
   - `getRawRequestValue()` delegates to `getRawRequestValueUnprocessed()` when a value source is injected, and keeps `getRequestParam()->get()` as the no-source fallback.
   - Update both docblocks to state that the no-source fallback is the only difference.
   - Add the tests listed in the Test Plan for AC-01 and AC-02.
2. **Body-size config.** Add `BaseConfigRegistry::API_MAX_REQUEST_BODY_SIZE` (docblock: `0` means derived), the `Bootstrap::registerOptionalSetting(..., 0)` default next to L273, `SetAppConfigSettingTrait::setAPIMaxRequestBodySize(int $bytes)`, and `AppConfig::getAPIMaxRequestBodySize()` delegating to the pure `AppConfig::deriveAPIMaxRequestBodySize()`, with `AppConfig::API_REQUEST_BODY_OVERHEAD = 1048576`, per Approach §B.
3. **Request body reader (new).** Create `src/classes/Application/API/Request/RequestBodyReader.php` and `src/classes/Application/API/Request/RequestBodyException.php` per Approach §B. Run `composer dump-autoload` (classmap autoloading).
4. **Wire the reader.**
   - Add `ERROR_REQUEST_BODY_TOO_LARGE = 182802` and `RESPONSE_KEY_ERROR_MAX_REQUEST_BODY_SIZE` to `src/classes/Application/API/Traits/JSONRequestInterface.php`.
   - Add `ErrorResponse::makePayloadTooLarge()`.
   - Add the overridable `BaseAPIMethod::createRequestBodyReader()` factory, and rewrite `BaseAPIMethod::getRequestBody()` to use the preset when set, otherwise the factory's reader, converting `RequestBodyException` as in Approach §B. Error messages pass numbers as `sprintf` arguments.
   - Add the in-process tests for AC-03 to AC-06.
5. **Live-HTTP body tests.** Add `test_unparseableBodyIsRejectedOverHTTP` and `test_oversizedBodyIsRejectedOverHTTP` to `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php`. The oversized test sends `AppConfig::getAPIMaxRequestBodySize() + 1` bytes of a valid JSON string field. Update the class docblock.
6. **SVG delivery hardening.**
   - Create `src/classes/Application/Media/Delivery/MediaResponseHeaders.php` per Approach §D.
   - In `DocumentTrait::serveFromRequest()`, store `createThumbnail($width, $height)` in a local `$path`, call `MediaResponseHeaders::send(FileHelper::getExtension($path))`, then pass `$path` to `FileHelper::sendFileAuto()`. In `DocumentTrait::sendFile()`, call `MediaResponseHeaders::send(FileHelper::getExtension($this->getPath()))` immediately before `FileHelper::sendFile()`.
   - In both methods, when `send()` returns `false`, log the skip through `$this->log()` as described in Approach §D.
   - Run `composer dump-autoload`.
   - Add the unit test, and a live-HTTP test that creates an SVG media document through `MediaDocumentWriter` and requests the document's thumbnail URL (`$record->getMediaDocument()->getThumbnailURL()`; `MediaRecord` has no URL method of its own, the method is `DocumentTrait::getThumbnailURL()`) without a cookie, relying on the test application's NoAuth session (Assumptions). The test extends plain `AppFrameworkTestClasses\ApplicationTestCase`, not `MediaTestCase`, and **commits** the record before the request so the separate `media.php` process can see it, then deletes the committed row and file itself (Test Plan entry `MediaDeliveryHeadersLiveHTTPTest`; shared DB/storage under Assumptions).
7. **Fixture runtime tests.** Add `tests/AppFrameworkTests/Media/API/MediaMetadataPieceTest.php` and `tests/AppFrameworkTests/Media/API/MediaFileUploadPieceTest.php` per Approach §E. Update both fixture docblocks (`tests/application/assets/classes/TestDriver/API/TestMediaMetadataMethod.php`, `TestMediaFileUploadMethod.php`) to name their committed test.
8. **Live-HTTP registry.** Add the new live-HTTP tests to the table in `docs/agents/project-manifest/testing.md` (L404–L410): the two body tests extend the `JSONBodyParamsLiveHTTPTest` row, and the new media delivery test class gets its own row.
9. **Media API README.** In `src/classes/Application/Media/API/README.md`:
   - split §"Error codes" into the two tables of Approach §F and add 182802 and 183003
   - extend §"Size limits" with the request-body ceiling, its derivation, `APP_API_MAX_REQUEST_BODY_SIZE`, 182802/HTTP 413, and the rule that `post_max_size` must be at least the effective ceiling (this replaces the "about 1.4×" wording)
   - rewrite §"SVG and duplicate uploads" to state the delivery headers sent by `media.php` and why inline `<img>` use is unaffected
10. **Remaining documentation and manifest.**
    - Apply every entry under Documentation Updates.
    - Run `composer build-docs`. Confirm that `.context/modules/api/architecture-core.md` contains `RequestBodyReader` and `.context/modules/media/architecture-core.md` contains `MediaResponseHeaders`. If the latter does not, add `./Delivery` to the `architecture-core` `sourcePaths` in `src/classes/Application/Media/module-context.yaml` and rebuild.
    - Do not commit `.context/` (AGENTS.md §4).
11. **Verification.** Run `composer analyze`, `composer test-filter -- API`, `composer test-filter -- Media`, and `composer test-group -- live-http` against the test webserver. Record the results.

## Dependencies
- Step 1 is independent.
- Step 2 → step 3 → step 4 → step 5.
- Step 6 is independent of steps 1–5.
- Step 7 is independent. It relies on step 1 only in that `GetMediaDocumentsAPI` uses `validateByCallback()`, which is unaffected.
- Step 8 depends on steps 5 and 6.
- Step 9 depends on steps 4 and 6.
- Step 10 depends on steps 1–9.
- Step 11 depends on all previous steps.

## Required Components
- **New:**
  - `src/classes/Application/API/Request/RequestBodyReader.php`
  - `src/classes/Application/API/Request/RequestBodyException.php`
  - `src/classes/Application/Media/Delivery/MediaResponseHeaders.php`
  - `tests/AppFrameworkTests/API/Parameters/CallbackValidationArgsTest.php`
  - `tests/AppFrameworkTests/API/Request/RequestBodyReaderTest.php`
  - `tests/AppFrameworkTests/API/RequestBodyCeilingTest.php`
  - `tests/AppFrameworkTests/API/Request/APIMaxRequestBodySizeTest.php`
  - `tests/AppFrameworkTests/Media/Delivery/MediaResponseHeadersTest.php`
  - `tests/AppFrameworkTests/Media/Delivery/MediaDeliveryHeadersLiveHTTPTest.php`
  - `tests/AppFrameworkTests/Media/API/MediaMetadataPieceTest.php`
  - `tests/AppFrameworkTests/Media/API/MediaFileUploadPieceTest.php`
- **Modified:**
  - `src/classes/Application/API/Parameters/BaseAPIParameter.php`
  - `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`
  - `src/classes/Application/API/Traits/JSONRequestInterface.php`
  - `src/classes/Application/API/ErrorResponse.php`
  - `src/classes/Application/ConfigSettings/BaseConfigRegistry.php`
  - `src/classes/Application/ConfigSettings/AppConfig.php`
  - `src/classes/Application/ConfigSettings/SetAppConfigSettingTrait.php`
  - `src/classes/Application/Bootstrap/Bootstrap.php`
  - `src/classes/Application/Media/DocumentTrait.php`
  - `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php`
  - `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php` (raw-accessor regression assertions)
  - `tests/application/assets/classes/TestDriver/API/TestMediaMetadataMethod.php` and `TestMediaFileUploadMethod.php` (docblocks only)
  - the documentation files listed under Documentation Updates
- **Infrastructure:** the existing test webserver at `APP_URL`, for the live-HTTP group.

## Assumptions
- The parent plan's uncommitted working tree is present and unchanged when this rework starts, and v7.7.0 is still unreleased. The rework's changelog lines therefore join the v7.7.0 entry, and `VERSION` stays `7.7.0`.
- PHP discards a POST body whose `Content-Length` exceeds `post_max_size`, so `php://input` reads `''` for any content type. This is documented SAPI behaviour. The implementer confirms it on the target PHP 8.4. If it does not hold, step 4 of `read()` is never triggered and is still harmless.
- `media.php` in the test application serves media to a cookieless live-HTTP client through the test application's **NoAuth session** (verified, research brief). `tests/application/bootstrap.php` L32 selects `\TestDriver\Session\TestSession{TESTS_SESSION_TYPE}`, and `TESTS_SESSION_TYPE` defaults to `'NoAuth'` (`tests/application/config/test-ui-config.dist.php` L26). `TestSessionNoAuth` uses `Application_Session_AuthTypes_None`, so `Application_Session_Base::authenticate()` resolves the system user on every request, with `RIGHT_LOGIN` and a no-op `redirectToReturnURI()`. The test therefore needs no session cookie or login step. Its precondition is `TESTS_SESSION_TYPE === 'NoAuth'`; under any other session type (for example a local `CAS` config) the test calls `markTestSkipped()` with that reason. AC-09 then reduces to the unit-level `MediaResponseHeadersTest` plus the manual check described under Risks.
- The PHPUnit process and the web server at `APP_URL` share **one database and one media storage folder** (verified, research brief). `tests/bootstrap.php` L19 and `tests/application/bootstrap.php` L11 both set `APP_ROOT` to `tests/application`; `tests/application/media.php` boots through the latter; media files resolve to `APP_ROOT/storage/media` (`Application::getStorageSubfolderPath('media')`); both processes read `tests/application/config/test-db-config.php` through `TestDriver/Environments/EnvironmentsConfig.php`. This holds when `APP_URL` serves this checkout's `tests/application`, which the existing live-HTTP group already assumes. A record written by the test becomes visible to `media.php` only once its transaction is **committed**, so the media delivery live test commits explicitly and removes what it committed. If the committed record is still not served (HTTP 404 or a non-200 that is not a login redirect, meaning `APP_URL` points at a different checkout or database), the test fails rather than skips: the delivery headers are the point of the test, and a silent skip would hide a misconfigured live-HTTP environment.
- The test webserver's `post_max_size` and the framework ceiling may differ. The oversized live test accepts either path, because both yield 182802/413.

## Constraints
- `array()` syntax only; strict types; namespaced new classes; `composer dump-autoload` after adding class files (AGENTS.md §4).
- No `.context/` commits on a feature branch.
- `hasRawRequestValue()` / `getRawRequestValueUnprocessed()` keep their names, visibility and semantics (insight `dfd00d1a`).
- SVG stays accepted and inline-displayable.
- Error messages carry dynamic values only as `sprintf` arguments.

## Out of Scope
- Reconciling the HCP Editor's `GetComtypesAPI`/`GetMailingsAPI` pagination with `PaginatedAPITrait`. That belongs to a different repository and needs a separate HCP Editor plan (Deferred Items).
- Sanitising SVG content.
- An audit trail for media reads and writes.
- Closing the residual non-transactional window after `replaceFile()` returns.
- Refactoring the legacy `Application_Media_Delivery` class.
- Bounding `AddJSErrorLog`'s `php://input` read.

## Human Actions

| # | Action | When | Why an agent cannot do it |
|---|--------|------|---------------------------|
| 1 | Set PHP `post_max_size` (and any web-server body limit) on target installs to at least the effective `AppConfig::getAPIMaxRequestBodySize()` (≈ 14.4 MiB with the default 10 MiB file limit) | After the run | Server configuration is outside the repository and the agents' access. |
| 2 | Release v7.7.0 (tag and publish) together with the parent plan's work | After the run | Releases are owned by the user. |
| 3 | Decide whether to open the HCP Editor plan for `GetComtypesAPI`/`GetMailingsAPI` pagination adoption (Deferred Item 1) | After the run | Cross-repository scheduling decision. |

## Acceptance Criteria
- AC-01: `validateByCallback($callback, $a, $b)` invokes `$callback($value, $result, $param, $a, $b)`. Existing callers that pass no args behave exactly as before.
- AC-02: `getRawRequestValue()` and `getRawRequestValueUnprocessed()` return identical values when a value source is injected. Without a source, `getRawRequestValue()` still drops an empty string and `getRawRequestValueUnprocessed()` still returns it. Only `getRawRequestValueUnprocessed()` reads from the value source directly.
- AC-03: `AppConfig::deriveAPIMaxRequestBodySize($configured, $fileLimit)` returns `$configured` when it is `> 0`, and otherwise `intdiv($fileLimit * 4, 3) + 1048576`. `AppConfig::getAPIMaxRequestBodySize()` returns that derivation for the booted settings: 15029589 with the test application's defaults.
- AC-04: `RequestBodyReader::read()` rejects a declared length above the ceiling without opening the stream. It rejects a stream longer than the ceiling after buffering at most `maxBytes + 1` bytes, reports `''` with a positive declared length as discarded, reports an unreadable stream as a read failure, and returns the body unchanged otherwise. `BaseAPIMethod::createRequestBodyReader()` returns a reader whose `getMaxBytes()` equals `AppConfig::getAPIMaxRequestBodySize()`, and a `setRequestBody()` preset bypasses the reader.
- AC-05: A JSON API method whose body exceeds the ceiling, or whose body PHP discarded, responds with top-level code 182802, HTTP 413, a message naming the limit, and `maxRequestBodySize` in the error data, in `processReturn()` mode. The `process()` mode differs only in the generic `BaseAPIMethod::sendErrorResponse()` branch (L617–L634), and is covered over HTTP by AC-07's oversized-body test, whichever limit (framework ceiling or `post_max_size`) trips first.
- AC-06: A read failure still yields 182801. A body within the ceiling is processed exactly as before (the existing JSON-body tests pass unchanged).
- AC-07: Over real HTTP (`process()` path), an unparseable JSON body yields HTTP 400, a JSON response, top-level code 182801, and the `JSONRequest.unparseableBody` echo in the dev environment. An oversized body yields HTTP 413 with code 182802.
- AC-08: `MediaResponseHeaders::forExtension()` returns `X-Content-Type-Options: nosniff` for every extension. It adds `Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; sandbox` for `svg`/`svgz` (case-insensitive) and for no other extension. `MediaResponseHeaders::send()` returns `false` and emits nothing when headers were already sent, and `true` otherwise. `DocumentTrait` keys the policy on the extension of the file actually sent and logs through `$this->log()` whenever `send()` returns `false`.
- AC-09: Serving an SVG media document through `media.php` sends `Content-Type: image/svg+xml` inline, plus the CSP and `nosniff` headers. Serving a PNG sends `nosniff` and no CSP.
- AC-10: `TestMediaMetadataMethod` has committed runtime tests covering resolved values, the default name, and the HTML rejection (681211).
- AC-11: `TestMediaFileUploadMethod` has committed runtime tests covering a valid PNG, 681001, 681003, 681004, and invalid base64 (top-level 183003 with `validationErrors[].code` 183508).
- AC-12: `src/classes/Application/Media/API/README.md` lists top-level codes and nested parameter-validation codes in separate tables. It includes 182802 and 183003, documents the request-body ceiling with its `post_max_size` rule, and describes the SVG delivery headers.
- AC-13: `AGENTS.md` §1 and `docs/agents/project-manifest/context-documentation.md` no longer contain hand-maintained module tables. Both point to `docs/agents/project-manifest/modules-overview.md`, and the `AGENTS.md` §2 "New module added" rule no longer asks for a manual table update.
- AC-14: `composer analyze` is clean, and the full `API` and `Media` test filters plus the `live-http` group pass.

## Testing Strategy
- **Unit and in-process:**
  - the parameter fixes
  - the config derivation
  - `RequestBodyReader`, using a temp file as `streamURI` and an explicit `declaredLength`
  - `getRequestBody()` conversion through an anonymous test subclass of `TestJSONBodyParamsMethod` that overrides `createRequestBodyReader()` with a small `maxBytes`, an explicit `declaredLength` and a temp-file `streamURI`, driven by `processReturn()`. Boot settings cannot be lowered at test time, so the factory is what lets the real `read()` path run at tiny sizes, covering the over-ceiling, discarded and read-failure conversions in-process.
  - the header policy class
  - the two fixture pieces through `processReturn()`
- **Live-HTTP:** proves the wiring that cannot be observed in CLI: the `process()` path for 182801 and 182802, and the delivery headers, which `header()` does not record under the CLI SAPI.
- **Regression:** the full `API` and `Media` filters, plus PHPStan.

## Test Plan
- `tests/AppFrameworkTests/API/Parameters/CallbackValidationArgsTest.php` (new): `test_argsAreForwardedToCallback` (the callback receives the extra args in order); `test_noArgsCallbackUnchanged`. Covers AC-01.
- `tests/AppFrameworkTests/API/Parameters/ClearableStringParameterTest.php` (modified): `test_rawAccessorsAgreeWithInjectedSource` (both accessors return the same value, including `''`, from an injected `RequestValueSource`); `test_noSourceFallbacksDifferOnEmptyString`. Both accessors are `protected`, so the tests invoke them through `ReflectionMethod` on a plain `ClearableStringParameter` instance, following `tests/AppFrameworkTests/API/Traits/PaginatedAPITraitTest.php` L173 and the `ReflectionMethod` access already planned for `createRequestBodyReader()`. No test subclass is added. Covers AC-02.
- `tests/AppFrameworkTests/API/Request/APIMaxRequestBodySizeTest.php` (new): `test_zeroDerivesFromFileLimit` (`deriveAPIMaxRequestBodySize(0, 10485760)` → 15029589); `test_derivedCeilingFollowsRaisedFileLimit`; `test_positiveValueIsReturnedAsIs`; `test_bootedGetterUsesDerivation` (the getter equals `deriveAPIMaxRequestBodySize(0, getAPIMaxFileContentSize())` in the test application, which sets no override). Covers AC-03.
- `tests/AppFrameworkTests/API/Request/RequestBodyReaderTest.php` (new):
  - `test_declaredLengthAboveCeilingThrowsWithoutReading` (the stream URI points at a nonexistent path, so a read would fail with a different code)
  - `test_streamLongerThanCeilingThrows`
  - `test_emptyStreamWithDeclaredLengthIsDiscarded`
  - `test_bodyWithinCeilingIsReturnedUntrimmed`
  - `test_emptyBodyWithoutDeclaredLengthIsEmptyString`
  - `test_unreadableStreamThrowsReadFailed` (no declared length, nonexistent stream URI → `ERROR_BODY_READ_FAILED`, no PHP warning)
  - `test_fromGlobalsParsesContentLength` (digit string → int; missing or non-digit → null)

  Covers AC-04.
- `tests/AppFrameworkTests/API/RequestBodyCeilingTest.php` (new). Each test builds an anonymous subclass of `TestJSONBodyParamsMethod` (constructed with `APIManager::getInstance()`) whose `createRequestBodyReader()` returns a `RequestBodyReader` with a small `maxBytes` (e.g. 64), a chosen `declaredLength` and a temp-file `streamURI`, then calls `processReturn()`:
  - `test_oversizedBodyYields182802And413` (temp file longer than `maxBytes`; asserts the code, the HTTP status, the message containing the limit, and the `maxRequestBodySize` data key)
  - `test_discardedBodyYields182802And413` (empty temp file with a positive `declaredLength`; asserts the code, the status and the `post_max_size` message)
  - `test_readFailureYields182801` (nonexistent stream URI)
  - `test_bodyWithinCeilingResolvesNormally` (a valid JSON body in the temp file resolves the fixture's parameters)
  - `test_presetBodyBypassesReader` (with `setRequestBody()` set, a factory returning a reader over a nonexistent URI is never read and the preset resolves normally)
  - `test_defaultReaderUsesBootedCeiling` (on the unmodified fixture, the factory's reader reports `getMaxBytes()` equal to `AppConfig::getAPIMaxRequestBodySize()`; the protected factory is reached through `ReflectionMethod`)

  Covers AC-04, AC-05 and AC-06.
- `tests/AppFrameworkTests/API/Parameters/JSONBodyParamResolutionTest.php` (existing, unchanged): must still pass, including its 182801 assertions. Covers AC-06.
- `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php` (modified): `test_unparseableBodyIsRejectedOverHTTP`; `test_oversizedBodyIsRejectedOverHTTP`. Covers AC-07, and the `process()`-mode clause of AC-05 (182802/413 over HTTP; the test does not distinguish which limit tripped).
- `tests/AppFrameworkTests/Media/Delivery/MediaResponseHeadersTest.php` (new): `test_nosniffForEveryExtension` (png, jpg, pdf, svg); `test_cspOnlyForActiveContent`; `test_activeContentMatchIsCaseInsensitive` (`SVG`); `test_sendReportsWhetherHeadersWereEmitted` (captures `$expected = !headers_sent()` immediately before calling `send('svg')` and asserts the return value equals it, which holds deterministically under the CLI runner whichever branch is taken). Covers AC-08. The `DocumentTrait` keying and skip-logging lines are verified by reading the diff in code review, since the CLI SAPI cannot reach `serveFromRequest()`'s exit path; the live-HTTP test below covers the emitting branch end to end.
- `tests/AppFrameworkTests/Media/Delivery/MediaDeliveryHeadersLiveHTTPTest.php` (new, `final`, `#[Group('live-http')]`): `test_svgIsServedInlineWithSandboxCSP`; `test_pngIsServedWithNosniffOnly`. Authentication comes from the test application's NoAuth session; `setUp()` calls `parent::setUp()` and then skips the class when `TESTS_SESSION_TYPE !== 'NoAuth'` (Assumptions). Covers AC-09.
  - **Base class:** plain `AppFrameworkTestClasses\ApplicationTestCase`, like the four existing live-HTTP tests. It opens no transaction in `setUp()`, and its `tearDown()` only calls `DBHelper::rollbackConditional()`, which is a no-op once the test has committed. The class must **not** extend `MediaTestCase` or implement `ImageMediaTestInterface`: `ImageMediaTestTrait::setUpImageTestCase()` opens a transaction and deletes every media row, and committing inside it would commit that deletion.
  - **Create and commit:** a private helper `createCommittedMedia(string $exampleKey) : MediaRecord` reads the sample file from `tests/files/Media/` named by `ImageMediaTestInterface::EXAMPLE_IMAGES[$exampleKey]` (keys `SVGImage::FORMAT_ID` and `PNGImage::FORMAT_ID`; the constant is read without implementing the interface), then runs `DBHelper::startTransaction()`, `(new MediaDocumentWriter())->create(MediaFileContent::fromFile(...), 'live-http-delivery-'.$exampleKey, Application::getUser())` and `DBHelper::commitTransaction()`. On an exception before the commit it calls `DBHelper::rollbackTransaction()` and rethrows. It stores the record in a `?MediaRecord $committedRecord` property before returning.
  - **Request:** curl fetches `$record->getMediaDocument()->getThumbnailURL()` (no size, so the original file is served) with `CURLOPT_HEADER`, `CURLOPT_FOLLOWLOCATION` off and no cookie, and asserts HTTP 200 before checking the headers. The assertion message names the shared DB/storage assumption, so a 404 from a web server bound to another checkout or database explains itself.
  - **Cleanup:** `tearDown()` runs before `parent::tearDown()`. When `$committedRecord` is set, it runs `DBHelper::startTransaction()`, `(new MediaDocumentWriter())->delete($record)` and `DBHelper::commitTransaction()`. `delete()` removes the row, and `MediaRecord::_onDeleted()` removes the stored file. If the file still exists afterwards (e.g. the delete threw and was rolled back), it is removed with `FileHelper::deleteFile()`. The row and file are therefore gone whether the assertions pass or fail, and later `MediaTestCase` runs see no leftover media rows.
- `tests/AppFrameworkTests/Media/API/MediaMetadataPieceTest.php` (new): `test_resolvesSubmittedValues`; `test_omittedNameUsesDefault`; `test_htmlInMetadataIsRejected` (681211). Covers AC-10.
- `tests/AppFrameworkTests/Media/API/MediaFileUploadPieceTest.php` (new): `test_validPngResolvesImageType`; `test_unsupportedExtensionIsRejected` (681001); `test_contentMismatchIsRejected` (681003); `test_emptyFileIsRejected` (681004); `test_invalidBase64IsNestedValidationError` (183003 + 183508). Covers AC-11.
- Documentation review in step 10: checks AC-12 and AC-13 by reading `src/classes/Application/Media/API/README.md`, `AGENTS.md` and `docs/agents/project-manifest/context-documentation.md` against the criteria.
- Step 11 runs `composer analyze`, `composer test-filter -- API`, `composer test-filter -- Media` and `composer test-group -- live-http`. Covers AC-14.

## Documentation Updates
- `src/classes/Application/Media/API/README.md`: §"Error codes", §"Size limits" and §"SVG and duplicate uploads" (step 9).
- `src/classes/Application/API/README.md`:
  - Key Concepts: a "Request body ceiling" bullet (`RequestBodyReader`, `APP_API_MAX_REQUEST_BODY_SIZE` and its derivation, 182802/HTTP 413, post_max_size discard detection, and the overridable `createRequestBodyReader()` factory; `setRequestBody()` presets bypass the ceiling)
  - the HTTP status passage near the `makeNotFound()` lines also names `makePayloadTooLarge()` (413)
  - folder table: a new `Request/` row
- `src/classes/Application/API/Parameters/README.md`: the callback-validation bullet states that `validateByCallback()` forwards `...$args` after `($value, $result, $param)`.
- `src/classes/Application/Media/README.md`: a "Delivery" note on `Delivery/MediaResponseHeaders`, the headers sent for served files (keyed on the extension of the file actually sent), and the log entry written when headers could not be sent.
- `src/classes/Application/Media/module-context.yaml`: a keyword entry for `MediaResponseHeaders`. Add `./Delivery` to `architecture-core` `sourcePaths` only if step 10 finds it missing from the generated doc.
- `src/classes/Application/API/module-context.yaml`: a keyword entry for `RequestBodyReader` (the `./` core source already includes `Request/`; verified in step 10).
- `docs/agents/project-manifest/constraints.md`:
  - "API Methods": never read `php://input` directly in an API method; request bodies go through `BaseAPIMethod::getRequestBody()` and its ceiling
  - Media: served media files get their headers from `MediaResponseHeaders`, and a new active-content type extends `ACTIVE_CONTENT_EXTENSIONS`
- `docs/agents/project-manifest/testing.md`: the live-HTTP table (step 8).
- `AGENTS.md`:
  - §1 "Module documents (`.context/modules/`)": replace the table with one sentence pointing to `docs/agents/project-manifest/modules-overview.md` (Context Docs column) as the authoritative, generated module list
  - §2 "New module added" row: drop "update module table above"
- `docs/agents/project-manifest/context-documentation.md` §"Available Modules": replace the table with the same pointer.
- `docs/agents/project-manifest/modules-overview.md` and `module-glossary.md`: regenerated by `composer build-docs` (step 10).
- `changelog.md`: append to the existing `v7.7.0 - **WIP; UNRELEASED**` entry, in house style:
  - API: request-body size ceiling (`APP_API_MAX_REQUEST_BODY_SIZE`, derived by default) with error 182802/HTTP 413, including bodies discarded by `post_max_size`
  - API: `ErrorResponse::makePayloadTooLarge()`
  - API: `validateByCallback()` now forwards its extra arguments to the callback
  - Media: served media files send `X-Content-Type-Options: nosniff`; SVG files are additionally sandboxed with a Content-Security-Policy

## Deferred Items

| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---------------|--------|-----------------|-------|
| 1 | Reconcile the HCP Editor's `GetComtypesAPI` (silent clamp) and `GetMailingsAPI` with `PaginatedAPITrait` (reject, never clamp) | Synthesis "Deferred & Follow-Up Items"; parent Deferred Item 6 | Different repository; framework plans stay single-repo (user preference) | Switching to rejection is a breaking change for those methods and needs a versioned HCP changelog entry. The response keys already match. |
| 2 | Close the residual non-transactional window after `replaceFile()` returns (new bytes on disk, old size in DB on a failed commit) | Synthesis, WP-004 | Needs post-commit hooks in DBHelper, which is a separate infrastructure change; the risk is accepted and documented in `Media/API/README.md` §"File consistency" | Reconsider if DBHelper gains `onCommit`/`onRollback` callbacks. |
| 3 | Audit-trail entries for `includeContent=true` reads and media writes | Synthesis, Security Auditor WP-007/WP-008 | No media audit facility exists and no compliance requirement is stated | Reconsider if compliance requirements change. |
| 4 | Bound `AddJSErrorLog`'s direct `php://input` read with `RequestBodyReader` | This plan's Structural Improvements | AJAX endpoint outside this plan's blast radius | `RequestBodyReader` is the ready seam. |
| 5 | In-use guard or veto before deleting media | Parent Deferred Item 1 | The framework has no usage concept | Unchanged from the parent plan. |
| 6 | Content-hash deduplication | Parent Deferred Item 2 | User decision | Unchanged. |
| 7 | Media tagging over the API | Parent Deferred Item 3 | No consumer | Unchanged. |
| 8 | Route `MediaSettingsManager` through `MediaDocumentWriter` | Parent Deferred Item 4 | Untested form-upload path | Unchanged. |
| 9 | Consolidate `Application_Uploads::validate()` onto `isContentValid()` | Parent Deferred Item 5 | Legacy upload path not touched | Unchanged. |

## Risks & Mitigations
| Risk | Mitigation |
|------|------------|
| **A legitimate large upload trips the new ceiling** | The default is derived from the file limit plus 1 MiB of JSON overhead, so any file the parameter accepts fits. Operators who raise the file limit get a matching ceiling automatically. 182802 names the limit. |
| **Operators keep a `post_max_size` below the ceiling** | The discard detection turns this into a clear 182802 instead of a misleading "missing parameter". README §"Size limits" and Human Action 1 state the rule. |
| **The CSP header breaks a legitimate SVG use** | `<img>`-embedded SVG never runs script and is unaffected by the response CSP. `style-src 'unsafe-inline'` keeps inline SVG styles. Only direct navigation is sandboxed, which is the intent. The live-HTTP test checks inline delivery. |
| **Headers are already sent before `MediaResponseHeaders::send()`** | `send()` checks `headers_sent()`, skips and returns `false`, and `DocumentTrait` logs the skip, so a lost header is observable in the application log. `Delivery.php` runs in script mode with no prior output, which the live-HTTP test confirms. |
| **The raw-accessor consolidation changes standalone parameter behaviour** | The no-source fallback is untouched. `test_noSourceFallbacksDifferOnEmptyString` pins it. |
| **The live-HTTP delivery test cannot authenticate against `media.php`** | The default NoAuth test session authenticates every cookieless request as the system user (Assumptions). The test disables `CURLOPT_FOLLOWLOCATION` and asserts HTTP 200, so a redirect to a login page fails loudly instead of passing on a wrong response. When the local test app runs a non-NoAuth session, the test skips with a stated reason; AC-09 then rests on `MediaResponseHeadersTest` plus a manual check: open an SVG's `getThumbnailURL()` in a logged-in browser and confirm the CSP and `nosniff` response headers in the developer tools. |
| **The live-HTTP delivery test's record is invisible to `media.php`, or leaks into the shared test DB** | `MediaDocumentWriter::create()` needs an open transaction, and uncommitted rows are invisible to the web server process. The test extends plain `ApplicationTestCase` (no auto-transaction, no media-table wipe), commits the record explicitly, and deletes the row and file in `tearDown()` through `MediaDocumentWriter::delete()`, with a file-level fallback. The HTTP 200 assertion message names the shared DB/storage assumption, so a web server bound to another database fails with a clear cause. |
| **The rework lands on a different tree than the one researched (the parent work is uncommitted)** | Assumptions record the dependency. The implementer re-verifies the cited line numbers before editing. |

## Recommended Workflow
- **Workflow:** ledger
- **Rationale:** The rework spans three areas: API ingress (new config, a new class and a new error code), a security hardening of Media delivery, and manifest restructuring. It includes two security-relevant changes that benefit from a formal security-audit stage, and it has 11 steps with distinct concerns.
