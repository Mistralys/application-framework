# Plan

## Plan Audit Cycles
- Audits: 4 (Sonnet 5.5 ×4) — Plan Auditor v1.12.0
- Architectural Reviews: 2 (Sonnet 5.5 ×2) — Plan Architect Reviewer v2.3.4

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
- **API Lego Rule (HCP Editor, `docs/agents/project-manifest/constraints.md` L696–L730).** Every shared API feature is a self-contained interface+trait pair. Traits never `use` other API traits, and each method explicitly `use`s the trait of every interface it implements. Validation codes come in one range per interface. A user review on 2026-10-16 found that the first version of this plan bundled the media features into one catch-all `MediaAPIInterface`/`MediaAPITrait`. The plan now splits them into three media pieces and one framework pagination piece (Approach §3a and §4). A fourth piece for writer access was dropped in the second architectural review (Decision 1), together with the API's columns-absent path.

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

The methods are built from composable interface+trait pieces, following the API Lego Rule. Each method implements and `use`s exactly the pieces it needs:
- **Media pieces:** document lookup by `mediaID`, metadata parameters, file upload parameters.
- **Framework piece:** a generic pagination piece in the API module, which the existing HCP Editor list methods can adopt later.

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
- **API method composition (Lego Rule).**
  - Shared features are interface+trait pairs, such as `Traits/DryRunAPIInterface.php` + `DryRunAPITrait.php`.
  - Records are resolved through a `manage{X}Params()` method that returns a `BaseParamsHandlerContainer` subclass. Its parameter handlers (`BaseParamHandler`) register their parameter through `APIParamManager::registerParam()`. The framework reference is `src/classes/Application/Countries/API/` (`AppCountryAPIInterface`, `AppCountryAPITrait`, `AppCountryParamsContainer`, `Params/AppCountryIDHandler`, `Params/AppCountryIDParam`).
  - The rule itself is documented only in `hcp-editor/docs/agents/project-manifest/constraints.md` (L696–L730). The framework's `docs/agents/project-manifest/constraints.md` (L246–L250) has one sentence about composition.
  - Paging is hand-written per method in the HCP Editor (`GetMailingsAPI`, `GetComtypesAPI`), with the response keys `limit`, `offset` and `totalCount`.
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
  - **Re-entry guard** (audit cycle 3, Major 1). `BaseAPIMethod::errorResponse()` (L636–L640) calls `collectRequestErrorData()` while building every error response, and in development that reads `getRequestData()`. `getRequestBody()` (L492–L505) can itself send an error response. Without a guard, an unparseable body recurses without end. `getRequestData()` therefore works in this order:
    1. If `$requestData` is already set, return it. This is the only re-entry point, and it never parses again.
    2. Before reading the body, assign a provisional empty collection to `$requestData`. Any error raised while reading or parsing, and any echo built for it, sees this empty collection instead of starting a second parse.
    3. Read the body and parse it. On success, replace the provisional collection with the parsed data.
    4. On failure (invalid JSON or a non-object top level), keep the empty collection, record the echo form of the raw body in a new private `?string $unparseableBody` (the raw body when it is at most `ECHO_MAX_STRING_LENGTH` characters, otherwise `[N characters truncated]`), and only then send 182801.
  - `collectRequestData()` just calls `getRequestData()`.
  - `collectRequestErrorData()` walks the body data recursively and replaces every string value longer than a class constant `ECHO_MAX_STRING_LENGTH = 512` with `[N characters truncated]` before echoing. This covers registered and unregistered keys alike (for example a mistyped `fileContent`), with no parameter lookup. (Design review Decision 3: this replaces the previously planned `OpaqueValueParamInterface` marker, which is dropped.)
  - **Echo for an unparseable body.** When `$unparseableBody` is set, `collectRequestErrorData()` returns `array(JSONRequest => array(unparseableBody => <recorded echo form>))` instead of the (empty) body data. The new key constant `RESPONSE_KEY_ERROR_UNPARSEABLE_BODY = 'unparseableBody'` sits on `JSONRequestInterface` beside `RESPONSE_KEY_ERROR_JSON_REQUEST_DATA` (L22). It reads only the recorded string, never the parser, so it cannot re-enter. A short malformed body stays visible for debugging, and a long one is summarised under the same 512-character rule. Outside development, the existing early `return array()` still applies.
- **Removed-parameter integration.** `BaseAPIMethod::rejectRemovedParams()` (shipped in `v7.6.0`, L279) passes `array_fill_keys($this->manageParams()->getValueSource()->getKeys(), true)` to `detectUsed()` instead of `$this->request->getParams()`. `detectUsed()` checks `array_key_exists()` on an associative array, so the key list is turned into a key-indexed array. Removed keys inside JSON bodies are then detected too. For non-JSON methods, `RequestValueSource::getKeys()` returns the request parameter names, so behaviour is unchanged.

### 2. Base64 file parameter type
- **New** `src/classes/Application/API/Parameters/Type/Base64FileParameter.php`:
  - `resolveValue() : ?string`:
    - An absent or empty value is `null`.
    - A non-string is a `VALIDATION_INVALID_VALUE_TYPE` warning.
    - An optional `data:<mime>;base64,` prefix and all whitespace are stripped.
    - Size pre-check, before decoding: the decoded size is computed from the normalised length as `intdiv(strlen($normalised) * 3, 4) - $padding` (exact for well-formed input, padded or not). Above the limit, the value is rejected with `VALIDATION_FILE_SIZE_EXCEEDED` (183509, new; the message names both sizes) and `base64_decode()` is never called (design review 2, Decision 2).
    - A strict `base64_decode($value, true)` failure is `VALIDATION_INVALID_BASE64` (183508, new).
    - The method returns the decoded content. The normalised base64 is a local variable that goes out of scope when `resolveValue()` returns.
  - `getValue() : ?string` returns the decoded binary content, the single value `BaseAPIParameter` caches (`$value`, L125). `getDecodedContent() : ?string` is a typed alias of `getValue()`, and `getDecodedSize() : int` is `strlen()` of it. No generic `getValue()` caller in the API module serializes or echoes values (research brief, review-integration area), so a binary value is safe.
  - Memory: the parameter retains exactly one copy, the decoded content. The previously planned retained normalised base64 copy is dropped (design review 2, Decision 2). Oversized input is rejected from its encoded length, so it is never decoded. The parameter never writes a temp file. The methods hand `getDecodedContent()` straight to `MediaFileContent::fromBinary()`.
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
    2. calls `MediaCollection::createNewRecord()` with name, author ID, extension, type, size, description and keywords, always. The metadata columns are a precondition of the writer and the media API (Assumptions); there is no columns-absent branch (design review 2, Decision 1).
    3. writes `$temp = $content->writeTempFile()` and calls `getMediaDocument()->setSourceFile($temp)` (the only temp-file use, after validation has passed)
    4. calls `$this->verifyWrittenFile($record, $content)` (below)
    5. deletes the temp file (in a `finally`, so it is also removed on failure)
    6. if anything throws after the target file was written (step 4 included), deletes the target file before rethrowing
  - `protected verifyWrittenFile(MediaRecord $record, MediaFileContent $content) : void` (audit cycle 3, Major 2): the named post-write step of `create()` and `replaceFile()`.
    - Production behaviour: it checks that the stored file (`getMediaDocument()->getPath()`) exists and that its size equals `$content->getSize()`. Otherwise it throws `MediaWriterException::ERROR_FILE_WRITE_FAILED` (681102). This catches a silently truncated copy, for example on a full disk, which `setSourceFile()` does not report.
    - It is `protected` and non-`final`, so it doubles as the test seam. `MediaDocumentWriterTest` uses a test-local subclass that calls the parent and then throws, which drives the cleanup branch of `create()` (AC-16) and the restore branch of `replaceFile()`. The seam has a real production duty, so it is not test-only structure.
  - `replaceFile(MediaRecord $record, MediaFileContent $content) : void`:
    - The extension must equal the record's (parity with `MediaSettingsManager::validateFile()`).
    - Order of operations (audit cycle 3, Minor 2). The file is overwritten last, and a failure after the overwrite restores the old bytes:
      1. Validate through `MediaFileValidator::validate()`.
      2. Store the new content's size on the record with `$record->setRecordKey(MediaCollection::COL_SIZE, $content->getSize())` followed by `$record->save()` (`BaseRecord.php` L243). A DB failure here throws before the file is touched. `MediaRecord::refreshFileSize()` is deliberately not used: it reads the size of the file currently on disk (`MediaRecord.php` L123–L130), which at this point is still the old file (audit cycle 2, Minor 1).
      3. Copy the current stored file to a backup temp file (`Application::getTempFile()`).
      4. Write the new content to the existing path, then call `verifyWrittenFile()`.
      5. If step 4 throws, copy the backup back over the stored path before rethrowing. The caller's transaction rollback restores the old size, so disk and DB agree again.
      6. Delete the backup temp file in a `finally`.
    - Clears the `Application_Media` document cache (`clearCollection()`) so cached image dimensions are not stale.
    - Residual window, accepted and documented in the Media API README: a failure after `replaceFile()` has returned, such as a failing transaction commit, leaves the new bytes on disk with the old size in the DB. File operations are not transactional (`MediaRecord.php` L141–L149), and this window is outside the writer's reach.
  - Callers that change both metadata and the file call `updateMetadata()` before `replaceFile()`, so the file overwrite is the last mutating writer call in a request (see the write-method shape in §4).
  - `updateMetadata(MediaRecord $record, ?string $name, ?string $description, ?string $keywords) : bool`: `null` means unchanged. Returns whether anything changed.
  - `delete(MediaRecord $record) : void`: delegates to `MediaCollection::deleteRecord()`.
  - (Removed in design review 2, Decision 1: the overridable `supportsMetadata()` proxy for `MediaCollection::hasSizeColumn()`. It existed only to serve the API's columns-absent path and its test seam, both of which are dropped. `hasSizeColumn()` itself stays untouched for its existing UI and search callers.)
  - The class is not declared `final`, so `MediaDocumentWriterTest` can subclass it and override `verifyWrittenFile()` to force a failure after the file write (AC-16).
- **New** `src/classes/Application/Media/Writer/MediaWriterException.php` extends `MediaException` (`src/classes/Application/Media/MediaException.php`, the module's failure idiom). Its `VALIDATION_*` constants are the single source of truth for the writer's client-facing codes; no other class re-declares them.
  - `isValidationError() : bool` (code in the `6810xx` block), so callers can tell client errors from internal ones.
  - client-facing validation codes `6810xx` (`VALIDATION_*`):
    - 681001 unsupported extension
    - 681002 type mismatch
    - 681003 content does not match extension
    - 681004 empty file
    - 681005 extension change not allowed
    - 681006 invalid file name
    - 681007 is not allocated. It was planned as `VALIDATION_METADATA_NOT_SUPPORTED` for the columns-absent path, which design review 2 (Decision 1) dropped; the number stays unused rather than being reassigned.
  - internal error codes `6811xx` (`ERROR_*`):
    - 681101 no transaction
    - 681102 file write failed
- **Modified** `MediaFilterCriteria`: `selectMediaType(string)` / `selectMediaTypes(array)` (`COL_TYPE` IN filter), for the list method's type filter.

### 3a. Pagination piece (API module)
This is a generic interface+trait pair beside `DryRunAPIInterface`/`DryRunAPITrait`, in the same style. Any list method combines it; `GetMediaDocuments` is the first consumer, and `GetMailingsAPI`/`GetComtypesAPI` in the HCP Editor are the growth path (Deferred Item 6).
- **New** `src/classes/Application/API/Traits/PaginatedAPIInterface.php`, which extends `APIMethodInterface`:
  - Parameter names: `PARAM_LIMIT = 'limit'` and `PARAM_OFFSET = 'offset'`.
  - Response keys: `KEY_LIMIT = 'limit'`, `KEY_OFFSET = 'offset'` and `KEY_TOTAL_COUNT = 'totalCount'`. These match the existing HCP response keys, so adopting the piece there changes no response.
  - Validation codes `1837xx` (grep-verified free): `VALIDATION_INVALID_LIMIT = 183701` and `VALIDATION_INVALID_OFFSET = 183702`.
  - `registerPaginationParams(int $defaultLimit, int $maxLimit) : self`
  - `getPaginationLimit() : int` and `getPaginationOffset() : int`
  - `applyPagination(Application_FilterCriteria $criteria) : void`, which calls `setLimit($limit, $offset)` through the public signature only
  - `addPaginationResponseKeys(ArrayDataCollection $response, int $totalCount) : void`, which writes the three keys
  - `getPaginationKeyDescriptions() : KeyDescription[]`, which the method merges into its `getResponseKeyDescriptions()`
- **New** `src/classes/Application/API/Traits/PaginatedAPITrait.php`:
  - Registers two `IntegerParameter`s through `manageParams()->registerParam()`, so their values come from the value source of step 2. Their descriptions state the default, the maximum and that out-of-range values are rejected.
  - A `limit` outside 1–max is rejected with 183701, and a negative `offset` with 183702, through `validateByCallback()`. Values are rejected rather than clamped, so a client never mistakes a truncated page for a complete one.
  - It holds no other trait.

### 4. Media API module
**New** folder `src/classes/Application/Media/API/`. Shared features are separate interface+trait pieces that the methods combine, following the API Lego Rule. No piece `use`s another trait. Every piece interface extends `APIMethodInterface` directly; there is no inheritance between media pieces (design review 2, Decision 1).

**Module-level classes**
- `MediaAPIGroup.php`: a singleton `GenericAPIGroup` (`media`, "Media", "Upload and manage media documents").
- `MediaAPIResponseInterface.php`: `KEY_*` constants for the document shape. It contains constants only (design review guidance) and is implemented by every media method.
  - `mediaDocument`, `mediaDocuments`, `mediaID`, `name`, `mediaType`, `mediaTypeLabel`, `extension`, `fileName`, `fileSize`, `dateAdded` (ATOM), `authorID`, `description`, `keywords`
  - `image` (`width`, `height`, `isVector`, `isAnimated`; `null` for non-images)
  - `content` (base64, only on request)
  - `modified`, `deleted`, `dryRun`
  - The list keys `limit`, `offset` and `totalCount` come from `PaginatedAPIInterface` and are not re-declared here.
- `MediaDocumentSerializer.php`:
  - `static serialize(MediaRecord $record, bool $includeContent=false) : array`
  - `static serializePreview(MediaFileContent $content, string $typeID, string $name, …) : array`, the dry-run shape with `mediaID: null`
  - `static buildExample(bool $withImage=true) : array`, a fixed, DB-free example for documentation
- (Removed in design review 2, Decision 1: `MediaAPIException.php`. It had no thrower: the API codes are sent through `errorResponse()`, and the writer codes belong to `MediaWriterException`. `CountryAPIException` exists because `AppCountryParamRule` throws it; a media API exception can be added when a real caller appears.)
- The API validation codes live in a new block, `6812xx` (grep-verified free). It is separate from the writer's `6810xx` validation block, which `MediaWriterException::isValidationError()` recognises, so the two cannot be confused. Each piece owns its own sub-range.

**Piece 1: media document lookup** (used by Get, Update and Delete)
- `MediaDocumentAPIInterface`, which extends `APIMethodInterface`:
  - `PARAM_MEDIA_ID = 'mediaID'`
  - `VALIDATION_UNKNOWN_MEDIA_ID = 681201` (sub-range `68120x`)
  - `manageMediaDocumentParams() : MediaDocumentParamsContainer`
- `MediaDocumentAPITrait`: creates the container lazily (the `AppCountryAPITrait` shape).
- `MediaDocumentParamsContainer extends BaseParamsHandlerContainer` (the `AppCountryParamsContainer` / `MailParamsContainer` shape):
  - `manageID() : MediaIDHandler`
  - `resolveValue() : ?MediaRecord`
  - `selectMediaDocument(MediaRecord $record) : self`
  - `isValidValueType()`
  - `requireValue() : MediaRecord`, overridden as in `MailParamsContainer`. When no record resolves, it sends `errorResponse(VALIDATION_UNKNOWN_MEDIA_ID)->makeNotFound()->setErrorMessage()->send()` (HTTP 404, through the new `ErrorResponse::makeNotFound()` below) instead of the generic 183004.
- `Params/MediaIDHandler extends BaseParamHandler` (the `AppCountryIDHandler` shape). `resolveValueFromSubject()` returns `$this->getParam()?->getMediaRecord()`.
- `Params/MediaIDParam extends IntegerParameter`:
  - Required, and described as the media document ID.
  - `getMediaRecord() : ?MediaRecord` uses `AppFactory::createMediaCollection()->idExists()`/`getByID()`.
  - Unlike `AppCountryIDParam`, it uses neither `SelectableValueParamTrait` (live installs hold 10,000+ documents, which must not be enumerated) nor `validateByValueExistsCallback()`. Either would turn an unknown ID into the generic 183401 before the container can send the domain 404.
- Growth: any future method in any app that takes a `mediaID` combines this piece. The container is also where a second resolution handler (for example by name) would be added.

**Former Piece 2: media writer access — removed** (design review 2, Decision 1)
- The earlier `MediaWriterAPIInterface`/`MediaWriterAPITrait` held a lazy `createMediaWriter()` factory and `sendWriterValidationError()`. Its main justification was the test seam for the columns-absent path, which is dropped (Considered Alternatives, "Legacy-schema (columns-absent) support").
- Without the seam, the piece had no feature boundary of its own. It forced `MediaMetadataAPIInterface` and `MediaFileUploadAPIInterface` into interface inheritance, and made two pieces unusable without a third.
- Its two remaining duties move:
  - Writer construction: Add, Update and Delete create a local `new MediaDocumentWriter()` in their real (non-dry-run) branch. The writer is stateless apart from its collaborators, so there is nothing to share.
  - Error conversion: `sendWriterValidationError()` moves to the file upload piece (Piece 3), the only piece whose validation produces `MediaWriterException`s.

**Piece 2: media metadata parameters** (used by Add and Update)
- `MediaMetadataAPIInterface`, which extends `APIMethodInterface`:
  - `PARAM_NAME = 'name'`, `PARAM_DESCRIPTION = 'description'`, `PARAM_KEYWORDS = 'keywords'`
  - `MAX_NAME_LENGTH = 160`, `MAX_DESCRIPTION_LENGTH = 1200`, `MAX_KEYWORDS_LENGTH = 500`
  - `VALIDATION_HTML_NOT_ALLOWED = 681211` (sub-range `68121x`)
  - `registerMediaMetadataParams(bool $clearable) : self`
  - `getMediaName() : ?string`, `getMediaDescription() : ?string`, `getMediaKeywords() : ?string`, where `null` means not sent
  - `resolveMediaName(string $default) : string` (audit cycle 3, Minor 1): returns the sent name, or `$default` cut to `MAX_NAME_LENGTH` characters (`mb_substr()`) when no name was sent. A sent name is already length-checked by its parameter. The default never passes through that parameter, so the piece applies the same limit here. It takes a plain string, so the piece still depends on no other piece.
  - `validateMediaMetadata() : void`
- `MediaMetadataAPITrait`:
  - `registerMediaMetadataParams(false)` (Add) registers three `string()` parameters with `setMaxLength()`.
  - `registerMediaMetadataParams(true)` (Update) registers `name` as a string and `description`/`keywords` as `clearableString()`, where absent means unchanged and empty means clear.
  - `validateMediaMetadata()` is the single home of the HTML rule. It is called identically by dry and real runs:
    - HTML tags in description or keywords (`strip_tags($v) !== $v`) are rejected with 681211, as the admin form does with `addRuleNoHTML()`.
    - (Removed in design review 2, Decision 1: the columns-absent rule that rejected description/keywords with 681007. The metadata columns are a precondition; see Assumptions.)
  - It needs no writer, so the piece depends on no other piece.

**Piece 3: media file upload parameters** (used by Add and Update)
- `MediaFileUploadAPIInterface`, which extends `APIMethodInterface`:
  - `PARAM_FILE_NAME = 'fileName'`, `PARAM_FILE_CONTENT = 'fileContent'`
  - `registerMediaFileParams(bool $required) : self`
  - `hasMediaFile() : bool`
  - `getMediaFileContent() : ?MediaFileContent`
  - `validateMediaFile(?string $requiredTypeID=null) : string`
- `MediaFileUploadAPITrait`:
  - `registerMediaFileParams(true)` (Add) registers both parameters as required: `fileName` as a string and `fileContent` as `base64File()`.
  - `registerMediaFileParams(false)` (Update) registers them as optional, plus two `RequiredIfOtherIsSetRule`s that enforce the pair.
  - `getMediaFileContent()` builds `MediaFileContent::fromBinary()` once from `fileName` and `Base64FileParameter::getDecodedContent()`.
  - `validateMediaFile()` calls `MediaFileValidator::validate()` and returns the resolved type ID. It converts a `MediaWriterException` (including the 681006 invalid file name from the `MediaFileContent` constructor) through `sendWriterValidationError()`. This is the one validation call for both dry and real runs, so they cannot drift apart (design review Decision 6 caution).
  - `protected sendWriterValidationError(MediaWriterException $e) : never` (moved here from the removed writer piece) converts a validation exception (`isValidationError()`) to `errorResponse($e->getCode())->setErrorMessage()->send()`. Internal `6811xx` exceptions are rethrown to the boundary (insight `af333a25-…`). Add and Update also route `MediaWriterException`s from their real-branch writer calls (`create()`, `replaceFile()`) through it, so a validation code still reaches the client if the writer's own re-validation fires.
  - It owns no codes; the file codes are `MediaWriterException`'s `6810xx` (design review Decision 5).

**Methods**

`Methods/`: every method implements `APIKeyMethodInterface`, `JSONResponseInterface`, `JSONResponseWithExampleInterface` and `MediaAPIResponseInterface`, has version `1.0`, and uses regions A/B/C as in `GetAppCountriesAPI`. The composition table below is binding: each listed interface is implemented, and the trait of every listed piece is declared with its own `use`, including traits of inherited interfaces (Lego Rule 4). The `MediaWriter` column entries of the earlier table are removed with the piece (design review 2, Decision 1).

| Method | Framework pieces | Media / pagination pieces | Right |
|---|---|---|---|
| `AddMediaDocumentAPI` (`AddMediaDocument`) | `JSONRequest`, `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` | `MediaFileUpload`, `MediaMetadata` | `CreateMedia` |
| `GetMediaDocumentAPI` (`GetMediaDocument`) | `RequestRequest`, `JSONResponse`, `JSONResponseWithExample`, `APIKeyMethod` | `MediaDocument` | `ViewMedia` |
| `GetMediaDocumentsAPI` (`GetMediaDocuments`) | `RequestRequest`, `JSONResponse`, `JSONResponseWithExample`, `APIKeyMethod`, `Paginated` | none | `ViewMedia` |
| `UpdateMediaDocumentAPI` (`UpdateMediaDocument`) | `JSONRequest`, `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` | `MediaDocument`, `MediaFileUpload`, `MediaMetadata` | `EditMedia` |
| `DeleteMediaDocumentAPI` (`DeleteMediaDocument`) | `RequestRequest`, `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` | `MediaDocument` | `DeleteMedia` |

| Method | Method-local parameters | Result |
|---|---|---|
| `AddMediaDocument` | `mediaType` (enum of `getTypeIDs()`, optional constraint passed to `validateMediaFile()`), plus the pieces' `fileName`, `fileContent`, `name` (default = file base name, cut to 160 characters through `resolveMediaName($content->getBaseName())`, used identically by dry and real runs), `description`, `keywords` and `dryRun` | `mediaDocument` + `dryRun`. A dry run validates fully and returns a preview with `mediaID: null`; it never touches disk or DB. |
| `GetMediaDocument` | `includeContent` (boolean, default false), plus `mediaID` | `mediaDocument` (with `content` when requested) |
| `GetMediaDocuments` | `search` (free-text terms matched against name, keywords and description), `mediaTypes` (stringList, enum-checked), `extensions` (stringList), `orderBy` (`dateAdded`\|`name`\|`fileSize`, default `dateAdded`), `orderDir` (`asc`\|`desc`, default `desc`), plus the pagination piece's `limit` (default 50, max 200) and `offset` | `mediaDocuments[]` (no content), `totalCount`, `limit`, `offset` |
| `UpdateMediaDocument` | `VALIDATION_NO_CHANGES = 681221` is declared on the method class (sub-range `68122x`, since only this method raises it). Pieces supply `mediaID`, `name`, `description`/`keywords` (clearable), the `fileName` + `fileContent` pair and `dryRun`. | `mediaDocument`, `modified`, `dryRun`. At least one change is required (681221). A replaced file must keep its extension (681005). |
| `DeleteMediaDocument` | none; pieces supply `mediaID` and `dryRun` | `mediaDocument` (captured before deletion), `deleted`, `dryRun`. A dry run never calls `delete()`, because record deletion unlinks the file immediately. |

- **List defaults for `GetMediaDocuments`.** Live installs hold 10,000+ media documents, so the list is never unbounded:
  - A call without parameters returns the 50 most recent documents (`dateAdded desc`, `limit` 50, `offset` 0), never the whole table.
  - `limit` is capped at 200 through `registerPaginationParams(50, 200)`. Values outside 1–200 are rejected with 183701 and a negative `offset` with 183702 (the pagination piece). `totalCount` reports the full match count, so clients can page with `offset`.
  - `search`, `orderBy` and `orderDir` stay method-local (Structural Improvements). `search` uses the collection's existing search (`MediaCollection::getRecordSearchableColumns()`): name, keywords and description. Each term is matched with `LIKE %term%` against any of these fields, and terms are combined with AND. The built-in `AND`/`OR`/`NOT` operators work as in the UI. The media API requires the metadata columns (Assumptions), so all three fields are always searched. (The collection still narrows to the name on schemas without the columns, but the API no longer documents or tests that case; design review 2, Decision 1.)
  - Ordering is stable across pages. `media_date_added` has one-second resolution, and bulk uploads share timestamps, so ordering on it alone can duplicate or skip documents between pages. `MediaFilterCriteria` appends the primary key as a tie-breaker in the same direction (step 7).
  - Every listed document is serialized without content (`includeContent` exists only on `GetMediaDocument`), which keeps a 200-item page small.
- **Write-method shape** (Add, Update, Delete):
  1. An own `DBHelper` transaction when none is open.
  2. `collectRequestData()` stays empty for RequestRequest methods. JSON methods keep the trait's body handling.
  3. Domain validation runs before any mutation, and dry and real runs share the same calls. Only the mutating calls are skipped on a dry run. The shared calls are:
     - `validateMediaFile()` for file content
     - `validateMediaMetadata()` for the HTML rule
     - `manageMediaDocumentParams()->requireValue()` for the record
     - the method's own no-changes and extension-change checks
  4. The pseudo-user from the API key is the author. Only the real branch creates the writer (`new MediaDocumentWriter()`), so a dry run never holds one. `UpdateMediaDocument` calls `updateMetadata()` before `replaceFile()`, so the file overwrite is its last mutating call (audit cycle 3, Minor 2).
  5. Commit, or roll back on dry run. Dry runs of Add, Delete and Update-with-file avoid the mutating calls entirely, so no file is ever left behind or removed.
- **Not-found status.** **Modified** `src/classes/Application/API/ErrorResponse.php`: new `makeNotFound() : self`, which sets `Connectors_ResponseCode::HTTP_NOT_FOUND` (404). It sits beside the existing `makeBadRequest()`/`makeInternalServerError()`/`makeUnauthorized()`/`makeForbidden()` helpers (L251–L277), with the same docblock style naming the intended use (an unknown record ID). The helper is generic: any future method that resolves a record by ID reuses it. Its first caller is `MediaDocumentParamsContainer::requireValue()`.
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
- **Base64 memory profile** (design review 2, Decision 2, adopted). A 10 MiB upload is about 13 MB of base64 in the raw body and again in the parsed array. The parameter adds only the decoded content: the normalised base64 is no longer retained, and oversized input is rejected from its encoded length before any decoding. Both changes are one comparison and one return-value choice, so the cheaper profile costs nothing in design.
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
  - Each API piece has current consumers:
    - `MediaDocument`: Get, Update and Delete. Any future method that takes a `mediaID` is the growth path.
    - `MediaMetadata` and `MediaFileUpload`: Add and Update.
    - The former `MediaWriter` piece and `MediaAPIException` were removed in design review 2 (Decision 1): the piece's justification was a test seam for a path the API no longer supports, and the exception had no thrower.
    - `Paginated`: `GetMediaDocuments` now. The growth path is `GetMailingsAPI` and `GetComtypesAPI` in the HCP Editor (Deferred Item 6), which already hand-write the same parameters and the `totalCount` key.
- **Composable pieces instead of one module trait.** This follows the API Lego Rule. A single `MediaAPITrait` would give every method parameters and helpers it does not use; `GetMediaDocuments` needs none of them. It would also hide which method depends on which feature, and it would collect unrelated validation codes on one interface. Separate pieces keep each method's dependencies visible in its declaration, and each piece's codes in its own range.
- **No interface inheritance between media pieces** (design review 2, Decision 1; this replaces the earlier "interface inheritance where a piece genuinely needs another" rationale). The earlier design had `MediaMetadataAPIInterface` and `MediaFileUploadAPIInterface` extend `MediaWriterAPIInterface`, mirroring `LinkAPIInterface extends MailAPIInterface`. With the columns-absent check gone, the metadata piece no longer asks the writer, and error conversion lives in the upload piece itself. Every piece is therefore usable on its own. Traits still never `use` each other, and every consumer declares all the traits it needs (Lego Rule 4).
- **The metadata columns are a precondition, not a supported variant** (design review 2, Decision 1). The columns come from `docs/sql/2023-10-16-news.sql`. No API key can authenticate without the tables from `docs/sql/2025-11-05-api-management.sql`, and framework migrations apply in date order, so every install that can call the media API already has the columns. The only consumer, the HCP Editor, added them in `20231023_upgrade_psa_and_framework.sql`. Supporting the columns-absent case in the API cost a piece, an interface-inheritance edge, an error code, a test seam with non-final method classes, and an acceptance criterion, all for a schema that cannot reach the API.

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
| Post-write failure trigger for the writer test (audit cycle 3, Major 2) | Protected `verifyWrittenFile()` step with a real duty (stored file exists and has the expected size, else 681102), overridden by a test-local subclass | An empty protected `afterFileWritten()` hook; a throwing event listener; forcing a record-update failure | An empty hook is structure with only a test consumer. The writer fires no events today, so a listener would need new event infrastructure. A forced DB failure needs schema tricks inside the test transaction. The verification step catches silently truncated copies in production and makes the seam legitimate. |
| Dry run | Validate-only preview, no mutation | Mutate inside a transaction and roll back | Files are not transactional. A rollback leaves orphan files (create) or lost files (delete). |
| Method set | Full set: Add/Get/Get-list/Update/Delete | Add + Get only | User decision ("whole spectrum"). The methods share one serializer, one writer and the composable pieces, so the marginal cost is low. |
| Unknown media ID status | New `ErrorResponse::makeNotFound()` (HTTP 404) | Default status of `errorResponse()`; `makeBadRequest()` (400) | A distinct 404 lets HTTP clients tell "no such record" from "bad input" without parsing the code. The helper matches the four existing status helpers and costs one method plus a test. |
| Legacy-schema (columns-absent) support | Not supported by the API: the metadata columns are a documented precondition, the writer always writes them, and there is no seam, no 681007 code and no `MediaWriter` piece (design review 2, Decision 1; replaces the earlier "Columns-absent test seam" row) | Overridable `supportsMetadata()` reached through a `createMediaWriter()` factory in a `MediaWriterAPITrait` piece (the earlier choice); a reset hook for `MediaCollection::$hasSizeColumn`; a writer constructor flag `new MediaDocumentWriter(bool $hasMetadataColumns)` | No install that lacks the columns can authenticate an API key (the API tables are a 2025 migration, the columns a 2023 one). Every alternative keeps a code path, a code and tests for an unreachable schema; the earlier choice also forced interface inheritance between pieces. On such a schema, the insert fails and the boundary reports it. |
| `Base64FileParameter` memory and size check | Exact decoded size computed from the encoded length and checked before decoding; only the decoded content is retained and returned by `getValue()` (design review 2, Decision 2) | Decode then check, retaining both the normalised base64 and the decoded copy (the earlier choice); stream-decode to a temp file | The earlier choice held one redundant full copy and decoded oversized input before rejecting it. Streaming contradicts "the parameter never writes a temp file" and is overkill at 10 MiB. |
| Media API composition | Three media interface+trait pieces (document lookup, metadata, file upload) plus a framework pagination piece; one composition table per method. The writer-access piece of the previous revision is removed (design review 2, Decision 1). | One module-wide `MediaAPIInterface`/`MediaAPITrait` (the earlier draft of this plan); an abstract `BaseMediaAPIMethod` class | The single trait handed every method features it does not need and mixed four sub-ranges of codes on one interface, which breaks the API Lego Rule (HCP `constraints.md` L696–L730). A base class cannot be combined: Get and Delete need different subsets, and PHP has single inheritance. |
| Record lookup by `mediaID` | `manageMediaDocumentParams()` container with an ID handler (`AppCountryParamsContainer` shape), whose `requireValue()` sends the domain 404 | `registerMediaIDParam()` + `requireMediaRecord()` helpers (earlier draft); `MediaIDParam` with `validateByValueExistsCallback()` | The container is the framework's established record-resolution piece, supports `selectValue()` for chained `processReturn()` calls, and has room for further handlers. The exists-callback would answer an unknown ID with the generic 183401 and no 404. |
| Paging | Generic `PaginatedAPIInterface`/`PaginatedAPITrait` in the API module, with the HCP key names (`limit`, `offset`, `totalCount`) | Method-local `limit`/`offset` (a third hand-written copy after `GetMailingsAPI` and `GetComtypesAPI`); a media-specific paging piece | A third copy would repeat the duplication with a different maximum and response key. A media-only piece cannot be reused by HCP list methods. Matching the HCP key names keeps later adoption free of breaking changes. |
| Error codes | New `681xxx` block (grep-verified free) plus `183508`/`183509` in the parameter-validation block | Reuse generic 183003 for everything | Specific codes let agents react programmatically (wrong type versus too large versus unknown ID). Following the HCP rule of one range per interface, the writer keeps `6810xx`/`6811xx`, each media API piece gets a sub-range of `6812xx`, and the pagination piece gets `1837xx`. |

## Pattern Alignment
- **Follows** the API Lego Rule (`hcp-editor/docs/agents/project-manifest/constraints.md` L696–L730): one interface+trait pair per shared feature, no trait `use`s another trait, every method declares the trait of every interface it implements (composition table in Approach §4), and one validation range per interface.
- **Follows** the record-resolution piece of `src/classes/Application/Countries/API/` (`AppCountryAPIInterface`/`AppCountryAPITrait`/`AppCountryParamsContainer`/`Params/AppCountryIDHandler`) and `hcp-editor/assets/classes/Maileditor/Mails/API/MailParamsContainer.php` (overridden `requireValue()` that sends a domain code) for `MediaDocumentAPIInterface`. It **departs** from `AppCountryIDParam` by using neither `SelectableValueParamTrait` nor `validateByValueExistsCallback()`: there are 10,000+ records to enumerate, and unknown IDs must produce the domain 404.
- **Follows** the generic piece shape of `src/classes/Application/API/Traits/DryRunAPIInterface.php`/`DryRunAPITrait.php` (constants plus `register…Param(s)()` on the interface, registration through `manageParams()->registerParam()`) for `PaginatedAPIInterface`/`PaginatedAPITrait`.
- **Follows** the module API layout of `src/classes/Application/Countries/API/` (group singleton, `Methods/`, README, `module-context.yaml`, static `getAPIMethodsFolder()` registered in `APIFoldersManager`). It **departs** by having no module API exception: `CountryAPIException` is thrown by `ParamSets/AppCountryParamRule.php`, whereas a `MediaAPIException` would have no thrower (design review 2, Decision 1).
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
| `src/classes/Application/API/Traits/JSONRequestTrait.php` with `BaseAPIMethod::errorResponse()` (L636–L640) | Nothing records that a parse is in progress or has failed; once parsing is lazy, the dev echo re-enters it on every error (audit cycle 3, Major 1) | Promoted to step 2 (re-entry guard) | Without it, the development and test environments recurse on the first unparseable body. The guard is a provisional assignment plus one recorded string. |
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
| Media API method composition (planned `MediaAPIInterface`/`MediaAPITrait`, earlier draft) | One catch-all trait for four features, used by different subsets of methods; the `registerMediaIDParam()` lookup departs from the container pattern | Promoted to step 8 (three pieces plus a binding composition table; the fourth, writer-access piece was removed in design review 2, Decision 1) | The API Lego Rule requires one piece per feature. The plan is the last point where splitting costs nothing. |
| Paging in API list methods (`GetMailingsAPI`, `GetComtypesAPI` in the HCP Editor; the planned `GetMediaDocuments`) | Hand-written `limit`/`offset` per method, with different maximums and validation | Promoted to step 8 (framework `PaginatedAPIInterface`/`PaginatedAPITrait`, consumed by `GetMediaDocuments`). The HCP adoption is Deferred Item 6. | The framework method would otherwise become the third copy. Switching the HCP methods is a different repository, outside this plan's blast radius. |
| Search and sorting parameters (`search`, `orderBy`, `orderDir` in `GetMediaDocuments`) | Candidates for further generic pieces | Rejected | There is a single consumer: the HCP list methods offer no sorting, and their search goes through `LookupSearchCapableTrait`, a different mechanism. A piece without a second consumer would be speculative. Revisit when a second framework list method appears. |
| `docs/agents/project-manifest/constraints.md` "API Methods" (L246–L250) | Only one sentence about composition; the Lego Rule is documented only in the HCP Editor | Promoted to step 14 | This plan adds the framework's first composable domain pieces, and the file is already edited (Documentation Updates). |
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
   - Implement the re-entry guard from Approach §1 in the same change: the provisional empty collection before the body is read, the recorded `$unparseableBody`, and `JSONRequestInterface::RESPONSE_KEY_ERROR_UNPARSEABLE_BODY`. The lazy parse must never land without the guard, because the development-mode echo would recurse on the first invalid body.
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
   - In `MediaDocumentWriter`, implement the protected `verifyWrittenFile()` step, the post-write cleanup of `create()`, and the size-first, backup-and-restore order of `replaceFile()` exactly as listed in Approach §3.
   - Add `MediaFilterCriteria::selectMediaType(s)()`.
   - Override `MediaFilterCriteria::buildOrderby()`: when the parent clause is non-empty and the order field is not the primary key, append `, {getColPrimary()} {orderDir}` as a tie-breaker.
   - Run `composer dump-autoload`.
8. **API pieces and Media API scaffolding.**
   - Add `ErrorResponse::makeNotFound()`.
   - Add the pagination piece `src/classes/Application/API/Traits/PaginatedAPIInterface.php` + `PaginatedAPITrait.php` (Approach §3a; grep-verify 183701/183702 are still free).
   - Create `src/classes/Application/Media/API/` with the module-level classes `MediaAPIGroup`, `MediaAPIResponseInterface` and `MediaDocumentSerializer` (grep-verify the `6812xx` block is still free). No `MediaAPIException` (design review 2, Decision 1).
   - Create the three media pieces from Approach §4, each as an interface+trait pair whose interface extends `APIMethodInterface` directly and whose trait `use`s no other trait:
     - `MediaDocumentAPIInterface`/`MediaDocumentAPITrait` with `MediaDocumentParamsContainer`, `Params/MediaIDHandler` and `Params/MediaIDParam`. `requireValue()` sends 681201 with `makeNotFound()`.
     - `MediaMetadataAPIInterface`/`MediaMetadataAPITrait` (HTML rule only).
     - `MediaFileUploadAPIInterface`/`MediaFileUploadAPITrait`, including the protected `sendWriterValidationError()`.
     - (Removed in design review 2, Decision 1: `MediaWriterAPIInterface`/`MediaWriterAPITrait`.)
   - Add `MediaCollection::getAPIMethodsFolder()` and register it in `APIFoldersManager::register()`.
9. **Read methods.**
   - Implement `GetMediaDocumentAPI` and `GetMediaDocumentsAPI` with exactly the interfaces and traits of the composition table (Approach §4), with descriptions, related method names (the other four methods), `getResponseKeyDescriptions()` for every key, examples from `MediaDocumentSerializer::buildExample()`, and an empty changelog.
10. **Write methods.**
    - Implement `AddMediaDocumentAPI`, `UpdateMediaDocumentAPI` and `DeleteMediaDocumentAPI` with exactly the interfaces and traits of the composition table, and with the write-method shape, dry-run semantics, rights and error codes from Approach §4.
    - Dry-run branches call the same piece methods (`validateMediaFile()`, `validateMediaMetadata()`, `resolveMediaName()`, `manageMediaDocumentParams()->requireValue()`) and the same method-level checks as the real branches.
    - `UpdateMediaDocumentAPI` calls `updateMetadata()` before `replaceFile()` in its real branch.
    - Method classes add no own helpers for features a piece provides.
    - Each description documents the request body, the size limit (with the PHP `post_max_size` guidance: about 1.4× the decoded limit), the error codes and the dry-run behaviour.
11. **Test fixtures (live HTTP and pagination).**
    - Add `tests/application/assets/classes/TestDriver/API/TestJSONBodyParamsMethod.php`. It is a JSON request method with `label` (string), `count` (integer), `flag` (boolean), `payload` (`base64File`) and `meta` (JSON), and it echoes the resolved values plus `payloadSize`.
    - Add `tests/application/assets/classes/TestDriver/API/TestPaginatedMethod.php`, a minimal `RequestRequestInterface` + `PaginatedAPIInterface` method. It calls `registerPaginationParams(10, 25)` and echoes the resolved limit and offset through `addPaginationResponseKeys()` with a fixed `totalCount`, so the pagination piece is tested without a database.
    - Run `composer dump-autoload`, then rebuild `tests/application/storage/api/method-index.json` through `MethodIndexEntryTest` and confirm that both fixtures and the five media methods are indexed.
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
- 2 + 5 → 8 (the piece parameters receive the value source; the upload piece uses `base64File()`).
- 5 → 10 (Add and Update use `base64File()`).
- 2 → 10 (JSON body parameters).
- 2 + 5 + 8 → 11 (the pagination fixture uses the step 8 piece).
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
  - `src/classes/Application/API/Traits/PaginatedAPIInterface.php`
  - `src/classes/Application/API/Traits/PaginatedAPITrait.php`
  - `src/classes/Application/Media/API/MediaDocumentAPIInterface.php`
  - `src/classes/Application/Media/API/MediaDocumentAPITrait.php`
  - `src/classes/Application/Media/API/MediaDocumentParamsContainer.php`
  - `src/classes/Application/Media/API/Params/MediaIDHandler.php`
  - `src/classes/Application/Media/API/Params/MediaIDParam.php`
  - `src/classes/Application/Media/API/MediaMetadataAPIInterface.php`
  - `src/classes/Application/Media/API/MediaMetadataAPITrait.php`
  - `src/classes/Application/Media/API/MediaFileUploadAPIInterface.php`
  - `src/classes/Application/Media/API/MediaFileUploadAPITrait.php`
  - `src/classes/Application/Media/API/MediaAPIResponseInterface.php`
  - `src/classes/Application/Media/API/MediaDocumentSerializer.php`
  - `src/classes/Application/Media/API/Methods/AddMediaDocumentAPI.php`
  - `src/classes/Application/Media/API/Methods/GetMediaDocumentAPI.php`
  - `src/classes/Application/Media/API/Methods/GetMediaDocumentsAPI.php`
  - `src/classes/Application/Media/API/Methods/UpdateMediaDocumentAPI.php`
  - `src/classes/Application/Media/API/Methods/DeleteMediaDocumentAPI.php`
  - `src/classes/Application/Media/API/README.md`
  - `src/classes/Application/Media/README.md`
  - `src/classes/Application/Media/module-context.yaml`
  - `tests/application/assets/classes/TestDriver/API/TestJSONBodyParamsMethod.php`
  - `tests/application/assets/classes/TestDriver/API/TestPaginatedMethod.php`
  - the test files in the Test Plan
  - (Removed in design review 2, Decision 1: `Media/API/MediaWriterAPIInterface.php`, `Media/API/MediaWriterAPITrait.php` and `Media/API/MediaAPIException.php`.)
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
  - `src/classes/Application/API/Traits/JSONRequestInterface.php` (new constant `RESPONSE_KEY_ERROR_UNPARSEABLE_BODY`)
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
- The test database contains the `media` table with the `file_size`/`keywords`/`description` columns (`docs/sql/pristine.sql`).
- Every install that uses the media API has the `file_size`/`keywords`/`description` columns. They come from `docs/sql/2023-10-16-news.sql`, and API keys need the later `docs/sql/2025-11-05-api-management.sql` tables, so an install without the columns cannot authenticate an API call. The HCP Editor added them in `hcp-editor/docs/sql/20231023_upgrade_psa_and_framework.sql`. The writer and the API rely on this precondition and have no columns-absent path (design review 2, Decision 1); the Media API README states it.
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
- AC-02: A JSON body that is not valid JSON, or whose top level is not an object, is rejected with 182801 in both `process()` and `processReturn()` modes. An empty body is treated as an empty object. In a development environment the rejection terminates (no re-parse while the error response is built), and its error data carries `JSONRequest.unparseableBody`: the raw body when it is at most 512 characters, otherwise `[N characters truncated]`. After a failed parse, `getRequestData()` returns an empty collection without parsing again.
- AC-03: `JSONParameter` accepts a native JSON object or array from the body as well as a JSON string.
- AC-04: `base64File()` accepts plain base64 and `data:<mime>;base64,` URIs (whitespace ignored), exposes the decoded content and size, and rejects:
  - non-base64 input with 183508
  - decoded content above the limit with 183509, determined from the encoded length before decoding (oversized input is rejected with 183509 even when it is also not valid base64)
  - The default limit is `APP_API_MAX_FILE_CONTENT_SIZE` (10 MiB unless configured), and `setMaxSize()` overrides it.
  - `getValue()` and `getDecodedContent()` both return the decoded content; the parameter retains no base64 copy.
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
  - HTML in description/keywords (681211)
  - over-length name/description/keywords (183507)
  - SVG files are accepted.
  - When `name` is omitted, the default name is the file base name cut to 160 characters, so a long `fileName` never stores an over-length name. A dry run previews the same cut name.
- AC-09: A dry run of `AddMediaDocument`, `UpdateMediaDocument` or `DeleteMediaDocument` performs the same validation as the real call (an input rejected by a real call is rejected with the same code by its dry run), returns the would-be result with `dryRun: true`, and leaves the DB and the storage folder unchanged, with no temp file written. A dry-run Add returns `mediaID: null`.
- AC-10: `GetMediaDocument` (right `ViewMedia`) returns the document shape for an existing ID, includes base64 `content` only when `includeContent=true`, and rejects an unknown ID with 681201 and HTTP status 404 (`ErrorResponse::makeNotFound()`, sent by `MediaDocumentParamsContainer::requireValue()`). `UpdateMediaDocument` and `DeleteMediaDocument` reject an unknown ID the same way.
- AC-11: `GetMediaDocuments` (right `ViewMedia`) supports `search`, `mediaTypes`, `extensions`, `orderBy`, `orderDir`, `limit` (1–200) and `offset`, and returns `totalCount` independent of paging, beside `limit` and `offset`. Additionally:
  - Without parameters it returns at most 50 documents, newest first.
  - `limit` outside 1–200 is rejected with 183701 and a negative `offset` with 183702.
  - `search` matches terms in name, keywords and description.
  - Paging is stable when documents share a `dateAdded` value: no document appears on two pages or is skipped.
- AC-12: `UpdateMediaDocument` (right `EditMedia`):
  - updates name/description/keywords (empty clears description/keywords) and optionally replaces the file
  - reports `modified`
  - rejects a request with no changes (681221), an extension change (681005), and `fileName` without `fileContent` or the reverse
- AC-13: `DeleteMediaDocument` (right `DeleteMedia`) deletes the record and its file and returns the pre-deletion document shape with `deleted: true`.
- AC-14: Each media method is denied (183005/183006) for an API key without a grant for that method, and succeeds with a grant.
- AC-15: (Re-scoped in design review 2, Decision 1; formerly the columns-absent 681007 behaviour.) `MediaDocumentWriter::create()` always writes `file_size`, `description` and `keywords`. The media API has no columns-absent branch: no `supportsMetadata()` seam, no 681007 code and no `MediaWriterAPIInterface`/`MediaWriterAPITrait`. The Media API README states the metadata columns as a precondition.
- AC-16: A failure after the file was written during `MediaDocumentWriter::create()` (raised from the protected `verifyWrittenFile()` step) removes the written file. `replaceFile()` stores the new content's size (`MediaFileContent::getSize()`, not the on-disk size of the old file) before overwriting the file, and clears cached document data. A failure after `replaceFile()` overwrote the file restores the previous file bytes. `verifyWrittenFile()` throws 681102 when the stored file is missing or its size differs from the content size.
- AC-17: Each document type validates its own content in memory through `isContentValid(string $content, string $extension)`, without filesystem access: JPEG/PNG/GIF through image type detection, SVG through its root element, PDF through its signature. A new document type class without `isContentValid()` fails at class level (abstract). Validation failures are reported only as `MediaWriterException` with a `6810xx` code.
- AC-18: Over real HTTP, a JSON body request to `TestJSONBodyParamsMethod` resolves string, integer, boolean, JSON and base64 parameters from the body, and query-string fallback values are used for keys absent from the body.
- AC-19: A declared removed name (`addRemovedParam()`) sent inside a JSON body is rejected with 183009. A removed name sent in the query string is still rejected for both JSON and non-JSON methods.
- AC-20: `changelog.md`, `VERSION` and `AGENTS.md` §5 state the same new version, and the Media module and API docs describe the new methods.
- AC-21: The media methods are composed of interface+trait pieces according to the API Lego Rule:
  - Each of the five methods implements exactly the interfaces of its row in the Approach §4 composition table.
  - For every `*APIInterface` it implements, including inherited ones, it declares the matching trait with its own `use`.
  - No media or pagination trait `use`s another trait.
  - Every media piece interface extends `APIMethodInterface` directly, with no inheritance between media pieces.
  - No media piece owns a validation code outside its own sub-range.
  - The framework's `docs/agents/project-manifest/constraints.md` documents the Lego Rule.
- AC-22: `PaginatedAPIInterface`/`PaginatedAPITrait` registers `limit` and `offset` with the method's default and maximum. It rejects a `limit` outside 1–max with 183701 and a negative `offset` with 183702, applies both to an `Application_FilterCriteria` through `setLimit()`, and writes the `limit`, `offset` and `totalCount` response keys.
- AC-23: `MediaDocumentParamsContainer` resolves a `MediaRecord` from `mediaID` and accepts a pre-selected record through `selectMediaDocument()`. Its `requireValue()` answers an unknown or missing ID with 681201 and HTTP 404, never with the generic 183004 or 183401.

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
- **Composition:** a reflection test enforces the composition table and the Lego Rule for the five media methods. The pagination piece is tested in isolation through `TestPaginatedMethod`, and the document lookup piece through its container.
- **OpenAPI:** `TypeMapperTest`/`ParameterConverterTest` additions plus a generator-level assertion on the media operations.
- Files created by tests are deleted in `tearDown()`, following `ImageMediaTestTrait`'s `$testMedia` cleanup.

## Test Plan
- `tests/AppFrameworkTests/API/Parameters/ValueSourceTest.php` (new): `RequestValueSource` parity with `getRequestParam()->get()`; `JSONBodyValueSource` body-wins, fallback, `getKeys()` union, and `hasKey()` with null values. — AC-01
- `tests/AppFrameworkTests/API/Parameters/JSONBodyParamResolutionTest.php` (new), using a JSON stub method with `setRequestBody()`:
  - each of the six types resolves native body values
  - header and reserved parameters stay request-only
  - non-JSON methods are unchanged
  - an invalid body, a non-object body and an empty body (AC-02) in `processReturn()`
  - re-entry guard (AC-02; the test application runs as a development environment, see `ErrorResponseTest::test_jsonRequestBodyPresentInDevelEnvironment()`): an invalid body returns 182801 and the test completes (no recursion); the error data holds `JSONRequest.unparseableBody` equal to a short raw body, and `[N characters truncated]` for a 513-character invalid body; a second `getRequestData()` call after the failure returns an empty collection; a non-object body (`[1,2]`) behaves the same; with `withDevelEnvironment(false)`-style switching (copied as a local helper, since the `ErrorResponseTest` helper is private), the error data has no `JSONRequest` key
  - dev error data truncates a 513-character string under a registered key, under an unregistered key and inside a nested object, and leaves a 512-character string unchanged (AC-05)
  - Covers AC-01, AC-02, AC-05
- `tests/AppFrameworkTests/API/Parameters/JSONParamTest.php` (modified): native array from the body is accepted. — AC-03
- `tests/AppFrameworkTests/API/Parameters/Base64FileParameterTest.php` (new): plain base64, data URI, whitespace, invalid input (183508), over limit (183509), default limit from config, `setMaxSize()`, decoded content/size, `selectValue()`; a value exactly at the limit (padded and unpadded) accepted and one byte over rejected, proving the encoded-length formula; an oversized value containing invalid base64 characters yields 183509, not 183508, proving the size check precedes decoding; `getValue()` equals `getDecodedContent()` (the decoded bytes, not base64). — AC-04
- `tests/AppFrameworkTests/API/OpenAPI/TypeMapperTest.php` (modified): `Base64 File` mapping. — AC-06
- `tests/AppFrameworkTests/API/OpenAPI/ParameterConverterTest.php` (modified): a base64 parameter on a JSON method becomes a body property with `contentEncoding`. — AC-06
- `tests/AppFrameworkTests/API/OpenAPI/MediaOperationsSpecTest.php` (new): the generated spec contains the five media paths with security, and Add/Update have a `requestBody`. — AC-06
- `tests/AppFrameworkTests/API/RemovedParamJSONBodyTest.php` (new; unconditional): a removed name inside a JSON body → 183009; a removed name in the query string of a JSON method and of a non-JSON method → 183009. — AC-19
- `tests/AppFrameworkTests/Media/DocumentContentValidationTest.php` (new): fixture bytes read into strings — JPG/PNG/GIF/SVG/PDF valid; PNG bytes with extension `jpg`, text with `png`, non-SVG XML with `svg` and non-PDF with `pdf` invalid; synthetic in-memory strings need no files; a reflection check that every discovered type class implements `isContentValid()`. — AC-17
- `tests/AppFrameworkTests/Media/MediaFileValidatorTest.php` (new): unsupported extension, type mismatch, empty file and content mismatch each throw `MediaWriterException` with the expected `6810xx` code and `isValidationError() === true`; valid SVG returns its type ID; no temp file is created during `validate()` (temp folder unchanged); `MediaFileContent` file-name rules (681006). — AC-08, AC-09, AC-17
- `tests/AppFrameworkTests/Media/MediaDocumentWriterTest.php` (new): `create()` sets all columns and writes the file; author is the passed user; a forced failure after the write removes the file (through a test-local writer subclass whose `verifyWrittenFile()` override calls the parent and then throws); `verifyWrittenFile()` on the base writer throws 681102 when the stored file was truncated or deleted by the test before the call; `replaceFile()` updates size and rejects an extension change; a forced failure in `replaceFile()` (same subclass) leaves the stored file byte-identical to the original and leaves no backup temp file behind; `updateMetadata()` modified flag; `delete()` removes the file; `create()` stores `file_size`, `description` and `keywords` on the record (read back from the DB). (The former columns-absent subclass case is removed with the seam, design review 2 Decision 1.) — AC-07, AC-15, AC-16
- `tests/AppFrameworkTests/API/ErrorResponseTest.php` (modified): `makeNotFound()` sets HTTP status 404, asserted through `ErrorResponse::getHttpStatusCode()` (L183). The method tests assert the 404 through `ErrorResponsePayload::getHttpStatusCode()` in `processReturn()` mode. — AC-10
- `tests/AppFrameworkTests/Media/MediaFilterCriteriaTypeTest.php` (new): `selectMediaTypes()` filters by type. — AC-11
- `tests/AppFrameworkTests/Media/API/AddMediaDocumentAPITest.php` (new): success for PNG and SVG (shape, dimensions, author = pseudo-user, key order); each rejection code; each rejection code is also returned by the same input with `dryRun=true` (data provider over both modes); dry run leaves the DB row count and storage folder unchanged and returns `mediaID: null`; a `fileName` with a 200-character base name and no `name` succeeds with a stored name of exactly the first 160 characters, and its dry run previews the same name; no grant → denied. (The former columns-absent subclass case is removed, design review 2 Decision 1.) — AC-07, AC-08, AC-09, AC-14
- `tests/AppFrameworkTests/Media/API/GetMediaDocumentAPITest.php` (new): shape, `includeContent` round-trips the bytes, unknown ID 681201 with HTTP status 404, grant check. — AC-10, AC-14
- `tests/AppFrameworkTests/Media/API/GetMediaDocumentsAPITest.php` (new): parameterless call returns at most 50 newest-first; search matching on name, on keywords only, and on description only; type and extension filters; order; paging with stable `totalCount`; limit 0 and 201 rejected with 183701 and offset -1 with 183702; grant check. — AC-11, AC-14
- `tests/AppFrameworkTests/Media/MediaFilterCriteriaOrderTest.php` (new): several records with identical `media_date_added`, paged one item at a time in both directions, yield every ID exactly once in primary-key order within the tie. — AC-11
- `tests/AppFrameworkTests/Media/API/UpdateMediaDocumentAPITest.php` (new): metadata update, clearing, file replacement, no-changes 681221, extension change 681005, pair rule, dry run unchanged, unknown ID 681201 with HTTP 404, grant check. (The former columns-absent 681007 case is removed, design review 2 Decision 1.) — AC-09, AC-10, AC-12, AC-14
- `tests/AppFrameworkTests/Media/API/DeleteMediaDocumentAPITest.php` (new): deletion removes the record and file, response captured before deletion, dry run keeps both, unknown ID 681201 with HTTP 404, grant check. — AC-09, AC-10, AC-13, AC-14
- `tests/AppFrameworkTests/API/JSONBodyParamsLiveHTTPTest.php` (new, `#[Group('live-http')]`): real HTTP POST with a JSON body to `TestJSONBodyParamsMethod` (body values, query fallback, base64 size). — AC-18
- `tests/AppFrameworkTests/API/Collection/MethodIndexEntryTest.php` (existing, run): the index contains the five media methods and both fixtures after rebuild. — AC-06
- `tests/AppFrameworkTests/Media/API/MediaAPICompositionTest.php` (new): a data provider over the five method classes. For each class, through reflection:
  - the implemented interfaces equal the composition-table row (Approach §4)
  - every implemented interface named `*APIInterface` from `Application\Media\API` or `Application\API\Traits` has its matching `*APITrait` among the class's directly declared traits
  - the media traits (`MediaDocumentAPITrait`, `MediaMetadataAPITrait`, `MediaFileUploadAPITrait`) and `PaginatedAPITrait` declare no traits of their own (`ReflectionClass::getTraitNames()` is empty)
  - each media piece interface's parent interfaces are exactly `APIMethodInterface` (and its own parents), with no other media piece interface among them
  - `Application\Media\API\MediaWriterAPIInterface`, `MediaWriterAPITrait` and `MediaAPIException` do not exist (`class_exists()`/`interface_exists()`/`trait_exists()` false), and `MediaWriterException` declares no constant with value 681007 (AC-15)
  - Covers AC-15, AC-21
- `tests/AppFrameworkTests/API/Traits/PaginatedAPITraitTest.php` (new), through `TestPaginatedMethod` in `processReturn()`:
  - defaults (10/0)
  - explicit values
  - `limit` 0 and 26 → 183701; `offset` -1 → 183702
  - `limit` 25 accepted
  - response keys `limit`, `offset` and `totalCount` present, with order asserted through `ResponseKeySorter::sortKeys()`
  - `applyPagination()` sets the limit and offset on a filter criteria instance (asserted through the SQL `LIMIT` clause it builds)
  - Covers AC-22
- `tests/AppFrameworkTests/Media/API/MediaDocumentParamsContainerTest.php` (new), through a `GetMediaDocumentAPI` instance:
  - resolves an existing record by `mediaID`
  - `selectMediaDocument()` overrides the request
  - an unknown ID and a missing ID → 681201 with HTTP 404 in `processReturn()` mode
  - Covers AC-10, AC-23
- Documentation/version consistency is checked by the reviewer against `changelog.md`, `VERSION` and `AGENTS.md`, including the Lego Rule section in `docs/agents/project-manifest/constraints.md` and the metadata-columns precondition in `src/classes/Application/Media/API/README.md`. — AC-15, AC-20, AC-21

## Documentation Updates
- `src/classes/Application/API/README.md`:
  - Key Concepts: a new "JSON body parameters" bullet (resolution order, fallback, empty body, 182801, long-string truncation in the dev echo, the `unparseableBody` echo for a body that fails to parse, removed-key detection in bodies)
  - folder table: `Parameters/` row mentions `ValueSource/`
  - Key Concepts "Traits" list (L30–L36): add `PaginatedAPIInterface` / `PaginatedAPITrait` (`limit`/`offset`/`totalCount`, codes 183701/183702)
  - Key Concepts: a short "Composing methods from pieces" bullet that points to the Lego Rule in `docs/agents/project-manifest/constraints.md` and names Countries and Media as the framework references
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
  - a "Pieces" section: the three media interface+trait pairs (purpose, parameters, codes; each extends `APIMethodInterface` directly) and the composition table from Approach §4, so a new media method knows which traits to declare
  - a "Requirements" note: the `media` table must have the `file_size`/`keywords`/`description` columns (`docs/sql/2023-10-16-news.sql`); there is no columns-absent mode
  - a "File consistency" note: what the writer guarantees on failure (a created file is removed, a replaced file is restored) and the accepted residual window (a failure after `replaceFile()` returns, such as a failing commit, leaves the new bytes with the old size)
  - the default name rule: an omitted `name` is the file base name cut to 160 characters
  - the single client-facing error-code table (6810xx with `MediaWriterException::VALIDATION_*` named as the source for 681001–681006, the API codes 681201 (`MediaDocumentAPIInterface`), 681211 (`MediaMetadataAPIInterface`) and 681221 (`UpdateMediaDocumentAPI`), 183701/183702 (`PaginatedAPIInterface`), 183508/183509 and 182801)
  - size limit and `post_max_size` guidance
  - SVG note, duplicate-upload note
- `src/classes/Application/Media/module-context.yaml` (new): module `media` (label "Media"); related modules `api`, `api-parameters`, `db-helper`; documents for overview, core architecture (domain + writer) and API.
- `AGENTS.md`:
  - §1 module table gains a "Media" row (`.context/modules/media/`)
  - §1 note at L34 ("`.context/` is committed to VCS") is reworded to match the L107 directive: `.context/` is committed to VCS on `main` only, as part of the pre-release flow
  - §5 version updated (step 15)
- `docs/agents/project-manifest/constraints.md`:
  - "API Methods": a new subsection "API Trait Composition Model (Lego Rule)", adapted from `hcp-editor/docs/agents/project-manifest/constraints.md` L696–L730. It covers one interface+trait pair per shared feature, no trait composing another API trait, every implemented interface (including inherited ones) satisfied by an explicit `use`, no copied trait implementations, and one validation range per interface. Framework references: `Countries/API` (record piece), `API/Traits/DryRun*` and `API/Traits/Paginated*` (generic pieces), `Media/API` (domain pieces).
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
  - API: `PaginatedAPIInterface`/`PaginatedAPITrait` pagination piece (`limit`, `offset`, `totalCount`)
  - Media: five API methods, built from composable pieces (document lookup, metadata, file upload)
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
| 6 | Switch the HCP Editor's `GetMailingsAPI` and `GetComtypesAPI` to `PaginatedAPIInterface`/`PaginatedAPITrait` | Lego-composition review (Structural Improvements) | Different repository, outside this plan's blast radius, and dependent on the framework release | The response keys already match. Decide in that plan whether out-of-range `limit` values keep being clamped (current HCP behaviour) or are rejected (the piece's behaviour). Rejecting would be a breaking change for those methods, so it needs a versioned changelog entry. |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| **Body resolution changes behaviour for existing JSON methods** | Only two test fixtures use `JSONRequestTrait`, and neither registers parameters. The request fallback preserves query parameters. `JSONBodyParamResolutionTest` locks the non-JSON path as unchanged. |
| **Lazy body parsing now happens during validation and could fail outside the old error path** | Parsing errors use `errorResponse(182801)->send()`, which works in both modes. AC-02 tests `processReturn()` explicitly. Unexpected throwables still hit the shared boundary. |
| **The development-mode echo re-enters the lazy parse and recurses without end** (audit cycle 3, Major 1) | `errorResponse()` reads `collectRequestErrorData()` on every error, which reads `getRequestData()`. The provisional empty collection is assigned before the body is read, so re-entry returns it instead of parsing again. The unparseable body is echoed from a recorded string, never from the parser. `JSONBodyParamResolutionTest` runs the invalid-body cases in the development environment, where the recursion would occur. |
| **Large payloads exhaust memory or exceed `post_max_size`** | A configurable decoded limit (10 MiB default) is checked from the encoded length before decoding, so oversized input is never decoded. The parameter retains only the decoded copy, not the normalised base64 (design review 2, Decision 2). `post_max_size` guidance is in the README. The body is parsed once and shared by the parameter system and `getRequestData()`. |
| **Orphan or lost files** | Dry runs never mutate. The writer deletes a written file on failure, including a failure in the named `verifyWrittenFile()` step, which the writer test drives through a subclass. Delete is only called in non-dry runs. Tests assert the storage folder state. |
| **File replacement leaves disk and DB disagreeing after a later failure** (audit cycle 3, Minor 2) | `replaceFile()` saves the size before touching the file, writes the file last, and restores the old bytes from a backup copy when anything after the overwrite throws. `UpdateMediaDocument` calls `updateMetadata()` before `replaceFile()`. The residual window (a failure after `replaceFile()` returns, such as a failing commit) is accepted and documented in the Media API README. |
| **Stale cached documents after file replacement** | `replaceFile()` clears `Application_Media`'s document cache, and AC-16 tests it. |
| **SVG served from the app origin can carry scripts** | Accepted per the user decision, unchanged from the admin UI. Delivery behaviour is unchanged. Consuming apps restrict SVG where needed. API access requires an explicitly granted key with `CreateMedia`. |
| **Agents delete media still referenced by HCP contents** | `DeleteMediaDocument` must be granted per key. The README warns about missing usage checks. Deferred Item 1 holds the long-term guard. |
| **Removed-parameter key source changes behaviour for existing methods** | `RequestValueSource::getKeys()` returns the same request parameter names as before, so non-JSON methods are unaffected. `array_fill_keys()` adapts the key list to `detectUsed()`'s associative contract. `RemovedParamJSONBodyTest` covers body and query cases for both method kinds. (This replaces the former "sibling plan not implemented" risk, which no longer applies: the feature shipped in `v7.6.0`.) |
| **Echo truncation hides a short-but-relevant value** | Only strings over 512 characters are truncated, and the summary keeps the length. The echo is development-only. |
| **Error codes collide with application codes** | `681xxx`, 183508/183509 and 183701/183702 are grep-verified free in the framework and the HCP Editor, and re-checked at implementation. |
| **A method forgets a trait or adds a feature-local helper, eroding the composition** | The composition table in Approach §4 is binding, `MediaAPICompositionTest` enforces it through reflection, and the Lego Rule is documented in the framework's `constraints.md`. A missing trait also fails at class level, because the interface methods would be unimplemented. |
| **Interface inheritance between pieces leads to duplicate `use` or a trait composing a trait** | Resolved by design: the inheritance edge to `MediaWriterAPIInterface` was removed with the piece (design review 2, Decision 1). Every media piece interface extends `APIMethodInterface` directly, and `MediaAPICompositionTest` asserts it. |
| **An install without the media metadata columns calls the media API** | Not reachable in practice: API keys require the `2025-11-05-api-management.sql` tables, which postdate the `2023-10-16-news.sql` columns. Should it happen anyway, the insert fails and the boundary reports an internal error. The Media API README states the precondition. |
| **`composer dump-autoload` wipes `method-index.json`** | Step 11 rebuilds it through `MethodIndexEntryTest` and verifies the entries, per `testing.md`. |

## Recommended Workflow
- **Workflow:** ledger
- **Rationale:** The plan spans two modules (an API parameter-resolution change that affects every JSON method, and a new media write path with five methods, including file I/O and destructive operations). It departs from an existing pattern and needs formal QA, a security audit (uploads, SVG, size limits, redaction, rights) and code review.
