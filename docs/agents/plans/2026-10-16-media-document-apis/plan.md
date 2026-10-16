# Plan

## Plan Audit Cycles
- Audits: 2 (Sonnet 5.5 ×2) — Plan Auditor v1.12.0
- Architectural Reviews: 1 (Sonnet 5.5 ×1) — Plan Architect Reviewer v2.3.4

## Prior Project Context
- **Consumer.** The HCP Editor plan `hcp-editor/docs/agents/plans/2026-10-16-mail-content-write-apis/` makes content image keys accept media document IDs only. It delegates uploading to "a separate framework plan for a media API" (its Deferred Item 2) and does not depend on it at runtime. This plan is that framework plan. Agents upload an image once, receive a `mediaID`, and pass it to `UpdateMailContent`.
- **Transport reality.** The Nexus MCP `callApi()` (`nexus-plugins/.../plugins/claude/hcp-agents/server/index.js`) sends all parameters in the query string and an empty `{}` JSON body. File content cannot travel that way, so the client must later send a JSON body. That change is a nexus-plugins follow-up.
- **2026-08 API hardening series.** It established:
  - one execution boundary in `BaseAPIMethod`
  - stable framework error codes sent from lifecycle steps
  - request-derived data kept in a redaction channel (`ErrorResponse::addRequestData()`)
  - one declared right per method, enforced through `APIKeyRights::satisfies()`

  This plan follows all of these.
- **Sibling removed-parameter feature (already shipped).** The plan `2026-10-16-api-removed-parameter-declaration` is implemented and committed (`v7.6.0`, commit `0aa5e764`). It added `addRemovedParam()` and the pre-validation `BaseAPIMethod::rejectRemovedParams()`, which calls `detectUsed($this->request->getParams())`. Its Deferred Item 1 ("Detect removed keys inside JSON request bodies") is an open gap in shipped code. Because the sibling landed first, this plan, landing second, owns the one-line key-source switch (design review Decision 8). No cross-plan sequencing or conditional fallback remains.
- **Insights applied:**
  - `1a8715bc-7d59-48f8-b841-8f9158ffcf7e` / `7097dd32-8abb-4d1f-a24f-75def23d416d`: never hand-order response keys, and assert key order dynamically through `ResponseKeySorter::sortKeys()`.
  - `af333a25-0c2e-4463-93d4-7afa13031704`: unexpected throwables are handled by the boundary. Methods only convert expected validation failures.
  - `7d13934f-9d72-4156-9616-15741f3e8f94`: validate what is mechanically checkable (extension, magic bytes or structure, size), and nothing more.
- **Strategic vision.** Mid term: test coverage, namespaces, stricter typing. All new code is namespaced, strictly typed and unit-tested.

## Summary
Give the Application Framework a complete media document API, so that API clients (first the HCP Editor's agents) can upload, inspect, list, update and delete media documents without the admin UI:
- `AddMediaDocument`
- `GetMediaDocument`
- `GetMediaDocuments`
- `UpdateMediaDocument`
- `DeleteMediaDocument`

File content travels as **base64 inside a JSON request body**. Getting there needs two infrastructure changes in the API module, both reusable by any future JSON method:
1. Registered parameters of `JSONRequestInterface` methods resolve from the parsed JSON body, with a query-string fallback. This makes the existing OpenAPI "request body" documentation true.
2. A new `base64File()` parameter type validates and decodes the content and enforces a configurable size limit. Development-mode error echoes truncate any long string value, so the payload never comes back in an error response.

A new namespaced media writing service, `MediaDocumentWriter`, creates and changes media records through the complete DBHelper collection path (size, description, keywords, author). It keeps disk and database consistent on failure. Content checks live on each document type class, so new types bring their own. SVG remains accepted, as in the admin UI. Duplicate uploads are accepted.

## Architectural Context
- **API lifecycle.**
  - `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` runs `validate()` → `authorize()` → `updateLastUsed()` → `getActiveVersion()` → `collectRequestData()` → `collectResponseData()` inside one private throwable boundary, shared by `process()` and `processReturn()`.
  - `getRequestBody()` (L428) reads `setRequestBody()` or `php://input`.
- **Parameters.**
  - `src/classes/Application/API/Parameters/APIParamManager.php` registers parameters per method.
  - `BaseAPIParameter::getValue()` resolves selected value → header value → `resolveValue()`.
  - All six concrete types read raw values through `getRequestParam()->get()`, which is `$_REQUEST`.
  - Types are created through `ParamTypeSelector`. Validation codes are `ParamValidationInterface::VALIDATION_*` (`1835xx`).
- **JSON requests.**
  - `src/classes/Application/API/Traits/JSONRequestTrait.php` parses the body only in `collectRequestData()`, after validation, into a method-owned `ArrayDataCollection`. In development it echoes the whole body into error responses.
  - The OpenAPI generator (`OpenAPI/ParameterConverter.php`, `OpenAPI/MethodConverter.php`) nevertheless documents every non-header parameter of a JSON method as a body property.
- **Auth.** `APIKeyMethodInterface::getRequiredRight()` is mandatory. The pseudo-user comes from `manageParamAPIKey()->requireValue()->getPseudoUser()`. Media rights (`CreateMedia`, `EditMedia`, `DeleteMedia`, `ViewMedia`) are registered on every app's base user (`src/classes/Application/User/User.php` L60, L1002; `src/classes/Application/Media/MediaRightsTrait.php`).
- **Media domain.**
  - Legacy `Application_Media` (`src/classes/Application/Media/Media.php`) handles type discovery from `Document/*.php`, `getByID()` with an in-memory cache, `idExists()` and `getExtensions()`.
  - Legacy `Application_Media_Document` (`Document.php`) handles paths, `setSourceFile()` and `toBase64()`. Types are `Document/Image.php` and `Document/PDF.php`.
  - The namespaced `Application\Media\Collection\MediaCollection` / `MediaRecord` / `MediaFilterCriteria` provide DBHelper records, the size/description/keywords columns behind `hasSizeColumn()`, file deletion on record delete, and search/limit/order.
  - The admin form path is `Collection/MediaSettingsManager.php`.
- **Framework API registration.** `src/classes/Application/API/APIFoldersManager.php::register()` adds each module's `getAPIMethodsFolder()`. The layout template is `src/classes/Application/Countries/API/` (group, exception, `Methods/`, README, `module-context.yaml`).
- **Config.** Optional `APP_*` settings consist of:
  - a `BaseConfigRegistry` constant
  - a default through `Bootstrap::registerOptionalSetting()` (L271)
  - a setter in `SetAppConfigSettingTrait`
  - a getter in `AppConfig`

## Approach / Architecture

### 1. JSON-body parameter resolution (API module)
- **New** `src/classes/Application/API/Parameters/ValueSource/ParamValueSourceInterface.php`:
  - `hasKey(string $name) : bool`
  - `getRawValue(string $name) : mixed`
  - `getKeys() : string[]`
- **New** `ValueSource/RequestValueSource.php`: wraps `Application_Request` with exactly today's semantics (`registerParam($name)->get()`). `getKeys()` returns the request parameter names.
- **New** `ValueSource/JSONBodyValueSource.php`:
  - Built from a `JSONRequestInterface` method plus a `RequestValueSource` fallback.
  - A key present in the body (`array_key_exists`) wins; otherwise it falls back to the request.
  - `getKeys()` returns the union of body and request keys.
  - The body is obtained through the method's `getRequestData()`.
- **Modified** `APIParamManager`:
  - New `getValueSource() : ParamValueSourceInterface`, created lazily: `JSONBodyValueSource` for `JSONRequestInterface` methods, `RequestValueSource` otherwise.
  - `registerParam()` injects it into every `BaseAPIParameter` that is neither a header parameter nor a `ReservedParamInterface` parameter. `method` and `apiVersion` stay request-only.
- **Modified** `BaseAPIParameter`:
  - New `setValueSource(ParamValueSourceInterface $source) : self`.
  - New `protected getRawRequestValue() : mixed`, which uses the injected source or falls back to `getRequestParam()->get()`.
  - The six types (`StringParameter`, `IntegerParameter`, `BooleanParameter`, `JSONParameter`, `StringListParameter`, `IDListParameter`) replace `getRequestParam()->get()` with `getRawRequestValue()`.
  - `JSONParameter::resolveValue()` additionally accepts a native array, since a JSON body carries objects natively.
- **Modified** `JSONRequestTrait`:
  - `getRequestData()` parses lazily on first access, once per request.
  - An empty or whitespace body is an empty collection, so query-only callers keep working.
  - Invalid JSON, or JSON whose top level is not an object, sends `ERROR_FAILED_TO_READ_INPUT` (182801). This works in both modes, because `errorResponse()->send()` throws `APIResponseDataException` in return mode.
  - `collectRequestData()` just calls `getRequestData()`.
  - `collectRequestErrorData()` walks the body data recursively and replaces every string value longer than a class constant `ECHO_MAX_STRING_LENGTH = 512` with `[N characters truncated]` before echoing. This covers registered and unregistered keys alike (for example a mistyped `fileContent`), with no parameter lookup. (Design review Decision 3: this replaces the previously planned `OpaqueValueParamInterface` marker, which is dropped.)
- **Removed-parameter integration.** `BaseAPIMethod::rejectRemovedParams()` (shipped in `v7.6.0`, L279) passes `array_fill_keys($this->manageParams()->getValueSource()->getKeys(), true)` to `detectUsed()` instead of `$this->request->getParams()`. `detectUsed()` checks `array_key_exists()` on an associative array, so the key list is turned into a key-indexed array. Removed keys inside JSON bodies are then detected too. For non-JSON methods, `RequestValueSource::getKeys()` returns the request parameter names, so behaviour is unchanged.

### 2. Base64 file parameter type
- **New** `src/classes/Application/API/Parameters/Type/Base64FileParameter.php`:
  - `resolveValue() : ?string`:
    - An absent or empty value is `null`.
    - A non-string is a `VALIDATION_INVALID_VALUE_TYPE` warning.
    - An optional `data:<mime>;base64,` prefix and all whitespace are stripped.
    - A strict `base64_decode($value, true)` failure is `VALIDATION_INVALID_BASE64` (183508, new).
    - A decoded size above the limit is `VALIDATION_FILE_SIZE_EXCEEDED` (183509, new; the message names both sizes).
  - `getValue() : ?string` returns the normalised base64. `getDecodedContent() : ?string` and `getDecodedSize() : int` return the decoded data.
  - Memory: the content is decoded once in `resolveValue()` and cached as the parameter's single decoded copy. The parameter never writes a temp file. The normalised base64 is kept only because `BaseAPIParameter::getValue()` returns the resolved value; the methods hand `getDecodedContent()` straight to `MediaFileContent::fromBinary()`.
  - `setMaxSize(int $bytes) : self`. The default is `AppConfig::getAPIMaxFileContentSize()`.
  - `getTypeLabel()` is `Base64 File`.
- **Modified** `ParamTypeSelector`: `base64File() : Base64FileParameter`.
- **Modified** `ParamValidationInterface`: `VALIDATION_INVALID_BASE64 = 183508`, `VALIDATION_FILE_SIZE_EXCEEDED = 183509`.
- **Modified** `OpenAPI/TypeMapper.php`: `TYPE_LABEL_BASE64_FILE = 'Base64 File'` → `{type: string, contentEncoding: base64}`.
- **New config** `BaseConfigRegistry::API_MAX_FILE_CONTENT_SIZE = 'APP_API_MAX_FILE_CONTENT_SIZE'`:
  - default 10 MiB (10485760 bytes) through `Bootstrap::registerOptionalSetting()`
  - setter `setAPIMaxFileContentSize(int $bytes)` in `SetAppConfigSettingTrait`
  - getter `AppConfig::getAPIMaxFileContentSize() : int`

### 3. Media domain: content validation and writing
- **Modified** document type classes. They gain per-type content verification, discovered through the existing `Document/*.php` registry:
  - `Application_Media_Document` declares `abstract public static function isContentValid(string $content, string $extension) : bool`, next to the existing abstract statics `getLabel()`/`getIcon()`/`getExtensions()` (L326–L337). The check is pure and in memory, with no filesystem access (design review Decision 4).
  - `Document/Image.php`:
    - raster: `getimagesizefromstring($content)` succeeds and its `IMAGETYPE_*` matches the extension family (jpg/jpeg→JPEG, png→PNG, gif→GIF)
    - SVG: `XMLReader::XML($content, null, LIBXML_NONET)` with no entity substitution finds `svg` as the first element. This checks file type only; it is not a content policy, and SVG stays accepted.
  - `Document/PDF.php`: the content starts with `%PDF-` (`str_starts_with()`).
- **Modified** `Application_Media`: public `getTypeIDs() : string[]` (sorted, from `loadTypes()`) and `getTypeLabel(string $typeID) : string`.
- **New** `src/classes/Application/Media/Writer/MediaFileContent.php`, an immutable value object:
  - factories `fromBinary(string $fileName, string $content)` and `fromFile(FileInfo $file)`
  - `getFileName()`, `getBaseName()` (file name without extension), `getExtension()` (lowercased), `getContent()`, `getSize()`
  - `writeTempFile() : FileInfo`: `Application::getTempFile(null, $this->getExtension())` returns a `string` path (L616). That path is wrapped in `FileInfo::factory()`, and the content is written with `FileInfo::putContents()`. Only the writer uses it, for the `setSourceFile()` copy in a real write; validation never does.
  - The constructor rejects path separators, an empty name and a missing extension (`MediaWriterException::VALIDATION_INVALID_FILE_NAME`, 681006).
- **New** `src/classes/Application/Media/Writer/MediaFileValidator.php`, a stateless, in-memory validator with a single failure channel (design review Decision 5):
  - `validate(MediaFileContent $content, ?string $requiredTypeID=null) : string` returns the resolved type ID, or throws `MediaWriterException` carrying a `VALIDATION_*` code and a client-safe message.
  - Checks, in order:
    1. the extension resolves to a type through `getTypeByExtension()`
    2. the type matches `$requiredTypeID` when one is given
    3. the size is non-zero
    4. the content is valid per the type's `isContentValid($content->getContent(), $content->getExtension())`, entirely in memory
  - It is the one validation path. `MediaDocumentWriter::create()`/`replaceFile()` call it, and the dry-run branches of the Add and Update methods call the same `validate()` directly, so a dry run cannot drift from the real call (design review Decision 6 caution).
  - The previously planned `MediaFileValidationResult` class is dropped: it duplicated the exception's codes as a second failure channel.
- **New** `src/classes/Application/Media/Writer/MediaDocumentWriter.php`, the single namespaced write path. All mutators require a DB transaction.
  - `create(MediaFileContent $content, string $name, Application_User $author, string $description='', string $keywords='') : MediaRecord`:
    1. validates the content through `MediaFileValidator::validate()`, which throws `MediaWriterException` on an invalid file
    2. calls `MediaCollection::createNewRecord()` with name, author ID, extension, type, size, and description/keywords when `$this->supportsMetadata()` (never `hasSizeColumn()` directly, so the test seam below covers the writer too)
    3. writes `$temp = $content->writeTempFile()` and calls `getMediaDocument()->setSourceFile($temp)` (the only temp-file use, after validation has passed)
    4. deletes the temp file (in a `finally`, so it is also removed on failure)
    5. if anything throws after the target file was written, deletes the target file before rethrowing
  - `replaceFile(MediaRecord $record, MediaFileContent $content) : void`:
    - The extension must equal the record's (parity with `MediaSettingsManager::validateFile()`).
    - Validates, writes to the existing path and updates the size.
    - Clears the `Application_Media` document cache (`clearCollection()`) so cached image dimensions are not stale.
  - `updateMetadata(MediaRecord $record, ?string $name, ?string $description, ?string $keywords) : bool`: `null` means unchanged. Returns whether anything changed.
  - `delete(MediaRecord $record) : void`: delegates to `MediaCollection::deleteRecord()`.
  - `supportsMetadata() : bool`: a public, non-final method that proxies `MediaCollection::hasSizeColumn()`. It is the only place the writer and the API methods ask about the metadata columns. `hasSizeColumn()` is static with a private static cache (`MediaCollection.php` L59–L68) and cannot be toggled, so overriding `supportsMetadata()` in a subclass is the test seam for the columns-absent path. The class is not declared `final`.
- **New** `src/classes/Application/Media/Writer/MediaWriterException.php` extends `MediaException` (`src/classes/Application/Media/MediaException.php`, the module's failure idiom). Its `VALIDATION_*` constants are the single source of truth for the writer's client-facing codes; no other class re-declares them.
  - `isValidationError() : bool` (code in the `6810xx` block), so callers can tell client errors from internal ones.
  - client-facing validation codes `6810xx` (`VALIDATION_*`):
    - 681001 unsupported extension
    - 681002 type mismatch
    - 681003 content does not match extension
    - 681004 empty file
    - 681005 extension change not allowed
    - 681006 invalid file name
    - 681007 metadata not supported
  - internal error codes `6811xx` (`ERROR_*`):
    - 681101 no transaction
    - 681102 file write failed
- **Modified** `MediaFilterCriteria`: `selectMediaType(string)` / `selectMediaTypes(array)` (`COL_TYPE` IN filter), for the list method's type filter.

### 4. Media API module
**New** folder `src/classes/Application/Media/API/`:
- `MediaAPIGroup.php`: singleton `GenericAPIGroup` (`media`, "Media", "Upload and manage media documents").
- `MediaAPIInterface.php`:
  - `PARAM_MEDIA_ID = 'mediaID'`
  - error codes:
    - `VALIDATION_UNKNOWN_MEDIA_ID = 681010`
    - `VALIDATION_NO_CHANGES = 681011`
    - `VALIDATION_HTML_NOT_ALLOWED = 681012`
  - It does **not** re-export the writer's `6810xx` codes (design review Decision 5: a second copy would have to be kept in sync). The single client-facing list is the error-code table in `Media/API/README.md`, which names `MediaWriterException::VALIDATION_*` as the source for the writer codes.
- `MediaAPITrait.php`:
  - `registerMediaIDParam()` (integer, required)
  - `requireMediaRecord() : MediaRecord`, which sends `errorResponse(VALIDATION_UNKNOWN_MEDIA_ID)->makeNotFound()->setErrorMessage()->send()` when the ID is unknown (HTTP 404, through the new `ErrorResponse::makeNotFound()` below). Get, Update and Delete all use it.
  - `protected createWriter() : MediaDocumentWriter`: the single place the methods obtain the writer (default `new MediaDocumentWriter()`). The methods never instantiate the writer themselves and always call `supportsMetadata()` on the instance this returns. This is the test seam: the framework instantiates methods with `new XxxAPI(APIManager::getInstance())`, so a test-local subclass of a method (not under any API methods folder, so it is never indexed) overrides `createWriter()` to return a writer subclass whose `supportsMetadata()` returns `false`. The method classes are not declared `final`.
  - `sendWriterValidationError(MediaWriterException $e)`: converts a validation exception (`isValidationError()`) to `errorResponse($e->getCode())->setErrorMessage()->send()`. Internal `6811xx` exceptions are rethrown to the boundary (insight `af333a25-…`).
- `MediaAPIResponseInterface.php`: `KEY_*` constants for the document shape:
  - `mediaDocument`, `mediaDocuments`, `mediaID`, `name`, `mediaType`, `mediaTypeLabel`, `extension`, `fileName`, `fileSize`, `dateAdded` (ATOM), `authorID`, `description`, `keywords`
  - `image` (`width`, `height`, `isVector`, `isAnimated`; `null` for non-images)
  - `content` (base64, only on request)
  - `total`, `limit`, `offset`, `modified`, `deleted`, `dryRun`
- `MediaDocumentSerializer.php`:
  - `static serialize(MediaRecord $record, bool $includeContent=false) : array`
  - `static serializePreview(MediaFileContent $content, string $typeID, string $name, …) : array`, the dry-run shape with `mediaID: null`
  - `static buildExample(bool $withImage=true) : array`, a fixed, DB-free example for documentation
- `MediaAPIException.php`.
- `Methods/` (all implement `APIKeyMethodInterface`, `JSONResponseInterface` and `JSONResponseWithExampleInterface`, version `1.0`, regions A/B/C as in `GetAppCountriesAPI`):

| Method | Request style | Right | Parameters | Result |
|---|---|---|---|---|
| `AddMediaDocumentAPI` (`AddMediaDocument`) | `JSONRequestInterface`, `DryRunAPIInterface` | `CreateMedia` | `fileName` (string, required), `fileContent` (base64File, required), `name` (string ≤160, default = file base name), `description` (≤1200), `keywords` (≤500), `mediaType` (enum of `getTypeIDs()`, optional constraint), `dryRun` | `mediaDocument` + `dryRun`. A dry run validates fully and returns a preview with `mediaID: null`; it never touches disk or DB. |
| `GetMediaDocumentAPI` (`GetMediaDocument`) | `RequestRequestInterface` | `ViewMedia` | `mediaID` (required), `includeContent` (boolean, default false) | `mediaDocument` (with `content` when requested) |
| `GetMediaDocumentsAPI` (`GetMediaDocuments`) | `RequestRequestInterface` | `ViewMedia` | `search` (free-text terms matched against name, keywords and description), `mediaTypes` (stringList, enum-checked), `extensions` (stringList), `orderBy` (`dateAdded`\|`name`\|`fileSize`, default `dateAdded`), `orderDir` (`asc`\|`desc`, default `desc`), `limit` (1–200, default 50), `offset` (≥0, default 0) | `mediaDocuments[]` (no content), `total`, `limit`, `offset` |
| `UpdateMediaDocumentAPI` (`UpdateMediaDocument`) | `JSONRequestInterface`, `DryRunAPIInterface` | `EditMedia` | `mediaID` (required), `name`, `description` / `keywords` (`clearableString`: absent = unchanged, empty = clear), `fileName` + `fileContent` (pair, enforced through two `RequiredIfOtherIsSetRule`s), `dryRun` | `mediaDocument`, `modified`, `dryRun`. At least one change is required (`VALIDATION_NO_CHANGES`). A replaced file must keep the extension (681005). |
| `DeleteMediaDocumentAPI` (`DeleteMediaDocument`) | `RequestRequestInterface`, `DryRunAPIInterface` | `DeleteMedia` | `mediaID` (required), `dryRun` | `mediaDocument` (captured before deletion), `deleted`, `dryRun`. A dry run never calls `delete()`, because record deletion unlinks the file immediately. |

- **List defaults for `GetMediaDocuments`.** Live installs hold 10,000+ media documents, so the list is never unbounded:
  - A call without parameters returns the 50 most recent documents (`dateAdded desc`, `limit` 50, `offset` 0), never the whole table.
  - `limit` is capped at 200. Values outside 1–200, and a negative `offset`, are rejected with a validation error rather than silently clamped, so a client never mistakes a truncated page for a complete one. `total` reports the full match count, so clients can page with `offset`.
  - `search` uses the collection's existing search (`MediaCollection::getRecordSearchableColumns()`): name, keywords and description. Each term is matched with `LIKE %term%` against any of these fields, and terms are combined with AND. The built-in `AND`/`OR`/`NOT` operators work as in the UI. On installs without the size column (`hasSizeColumn()` false), only the name is searched; the method description states this.
  - Ordering is stable across pages. `media_date_added` has one-second resolution, and bulk uploads share timestamps, so ordering on it alone can duplicate or skip documents between pages. `MediaFilterCriteria` appends the primary key as a tie-breaker in the same direction (step 7).
  - Every listed document is serialized without content (`includeContent` exists only on `GetMediaDocument`), which keeps a 200-item page small.
- **Write-method shape** (Add, Update, Delete):
  1. An own `DBHelper` transaction when none is open.
  2. `collectRequestData()` stays empty for RequestRequest methods. JSON methods keep the trait's body handling.
  3. Domain validation runs before any mutation and is converted to `errorResponse(code)->setErrorMessage()->send()`. Dry and real runs share the same validation path: `MediaFileValidator::validate()` for file content, and the same method-level checks (HTML rule, no-changes, extension change, metadata support) for everything else. Only the mutating calls are skipped on a dry run.
  4. The pseudo-user from the API key is the author.
  5. Commit, or roll back on dry run. Dry runs of Add, Delete and Update-with-file avoid the mutating calls entirely, so no file is ever left behind or removed.
- **HTML rule.** Description and keywords reject HTML tags (`strip_tags($v) !== $v` → 681012), as the admin form does with `addRuleNoHTML()`. When `supportsMetadata()` is false, a non-empty description or keywords value is rejected with 681007.
- **Not-found status.** **Modified** `src/classes/Application/API/ErrorResponse.php`: new `makeNotFound() : self`, which sets `Connectors_ResponseCode::HTTP_NOT_FOUND` (404). It sits beside the existing `makeBadRequest()`/`makeInternalServerError()`/`makeUnauthorized()`/`makeForbidden()` helpers (L251–L277), with the same docblock style naming the intended use (an unknown record ID). The helper is generic: any future method that resolves a record by ID reuses it.
- **Registration.**
  - New static `MediaCollection::getAPIMethodsFolder() : FolderInfo` → `Media/API/Methods`.
  - Added to `APIFoldersManager::register()`.

### 5. Example request (agent / MCP view)
```json
POST /api/AddMediaDocument
Authorization: Bearer <key>
Content-Type: application/json

{ "fileName": "hero.png", "fileContent": "iVBORw0KGgo…", "name": "Autumn hero", "mediaType": "Image" }
```
The response's `mediaDocument.mediaID` is what the HCP Editor's `UpdateMailContent` image keys accept as `{mediaID}`.

## Rationale
- **Base64 in JSON** is what the user chose. It is trivial for any HTTP or MCP client and needs no multipart parsing. The costs are about 33% transfer overhead and PHP memory for the decoded copy. Both are bounded by the configurable limit, and email images are small.
- **Resolving parameters from the JSON body** is the minimum change that lets a JSON method use typed, validated, documented parameters. Without it, `fileContent` would be read outside the parameter system: no required-check, no size validation, no OpenAPI accuracy. It also fixes a latent inconsistency (the spec already claims these are body properties) for every future JSON method. The blast radius today is two test fixture methods.
- **The query fallback** keeps small parameters usable from the existing `callApi()` style, and keeps both existing JSON fixtures working unchanged.
- **A dedicated parameter type** (rather than a string parameter plus ad-hoc decoding in the method) puts the decode, the strictness and the size limit in one tested place. Any future JSON method with file content (attachments, imports) reuses it.
- **Per-type `isContentValid()`** follows "a registry that finds its own members beats a hand-maintained list". `Application_Media` already discovers types from `Document/*.php`, so a new type brings its own check, and the validator never grows a switch. The check takes the content string and extension rather than a file. The content is already in memory, validation stays pure and unit-testable without the filesystem, and a dry run truly never touches disk.
- **One failure channel.** Validation reports failures only through `MediaWriterException`, matching the module's `MediaException` idiom. A separate result object would have carried the same codes a second way.
- **Generic echo truncation** rather than a per-parameter marker. The dev-only echo's real failure mode is multi-MB responses, and a length cap covers every key, registered or not, with no new concept.
- **`MediaDocumentWriter` over the collection path.**
  - The collection path is the only one that records size, description, keywords and the explicit author.
  - Centralising it gives the API (and later, programmatic importers) one consistent write path with disk/DB failure cleanup.
  - The legacy `createFromFile()` stays for its existing callers.
- **Dry runs that skip mutation** rather than "mutate then roll back". File writes and record deletion are not transactional, so a rollback would leave orphans or delete files. A side-effect-free validation preview is the only safe dry run.
- **The SVG and duplicate policies** follow the user: SVG behaves as in the admin UI, and dedup is out of scope. The type check for SVG only confirms that the file really is SVG.
- **The `mediaType` constraint on Add** lets consumers such as the HCP Editor fail fast at upload instead of at content write.
- **Speculation check.**
  - `ParamValueSourceInterface` has two current implementations and the removed-parameter detection as a third consumer.
  - `OpaqueValueParamInterface` was dropped after design review (Decision 3). Its only justification was a speculative trajectory (future secret-bearing parameters); it can return if that need becomes real.
  - `MediaFileContent`, `MediaFileValidator` and `MediaDocumentWriter` are consumed by three of the five methods each. The writer subsystem is four classes (`MediaFileValidationResult` dropped, Decision 5).

## Considered Alternatives

| Decision | Chosen Shape | Alternatives Considered | Trade-Off Summary |
|----------|--------------|-------------------------|-------------------|
| Upload transport | Base64 string in a JSON body | multipart/form-data; URL import | User decision. Multipart is a hassle for clients and needs a new request format plus `$_FILES` handling. URL import adds SSRF surface. |
| Where file content is read | Typed `base64File()` parameter resolved from the JSON body | Read `getRequestData()` manually in each method | Manual reading bypasses required-checks, validation codes and OpenAPI. Every future JSON method would repeat it. |
| JSON-body precedence | Body wins, request (query) fallback | Body only; request only (status quo) | Body-only breaks the current `callApi()` style for small parameters. Request-only cannot carry file content. |
| Value-source injection | `APIParamManager` injects the source into parameters at registration | Each parameter asks a global for the current method; method-level override of `getRequestParam()` | Injection keeps parameters ignorant of methods and is testable with a stub source. Globals break with nested `processReturn()` calls. |
| Content verification | Static `isContentValid(string $content, string $extension)` per document type | Central switch in a validator; reuse `Application_Uploads::validate()`; `isContentValid(FileInfo)` via a temp file | A switch must be edited for every new type. `Application_Uploads::validate()` is bound to `$_FILES` and lets SVG pass unchecked. The `FileInfo` signature forced a temp-file round trip during validation, adding an I/O failure mode and breaking "dry run never touches disk" (design review Decision 4). |
| Validation failure reporting | `MediaFileValidator` throws `MediaWriterException` (one channel) | A `MediaFileValidationResult` object plus the exception; folding validation into `MediaDocumentWriter::validate()` | The result object duplicated the exception's codes and forced a dual-input trait method. Folding into the writer would grow it; a separate stateless validator is still needed by the dry-run branches (design review Decision 5). |
| Dev-mode body echo | Generic truncation of strings over 512 characters | `OpaqueValueParamInterface` marker on registered parameters; disabling the echo for base64 methods | The marker needed a new interface and a key-to-parameter lookup, and missed unregistered keys. Disabling loses the debugging value (design review Decision 3). |
| Removed-key detection in JSON bodies | This plan switches `rejectRemovedParams()` to the value source's keys | Ship only `getKeys()` and leave the switch to a later plan; detect from the request only | The sibling feature already shipped, so this plan is the one landing second and owns the switch. Deferring would leave a known gap in a function this plan already reshapes the inputs of (design review Decision 8). |
| Write path | New `MediaDocumentWriter` on `MediaCollection::createNewRecord()` | Legacy `Application_Media::createFromFile()`; driving `MediaSettingsManager` with simulated forms | The legacy path drops size, description and keywords. Simulated forms cannot carry file uploads cleanly and couple the API to UI rules. |
| Dry run | Validate-only preview, no mutation | Mutate inside a transaction and roll back | Files are not transactional. A rollback leaves orphan files (create) or lost files (delete). |
| Method set | Full set: Add/Get/Get-list/Update/Delete | Add + Get only | User decision ("whole spectrum"). It shares one serializer, trait and writer, so the marginal cost is low. |
| Unknown media ID status | New `ErrorResponse::makeNotFound()` (HTTP 404) | Default status of `errorResponse()`; `makeBadRequest()` (400) | A distinct 404 lets HTTP clients tell "no such record" from "bad input" without parsing the code. The helper matches the four existing status helpers and costs one method plus a test. |
| Columns-absent test seam | Overridable `supportsMetadata()` on the writer, reached through a protected `createWriter()` factory in `MediaAPITrait` | A reset hook for `MediaCollection::$hasSizeColumn`; narrowing AC-15 to the writer level | A cache-reset hook adds test-only API to a production collection and still needs a real schema change to observe. Narrowing the AC would leave the method-level 681007 check untested. The factory is the writer's single construction point anyway. |
| Error codes | New `681xxx` block (grep-verified free) plus `183508`/`183509` in the parameter-validation block | Reuse generic 183003 for everything | Specific codes let agents react programmatically (wrong type versus too large versus unknown ID). |

## Pattern Alignment
- **Follows** the module API layout of `src/classes/Application/Countries/API/` (group singleton, exception, `Methods/`, README, `module-context.yaml`, static `getAPIMethodsFolder()` registered in `APIFoldersManager`).
- **Follows** the write-method shape of `hcp-editor/assets/classes/Maileditor/Mails/API/Methods/UpdateMailLinkAPI.php`: own transaction, dry-run rollback, `errorResponse()->send()` for expected failures, pseudo-user from the API key.
- **Follows** the parameter type pattern of `Parameters/Type/JSONParameter.php` (`getTypeLabel()`, `resolveValue()`, `ParamValidationInterface` codes) and its `ParamTypeSelector` registration.
- **Follows** the HTTP status helper pattern of `src/classes/Application/API/ErrorResponse.php` (`makeBadRequest()`, `makeForbidden()` and the others) for the new `makeNotFound()`.
- **Follows** the optional config setting pattern of `AUTOMATIC_DELETION_DELAY` (`BaseConfigRegistry`, `Bootstrap::registerOptionalSetting()`, `SetAppConfigSettingTrait`, `AppConfig`).
- **Follows** the registry discovery of `Application_Media::loadTypes()`; per-type checks are static methods like `getExtensions()`/`getLabel()`.
- **Follows** the Media module's exception idiom (`MediaException` subclasses with unique codes) as the only validation failure channel.
- **Departs** from "parameters resolve from `$_REQUEST`" for JSON-body methods. This is justified because the OpenAPI generator already documents them as body properties, and the only affected methods are two test fixtures.
- **Departs** from the HCP "example from live records" approach (`APIExampleRecordGenerator`, HCP only). Framework examples use fixed arrays (`MediaDocumentSerializer::buildExample()`), because the framework has no example record generator and documentation builds must not write media files.

## Structural Improvements

| Structure | Observation | Decision | Reason |
|-----------|-------------|----------|--------|
| `src/classes/Application/API/Parameters/Type/*Parameter.php` (six classes) | The raw value is read through a duplicated `getRequestParam()->get()`, with no value-source seam | Promoted to step 2 | The JSON body needs the seam. One protected accessor removes the duplication. |
| `src/classes/Application/API/Traits/JSONRequestTrait.php` | The body is parsed after validation and is disconnected from the parameters; the full body is echoed in dev errors | Promoted to steps 2 and 3 | Required for body parameters. Without the truncation of long strings, error responses would carry multi-MB payloads. |
| `src/classes/Application/API/Parameters/Type/JSONParameter.php` | Rejects native arrays | Promoted to step 2 | Body JSON delivers objects natively. |
| `src/classes/Application/API/BaseMethods/BaseAPIMethod.php::rejectRemovedParams()` (shipped in `v7.6.0`, L279) | Passes `$this->request->getParams()` to `detectUsed()`, so it sees only request keys (the sibling plan's Deferred Item 1) | Promoted to step 3 | A one-line change once the value source exists. The sibling has landed, so this plan owns it. Otherwise the gap persists in the module this plan reshapes. |
| `src/classes/Application/Media/Media.php` | No public accessor for registered types | Promoted to step 6 | Needed for the `mediaType` enum and labels. |
| Media content verification (`Application_Uploads::validate()` switch; none on `Document/*`) | Not reusable and not per type | Promoted to step 6 (per-type `isContentValid()`) | The registry-based check serves the API. |
| `src/classes/Application/Uploads/Uploads.php::validate()` | Duplicates raster checks as a hand-maintained switch | Rejected | Legacy `$_FILES`-bound upload path that this plan does not touch. Rewiring it risks the image uploader UI without a consumer need. |
| Media creation paths (legacy `Document::createNew()` vs. collection) | The legacy path omits `file_size`, description and keywords | Promoted to step 7 (new writer on the collection path) | The API needs the complete path. The legacy path stays for compatibility. |
| `src/classes/Application/Media/Collection/MediaSettingsManager.php` | Inlines about six lines of derive-type/size and replace-file logic that the writer now also implements | Rejected | Bound to the `HTML_QuickForm2_Element_InputFile` upload object. There is no file-element test harness, so routing it through the writer would change an untested UI path for marginal deduplication. |
| `src/classes/Application/Media/Collection/MediaFilterCriteria.php` | No type filter | Promoted to step 7 | Needed by `GetMediaDocuments`' `mediaTypes`. |
| `src/classes/Application/Media/Collection/MediaFilterCriteria.php` | Ordering is a single column (`FilterCriteria/Database::buildOrderby()`, L1210), so equal `media_date_added` values page unstably | Promoted to step 7 (local `buildOrderby()` override that appends the primary key) | Paging 10,000+ documents by date must not duplicate or skip items. |
| `src/classes/Application/FilterCriteria/Database.php` | Generic multi-column ordering is missing for every collection | Rejected | Every filter criteria class and data grid in the framework builds on it, which is outside this plan's blast radius. The media override covers the only consumer that pages through the API. |
| `src/classes/Application/FilterCriteria/FilterCriteria.php::setLimit()` (L124) | Stores `$limit` in `offset` and `$offset` in `limit`. `buildLimit()` swaps them back, so the SQL is correct but the fields are misleading | Rejected | Behaviour is correct, and data grids and pagination rely on it. The API method calls `setLimit($limit, $offset)` through the public signature only; the paging test guards it. |
| Media module documentation | No README and no `module-context.yaml`; absent from the modules overview | Promoted to step 14 | The plan adds a whole API surface to the module. Without docs, agents cannot discover it through the manifest. |
| `src/classes/Application/API/ErrorResponse.php` | Status helpers exist for 400, 401, 403 and 500, but none for 404 | Promoted to step 8 | The media methods need a not-found status for unknown IDs. One helper keeps status codes out of the methods as magic numbers. |
| `AGENTS.md` §1 note (L34) vs. §4 Directives (L107) | §1 says `.context/` "is committed to VCS"; L107 says it is committed only on `main` | Promoted to step 14 | The plan already edits the §1 module table, and the contradiction is exactly what the commit rule in step 14 depends on. |
| `AGENTS.md` §5 (7.0.8) vs. `VERSION` and changelog (both 7.6.0, re-verified) | Stale version statement in `AGENTS.md` | Promoted to step 15 | Release preparation is in scope. `VERSION` is current today and only needs the new number. |

## Detailed Steps
1. **Baseline.**
   - Run the existing targeted suites before changing anything and record the results in the work package notes:
     - `composer test-filter -- Parameter`
     - `composer test-filter -- OpenAPI`
     - `composer test-file -- tests/AppFrameworkTests/API/ProcessReturnTest.php`
     - `composer test-filter -- Media`
   - This separates pre-existing failures from regressions.
2. **Value-source seam and body resolution.**
   - Create `src/classes/Application/API/Parameters/ValueSource/` with `ParamValueSourceInterface`, `RequestValueSource` and `JSONBodyValueSource` (semantics as in Approach §1).
   - Add `BaseAPIParameter::setValueSource()` and `getRawRequestValue()`.
   - Switch the six types to `getRawRequestValue()`, and let `JSONParameter` accept native arrays.
   - Add `APIParamManager::getValueSource()` and the injection in `registerParam()`, skipping header and reserved parameters.
   - Make `JSONRequestTrait::getRequestData()` lazy, with the empty-body and non-object rules (182801).
   - Run `composer dump-autoload`.
3. **Echo truncation and removed-parameter integration.**
   - Add `ECHO_MAX_STRING_LENGTH = 512` and the recursive long-string truncation (`[N characters truncated]`) in `JSONRequestTrait::collectRequestErrorData()`.
   - In `BaseAPIMethod::rejectRemovedParams()` (L279), pass `array_fill_keys($this->manageParams()->getValueSource()->getKeys(), true)` to `detectUsed()` instead of `$this->request->getParams()`.
   - (Removed after design review: the annotation of the sibling plan document. That plan folder no longer exists under `docs/agents/plans/`; the changelog entry in step 15 records the resolved gap instead.)
4. **Config setting.** Add `API_MAX_FILE_CONTENT_SIZE` to `BaseConfigRegistry`, its default (10485760) in `Bootstrap` beside L271, the setter in `SetAppConfigSettingTrait` and the getter in `AppConfig`.
5. **Base64 file parameter.**
   - Add `Parameters/Type/Base64FileParameter.php`, `ParamTypeSelector::base64File()`, and the validation codes 183508/183509 in `ParamValidationInterface` (grep-verify they are still free at implementation time).
   - Add the `TypeMapper` case. Its documentation row goes into the type mapping table of `src/classes/Application/API/OpenAPI/README.md` (L51–L58) in step 14.
6. **Media type registry additions.**
   - Add `Application_Media::getTypeIDs()` and `getTypeLabel()`.
   - Add abstract static `isContentValid(string $content, string $extension)` to `Application_Media_Document`, implemented in `Document/Image.php` (`getimagesizefromstring()` for raster, `XMLReader::XML()` for SVG structure) and `Document/PDF.php` (signature).
7. **Media writer.**
   - Create `src/classes/Application/Media/Writer/` with `MediaFileContent`, `MediaFileValidator` (throws, returns the type ID), `MediaDocumentWriter` and `MediaWriterException` (codes per Approach §3, `isValidationError()`).
   - Add `MediaFilterCriteria::selectMediaType(s)()`.
   - Override `MediaFilterCriteria::buildOrderby()`: when the parent clause is non-empty and the order field is not the primary key, append `, {getColPrimary()} {orderDir}` as a tie-breaker.
   - Run `composer dump-autoload`.
8. **Media API scaffolding.**
   - Add `ErrorResponse::makeNotFound()`.
   - Create `src/classes/Application/Media/API/` with `MediaAPIGroup`, `MediaAPIInterface`, `MediaAPITrait` (including `requireMediaRecord()` with `makeNotFound()` and the protected `createWriter()` factory), `MediaAPIResponseInterface`, `MediaDocumentSerializer` and `MediaAPIException`.
   - Add `MediaCollection::getAPIMethodsFolder()` and register it in `APIFoldersManager::register()`.
9. **Read methods.**
   - Implement `GetMediaDocumentAPI` and `GetMediaDocumentsAPI`, with descriptions, related method names (the other four methods), `getResponseKeyDescriptions()` for every key, examples from `MediaDocumentSerializer::buildExample()`, and an empty changelog.
10. **Write methods.**
    - Implement `AddMediaDocumentAPI`, `UpdateMediaDocumentAPI` and `DeleteMediaDocumentAPI` with the write-method shape, dry-run semantics, rights and error codes from Approach §4. Dry-run branches call the same `MediaFileValidator::validate()` and method-level checks as the real branches.
    - Each description documents the request body, the size limit (with the PHP `post_max_size` guidance: about 1.4× the decoded limit), the error codes and the dry-run behaviour.
11. **Test fixture for live HTTP.**
    - Add `tests/application/assets/classes/TestDriver/API/TestJSONBodyParamsMethod.php`. It is a JSON request method with `label` (string), `count` (integer), `flag` (boolean), `payload` (`base64File`) and `meta` (JSON), and it echoes the resolved values plus `payloadSize`.
    - Run `composer dump-autoload`, then rebuild `tests/application/storage/api/method-index.json` through `MethodIndexEntryTest` and confirm that the fixture and the five media methods are indexed.
12. **Tests.** Write every test listed in the Test Plan.
13. **OpenAPI regeneration check.**
    - Run `composer generate-openapi-spec` for the test application (`tests/application/api/openapi.json`).
    - Verify that the five media operations exist, that the JSON methods have `requestBody` with `fileContent` as `contentEncoding: base64`, and that the read methods use query parameters.
    - Commit the regenerated test `openapi.json` only if it is git-tracked.
14. **Documentation.** Apply every entry in Documentation Updates, including the new Media module `README.md` and `module-context.yaml`. Run `composer build-docs` locally to validate `relatedModules`. The regenerated `docs/agents/project-manifest/*` files (`modules-overview.md`, `module-glossary.md`) are committed with the feature; only `.context/` is not committed from the feature branch.
15. **Release preparation.**
    - Add the changelog entry. Its version is the next minor after the newest entry at implementation time, proposed `v7.7.0` (the newest entry is `v7.6.0` today).
    - Set `VERSION` and the `AGENTS.md` §5 version to that number.
16. **Static analysis and final targeted tests.**
    - Run `composer analyze` and fix new findings in touched files.
    - Re-run the step 1 suites plus every new test file.
    - Run `composer test-group -- live-http` for the new live test where a web server is available, and record the result.

## Dependencies
- Step 1 runs before anything else.
- Steps 2 → 3 (3 needs the value source).
- 2 → 5 (5 uses `getRawRequestValue()`).
- 4 → 5 (default size).
- 6 → 7 → 8 → 9 → 10.
- 5 → 10 (Add and Update use `base64File()`).
- 2 → 10 (JSON body parameters).
- 2 + 5 → 11.
- 10 + 11 → 12 (method tests).
- Parameter, value-source and writer unit tests can be written alongside steps 2, 5 and 7.
- 10 → 13.
- 10 → 14 → 15 → 16.
- No external plan dependency. The removed-parameter code that step 3 changes already exists (`v7.6.0`).

## Required Components
- **New:**
  - `src/classes/Application/API/Parameters/ValueSource/ParamValueSourceInterface.php`
  - `src/classes/Application/API/Parameters/ValueSource/RequestValueSource.php`
  - `src/classes/Application/API/Parameters/ValueSource/JSONBodyValueSource.php`
  - `src/classes/Application/API/Parameters/Type/Base64FileParameter.php`
  - `src/classes/Application/Media/Writer/MediaFileContent.php`
  - `src/classes/Application/Media/Writer/MediaFileValidator.php`
  - `src/classes/Application/Media/Writer/MediaDocumentWriter.php`
  - `src/classes/Application/Media/Writer/MediaWriterException.php`
  - `src/classes/Application/Media/API/MediaAPIGroup.php`
  - `src/classes/Application/Media/API/MediaAPIInterface.php`
  - `src/classes/Application/Media/API/MediaAPITrait.php`
  - `src/classes/Application/Media/API/MediaAPIResponseInterface.php`
  - `src/classes/Application/Media/API/MediaDocumentSerializer.php`
  - `src/classes/Application/Media/API/MediaAPIException.php`
  - `src/classes/Application/Media/API/Methods/AddMediaDocumentAPI.php`
  - `src/classes/Application/Media/API/Methods/GetMediaDocumentAPI.php`
  - `src/classes/Application/Media/API/Methods/GetMediaDocumentsAPI.php`
  - `src/classes/Application/Media/API/Methods/UpdateMediaDocumentAPI.php`
  - `src/classes/Application/Media/API/Methods/DeleteMediaDocumentAPI.php`
  - `src/classes/Application/Media/API/README.md`
  - `src/classes/Application/Media/README.md`
  - `src/classes/Application/Media/module-context.yaml`
  - `tests/application/assets/classes/TestDriver/API/TestJSONBodyParamsMethod.php`
  - the test files in the Test Plan
- **Modified:**
  - `src/classes/Application/API/Parameters/BaseAPIParameter.php`
  - `src/classes/Application/API/Parameters/APIParamManager.php`
  - `src/classes/Application/API/Parameters/ParamTypeSelector.php`
  - `src/classes/Application/API/Parameters/Validation/ParamValidationInterface.php`
  - `src/classes/Application/API/Parameters/Type/StringParameter.php`
  - `src/classes/Application/API/Parameters/Type/IntegerParameter.php`
  - `src/classes/Application/API/Parameters/Type/BooleanParameter.php`
  - `src/classes/Application/API/Parameters/Type/JSONParameter.php`
  - `src/classes/Application/API/Parameters/Type/StringListParameter.php`
  - `src/classes/Application/API/Parameters/Type/IDListParameter.php`
  - `src/classes/Application/API/Traits/JSONRequestTrait.php`
  - `src/classes/Application/API/BaseMethods/BaseAPIMethod.php` (removed-parameter key source only)
  - `src/classes/Application/API/OpenAPI/TypeMapper.php`
  - `src/classes/Application/API/ErrorResponse.php` (new `makeNotFound()`)
  - `src/classes/Application/API/APIFoldersManager.php`
  - `src/classes/Application/ConfigSettings/BaseConfigRegistry.php`
  - `src/classes/Application/ConfigSettings/SetAppConfigSettingTrait.php`
  - `src/classes/Application/ConfigSettings/AppConfig.php`
  - `src/classes/Application/Bootstrap/Bootstrap.php`
  - `src/classes/Application/Media/Media.php`
  - `src/classes/Application/Media/Document.php`
  - `src/classes/Application/Media/Document/Image.php`
  - `src/classes/Application/Media/Document/PDF.php`
  - `src/classes/Application/Media/Collection/MediaCollection.php`
  - `src/classes/Application/Media/Collection/MediaFilterCriteria.php`
  - `tests/application/storage/api/method-index.json` (rebuilt)
  - documentation per Documentation Updates
  - `VERSION`
  - `changelog.md`

## Assumptions
- The removed-parameter feature (`v7.6.0`, `BaseAPIMethod::rejectRemovedParams()`) is present in the codebase. This was verified during design-review integration, so no fallback path is needed.
- The test database contains the `media` table with the `file_size`/`keywords`/`description` columns (`docs/sql/pristine.sql`). The `hasSizeColumn()`-false path is covered through the `supportsMetadata()` override reached via `MediaAPITrait::createWriter()` (Approach §3/§4), not by altering the test schema.
- PHP's `post_max_size` on target installs is at least about 1.4× `APP_API_MAX_FILE_CONTENT_SIZE`. This is documented, not enforced.
- `XMLReader` (ext-xmlreader) is available. It is part of PHP's default build, and the framework already depends on DOM/XML extensions.

## Constraints
- `array()` syntax only; `declare(strict_types=1)`; `#[Override]` with `use Override;` in namespaced files.
- New classes are namespaced. Legacy classes (`Application_Media`, `Application_Media_Document*`) get only the additive methods listed, with no refactoring.
- `composer dump-autoload` after adding classes, then rebuild the method index through `MethodIndexEntryTest`.
- Never run the whole test suite; use `composer test-file` / `test-filter` / `test-group`.
- `.context/` is not committed from the feature branch (`AGENTS.md` L107). Generated manifest files under `docs/agents/project-manifest/` are not `.context/` and are committed with the feature.
- Error codes must be grep-verified unused at implementation time (`681xxx`, 183508, 183509).
- No change to `ErrorResponse::getErrorData()` redaction semantics. The long-string truncation happens before data reaches `addRequestData()`.

## Out of Scope
- Changing the Nexus MCP `callApi()` to send JSON bodies (nexus-plugins repository).
- HCP Editor changes (its content write plan already accepts media IDs; its README pointer is its own Human Action).
- SVG sanitisation or restrictions, which belong to the consuming application.
- Content-hash deduplication (user decision).
- multipart/form-data uploads.
- Media tagging over the API (tag listing, assignment, tag filters).
- Thumbnail or resized-image URLs, and media configurations/processing presets.
- Rewriting `Application_Uploads` or the admin `MediaSettingsManager`.
- Usage tracking or an in-use guard before deleting a media document.

## Human Actions

| # | Action | When | Why an agent cannot do it |
|---|--------|------|---------------------------|
| 1 | (Satisfied — retained for numbering.) Execute the plan `2026-10-16-api-removed-parameter-declaration` | Before the run | Already done: the feature shipped as `v7.6.0` (commit `0aa5e764`). No action remains. |
| 2 | Release the framework version containing these APIs (changelog version from step 15) | After the run | Releases and tags belong to the user. |
| 3 | Schedule a nexus-plugins plan so `callApi()` sends parameters as a JSON body (at least for `AddMediaDocument`/`UpdateMediaDocument`), with an MCP tool that reads a local image file and base64-encodes it | After the run | It is a different repository with its own plan, and it depends on the release. |
| 4 | In the HCP Editor, raise the framework constraint and grant `AddMediaDocument` (and the other media methods as wanted) to agent API keys, then complete that plan's Human Action 2 (point the Content API README's image section at `AddMediaDocument`) | After the run | Version constraints and key grants are user decisions, and they depend on the release. |

## Acceptance Criteria
- AC-01: For a method implementing `JSONRequestInterface`, a registered non-header, non-reserved parameter resolves from the JSON body. When the key is absent from the body, it falls back to the request (query/POST). The body value wins when both are present. Methods without `JSONRequestInterface` behave exactly as before.
- AC-02: A JSON body that is not valid JSON, or whose top level is not an object, is rejected with 182801 in both `process()` and `processReturn()` modes. An empty body is treated as an empty object.
- AC-03: `JSONParameter` accepts a native JSON object or array from the body as well as a JSON string.
- AC-04: `base64File()` accepts plain base64 and `data:<mime>;base64,` URIs (whitespace ignored), exposes the decoded content and size, and rejects:
  - non-base64 input with 183508
  - decoded content above the limit with 183509
  - The default limit is `APP_API_MAX_FILE_CONTENT_SIZE` (10 MiB unless configured), and `setMaxSize()` overrides it.
- AC-05: In development-mode error responses, long values in the echoed JSON body are summarised: every string longer than 512 characters, at any nesting depth and whether or not its key is a registered parameter, is replaced with `[N characters truncated]`. Shorter values are echoed unchanged.
- AC-06: The OpenAPI spec maps `Base64 File` parameters to `{type: string, contentEncoding: base64}` and lists all five media operations with their security requirement.
- AC-07: `AddMediaDocument` (right `CreateMedia`):
  - creates a media record and file from `fileName` and `fileContent`
  - sets the name (default: the file base name), description, keywords, size and the API key's pseudo-user as author
  - returns the `mediaDocument` shape, including image dimensions for images
- AC-08: `AddMediaDocument` rejects:
  - an unsupported extension (681001)
  - a `mediaType` mismatch (681002)
  - content not matching the extension (681003)
  - an empty file (681004)
  - an invalid file name (681006)
  - HTML in description/keywords (681012)
  - over-length name/description/keywords (183507)
  - SVG files are accepted.
- AC-09: A dry run of `AddMediaDocument`, `UpdateMediaDocument` or `DeleteMediaDocument` performs the same validation as the real call (an input rejected by a real call is rejected with the same code by its dry run), returns the would-be result with `dryRun: true`, and leaves the DB and the storage folder unchanged, with no temp file written. A dry-run Add returns `mediaID: null`.
- AC-10: `GetMediaDocument` (right `ViewMedia`) returns the document shape for an existing ID, includes base64 `content` only when `includeContent=true`, and rejects an unknown ID with 681010 and HTTP status 404 (`ErrorResponse::makeNotFound()`). `UpdateMediaDocument` and `DeleteMediaDocument` reject an unknown ID the same way.
- AC-11: `GetMediaDocuments` (right `ViewMedia`) supports `search`, `mediaTypes`, `extensions`, `orderBy`, `orderDir`, `limit` (1–200) and `offset`, and returns `total` independent of paging. Additionally:
  - Without parameters it returns at most 50 documents, newest first.
  - `limit` outside 1–200 or a negative `offset` is rejected with a validation error.
  - `search` matches terms in name, keywords and description.
  - Paging is stable when documents share a `dateAdded` value: no document appears on two pages or is skipped.
- AC-12: `UpdateMediaDocument` (right `EditMedia`):
  - updates name/description/keywords (empty clears description/keywords) and optionally replaces the file
  - reports `modified`
  - rejects a request with no changes (681011), an extension change (681005), and `fileName` without `fileContent` or the reverse
- AC-13: `DeleteMediaDocument` (right `DeleteMedia`) deletes the record and its file and returns the pre-deletion document shape with `deleted: true`.
- AC-14: Each media method is denied (183005/183006) for an API key without a grant for that method, and succeeds with a grant.
- AC-15: When `MediaDocumentWriter::supportsMetadata()` returns `false` (the columns-absent case, simulated by overriding it in a writer subclass supplied through `MediaAPITrait::createWriter()`), `AddMediaDocument` and `UpdateMediaDocument` reject a non-empty description or keywords value with 681007, and `MediaDocumentWriter::create()` still creates the record without those columns.
- AC-16: A failure after the file was written during `MediaDocumentWriter::create()` removes the written file. `replaceFile()` refreshes the stored size and clears cached document data.
- AC-17: Each document type validates its own content in memory through `isContentValid(string $content, string $extension)`, without filesystem access: JPEG/PNG/GIF through image type detection, SVG through its root element, PDF through its signature. A new document type class without `isContentValid()` fails at class level (abstract). Validation failures are reported only as `MediaWriterException` with a `6810xx` code.
- AC-18: Over real HTTP, a JSON body request to `TestJSONBodyParamsMethod` resolves string, integer, boolean, JSON and base64 parameters from the body, and query-string fallback values are used for keys absent from the body.
- AC-19: A declared removed name (`addRemovedParam()`) sent inside a JSON body is rejected with 183009. A removed name sent in the query string is still rejected for both JSON and non-JSON methods.
- AC-20: `changelog.md`, `VERSION` and `AGENTS.md` §5 state the same new version, and the Media module and API docs describe the new methods.

## Testing Strategy
- **Unit level:**
  - value sources with synthetic arrays
  - each parameter type through a stub JSON method (`setRequestBody()`)
  - `Base64FileParameter` edge cases
  - per-type content checks against `tests/files/Media/` fixture bytes and synthetic in-memory strings (no filesystem needed)
  - `MediaFileContent`/`MediaFileValidator` without DB
- **Integration level (DB):**
  - `MediaDocumentWriter` inside the test transaction, asserting record columns and files on disk
  - each API method in-process through `processReturn()` with `setRequestBody()` for JSON methods and API keys created with `createTestAPIKeyForMethod()`, selected as in `KeyAuthorizationTest`
  - Response key order is asserted with `ResponseKeySorter::sortKeys()` (insight `7097dd32-…`).
  - The storage folder is checked before and after for dry-run assertions.
- **Live HTTP:** one `#[Group('live-http')]` test proving body resolution through the real dispatcher.
- **OpenAPI:** `TypeMapperTest`/`ParameterConverterTest` additions plus a generator-level assertion on the media operations.
- Files created by tests are deleted in `tearDown()`, following `ImageMediaTestTrait`'s `$testMedia` cleanup.

## Test Plan
- `tests/AppFrameworkTests/API/Parameters/ValueSourceTest.php` (new): `RequestValueSource` parity with `getRequestParam()->get()`; `JSONBodyValueSource` body-wins, fallback, `getKeys()` union, and `hasKey()` with null values. — AC-01
- `tests/AppFrameworkTests/API/Parameters/JSONBodyParamResolutionTest.php` (new), using a JSON stub method with `setRequestBody()`:
  - each of the six types resolves native body values
  - header and reserved parameters stay request-only
  - non-JSON methods are unchanged
  - an invalid body, a non-object body and an empty body (AC-02) in `processReturn()`
  - dev error data truncates a 513-character string under a registered key, under an unregistered key and inside a nested object, and leaves a 512-character string unchanged (AC-05)
  - Covers AC-01, AC-02, AC-05
- `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php` (modified): native array from the body is accepted. — AC-03
- `tests/AppFrameworkTests/API/Parameters/Base64FileParameterTest.php` (new): plain base64, data URI, whitespace, invalid input (183508), over limit (183509), default limit from config, `setMaxSize()`, decoded content/size, `selectValue()`. — AC-04
- `tests/AppFrameworkTests/API/OpenAPI/TypeMapperTest.php` (modified): `Base64 File` mapping. — AC-06
- `tests/AppFrameworkTests/API/OpenAPI/ParameterConverterTest.php` (modified): a base64 parameter on a JSON method becomes a body property with `contentEncoding`. — AC-06
- `tests/AppFrameworkTests/API/OpenAPI/MediaOperationsSpecTest.php` (new): the generated spec contains the five media paths with security, and Add/Update have a `requestBody`. — AC-06
- `tests/AppFrameworkTests/API/RemovedParamJSONBodyTest.php` (new; unconditional): a removed name inside a JSON body → 183009; a removed name in the query string of a JSON method and of a non-JSON method → 183009. — AC-19
- `tests/AppFrameworkTests/Media/DocumentContentValidationTest.php` (new): fixture bytes read into strings — JPG/PNG/GIF/SVG/PDF valid; PNG bytes with extension `jpg`, text with `png`, non-SVG XML with `svg` and non-PDF with `pdf` invalid; synthetic in-memory strings need no files; a reflection check that every discovered type class implements `isContentValid()`. — AC-17
- `tests/AppFrameworkTests/Media/MediaFileValidatorTest.php` (new): unsupported extension, type mismatch, empty file and content mismatch each throw `MediaWriterException` with the expected `6810xx` code and `isValidationError() === true`; valid SVG returns its type ID; no temp file is created during `validate()` (temp folder unchanged); `MediaFileContent` file-name rules (681006). — AC-08, AC-09, AC-17
- `tests/AppFrameworkTests/Media/MediaDocumentWriterTest.php` (new): `create()` sets all columns and writes the file; author is the passed user; a forced failure after the write removes the file (through a writer subclass/test seam that throws after `setSourceFile()`); `replaceFile()` updates size and rejects an extension change; `updateMetadata()` modified flag; `delete()` removes the file; with a test-local writer subclass whose `supportsMetadata()` returns `false`, `create()` succeeds and does not pass the description/keywords/size columns to `createNewRecord()`. — AC-07, AC-15, AC-16
- `tests/AppFrameworkTests/API/ErrorResponseTest.php` (modified): `makeNotFound()` sets HTTP status 404, asserted through `ErrorResponse::getHttpStatusCode()` (L183). The method tests assert the 404 through `ErrorResponsePayload::getHttpStatusCode()` in `processReturn()` mode. — AC-10
- `tests/AppFrameworkTests/Media/MediaFilterCriteriaTypeTest.php` (new): `selectMediaTypes()` filters by type. — AC-11
- `tests/AppFrameworkTests/Media/API/AddMediaDocumentAPITest.php` (new): success for PNG and SVG (shape, dimensions, author = pseudo-user, key order); each rejection code; each rejection code is also returned by the same input with `dryRun=true` (data provider over both modes); dry run leaves the DB row count and storage folder unchanged and returns `mediaID: null`; no grant → denied; columns-absent path through a test-local subclass of `AddMediaDocumentAPI` (defined in the test file, outside every API methods folder, created with `new …(APIManager::getInstance())`) whose `createWriter()` returns a writer with `supportsMetadata() === false`: a non-empty description or keywords → 681007, and an upload without them succeeds. — AC-07, AC-08, AC-09, AC-14, AC-15
- `tests/AppFrameworkTests/Media/API/GetMediaDocumentAPITest.php` (new): shape, `includeContent` round-trips the bytes, unknown ID 681010 with HTTP status 404, grant check. — AC-10, AC-14
- `tests/AppFrameworkTests/Media/API/GetMediaDocumentsAPITest.php` (new): parameterless call returns at most 50 newest-first; search matching on name, on keywords only, and on description only; type and extension filters; order; paging with stable `total`; limit 0 and 201 and offset -1 rejected; grant check. — AC-11, AC-14
- `tests/AppFrameworkTests/Media/MediaFilterCriteriaOrderTest.php` (new): several records with identical `media_date_added`, paged one item at a time in both directions, yield every ID exactly once in primary-key order within the tie. — AC-11
- `tests/AppFrameworkTests/Media/API/UpdateMediaDocumentAPITest.php` (new): metadata update, clearing, file replacement, no-changes 681011, extension change 681005, pair rule, dry run unchanged, unknown ID 681010 with HTTP 404, columns-absent 681007 through the same `createWriter()` subclass pattern as the Add test, grant check. — AC-09, AC-10, AC-12, AC-14, AC-15
- `tests/AppFrameworkTests/Media/API/DeleteMediaDocumentAPITest.php` (new): deletion removes the record and file, response captured before deletion, dry run keeps both, unknown ID 681010 with HTTP 404, grant check. — AC-09, AC-10, AC-13, AC-14
- `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php` (new, `#[Group('live-http')]`): real HTTP POST with a JSON body to `TestJSONBodyParamsMethod` (body values, query fallback, base64 size). — AC-18
- `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` (existing, run): the index contains the five media methods and the fixture after rebuild. — AC-06
- Documentation/version consistency is checked by the reviewer against `changelog.md`, `VERSION` and `AGENTS.md`. — AC-20

## Documentation Updates
- `src/classes/Application/API/README.md`:
  - Key Concepts: a new "JSON body parameters" bullet (resolution order, fallback, empty body, 182801, long-string truncation in the dev echo, removed-key detection in bodies)
  - folder table: `Parameters/` row mentions `ValueSource/`
  - the HTTP status passage (the `makeUnauthorized()`/`makeForbidden()` lines near L78–L92) also names `ErrorResponse::makeNotFound()` (404) for unknown record IDs
- `src/classes/Application/API/Parameters/README.md`: Key Concepts bullets "Value sources" (including `getKeys()` as the removed-parameter key source) and "Base64 file parameter" (limit config, data URIs, codes 183508/183509); Folder Structure row `ValueSource/`.
- `src/classes/Application/API/Parameters/module-context.yaml`: keywords for `ValueSource` and `Base64FileParameter`, and include `ValueSource/` in the documents.
- `src/classes/Application/API/OpenAPI/README.md`:
  - type mapping table row `Base64 File` → `string` + `contentEncoding: base64`
  - note that JSON-body parameters now resolve from the body
- `src/classes/Application/Media/README.md` (new):
  - module purpose, domain classes (legacy and namespaced), document types and `isContentValid()`
  - `Writer/` services, rights, configuration
  - link to `API/README.md`
- `src/classes/Application/Media/API/README.md` (new):
  - the five methods with rights, request examples (JSON body with base64), response shape, dry-run semantics
  - the single client-facing error-code table (6810xx with `MediaWriterException::VALIDATION_*` named as the source for 681001–681007, `MediaAPIInterface` codes 681010–681012, 183508/183509, 182801)
  - size limit and `post_max_size` guidance
  - SVG note, duplicate-upload note
- `src/classes/Application/Media/module-context.yaml` (new): module `media` (label "Media"); related modules `api`, `api-parameters`, `db-helper`; documents for overview, core architecture (domain + writer) and API.
- `AGENTS.md`:
  - §1 module table gains a "Media" row (`.context/modules/media/`)
  - §1 note at L34 ("`.context/` is committed to VCS") is reworded to match the L107 directive: `.context/` is committed to VCS on `main` only, as part of the pre-release flow
  - §5 version updated (step 15)
- `docs/agents/project-manifest/constraints.md`:
  - "API Methods": JSON-body methods read parameters from the body via the parameter system, never by hand from `getRequestData()`; file content uses `base64File()`
  - Media: create and modify media through `MediaDocumentWriter`; document types must implement `isContentValid()`
- `docs/agents/project-manifest/testing.md`: add `JSONBodyParamsLiveHTTPTest` to the live-HTTP table (L404–L407).
- `docs/agents/project-manifest/modules-overview.md` and `module-glossary.md`: regenerated by `composer build-docs`. These generated manifest files are exempt from the "no `.context/` on feature branches" rule (`AGENTS.md` L107 covers `.context/` only), so their diffs for the new module are committed with the feature as the manifest rules require; `.context/` itself is not.
- (Removed after design review: the annotation of `docs/agents/plans/2026-10-16-api-removed-parameter-declaration/plan.md`. That plan folder no longer exists in the repository; the changelog entry below records the resolved gap.)
- `changelog.md`: new top entry (proposed `v7.7.0 - Media Document APIs`) in house style:
  - API: JSON-body parameters
  - API: removed parameters are now also detected inside JSON request bodies
  - API: long strings are truncated in the development-mode request echo
  - API: base64 file parameter
  - API: `ErrorResponse::makeNotFound()` (HTTP 404)
  - Media: five API methods
  - Media: per-type content validation and writer
- `VERSION`: the new version (step 15).

## Deferred Items

| # | Deferred Item | Origin | Reason Deferred | Notes |
|---|---------------|--------|-----------------|-------|
| 1 | In-use guard or usage veto before deleting media (app-provided usage resolvers, e.g. HCP mail contents) | Planning (Risks) | The framework has no usage concept. A hook without a consumer implementation would be speculative. | Reconsider when the HCP Editor needs safe agent-driven deletion. A listener-based veto on `MediaDocumentWriter::delete()` is the natural seam. |
| 2 | Content-hash deduplication | User decision | Retries are not expected once an ID is obtained | Needs a `media` column plus a migration. |
| 3 | Media tagging over the API | Planning | Not needed by the consumer | `MediaFilterCriteria` already supports tag filters. |
| 4 | Route `MediaSettingsManager` through `MediaDocumentWriter` | Structural Improvements | Untested file-element UI path | Do it together with a form-upload test harness. |
| 5 | Consolidate `Application_Uploads::validate()` onto `isContentValid()` | Structural Improvements | Legacy upload path not touched | Reconsider when the image uploader is modernised. |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Body resolution changes behaviour for existing JSON methods** | Only two test fixtures use `JSONRequestTrait`, and neither registers parameters. The request fallback preserves query parameters. `JSONBodyParamResolutionTest` locks the non-JSON path as unchanged. |
| **Lazy body parsing now happens during validation and could fail outside the old error path** | Parsing errors use `errorResponse(182801)->send()`, which works in both modes. AC-02 tests `processReturn()` explicitly. Unexpected throwables still hit the shared boundary. |
| **Large payloads exhaust memory or exceed `post_max_size`** | A configurable decoded limit (10 MiB default) is checked right after decoding. `post_max_size` guidance is in the README. The body is parsed once and shared by the parameter system and `getRequestData()`. |
| **Orphan or lost files** | Dry runs never mutate. The writer deletes a written file on failure. Delete is only called in non-dry runs. Tests assert the storage folder state. |
| **Stale cached documents after file replacement** | `replaceFile()` clears `Application_Media`'s document cache, and AC-16 tests it. |
| **SVG served from the app origin can carry scripts** | Accepted per the user decision, unchanged from the admin UI. Delivery behaviour is unchanged. Consuming apps restrict SVG where needed. API access requires an explicitly granted key with `CreateMedia`. |
| **Agents delete media still referenced by HCP contents** | `DeleteMediaDocument` must be granted per key. The README warns about missing usage checks. Deferred Item 1 holds the long-term guard. |
| **Removed-parameter key source changes behaviour for existing methods** | `RequestValueSource::getKeys()` returns the same request parameter names as before, so non-JSON methods are unaffected. `array_fill_keys()` adapts the key list to `detectUsed()`'s associative contract. `RemovedParamJSONBodyTest` covers body and query cases for both method kinds. (This replaces the former "sibling plan not implemented" risk, which no longer applies: the feature shipped in `v7.6.0`.) |
| **Echo truncation hides a short-but-relevant value** | Only strings over 512 characters are truncated, and the summary keeps the length. The echo is development-only. |
| **Error codes collide with application codes** | `681xxx` and 183508/183509 are grep-verified free in the framework and the HCP Editor, and re-checked at implementation. |
| **`composer dump-autoload` wipes `method-index.json`** | Step 11 rebuilds it through `MethodIndexEntryTest` and verifies the entries, per `testing.md`. |

## Recommended Workflow
- **Workflow:** ledger
- **Rationale:** The plan spans two modules (an API parameter-resolution change that affects every JSON method, and a new media write path with five methods, including file I/O and destructive operations). It departs from an existing pattern and needs formal QA, a security audit (uploads, SVG, size limits, redaction, rights) and code review.
