# Plan

## Plan Audit Cycles
- Audits: 1 (Sonnet 5.5 ×1) — Plan Auditor v1.12.0
- Architectural Reviews: 1 (Sonnet 5.5 ×1) — Plan Architect Reviewer v2.3.4

## Prior Project Context
- **Lineage.**
  - `docs/agents/plans/2026-10-16-media-document-apis/` shipped the media document API (WIP v7.7.0).
  - `docs/agents/plans/2026-10-19-media-document-apis-rework-1/` added the request-body ceiling (`RequestBodyReader`, 182802/HTTP 413), the SVG delivery headers (`MediaResponseHeaders`), and further fixes. Its 8 WPs passed all 40 stages.
  - This plan is rework 2: it handles the follow-up items in rework-1's `synthesis.md`.
- **Working tree.** Rework-1's implementation is uncommitted on branch `feature-media-apis`. This plan builds on that tree and adds to the same unreleased `v7.7.0` changelog entry, with no version bump.
- **Insights applied:**
  - `ea2df729-ec33-4517-b1d3-7c028edb974b`: dynamic values go into `setErrorMessage()`/`log()` as sprintf arguments, never into the format string. This applies to steps 4 and 5.
  - `af333a25-0c2e-4463-93d4-7afa13031704`: expected failures become client errors; unexpected ones stay with the throwable boundary. The new ceiling helper never throws to the client.
  - `dfd00d1a-ab39-4907-9ea3-51d7ac76809c` and `08921e90-d1ea-4971-b0d7-974084d8a35e`: the framework collapses empty values to "absent". Step 8 documents rather than changes this.
  - `39ca30b5-fbb7-46a4-8147-d9475ca70ab4`: a freshness check must reuse the real generation path. This is why a separate `.context/` staleness checker is rejected (Considered Alternatives).
- **Strategic vision.** Mid-term goals: test coverage and stricter typing. This plan is mainly coverage, ingress hardening and doc-tooling hygiene.

## Summary
This rework plan addresses the actionable items in `docs/agents/plans/2026-10-19-media-document-apis-rework-1/synthesis.md`:
- **Thumbnail-size defect:** removes the defective `DocumentTrait::getMaxThumbnailSize()` default by making the method abstract on the trait and completing the `DocumentInterface` contract.
- **`post_max_size` gap:** makes the request-body ceiling aware of the server's own `post_max_size`, so clients are told the limit that actually applies. Adds the distinct "discarded by `post_max_size`" message that rework-1's plan specified but did not ship. Makes the oversized-body live test report an under-provisioned server as a skip with a clear reason, instead of a misleading failure.
- **Rejection logging:** writes a diagnostic log entry for every rejected request body, as `authorize()` already does for denied requests. Entries are written only when logging is enabled; they are not a durable audit store.
- **Test gaps:** closes the two low-priority coverage gaps (`RequestBodyReader` exact boundaries; HTML in `keywords`).
- **`.context/` staleness:** already resolved before the run by a dedicated workspace-architect pass (see Assumptions). The freshness timestamp is now written by both build chains, the manifest no longer recommends raw `ctx generate`, and the drift lifecycle is documented. This plan only re-verifies it in the gate.
- **Documentation:** records the synthesis's reusable test patterns in the testing manifest and corrects the Media API README's `681004` entry.

**Two synthesis claims are corrected by research:**
- **The `getMaxThumbnailSize()` defect is unreachable, not a live `TypeError`.** Every concrete consumer (`Image` via `ImageDocumentTrait`, `PDF`, `Upload`) overrides it, and `Application_Media_Document` re-declares it abstract. It is still promoted: it is wrong, dead code and a trap for the next `DocumentTrait` consumer.
- **The local `post_max_size` gap is partly a code defect.** The 413 response for a discarded body reports the framework ceiling as the limit, although the body was rejected for exceeding the lower `post_max_size`. The message rework-1 specified for that case was never implemented, and its test asserts no message.

## Architectural Context
- **Media documents.**
  - `src/classes/Application/Media/DocumentTrait.php` is the shared implementation for `Application_Media_Document` (`src/classes/Application/Media/Document.php`; subclasses `Document/Image.php`, `Document/PDF.php`) and `Application_Uploads_Upload` (`src/classes/Application/Uploads/Upload.php`; subclass `LocalFileUpload.php`).
  - Its `getThumbnailDefaultSize()` (L205–L223) is the only internal caller of `getMaxThumbnailSize()`.
  - `src/classes/Application/Media/DocumentInterface.php` declares `getThumbnailDefaultSize()` but not `getMaxThumbnailSize()`.
- **Request-body ingress.**
  - `BaseAPIMethod::getRequestBody()` (L504) reads through `createRequestBodyReader()` (L567, default `RequestBodyReader::fromGlobals(AppConfig::getAPIMaxRequestBodySize())`).
  - It converts `RequestBodyException` in `handleRequestBodyException()` (L536–L551): TOO_LARGE/DISCARDED → 182802/413 with one shared message; READ_FAILED → 182801.
  - `src/classes/Application/API/Request/RequestBodyReader.php` holds the bounded read.
  - `authorize()` (L216–L257) logs each rejection via `$this->log()` before sending the error.
- **Live-HTTP tests.** These are `#[Group('live-http')]` classes excluded by `phpunit.xml`. `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php::test_oversizedBodyIsRejectedOverHTTP()` posts ceiling + 1 bytes.
- **CTX tooling.**
  - `composer build` = `ComposerScripts::build` → `ctx generate` → `ComposerScripts::postCtxGenerate`.
  - `composer build-docs` = `ComposerScripts::buildDocs` → `ctx generate` → `ComposerScripts::postCtxGenerate`.
  - `.context/generated-at.txt` is written by `ComposerScripts::postCtxGenerate()` via the public static `doUpdateContextGenerateDate(string $contextFolder)`, so by both chains. This was changed before the run (see Assumptions).
  - `.context/` is committed only on `main`, pre-release (`AGENTS.md` §1/§4, `.github/skills/framework-release-check/SKILL.md`).

## Approach / Architecture

### A. `getMaxThumbnailSize()` becomes an explicit contract
- `DocumentTrait::getMaxThumbnailSize()` loses its body and becomes `abstract public function getMaxThumbnailSize() : int;`, with a docblock: the largest thumbnail width the document's source can produce without upscaling, which `getThumbnailDefaultSize()` uses to cap the preferred size.
- `DocumentInterface` gains `public function getMaxThumbnailSize() : int;`, completing the contract that `getThumbnailDefaultSize()` already depends on.
- `Application_Media_Document`'s own abstract re-declaration (`Document.php` L358) is removed. The trait now carries it, and it only ever existed to mask the defective default.
- `ImageDocumentTrait`, `PDF` and `Upload` keep their implementations unchanged.

### B. Effective request-body ceiling
- **New** pure static `RequestBodyReader::resolveEffectiveCeiling(int $configuredCeiling, string|false $postMaxSize) : int`:
  - If `$postMaxSize` is `false`, `''`, or does not match `/^\s*\d+\s*[kmg]?\s*$/i`, return `$configuredCeiling`. This guard comes before `ini_parse_quantity()`, which warns on malformed input, so no warning can surface.
  - Otherwise `$serverLimit = ini_parse_quantity(trim($postMaxSize))`. If `$serverLimit <= 0` (`0` disables the PHP limit), return `$configuredCeiling`.
  - Return `min($configuredCeiling, $serverLimit)`.
  - An inline comment records that the failure direction is deliberate: a value the guard rejects (including ini forms PHP itself accepts, such as a sign or a `0x` prefix) falls back to the configured ceiling, so the limit may be over-reported in that rare case, and the DISCARDED branch still covers it.
- **Unchanged** `RequestBodyReader::fromGlobals(int $maxBytes)`: it stays a literal-ceiling factory. Its signature, parameter name and behaviour do not change; its docblock gains one sentence stating that it applies `$maxBytes` as given and that callers wanting the server-aware limit pass it through `resolveEffectiveCeiling()` first. The reader stays the mechanism and carries no server-limit policy.
- **Modified** `BaseAPIMethod::createRequestBodyReader()` (the single production caller of `fromGlobals()`, and the overridable seam that already encodes the ceiling policy):
  - It returns `RequestBodyReader::fromGlobals(RequestBodyReader::resolveEffectiveCeiling(AppConfig::getAPIMaxRequestBodySize(), ini_get('post_max_size')))`.
  - Its docblock explains that the default reader's limit is the effective ceiling: the lower of the framework ceiling and the server's `post_max_size`.
- **Effect.**
  - A body whose `Content-Length` exceeds `post_max_size` is now rejected by `read()`'s existing declared-length check as TOO_LARGE, with `maxRequestBodySize` = the limit that actually applies.
  - The DISCARDED branch remains as a defensive fallback, for a server limit the ini value does not reflect.
  - `AppConfig::getAPIMaxRequestBodySize()` keeps meaning "the configured framework ceiling" and is not changed.

### C. Rejection handling in `BaseAPIMethod`
`handleRequestBodyException()` is restructured into one branch per exception code:
- **Every branch first logs** via `$this->log()`, using the `authorize()` pattern and passing values as arguments:
  - `'Request body rejected for method [%s]: [%s]. Declared length [%s], limit [%s] bytes.'`
  - The method name comes from `$this->getMethodName()`. No API key ID is logged: the body is read during parameter validation, which `_process()` runs before `authorize()`, so no authenticated caller identity exists yet.
  - Reason labels: `too large`, `discarded by post_max_size`, `read failed`.
  - The declared length is rendered as `'unknown'` when `null`.
  - These are diagnostic entries: `Loggable::log()` writes nothing when logging is disabled, so the documentation describes them as "logged when logging is enabled", never as an audit trail.
- **READ_FAILED** → 182801, unchanged.
- **TOO_LARGE** → 182802/413, unchanged message `'The request body exceeds the maximum allowed size of [%d] bytes.'`, plus `maxRequestBodySize`.
- **DISCARDED** → 182802/413, the same `maxRequestBodySize` data, and the distinct message from rework-1's plan: `'The request body ([%s] bytes declared) was discarded by the server because it exceeds the server upload limit (post_max_size).'`, with the declared length as the argument.
- The three reason labels stay literals inside the method, not public constants, since nothing outside reads them.

### D. Live-HTTP oversized test that diagnoses its environment
`test_oversizedBodyIsRejectedOverHTTP()` is updated:
1. It detects the PHP request-startup signature in the response body (`'POST Content-Length of'`). If found, it calls `markTestSkipped()` with a message stating that the webserver's `post_max_size` is below the posted size and `display_errors` is on, pointing to `docs/agents/project-manifest/testing.md` §"Live-HTTP Tests".
2. Otherwise it asserts:
   - 413, a JSON content type, and code 182802, as today
   - **new:** `data.maxRequestBodySize` is a positive `int` no greater than `AppConfig::getAPIMaxRequestBodySize()`. The server-side effective limit may legitimately be lower.

The signature is produced only by PHP before the application runs, so the skip can never mask a framework regression.

### E. CTX freshness timestamp (done before the run)
A workspace-architect pass completed before this run:
- It moved the `generated-at.txt` write into `ComposerScripts::postCtxGenerate()` (`doUpdateContextGenerateDate()`), covered by `tests/AppFrameworkTests/Composer/ContextGenerateDateTest.php`.
- It added `/ctx-*.log` to `.gitignore`.
- It documented the drift lifecycle in `docs/agents/project-manifest/context-documentation.md` §"Freshness and drift", `AGENTS.md` §1/§4, `src/classes/Application/API/OpenAPI/README.md`, `docs/agents/references/module-context-reference.md`, `.github/skills/framework-release-check/SKILL.md`, the root `README.md` and `changelog.md`.

This plan only re-verifies it (step 9).

## Rationale
- **Abstract over fixing the expression.** Changing `$this-$this->…` to `$this->…` would leave a concrete default that no consumer may use (`Document` masks it, everything overrides it). Its "source image width" semantics are also wrong for non-image documents, where `PDF`/`Upload` return 64. The method is abstract in meaning, so the code should say so. That removes the defect class, not just the typo.
- **Capping at `post_max_size` instead of only fixing the message.** The client action for a 413 is "send at most `maxRequestBodySize` bytes". If that number is the framework ceiling while the server enforces 8 MiB, a client that shrinks its body to 10 MiB fails again. Reading `post_max_size` costs one `ini_get()` per request. The pure helper keeps it testable. The DISCARDED branch stays as defence in depth.
- **Resolving the effective ceiling in `createRequestBodyReader()`, not `fromGlobals()`.** The factory method is where the ceiling policy already lives (it chooses `AppConfig::getAPIMaxRequestBodySize()`), so "framework ceiling vs server limit" belongs there too. `fromGlobals()` keeps its existing contract, which avoids silently changing the meaning of its parameter for any other caller, and `RequestBodyReader` stays a dependency-free mechanism.
- **Logging in `handleRequestBodyException()`.** It is the single conversion point for all three rejection types, and it mirrors the existing `authorize()` logging pattern. That uses the method's own logger and adds no new dependency. The entries are diagnostic: they exist only when logging is enabled and carry the method name but no caller identity, so the plan does not present them as an audit trail.
- **Skip instead of committed `php_value`.** A `php_value` directive breaks PHP-FPM/CGI servers with HTTP 500, so it cannot be committed. A skip that names the cause turns an environment problem into a readable signal, while a real regression still fails.

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| `getMaxThumbnailSize()` defect | Abstract on `DocumentTrait`, declared on `DocumentInterface`, redundant abstract in `Document.php` removed | (a) Fix the stray token only; (b) delete the trait method and keep `Document.php`'s abstract | (a) keeps a default that is wrong for non-images and that nobody may use. (b) leaves `Application_Uploads_Upload` uncontracted by the trait, and the interface stays incomplete. |
| Reported limit when the server limit is lower | Effective ceiling = min(framework ceiling, `post_max_size`) applied to the default reader | (a) Only fix the DISCARDED message; (b) add a separate `serverPostMaxSize` data key; (c) `AppConfig` warns at boot when `post_max_size` < ceiling | (a) still reports a limit the client cannot reach. (b) makes clients compute the minimum themselves. (c) warns operators but not clients, and adds boot-time I/O to every request type. |
| Where the effective ceiling is resolved | In `BaseAPIMethod::createRequestBodyReader()`; `fromGlobals()` stays a literal-ceiling factory | (a) Inside `RequestBodyReader::fromGlobals()`, renaming its parameter to `$configuredCeiling`; (b) a sibling getter on `AppConfig` next to `deriveAPIMaxRequestBodySize()` | (a) changes the meaning of an existing parameter with no type-level signal and puts policy into the I/O class. (b) puts a per-request ini read into boot-time configuration state. The factory is the single production caller and already holds the policy (design review, Decision 1). |
| Parsing `post_max_size` | `ini_parse_quantity()` behind a format guard | `AppUtils\FileHelper\UploadFileSizeInfo::getFileSize()`; `ConvertHelper::size2bytes()` | `UploadFileSizeInfo` also folds in `upload_max_filesize` (irrelevant to raw JSON bodies) and caches statically. `size2bytes()` targets "50 MB" notation and does not document ini shorthand. The native PHP 8.2+ parser is exact for ini values. |
| Diagnostic logging of rejected bodies | `$this->log()` in `handleRequestBodyException()`, with the method name, documented as "logged when logging is enabled" | (a) `Application_ErrorLog` entry; (b) log inside `RequestBodyReader` | (a) error-log entries are reserved for unexpected throwables (insight `af333a25`), and a rejected body is an expected client error. (b) would give the I/O class a logging dependency and duplicate the method context. |
| Live test vs. under-provisioned server | `markTestSkipped()` on the PHP startup-warning signature | (a) Commit `tests/application/.htaccess` with `php_value post_max_size`; (b) a probe endpoint that reports server ini values; (c) leave as is | (a) causes HTTP 500 under PHP-FPM. (b) adds an info-disclosure surface to the test application. (c) keeps a misleading failure. |
| `.context/` staleness (implemented before the run) | Move the timestamp write to `postCtxGenerate()` and document the drift lifecycle | A separate `composer check-context` freshness checker comparing source mtimes to `generated-at.txt` | Insight `39ca30b5`: a separate staleness check drifts from the real generator. Feature-branch drift is expected by design (`.context/` is regenerated and committed on `main` pre-release), so a checker would mostly report non-problems. |

## Pattern Alignment
- **Follows** pure static helpers for global state: `AppConfig::deriveAPIMaxRequestBodySize()` (`src/classes/Application/ConfigSettings/AppConfig.php` L222) and `MediaResponseHeaders::send(..., ?bool $headersSent)` (`src/classes/Application/Media/Delivery/MediaResponseHeaders.php`). `RequestBodyReader::resolveEffectiveCeiling()` takes the ini value as a parameter.
- **Follows** "factory = policy, reader = mechanism": the overridable `BaseAPIMethod::createRequestBodyReader()` (`src/classes/Application/API/BaseMethods/BaseAPIMethod.php` L567–L570) already chooses the ceiling, so it also applies the server limit; `RequestBodyReader::fromGlobals()` keeps its contract.
- **Follows** rejection logging via `$this->log()` before `errorResponse()->send()`: `BaseAPIMethod::authorize()` (`src/classes/Application/API/BaseMethods/BaseAPIMethod.php` L216–L257).
- **Follows** `// region: AC-NN — …` test grouping (`tests/AppFrameworkTests/API/RequestBodyCeilingTest.php`) in every test class this plan adds or extends.
- **Follows** `MediaTestCase` / `ImageMediaTestTrait` fixtures for media tests (`tests/AppFrameworkTestClasses/MediaTestCase.php`).
- **Follows** legacy-class style in `Document.php` and `Upload.php` (AGENTS.md §4): only the abstract declaration line is removed, with no other edits.
- No departures.

## Structural Improvements

| Structure | Observation | Decision | Reason |
|-----------|-------------|----------|--------|
| `src/classes/Application/Media/DocumentTrait.php` `getMaxThumbnailSize()` | Concrete default that no consumer may use, and it is defective | Promoted to step 1 | The method is semantically abstract. Making it abstract removes the defect class. |
| `src/classes/Application/Media/DocumentInterface.php` | `getThumbnailDefaultSize()` is in the contract but its dependency `getMaxThumbnailSize()` is not | Promoted to step 1 | Completes the interface at no cost; every implementer already has the method. |
| `src/classes/Application/Media/Document.php` L358 | Abstract re-declaration that exists only to mask the trait default | Promoted to step 1 | Redundant once the trait is abstract. Keeping two declarations invites them to diverge. |
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` `createRequestBodyReader()` (with the new `RequestBodyReader::resolveEffectiveCeiling()`) | The default reader is blind to the server's lower `post_max_size`, so the reported limit is wrong | Promoted to step 3 | The reported limit is the client's only actionable datum. Resolved at the factory so `RequestBodyReader::fromGlobals()` keeps its literal-ceiling contract. |
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` `handleRequestBodyException()` | TOO_LARGE/DISCARDED share one message, and no rejection is logged | Promoted to steps 4–5 | Closes rework-1's unshipped message requirement and the synthesis's audit-trail item (delivered as diagnostic logging) at the single conversion point. |
| `tests/AppFrameworkTests/API/Request/RequestBodyReaderTest.php` | No exact-boundary cases | Promoted to step 2 | Synthesis follow-up item (QA, WP-003). |
| `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php` | Cannot tell an environment shortfall from a regression | Promoted to step 6 | Synthesis follow-up item (local `post_max_size` gap). |
| `src/classes/Application/Composer/ComposerScripts.php` `updateContextGenerateDate()` | Timestamp written only by `build()`, before CTX runs | Promoted, done before the run | Mechanical root cause of the recurring stale-docs findings. Fixed by the pre-run workspace-architect pass, and re-verified in step 9. |
| `.gitignore` | No entry for CTX run logs (`ctx-2026-10-19.log` sits untracked in the root) | Promoted, done before the run | Prevents accidental commits of a generated artefact. Fixed by the pre-run workspace-architect pass. |
| `src/classes/Application/API/Parameters/Type/Base64FileParameter.php` `resolveValue()` | An empty `fileContent` resolves to absent, so `681004` is unreachable through the API | Rejected (documentation only, step 8) | Consistent with the framework-wide empty-is-absent semantics (`RequestParam::get()`, insight `dfd00d1a`). The client still gets a clear "missing parameter" error, and `681004` remains defence in depth for future non-base64 sources. Changing it would split the parameter semantics for one type. |
| `src/classes/Application/Media/API/MediaMetadataAPITrait.php` `validateMediaMetadata()` | `name` is not HTML-checked, only description and keywords | Rejected | Outside the synthesis items. Whether `name` may carry markup is a product decision, not a symmetry fix. This plan's step 7 tests only the existing keywords contract. |
| `tests/application/.htaccess` (untracked `php_value post_max_size 32M`) | Local workaround from rework-1 | Rejected | `php_value` is not portable (HTTP 500 under PHP-FPM). Server configuration stays a human action, and step 6 makes the test self-diagnosing instead. |

## Detailed Steps

1. **Make `getMaxThumbnailSize()` an explicit contract (Media).**
   - In `src/classes/Application/Media/DocumentTrait.php`, replace the concrete `getMaxThumbnailSize()` (L34–L37) with `abstract public function getMaxThumbnailSize() : int;`, with a docblock describing its role for `getThumbnailDefaultSize()`.
   - In `src/classes/Application/Media/DocumentInterface.php`, add `public function getMaxThumbnailSize() : int;` next to `getThumbnailDefaultSize()`.
   - In `src/classes/Application/Media/Document.php`, remove the now-redundant `abstract public function getMaxThumbnailSize() : int;` (L358).
   - Add **new** `tests/AppFrameworkTests/Media/MaxThumbnailSizeTest.php` (extends `MediaTestCase`; see Test Plan).
2. **Add exact-boundary coverage to `RequestBodyReaderTest`.** In `tests/AppFrameworkTests/API/Request/RequestBodyReaderTest.php`, add a `// region: Exact boundaries` with the five cases listed in the Test Plan. No production change.
3. **Effective request-body ceiling.**
   - In `src/classes/Application/API/Request/RequestBodyReader.php`, add `public static function resolveEffectiveCeiling(int $configuredCeiling, string|false $postMaxSize) : int` per §B, including the failure-direction comment.
   - Leave `fromGlobals()`'s signature and behaviour unchanged; add the one docblock sentence from §B (it applies `$maxBytes` as given; use `resolveEffectiveCeiling()` for the server-aware limit), and mention the helper in the class docblock.
   - Change `BaseAPIMethod::createRequestBodyReader()` to pass `RequestBodyReader::resolveEffectiveCeiling(AppConfig::getAPIMaxRequestBodySize(), ini_get('post_max_size'))` to `fromGlobals()`, and update its docblock to say the default reader's limit is the effective ceiling.
   - Update `RequestBodyCeilingTest::test_defaultReaderUsesBootedCeiling()` to expect `RequestBodyReader::resolveEffectiveCeiling(AppConfig::getAPIMaxRequestBodySize(), ini_get('post_max_size'))`. Under the CLI's `8M`, that is 8,388,608.
   - Add the resolver tests (Test Plan).
4. **Distinct discarded-body message.** In `BaseAPIMethod::handleRequestBodyException()`:
   - Split TOO_LARGE and DISCARDED into separate branches per §C. Both keep code 182802, HTTP 413 and `maxRequestBodySize`.
   - DISCARDED gets the `post_max_size` message, with the declared length passed as an argument.
   - Extend `RequestBodyCeilingTest::test_discardedBodyYields182802And413()` to assert the message, and add a negative assertion to the oversized test that its message does not mention `post_max_size`.
5. **Diagnostic logging for rejected bodies.**
   - In the same method, log each rejection via `$this->log()` before sending, per §C. The method name, the reason label (`too large`, `discarded by post_max_size`, `read failed`), the declared length or `unknown`, and the limit are passed as arguments.
   - Update the method's docblock to state that rejections are logged when logging is enabled.
   - Add logging assertions to `RequestBodyCeilingTest` (Test Plan). Enable the logger (`AppFactory::createLogger()->setLoggingEnabled(true)`, then `clearLog()`) before creating the method, and restore the previous state in `tearDown()`.
6. **Self-diagnosing live oversized test.** Update `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php::test_oversizedBodyIsRejectedOverHTTP()` per §D: skip on the `'POST Content-Length of'` signature with a message naming `post_max_size`, `display_errors` and testing.md; otherwise also assert `maxRequestBodySize` is a positive int ≤ `AppConfig::getAPIMaxRequestBodySize()`. Update the class docblock to match.
7. **HTML-in-keywords symmetry test.** Add `test_htmlInKeywordsIsRejected()` to `tests/AppFrameworkTests/Media/API/MediaMetadataPieceTest.php`: set `PARAM_KEYWORDS` to `'<i>tag</i>, other'` with no description, then assert an `ErrorResponsePayload` with code `MediaMetadataAPIInterface::VALIDATION_HTML_NOT_ALLOWED`.
8. **Module docs, testing manifest, changelog.**
    - `src/classes/Application/API/README.md` §"Request body ceiling":
      - the default reader's limit (from `createRequestBodyReader()`) is the effective ceiling, the lower of `AppConfig::getAPIMaxRequestBodySize()` and `post_max_size`; `RequestBodyReader::fromGlobals()` applies its argument as given (update the L107 reference accordingly)
      - `maxRequestBodySize` reports that effective limit
      - a discarded body gets its own message
      - rejections are written to the application log (with the method name) when logging is enabled; these are diagnostic entries, not an audit trail
    - `src/classes/Application/Media/API/README.md`:
      - §"Size limits" / `post_max_size` guidance: the effective-limit behaviour, and that the operator rule (`post_max_size` ≥ ceiling) still holds so the configured ceiling is reachable
      - error-code table row `681004`: add "not reachable via `fileContent`. An empty value reports as a missing parameter (183003), and 681004 guards non-base64 content sources"
    - `docs/agents/project-manifest/testing.md`:
      - §"Live-HTTP Tests", the `JSONBodyParamsLiveHTTPTest` row: the skip-on-startup-warning behaviour and its remedy
      - new short subsection "Reusable test patterns", one line each: overridable protected factory to exercise boot-time limits at test scale (`BaseAPIMethod::createRequestBodyReader()`); `error_reporting() & $errno` check to assert that a suppressed warning stayed suppressed (`RequestBodyReaderTest`); `// region: AC-NN` grouping for multi-AC test classes
    - `docs/agents/project-manifest/constraints.md` §"Request Body Reading" (L330 bullet): reword so that `getRequestBody()` enforces the effective ceiling (the lower of `AppConfig::getAPIMaxRequestBodySize()` and `post_max_size`, resolved in `createRequestBodyReader()`), and that a `post_max_size`-discarded body keeps code 182802/HTTP 413 but gets its own message. Keep the rule itself ("read only through `getRequestBody()`") unchanged. The `module-glossary.md` `RequestBodyReader` row is auto-generated and needs no manual edit.
    - `changelog.md`, under the existing `v7.7.0` WIP entry:
      - Media: `getMaxThumbnailSize()` is now an explicit abstract contract on `DocumentTrait` and `DocumentInterface` (removes a defective, unreachable default)
      - API: the request-body limit is capped at the server's `post_max_size`, so `maxRequestBodySize` reports the limit that actually applies; a body discarded by `post_max_size` gets its own message; rejected bodies are logged when logging is enabled
9. **Verification gate.** Run:
    - `composer analyze` (0 errors)
    - `composer test-filter -- API`
    - `composer test-filter -- Media`
    - `composer test-file -- tests/AppFrameworkTests/Composer/ContextGenerateDateTest.php`
    - `composer test-file -- tests/AppFrameworkTests/API/OpenAPI/BuildPipelineTest.php`
    - `composer test-group -- live-http`: `test_oversizedBodyIsRejectedOverHTTP` must pass or skip with the documented reason; every other live test must pass
    - `composer build-docs`: confirm `.context/generated-at.txt` carries the run's timestamp (regression check on the pre-run CTX fix). Do not commit `.context/` (feature branch).
    - `git check-ignore ctx-2026-10-19.log` (read-only): still matched by `.gitignore`.

## Dependencies
- Step 4 depends on step 3: both edit the request-body path, and step 4's tests assume the effective-ceiling semantics.
- Step 5 depends on step 4, since it edits the same method's restructured branches.
- Step 6 depends on steps 3–4, because its `maxRequestBodySize` assertion relies on the effective ceiling.
- Step 2 is independent, and is best done before step 3 so the boundary tests pin the current `read()` behaviour first.
- Steps 1 and 7 are independent of each other and of steps 2–6.
- Step 8 depends on steps 1, 3–6.
- Step 9 depends on all prior steps.

## Required Components
- `src/classes/Application/Media/DocumentTrait.php` (modified)
- `src/classes/Application/Media/DocumentInterface.php` (modified)
- `src/classes/Application/Media/Document.php` (modified)
- `src/classes/Application/API/Request/RequestBodyReader.php` (modified)
- `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` (modified)
- `tests/AppFrameworkTests/Media/MaxThumbnailSizeTest.php` (**new**)
- `tests/AppFrameworkTests/API/Request/RequestBodyReaderTest.php` (modified)
- `tests/AppFrameworkTests/API/RequestBodyCeilingTest.php` (modified)
- `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php` (modified)
- `tests/AppFrameworkTests/Media/API/MediaMetadataPieceTest.php` (modified)
- Docs: `docs/agents/project-manifest/testing.md`, `docs/agents/project-manifest/constraints.md`, `src/classes/Application/API/README.md`, `src/classes/Application/Media/API/README.md`, `changelog.md`

## Assumptions
- Rework-1's uncommitted implementation stays in the working tree as described in the research brief. Line numbers cited here refer to that tree.
- The PHP runtime is 8.4+, so `ini_parse_quantity()` is available. It was verified on the local CLI (PHP 8.5.11).
- PHP's request-startup message for an over-`post_max_size` body contains `POST Content-Length of` (PHP 8.x wording). The implementer confirms it against the local server's output when running step 9.
- The test database is available for `MaxThumbnailSizeTest`'s upload case (`LocalFileUpload` inserts into `uploads`), as for every existing `MediaTestCase`.
- **The CTX fixes are already in the working tree.** A workspace-architect pass on 2026-10-19 made them before the run, and they passed `composer analyze`, `ContextGenerateDateTest` and `BuildPipelineTest`. They touched `ComposerScripts.php`, `ContextGenerateDateTest.php`, `.gitignore`, `context-documentation.md`, `AGENTS.md`, `API/OpenAPI/README.md`, `module-context-reference.md`, `framework-release-check/SKILL.md`, the root `README.md`, and the `v7.7.0` changelog "Build" bullet. No step in this plan modifies those CTX aspects again.
- `tests/application/storage/api/method-index.json` shows as deleted after that pass's cache clear. `APIMethodIndex` rebuilds a missing index on first use (`src/classes/Application/API/Collection/APIMethodIndex.php` L138–L140), so the first API test run restores it. No step is needed.
- The local untracked `tests/application/.htaccess` may or may not be present. Step 6's test behaves correctly either way, and the file is not committed.

## Constraints
- `array()` syntax only. Run `composer dump-autoload` after adding class files. Use `composer analyze`, never `vendor/bin/phpstan` (AGENTS.md §4, §7).
- `.context/` must not be committed on this feature branch.
- Error codes 182801/182802, HTTP 413, and the `maxRequestBodySize` key name are a published contract and stay unchanged.
- Legacy classes (`Document.php`, `Upload.php`) receive only the minimal edits listed.

## Out of Scope
- Committing the synthesis's strategic recommendations (bounded-read ordering, headers over sanitisation, protected-factory seam, generated-docs pointer) to the knowledge base. That belongs to the Ledger Knowledge Archiver after this plan's synthesis. Step 8 records only the testing patterns in `testing.md`.
- HTML validation of the media `name` field (see Structural Improvements).
- Changing `Base64FileParameter`'s empty-is-absent semantics.
- A `.context/` staleness checker command (see Considered Alternatives).
- Web-server body limits (`LimitRequestBody`, nginx `client_max_body_size`). These are operator configuration.

## Human Actions

| # | Action | When | Why an agent cannot do it |
|---|--------|------|---------------------------|
| 1 | Set PHP `post_max_size` (and any web-server body limit) on every target install to at least `AppConfig::getAPIMaxRequestBodySize()` (≈ 14.3 MiB with the default 10 MiB file limit), so the configured ceiling is the limit clients actually get | After the run | Server configuration is outside the repository and the agents' access. |
| 2 | Release v7.7.0 (merge, regenerate and commit `.context/` on `main`, tag), following `.github/skills/framework-release-check/SKILL.md` | After the run | Releases, merges and `main`-branch commits are the user's responsibility. |

## Acceptance Criteria
- AC-01: `DocumentTrait::getMaxThumbnailSize()` is abstract (no body), `DocumentInterface` declares `getMaxThumbnailSize() : int`, and `Application_Media_Document` no longer re-declares it. PNG/JPG images return their pixel width, a PDF document returns 64, and a PNG upload returns its pixel width.
- AC-02: `getThumbnailDefaultSize($preferred)` returns `min(getMaxThumbnailSize(), $preferred)` for raster documents, and `$preferred` for SVG (vector).
- AC-03: `RequestBodyReader::resolveEffectiveCeiling()` returns `min(configured, parsed post_max_size)` for valid positive values. Against a configured ceiling of 15,029,589: `'8M'` → 8,388,608, `'512K'` → 524,288, `'1048576'` → 1,048,576 (server limit lower), and `'1G'` → 15,029,589 (server limit higher, so the configured ceiling applies). It returns the configured ceiling for `false`, `''`, `'0'`, and malformed values (`'abc'`, `'-1'`, `'8 MB'`), without surfacing a PHP warning.
- AC-04: The default `BaseAPIMethod::createRequestBodyReader()` produces a reader whose `getMaxBytes()` equals `resolveEffectiveCeiling(AppConfig::getAPIMaxRequestBodySize(), ini_get('post_max_size'))`. `RequestBodyReader::fromGlobals($n)` keeps its contract and produces a reader whose `getMaxBytes()` equals `$n`.
- AC-05: `RequestBodyReader::read()` returns the body when `declaredLength == maxBytes`, and when stream length `== maxBytes` with no declared length. It returns `''` for `maxBytes = 0` with an empty stream, and for `declaredLength = 0` with an empty stream (not DISCARDED). It throws TOO_LARGE for `maxBytes = 0` with a one-byte stream.
- AC-06: A DISCARDED body yields 182802/HTTP 413, `maxRequestBodySize` in the data, and a message that names the declared length and `post_max_size`. A TOO_LARGE body keeps its existing message, which does not mention `post_max_size`. READ_FAILED keeps 182801.
- AC-07: With logging enabled, each of the three rejection types writes one log entry through the method's logger that contains `Request body rejected`, the method name, the reason label, the declared length (or `unknown`) and the limit.
- AC-08: `test_oversizedBodyIsRejectedOverHTTP` is skipped with a message naming `post_max_size` when the response contains the PHP request-startup signature. Otherwise it asserts 413, JSON, 182802, and a positive `maxRequestBodySize` ≤ `AppConfig::getAPIMaxRequestBodySize()`.
- AC-09: HTML in the media `keywords` field alone is rejected with `VALIDATION_HTML_NOT_ALLOWED` (681211).
- AC-10 (satisfied before the run; re-verified in step 9): `.context/generated-at.txt` is written by `ComposerScripts::postCtxGenerate()` (so by both `composer build` and `composer build-docs`) and no longer by `build()`. `ComposerScripts::doUpdateContextGenerateDate(string $contextFolder)` is public static and writes a parseable ISO timestamp into the given folder.
- AC-11 (satisfied before the run; re-verified in step 9): `.gitignore` ignores `/ctx-*.log`.
- AC-12: The documentation listed in Documentation Updates is updated as described.
- AC-13: `composer analyze` reports 0 errors. `composer test-filter -- API` and `composer test-filter -- Media` pass. `BuildPipelineTest` and `ContextGenerateDateTest` pass. The `live-http` group passes, with `test_oversizedBodyIsRejectedOverHTTP` passing or skipped for the documented reason.

## Testing Strategy
- Unit-level tests for the pure helper (`resolveEffectiveCeiling()`) and the reader boundaries use temp-file streams, following `RequestBodyReaderTest`'s existing set-up and tear-down.
- In-process API tests use the existing `RequestBodyCeilingTestMethod` harness (`setTestReader()`, `exposeCreateRequestBodyReader()`) and `processReturn()`, asserting codes, status, messages, data and log content.
- Media tests run against real documents and uploads through `MediaTestCase` fixtures.
- One live-HTTP test is made environment-aware but keeps asserting the full contract whenever the environment allows.
- The pre-run CTX fix is re-verified by its existing test and the gate commands.
- Static analysis covers the interface/trait/abstract changes (PHPStan reports any implementer missing `getMaxThumbnailSize()`).

## Test Plan
- `tests/AppFrameworkTests/Media/MaxThumbnailSizeTest.php` (**new**) — AC-01, AC-02:
  - `test_traitDeclaresMethodAbstract`: `ReflectionMethod(DocumentTrait::class, 'getMaxThumbnailSize')->isAbstract()` is true, and `ReflectionClass(Application_Media_DocumentInterface::class)->hasMethod('getMaxThumbnailSize')` is true.
  - `test_rasterImageReturnsPixelWidth`: `createTestPNGImage()` and `createTestJPGImage()` each return `getWidth()`.
  - `test_pdfReturnsTypeIconSize`: a document created via `$this->media->createFromFile()` from a temp file named `*.pdf` with content `%PDF-1.4` returns `DEFAULT_TYPE_ICON_THUMBNAIL_SIZE`. The test deletes it afterwards.
  - `test_imageUploadReturnsPixelWidth`: `$this->uploads->addFromLocalFile(FileInfo::factory($this->getExampleImagePath()))` returns the PNG's pixel width.
  - `test_defaultSizeIsCappedByMaxSize`: for a PNG, `getThumbnailDefaultSize(PHP_INT_MAX)` equals its width and `getThumbnailDefaultSize(1)` equals 1.
  - `test_vectorDefaultSizeUsesPreferred`: for an SVG, `getThumbnailDefaultSize(300)` equals 300.
- `tests/AppFrameworkTests/API/Request/RequestBodyReaderTest.php` — AC-05, AC-03:
  - `test_declaredLengthEqualToCeilingIsAccepted` (10-byte stream, maxBytes 10, declared 10 → body)
  - `test_streamLengthEqualToCeilingIsAccepted` (10-byte stream, maxBytes 10, declared null → body)
  - `test_zeroCeilingAcceptsEmptyBody` (empty stream, maxBytes 0 → `''`)
  - `test_zeroCeilingRejectsOneByte` (1-byte stream, maxBytes 0 → TOO_LARGE)
  - `test_zeroDeclaredLengthWithEmptyBodyIsNotDiscarded` (empty stream, declared 0 → `''`)
  - `test_resolveEffectiveCeilingTakesLowerServerLimit` (against configured 15,029,589: `'8M'` → 8388608, `'512K'` → 524288, `'1048576'` → 1048576)
  - `test_resolveEffectiveCeilingKeepsConfiguredWhenServerLimitIsHigher` (`'1G'` against configured 15,029,589 → 15029589)
  - `test_resolveEffectiveCeilingIgnoresUnusableValues` (`false`, `''`, `'0'`, `'abc'`, `'-1'`, `'8 MB'` → configured; wrapped in the existing `error_reporting() & $errno` handler pattern to assert no warning surfaces)
  - **new** `test_fromGlobalsUsesCeilingAsGiven` (`RequestBodyReader::fromGlobals(10000)->getMaxBytes()` is `10000` regardless of `post_max_size`; pins the unchanged factory contract, AC-04)
- `tests/AppFrameworkTests/API/RequestBodyCeilingTest.php` — AC-04, AC-06, AC-07:
  - `test_defaultReaderUsesBootedCeiling` updated to expect the effective ceiling from `createRequestBodyReader()` via `exposeCreateRequestBodyReader()` (AC-04)
  - `test_discardedBodyYields182802And413` extended: message contains `post_max_size` and `100` (AC-06)
  - `test_oversizedBodyYields182802And413` extended: message does not contain `post_max_size` (AC-06)
  - **new** `test_oversizedBodyIsLogged`, `test_discardedBodyIsLogged`, `test_readFailureIsLogged`: logger enabled and cleared, then `implode("\n", AppFactory::createLogger()->getLog())` contains `Request body rejected`, the test method's name (`getMethodName()`), the reason label, the limit, and the declared length or `unknown` (AC-07)
- `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php` — `test_oversizedBodyIsRejectedOverHTTP` updated per §D (AC-08).
- `tests/AppFrameworkTests/Media/API/MediaMetadataPieceTest.php` — **new** `test_htmlInKeywordsIsRejected` (AC-09).
- AC-10, AC-11: covered by the existing `tests/AppFrameworkTests/Composer/ContextGenerateDateTest.php` (added before the run), plus step 9's `composer build-docs` and `git check-ignore` checks.
- Documentation review in the documentation stage — AC-12.
- Step 9 gate commands — AC-13.

## Documentation Updates
- `docs/agents/project-manifest/testing.md`: §"Live-HTTP Tests", the `JSONBodyParamsLiveHTTPTest` row (skip behaviour); new "Reusable test patterns" subsection. This applies the manifest rule "Testing infrastructure changed → `testing.md`".
- `docs/agents/project-manifest/constraints.md`: §"Request Body Reading" bullet reworded for the effective ceiling and the distinct discard message (step 8). AGENTS.md §4 makes the manifest win over code in a conflict, so leaving the stale wording would mislead later agents.
- `src/classes/Application/API/README.md`: §"Request body ceiling" (effective limit resolved in `createRequestBodyReader()`, `fromGlobals()` reference at L107, discard message, diagnostic logging when enabled).
- `src/classes/Application/Media/API/README.md`: §"Size limits" `post_max_size` guidance (effective limit); the `681004` row note.
- `changelog.md`: entries under `v7.7.0 - **WIP; UNRELEASED**` (the CTX "Build" bullet already exists).
- `.context/`: regenerated locally by step 9 only. It is not committed on this branch, per AGENTS.md §4.

## Deferred Items

| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---------------|--------|-----------------|-------|
| 1 | Confirm `post_max_size` and web-server body limits on real target installs | Synthesis "Deferred & Follow-Up Items" (local `post_max_size` gap) and "Next Steps" | Operator configuration, not agent work. Carried as Human Action 1. | Step 3 makes a lower server limit visible to clients, so the action now improves capacity rather than correctness. |
| 2 | `composer build-dev` is referenced in the root `README.md` and `docs/agents/project-manifest/README.md` but does not exist in `composer.json` | Pre-run workspace-architect pass (follow-up) | Outside this plan's blast radius (neither file is touched here) | Route to the README Curator and Manifest Curator. |
| 3 | Stale "1,545+ class files" count in `docs/agents/project-manifest/context-documentation.md`, and no manifest curation log | Pre-run workspace-architect pass (follow-up) | Manifest hygiene unrelated to this plan's changes | Route to the Manifest Curator. |
| 4 | `changelog.md` holds 38 releases, past its archive threshold | Pre-run workspace-architect pass (follow-up) | Archiving reshapes the whole file. Doing it mid-WIP risks conflicting with this plan's entries | Run the Changelog Curator's archive pass at release time, after this run. |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Capping at `post_max_size` lowers the advertised limit on servers configured below the ceiling** | This is the intended correction: those servers already rejected such bodies, with a misleading limit. Human Action 1 and the README guidance tell operators how to reach the configured ceiling. |
| **`ini_get('post_max_size')` differs from what the web SAPI actually enforces (e.g. a web-server limit)** | The DISCARDED branch remains as a fallback, now with an accurate message. Web-server limits stay out of scope and are documented as operator configuration. |
| **The startup-warning signature changes in a future PHP version, so the live test fails instead of skipping** | The failure is then still a clear, non-JSON response from an under-provisioned server. The skip message and testing.md name the remedy, and updating the signature constant is a one-line change. |
| **Removing `Document.php`'s abstract declaration breaks a subclass in a consuming application** | Any subclass must already implement the method: the abstract declaration forced it, and the trait's abstract now forces it identically. PHPStan in step 9 confirms within the framework. |
| **Adding `getMaxThumbnailSize()` to `DocumentInterface` breaks an external implementer that does not use `DocumentTrait`** | Every known implementer uses `DocumentTrait` and already defines the method. It is recorded in the changelog so consuming applications see it. |
| **Logging rejected bodies leaks request content** | Only the method name, the reason, the declared length and the limit are logged, never body bytes. |
| **Operators read the rejection log entries as an audit trail** | The entries exist only when logging is enabled and carry no caller identity (the body is read before `authorize()`). The README and changelog describe them as diagnostic entries written when logging is enabled. |

## Recommended Workflow
- **Workflow:** ledger
- **Rationale:** The plan spans three modules (Media, API ingress, Composer build tooling) plus manifest documentation, and changes security-relevant request ingress and a public interface, so it benefits from separate QA, security-audit and review stages.
