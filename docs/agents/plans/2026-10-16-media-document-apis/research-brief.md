# Research Brief

## Scope Sketch
- API parameter resolution for JSON-body methods: `src/classes/Application/API/Parameters/`, `src/classes/Application/API/Traits/JSONRequestTrait.php` (modification)
- New base64 file parameter type and its OpenAPI mapping: `src/classes/Application/API/Parameters/Type/`, `src/classes/Application/API/OpenAPI/TypeMapper.php` (new code + modification)
- Media domain write/read service: `src/classes/Application/Media/` (new code + small modifications)
- Media API module (group, shared trait, exceptions, five methods): `src/classes/Application/Media/API/` (new code)
- API folder registration: `src/classes/Application/API/APIFoldersManager.php` (integration)
- Configuration (max upload size): `src/classes/Application/ConfigSettings/`, `src/classes/Application/Bootstrap/Bootstrap.php` (modification)
- Tests and test application fixtures: `tests/AppFrameworkTests/`, `tests/application/` (new code)
- Documentation: module READMEs, `module-context.yaml`, project manifest, `changelog.md`, `VERSION` (modification)
- Consumer context (read only): `hcp-editor/docs/agents/plans/2026-10-16-mail-content-write-apis/plan.md`, Nexus MCP `callApi()`

## Area: Consumer requirements (HCP Editor, read only)

### Verified References
- `hcp-editor/docs/agents/plans/2026-10-16-mail-content-write-apis/plan.md`:
  - Content image keys accept **media IDs only** (`{mediaID}`), resolved through `Application_Media::getInstance()->idExists()`/`getByID()`, and they require an `Application_Media_Document_Image`.
  - Upload is delegated to "a separate framework plan for a media API" (Deferred Item 2: "Media upload API (`AddMediaImage`) incl. SVG policy"). Human Action 2: once the framework method exists, point the Content API README's image section at it.
- The Nexus MCP client `nexus-plugins/STABLE/nexus-plugins/plugins/claude/hcp-agents/server/index.js` (`callApi()`, ~L23228–L23280):
  - It sends every parameter as a query string (`URLSearchParams`).
  - The method is `POST` with `Content-Type: application/json`, the body is always `JSON.stringify({})`, and the key travels in `Authorization: Bearer`.
  - A base64 file therefore cannot be transported by the current client. A change to the client belongs to nexus-plugins, outside this repository.

### Constraints
- User decisions (2026-10-16):
  - transport is base64 in a JSON body
  - method names `AddMediaDocument`/`GetMediaDocument`, plus the "whole spectrum" (list, update, delete)
  - SVG accepted as today
  - duplicates accepted, no content-hash dedup
- Framework changes are a separate plan from HCP Editor plans; the HCP plan does not depend on this one at runtime.

## Area: API parameter resolution (JSON body)

### Verified References
- `src/classes/Application/API/Parameters/BaseAPIParameter.php`:
  - `getRequestParam()` (L74–L85) registers and returns an `AppUtils\RequestParam` from the shared `Application_Request` (`getRequest()` L113).
  - `getValue()` (L230–L265) uses a selected value first, then header parameters through `getHeaderValue()`, then the abstract `resolveValue()` (L339), followed by validation.
  - Value types are `int|float|bool|string|array|null`.
- **All six concrete types** read the raw value through `$this->getRequestParam()->get()`:
  - `Type/StringParameter.php` L145
  - `Type/IntegerParameter.php` L78
  - `Type/BooleanParameter.php` L41
  - `Type/JSONParameter.php` L44 (rejects non-string values with `VALIDATION_INVALID_VALUE_TYPE`)
  - `Type/StringListParameter.php` L124
  - `Type/IDListParameter.php` L86
  - `CommonTypes/*` extend `StringParameter`. `ClearableStringParameter` extends `StringParameter`.
- `src/classes/Application/API/Parameters/APIParamManager.php`:
  - one instance per method (`$method` L34, constructor L42)
  - `registerParam()` L63–L95
  - `getParams()`, `getHeaderParams()`
- `src/classes/Application/API/Traits/JSONRequestTrait.php`:
  - `collectRequestData()` L24–L35 parses `getRequestBody()` into `ArrayDataCollection`, or sends `ERROR_FAILED_TO_READ_INPUT` (182801)
  - `getRequestData()`
  - `getRequestMime()` returns `application/json`
  - `collectRequestErrorData()` echoes the full parsed body under `JSONRequest`, in development environments only
- `src/classes/Application/API/Traits/JSONRequestInterface.php`: `ERROR_FAILED_TO_READ_INPUT = 182801`, `RESPONSE_KEY_ERROR_JSON_REQUEST_DATA = 'JSONRequest'`, `getRequestData()`.
- `src/classes/Application/API/BaseMethods/BaseAPIMethod.php`:
  - `getRequestBody()` (L428, final protected) reads `setRequestBody()` (L442) or `php://input`
  - `process()` L79 and `processReturn()` L88
  - the lifecycle (per the API README) is `validate()` → `authorize()` → … → `collectRequestData()` → `collectResponseData()`
- `src/classes/Application/API/OpenAPI/ParameterConverter.php` L70 and L110 and `MethodConverter.php` L100: for `JSONRequestInterface` methods, every non-header parameter is documented as a **request body property**.
- JSON-body users today: only `tests/application/assets/classes/TestDriver/API/TestJSON2JSONMethod.php` and `TestJSONRequestRedactionMethod.php`. The HCP Editor has none (grep of `JSONRequestTrait` in `hcp-editor/assets`: 0 hits).

### Established Patterns
- Parameter types are registered through `ParamTypeSelector` (`Parameters/ParamTypeSelector.php`: `boolean()`, `integer()`, `string()`, `clearableString()`, `stringList()`, `idList()`, `JSON()`, `alias()`, …).
- Validation codes live in `Parameters/Validation/ParamValidationInterface.php`, `1835xx` (183501–183507 used; 183508+ free, grep-verified in the framework and the HCP Editor).

### Structural Observations
- **The parameter system and the JSON body are disconnected.** Registered parameters always resolve from `$_REQUEST`, yet the OpenAPI generator documents them as body properties for JSON methods. This makes the spec untrue for those methods, and it is why no JSON-body method can use typed parameters today.
- **Raw-value access is repeated in six classes** (`getRequestParam()->get()`), with no single seam for choosing the value source.
- `JSONParameter::resolveValue()` assumes a string, so a native JSON array or object would be rejected.
- The development-mode body echo would return a multi-megabyte base64 string in error responses.

### Constraints
- Pending plan `docs/agents/plans/2026-10-16-api-removed-parameter-declaration/` (not yet implemented; untracked) changes `APIParamManager` (`addRemovedParam()`, conflict checks in `registerParam()`) and adds a pre-validation step that calls `detectUsed($this->request->getParams())`. Its Deferred Item 1 is "Detect removed keys inside JSON request bodies".

## Area: Base64 file parameter and OpenAPI mapping

### Verified References
- `src/classes/Application/API/OpenAPI/TypeMapper.php`:
  - type label constants (`TYPE_LABEL_*`)
  - `mapType()` falls back to `{type: string}`
- `src/classes/Application/API/Parameters/Type/StringParameter.php`: `setMaxLength()` L137 uses `Validation/Type/MaxLengthValidation.php`.
- Tests exist per type: `tests/AppFrameworkTests/API/Parameters/*Test.php` (e.g. `JSONParamTest.php`, `StringParamTest.php`) and `tests/AppFrameworkTests/API/OpenAPI/TypeMapperTest.php`, `ParameterConverterTest.php`, `MethodConverterTest.php`.

### Established Patterns
- Each type defines `getTypeLabel()`, `resolveValue()` and `getValue()` narrowing, and validates through `$this->result->makeError()/makeWarning()` with `ParamValidationInterface` codes.

### Structural Observations
- New code only. `TypeMapper` is a hand-maintained switch; adding one case follows the existing pattern.

### Constraints
- OpenAPI 3.1 / JSON Schema 2020-12 expresses base64 strings as `{"type":"string","contentEncoding":"base64"}`.

## Area: Media domain

### Verified References
- `src/classes/Application/Media/Media.php` (`Application_Media`, legacy singleton):
  - `createFromFile()` L81
  - `createImageFromFile()` L101
  - `getByID()` L150, with an in-memory `$knownDocuments` cache and `clearCollection()` L163
  - `getTypeByExtension()` L210
  - `loadTypes()` L230 discovers `Document/*.php`; `$types` is protected and has no public getter
  - `getExtensions()` L274
  - `idExists()` L310
  - error codes `6800xx`
- `src/classes/Application/Media/Document.php` (`Application_Media_Document`):
  - `createNew()` L95 does a raw insert. It does not set `file_size`, and it defaults the user to `Application::getUser()`.
  - `getPath()` L135 builds `{storage}/{Y}/{m}/{id}.{ext}`
  - `createNewFromFile()` L191
  - `delete()` L497 requires a transaction and unlinks the file unless simulating
  - `setSourceFile()` L543
  - `toBase64()` L471
  - `getRecord()` L595
- `src/classes/Application/Media/Document/Image.php`:
  - `IMAGE_EXTENSIONS = jpg,jpeg,png,gif,svg` (L20)
  - `getWidth()`/`getHeight()`/`getDimensions()`
  - `isAnimatedGIF()`
- `Document/PDF.php` is the second document type.
- `src/classes/Application/Media/DocumentTrait.php`:
  - `getFilesize()` L223
  - `isVector()`
  - `getFilename()`
  - `getThumbnailURL()` (`APP_URL/media.php?...`)
  - `serveFromRequest()` L304
- `src/classes/Application/Media/Collection/MediaCollection.php`:
  - DBHelper collection; `COL_*` constants (L40–L47); `MEDIA_TYPE = 'media'`
  - `hasSizeColumn()` L61, which guards `file_size`/`keywords`/`description`
  - `_registerKeys()` L168: name, user (default current user), date, extension and type are required; size is required when the column exists
- `src/classes/Application/Media/Collection/MediaRecord.php`:
  - `getLabel()`, `getDateAdded()`, `getAuthorID()`, `getDescription()`, `getKeywords()`, `getFileSize()`, `getMediaDocument()`, `refreshFileSize()`
  - `_onBeforeDelete()`/`_onDeleted()` delete the file on disk immediately (L141–L149)
- `src/classes/DBHelper/BaseCollection.php`: `createNewRecord()` L598 (requires a transaction) and `deleteRecord()` L810.
- `src/classes/Application/Media/Collection/MediaSettingsManager.php`:
  - In create mode, `getCreateData()` (L49) derives extension, type and size from the upload.
  - `Formable/RecordSettings/Extended.php::createRecordFromValues()` (L89–L115) then calls `updateRecord()` with the new record. That is where the file is copied in, through `setSourceFile()` (L65–L81).
  - Edit mode restricts a file replacement to the same extension (`validateFile()` L201).
  - The name has a max length of 160 (L158), the description 1200 (L133) and the keywords 500 (L147).
- `src/classes/Application/Media/Collection/MediaFilterCriteria.php`: `selectExtension(s)()`, tag filters. Generic `setSearch()`, `setLimit()` and `setOrderBy()` come from `Application/FilterCriteria/FilterCriteria.php` (L105, L124, L221). `countItems()` is in `FilterCriteria/Database.php` L88.
- `src/classes/Application/Media/MediaRightsInterface.php`: `CreateMedia`, `EditMedia`, `DeleteMedia`, `ViewMedia`, `AdminMedia`. They are registered for every app by `MediaRightsTrait::registerMediaRights()`, used in `src/classes/Application/User/User.php` L60 and L1002. Grants: Admin→Delete→Create→Edit→View.
- `src/classes/Application/Media/Delivery.php`: serves media through `media.php` (`serveMedia()` L68).
- `src/classes/Application/Uploads/Uploads.php` `validate()` checks content against extension with `getimagesize()` for jpg/gif/png and lets SVG through.
- Tests: `tests/AppFrameworkTests/Media/CreateDocumentTest.php`, `CreateUploadTest.php`, `ThumbnailGenerationTest.php`, `TaggingTest.php`; base class `tests/AppFrameworkTestClasses/MediaTestCase.php` with `Traits/ImageMediaTestTrait.php` (`createTestImage()` and per-format helpers). Fixtures are in `tests/files/Media/` (`example-image.jpg`, `.png`, `.svg`, `example-gif-animated.gif`, `example-gif-non-animated.gif`).

### Established Patterns
- New-style domain code is namespaced under `Application\Media\…` (`Collection/`, `Admin/`, `Events/`). The legacy entry points are `Application_Media` and `Application_Media_Document*`.
- Exceptions use `Application\Media\MediaException` with unique integer codes.

### Structural Observations
- **Two creation paths with different completeness.**
  - `Application_Media::createFromFile()` (legacy raw insert) leaves `file_size = 0` and cannot set description or keywords.
  - The collection path (`createNewRecord()` + `setSourceFile()`, used by the admin form) sets everything.
  - An API needs the complete path without a form.
- **Creation is not atomic across DB and disk.** The file is copied after the insert, inside the caller's transaction. A later rollback, such as a dry run, leaves an orphan file, and an exception after the copy leaves the file too.
- **Deletion removes the file immediately** in `_onDeleted()`, before the transaction commits, so a dry-run delete through the collection would destroy the file.
- The admin create/edit logic (derive extension, type and size; replace a file through a temp file) is about six lines inlined in `MediaSettingsManager`.
- `Application_Media` has no public accessor for the registered document types (`$types` is protected).
- The Media module has **no README and no `module-context.yaml`**. It does not appear in `docs/agents/project-manifest/modules-overview.md`.
- No admin create bug: an earlier suspicion was disproved. `createRecordFromValues()` calls `updateRecord()`, which copies the file.

### Constraints
- `file_size`, `keywords` and `description` exist in `docs/sql/pristine.sql` (L465–L467) and through the `docs/sql/2023-10-16-news.sql` migration. Older installs may lack them, hence `hasSizeColumn()`.

## Area: API module integration (method registration, auth, groups)

### Verified References
- `src/classes/Application/API/APIFoldersManager.php::register()` L33–L41 adds the framework method folders (`Locales`, `CountriesCollection`, `AdminScreenIndex`, `DBHelper`, `GetOpenAPISpec`), each through a static `getAPIMethodsFolder()` (e.g. `src/classes/Application/Countries/CountriesCollection.php` L79).
- `src/classes/Application/Countries/API/` is the reference layout:
  - `CountriesAPIGroup.php`, `CountryAPIException.php`
  - `Methods/GetAppCountriesAPI.php`, which uses `RequestRequestTrait`, `JSONResponseTrait`, `KeyDescription`, regions A/B/C, `METHOD_NAME`/`VERSIONS` constants and `#[Override]`
  - `README.md`, `module-context.yaml`
- `src/classes/Application/API/Groups/FrameworkAPIGroup.php` is a singleton `GenericAPIGroup` (id, label, description).
- `src/classes/Application/API/Clients/API/APIKeyMethodInterface.php`: `API_KEY_PARAM_NAME = 'apiKey'`, `getRequiredRight(): ?string` (mandatory). `APIKeyMethodTrait::manageParamAPIKey()`. The pseudo-user comes from `manageParamAPIKey()->requireValue()->getPseudoUser()`, as in `hcp-editor/.../UpdateMailLinkAPI.php`.
- `src/classes/Application/API/Clients/Keys/APIKeyRights.php::satisfies()`: authority comes from the key's method grants plus one-level right expansion.
- `src/classes/Application/API/Traits/DryRunAPITrait.php`: `registerDryRunParam()`, `isDryRun()`.
- The HCP Editor write-method template is `hcp-editor/assets/classes/Maileditor/Mails/API/Methods/UpdateMailLinkAPI.php`:
  - an own transaction, rolled back on dry run
  - an empty `collectRequestData()`
  - `APIParameterException` converted to `errorResponse(code)->setErrorMessage()->send()`
- The framework has **no write API methods yet**, and no `APIExampleRecordGenerator` (that is HCP-only).

### Established Patterns
- Error-code allocation: globally unique `{id}{NNN}` (`docs/agents/project-manifest/constraints.md` L161–L170), picked as grep-verified free blocks. Block `681xxx` is unused in the framework and the HCP Editor (`6800xx` is `Application_Media`, `650xxx` is `Document`).
- `tests/application/storage/api/method-index.json` is wiped by `composer dump-autoload` and rebuilt through a test that calls `APIMethodIndex::build()`, e.g. `MethodIndexEntryTest` (`docs/agents/project-manifest/testing.md` L237).
- Live-HTTP tests use `#[Group('live-http')]` and are excluded by default (`testing.md` L385–L409; example `tests/AppFrameworkTests/API/ErrorPayloadRedactionLiveHTTPTest.php`). Test fixture methods live in `tests/application/assets/classes/TestDriver/API/`.
- API test base: `tests/AppFrameworkTestClasses/API/APITestCase.php` (resets superglobals). `APIClientTestTrait::createTestAPIKeyForMethod()` L38.
- The in-process API key pattern is in `tests/AppFrameworkTests/API/Keys/KeyAuthorizationTest.php` (L105: `$method->manageParamAPIKey()->getParam()?->selectValue(...)`). `KeyParamTest.php` L32 sets `$_SERVER['HTTP_AUTHORIZATION']`.
- Parameter rules: `Parameters/Rules/Type/RequiredIfOtherIsSetRule.php` (constructor `(label, target, other)`), tested in `tests/AppFrameworkTests/API/Rules/RequiredIfOtherIsSetTest.php`.

### Constraints
- `.context/` is never committed from feature branches (`AGENTS.md` §4).
- New class files need `composer dump-autoload` (classmap).
- `array()` syntax only. Legacy classes are not refactored unless asked.

## Area: Configuration

### Verified References
- `src/classes/Application/ConfigSettings/BaseConfigRegistry.php`: `APP_*` constant names, e.g. `AUTOMATIC_DELETION_DELAY` L156.
- `src/classes/Application/Bootstrap/Bootstrap.php` L271: `registerOptionalSetting(BaseConfigRegistry::AUTOMATIC_DELETION_DELAY, 60*60*24*5)`.
- `src/classes/Application/ConfigSettings/SetAppConfigSettingTrait.php` L171: typed setter pattern.
- `src/classes/Application/ConfigSettings/AppConfig.php`: static typed getters through `boot_constant()`.

### Established Patterns
- An optional setting is a registry constant, plus a default through `registerOptionalSetting()`, plus a setter in `SetAppConfigSettingTrait`, plus a getter in `AppConfig`.

## Area: Documentation and release preparation

### Verified References
- `AGENTS.md` §2 Manifest Maintenance Rules (new module → `module-context.yaml` + `composer build` + module table). §5 lists the version as 7.0.8.
- `VERSION` contains `7.3.1`, read by `src/classes/Application/Framework/AppFramework.php` L128. The latest `changelog.md` entry is `v7.5.3`, so both version statements are stale.
- The pending removed-parameter plan proposes `v7.6.0`.
- `src/classes/Application/API/README.md`, `Parameters/README.md`, `OpenAPI/README.md` and `docs/agents/project-manifest/constraints.md` / `testing.md` exist.

## Strategic Context
- Framework strategy: mid term is improved test coverage, more namespaces, stricter typing and PHPStan level. There are no short-term goals.
- Prior projects: the 2026-08 API hardening series (single execution boundary, JSON error boundary, redaction channel, one right per method).
- Insights used:
  - `1a8715bc-…` / `7097dd32-…`: `ResponseKeySorter` canonical ordering and the dynamic key-order assertion pattern.
  - `af333a25-…`: JSON error boundary.
  - `7d13934f-…`: validate only what is mechanically checkable.
- None of these is overtaken by this plan.
