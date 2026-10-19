# Media - API (Document Methods & Composable Pieces)
_SOURCE: Media API README (methods, pieces, composition table, error codes)_
# Media API README (methods, pieces, composition table, error codes)
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Media/
                └── API/
                    └── README.md

```
###  Path: `/src/classes/Application/Media/API/README.md`

```md
# Submodule: Media / API

## Purpose

The Media Document API: five API methods letting any API client — first the
HCP Editor's agents — upload, inspect, list, update and delete media
documents without the admin UI. File content travels as base64 inside a
JSON request body. The methods are built from composable interface+trait
"pieces" following the framework's API Lego Rule
(`docs/agents/project-manifest/constraints.md`, "API Trait Composition
Model"), so each method declares exactly the features it needs.

---

## Requirements

The `media` table must have the `file_size`, `keywords` and `description`
columns, added by `docs/sql/2023-10-16-news.sql`. This is a **precondition**,
not an optional mode: every install that can authenticate an API key already
has these columns, because API key authentication requires the later
`docs/sql/2025-11-05-api-management.sql` tables, and framework migrations
apply in date order. `MediaDocumentWriter::create()` therefore always writes
`file_size`, `description` and `keywords` — there is no columns-absent
branch anywhere in the media API.

---

## Methods

All five methods are versioned `1.0`, live in `Methods/`, and share the
`media`/"Media" method group (`MediaAPIGroup`).

| Method | Right | Summary |
|---|---|---|
| `AddMediaDocument` | `CreateMedia` | Creates a media document from a base64 file upload. |
| `GetMediaDocument` | `ViewMedia` | Retrieves a single document by `mediaID`, optionally with its base64 content. |
| `GetMediaDocuments` | `ViewMedia` | Retrieves a paginated, searchable, filterable list of documents (no content). |
| `UpdateMediaDocument` | `EditMedia` | Updates name/description/keywords and/or replaces the file content. |
| `DeleteMediaDocument` | `DeleteMedia` | Deletes a document's record and file. |

### `AddMediaDocument`

Request (JSON body):

```json
POST /api/AddMediaDocument
Authorization: Bearer <key>
Content-Type: application/json

{
  "fileName": "hero.png",
  "fileContent": "iVBORw0KGgo…",
  "name": "Autumn hero",
  "description": "A hero image",
  "keywords": "autumn, hero, banner",
  "mediaType": "Image"
}
```

- `fileName` and `fileContent` (base64, optionally a `data:<mime>;base64,`
  URI) are required.
- `mediaType` is an optional enum constraint (one of `getTypeIDs()`, e.g.
  `Image`, `PDF`): when given, the uploaded file's resolved type must match
  it exactly, so a consumer such as the HCP Editor can fail fast at upload
  time rather than at content write.
- See "Default name rule" below for the `name` fallback.
- Response: `mediaDocument` (including image dimensions for images) +
  `dryRun`. A dry run returns a preview with `mediaID: null` and persists
  nothing.

### `GetMediaDocument`

Query parameters: `mediaID` (required), `includeContent` (boolean, default
`false`). Returns the `mediaDocument` shape; `content` (base64) is present
only when `includeContent=true`. An unknown `mediaID` is rejected with
`681201` and HTTP 404.

### `GetMediaDocuments`

Query parameters: `search`, `mediaTypes`, `extensions`, `orderBy`
(`dateAdded`|`name`|`fileSize`, default `dateAdded`), `orderDir` (`asc`|`desc`,
default `desc`), `limit` (default 50, max 200), `offset`. Without parameters,
returns the 50 most recently added documents — never the whole table, since
live installs hold 10,000+ documents. Response: `mediaDocuments[]` (no
content), `totalCount`, `limit`, `offset`.

### `UpdateMediaDocument`

Request (JSON body): `mediaID` (required), plus any of `name`,
`description`, `keywords` (an explicit empty string clears the field; an
omitted parameter leaves it unchanged), and the `fileName` + `fileContent`
pair (sent together or not at all) to replace the file.

- At least one change must be submitted — a request with none is rejected
  with `681221`.
- A replacement file must keep the document's current extension; a change
  is rejected with `681005`.
- Response: `mediaDocument`, `modified` (whether anything actually changed),
  `dryRun`.

### `DeleteMediaDocument`

Query parameters: `mediaID` (required). Deletes the record and its file.
Response: `mediaDocument` (the document's shape as it was immediately
before deletion), `deleted`, `dryRun`. There is no in-use guard — the
framework has no concept of what else might reference a media document, so
deleting one still referenced elsewhere (e.g. by an HCP Editor mail content)
is the caller's responsibility.

---

## Dry-run semantics

`AddMediaDocument`, `UpdateMediaDocument` and `DeleteMediaDocument` accept
`dryRun=1`. A dry run performs the identical validation a real call would —
an input rejected by a real call is rejected with the same code by its dry
run — and returns the would-be result with `dryRun: true`, but **never
mutates disk or the database**: it does not write a temp file, does not
create or modify a record, and does not call `MediaDocumentWriter::delete()`.

This is a deliberate departure from a "mutate inside a transaction, then
roll back on dry run" shape: file writes and record deletion are not
transactional, so a rollback would leave orphan files on create or
irretrievably lost files on delete. A side-effect-free validation preview is
the only approach that is safe for every one of the three write methods.

`DeleteMediaDocument`'s dry run in particular never calls `delete()` at
all, since deletion unlinks the file immediately and there is nothing to
safely "undo" within a preview.

---

## Pieces

Following the API Lego Rule, shared features are self-contained
interface+trait pairs. No media trait `use`s another trait, every method
`use`s the trait of every piece interface it implements (including
inherited ones), and each piece owns its own validation code sub-range.
Every piece interface extends `APIMethodInterface` directly — there is no
inheritance between media pieces.

| Piece | Interface / Trait | Purpose | Codes |
|---|---|---|---|
| Document lookup | `MediaDocumentAPIInterface` / `MediaDocumentAPITrait` | Resolves a `MediaRecord` from a `mediaID` parameter (`manageMediaDocumentParams()`). `requireValue()` answers an unknown or missing ID with the domain `681201` via `ErrorResponse::makeNotFound()` (HTTP 404) — never the generic `183004`/`183401`. | `68120x` |
| Metadata | `MediaMetadataAPIInterface` / `MediaMetadataAPITrait` | Registers `name`/`description`/`keywords` (plain strings for Add, clearable for Update via `registerMediaMetadataParams(bool $clearable)`), resolves the effective name (`resolveMediaName()`), and enforces the "no HTML" rule identically for dry and real runs (`validateMediaMetadata()`). | `68121x` |
| File upload | `MediaFileUploadAPIInterface` / `MediaFileUploadAPITrait` | Registers the `fileName` + `fileContent` (`base64File()`) pair (required for Add, optional with a pair rule for Update), builds a `MediaFileContent`, and is the single validation call (`validateMediaFile()`) used identically by dry and real runs. Owns no codes of its own — failures surface through `MediaWriterException`'s `6810xx` block, converted to a client error by the protected `sendWriterValidationError()`. | none (delegates to `MediaWriterException`) |

A framework-level `PaginatedAPIInterface`/`PaginatedAPITrait` piece (outside
this module, in `src/classes/Application/API/Traits/`) supplies
`GetMediaDocuments`' `limit`/`offset`/`totalCount` paging.

### Composition table

| Method | Framework pieces | Media / pagination pieces | Right |
|---|---|---|---|
| `AddMediaDocumentAPI` | `JSONRequest`, `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` | `MediaFileUpload`, `MediaMetadata` | `CreateMedia` |
| `GetMediaDocumentAPI` | `RequestRequest`, `JSONResponse`, `JSONResponseWithExample`, `APIKeyMethod` | `MediaDocument` | `ViewMedia` |
| `GetMediaDocumentsAPI` | `RequestRequest`, `JSONResponse`, `JSONResponseWithExample`, `APIKeyMethod`, `Paginated` | none | `ViewMedia` |
| `UpdateMediaDocumentAPI` | `JSONRequest`, `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` | `MediaDocument`, `MediaFileUpload`, `MediaMetadata` | `EditMedia` |
| `DeleteMediaDocumentAPI` | `RequestRequest`, `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` | `MediaDocument` | `DeleteMedia` |

`MediaAPICompositionTest` enforces this table through reflection: a method
implementing a piece interface without its matching trait fails the test
(and, independently, fails at class level, since the interface's methods
would be unimplemented).

---

## Default name rule

When `AddMediaDocument`'s `name` is omitted, the stored name defaults to the
uploaded file's base name (the file name without its extension), cut to 160
characters (`MediaMetadataAPIInterface::MAX_NAME_LENGTH`) via
`resolveMediaName()`. A sent `name` is already length-checked by its own
parameter; the 160-character cut applies only to the derived default, so a
long `fileName` never silently produces an over-length stored name. A dry
run previews the same, identically-cut name.

---

## File consistency

`MediaDocumentWriter` keeps disk and database consistent on failure, since
file writes and record deletion are not transactional:

- **`create()`** — if anything fails after the target file was written
  (including the dedicated `verifyWrittenFile()` post-write check), the
  written file is deleted before the exception propagates. The temp file
  staging the upload is always removed, success or failure.
- **`replaceFile()`** — the new content's size is stored on the record
  *before* the file is touched (so a DB failure here never reaches disk),
  the current file is backed up, the new content is written last, and a
  failure at or after that point restores the backup before rethrowing.
- **Residual window (accepted, not fixed).** A failure *after*
  `replaceFile()` has already returned successfully — for example a failing
  transaction commit — can leave the new bytes on disk with the old size
  still recorded in the database. File operations are outside the writer's
  transactional reach, so this window cannot be closed by the writer itself.
  `UpdateMediaDocument` minimises the exposure by calling `updateMetadata()`
  before `replaceFile()`, so the file overwrite is always the request's last
  mutating writer call.

---

## Error codes

A caller branching on the response envelope's top-level `code` must not
confuse it with the nested `validationErrors[].code` a `183003` response
carries — the two tables below are reported on the wire in different
places.

### Top-level codes

Codes a caller sees as the response envelope's own `code`:

| Code | Constant | Source |
|---|---|---|
| `681001` | `VALIDATION_UNSUPPORTED_EXTENSION` | `MediaWriterException` |
| `681002` | `VALIDATION_TYPE_MISMATCH` | `MediaWriterException` |
| `681003` | `VALIDATION_CONTENT_MISMATCH` | `MediaWriterException` |
| `681004` | `VALIDATION_EMPTY_FILE` | `MediaWriterException` (an empty `fileContent` value never reaches this check — `Base64FileParameter` resolves an empty string to `null`, which is instead reported as a missing required parameter, `183003`; `681004` only guards a zero-byte `MediaFileContent` built from a non-base64 content source) |
| `681005` | `VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED` | `MediaWriterException` |
| `681006` | `VALIDATION_INVALID_FILE_NAME` | `MediaWriterException` |
| `681201` | `VALIDATION_UNKNOWN_MEDIA_ID` | `MediaDocumentAPIInterface` |
| `681211` | `VALIDATION_HTML_NOT_ALLOWED` | `MediaMetadataAPIInterface` |
| `681221` | `VALIDATION_NO_CHANGES` | `UpdateMediaDocumentAPI` (the only method that raises it) |
| `182801` | `ERROR_FAILED_TO_READ_INPUT` | `JSONRequestInterface` (an unparseable or non-object JSON body) |
| `182802` | `ERROR_REQUEST_BODY_TOO_LARGE` | `JSONRequestInterface` (request body over the ceiling, or discarded by PHP's `post_max_size` — see "Size limits" below; HTTP 413 via `ErrorResponse::makePayloadTooLarge()`) |
| `183003` | `ERROR_INVALID_REQUEST_PARAMS` | `BaseAPIMethod::validate()` — the envelope's top-level code for **every** parameter-validation failure, including the `1835xx`/`1837xx` codes below, which never themselves reach the top level |

`681001`–`681006` are reported only as `MediaWriterException`, the module's
one validation failure channel (`isValidationError()` distinguishes them
from the writer's internal `6811xx` codes, which are never surfaced to a
client and instead reach the shared execution boundary as unexpected
throwables).

### Parameter-validation codes

Codes reported only inside the `183003` response's `validationErrors[].code`
(see the `api` module's README for the full `validationErrors` shape) —
never as the envelope's own top-level `code`:

| Code | Constant | Source |
|---|---|---|
| `183508` | `VALIDATION_INVALID_BASE64` | `ParamValidationInterface` (`Base64FileParameter`) |
| `183509` | `VALIDATION_FILE_SIZE_EXCEEDED` | `ParamValidationInterface` (`Base64FileParameter`) |
| `183701` | `VALIDATION_INVALID_LIMIT` | `PaginatedAPIInterface` |
| `183702` | `VALIDATION_INVALID_OFFSET` | `PaginatedAPIInterface` |

---

## Size limits

The base64 `fileContent` parameter enforces a configurable decoded-size
limit, `AppConfig::getAPIMaxFileContentSize()` (`APP_API_MAX_FILE_CONTENT_SIZE`,
default 10 MiB), checked from the *encoded* length before any decoding
occurs. Oversized input is rejected with `183509` without ever being
decoded.

**Request-body ceiling.** Independently of the file-content limit above,
every JSON API method — including this module's — enforces a ceiling on the
raw request body itself via `BaseAPIMethod::getRequestBody()` (see the `api`
module's README, "Request body ceiling"). The *configured* ceiling,
`AppConfig::getAPIMaxRequestBodySize()`, uses the explicitly configured
`APP_API_MAX_REQUEST_BODY_SIZE` when set; otherwise it is derived from
`APP_API_MAX_FILE_CONTENT_SIZE` as `floor(fileContentLimit × 4 / 3) + 1 MiB`
— the base64 transfer overhead plus a fixed 1 MiB allowance for the
surrounding JSON structure and other fields (at the 10 MiB default this
derives to roughly 14.3 MiB). The *effective* ceiling actually enforced is
the lower of this configured value and the server's own `post_max_size`
(`RequestBodyReader::resolveEffectiveCeiling()`), and it is the effective
ceiling — not the raw configured one — that the response's
`maxRequestBodySize` key reports. A body over the effective ceiling surfaces
as `182802`/HTTP 413 with the standard "exceeds the maximum allowed size"
message; a body PHP's own `post_max_size` silently discarded before the
application could even read it surfaces as the same `182802`/HTTP 413, but
with a distinct message naming the declared length and `post_max_size`
explicitly (see "Error codes" above).

**`post_max_size` guidance.** PHP's `post_max_size` must still be configured
on every target install to **at least the configured
`AppConfig::getAPIMaxRequestBodySize()` ceiling** — not a fixed multiplier
of the file-content limit. The effective-ceiling resolution makes the
framework *report* accurately when `post_max_size` is lower (so a shrunk
request can actually succeed), but it does not raise `post_max_size` itself:
an install where `post_max_size` is below the configured ceiling still
discards otherwise-valid bodies before `getRequestBody()` ever runs, which
this module's request-body ceiling work detects and reports as `182802`
(with its own discard-specific message) rather than letting it masquerade
as a missing-parameter error.

---

## SVG and duplicate uploads

- **SVG** is accepted, exactly as in the admin UI. The content check for SVG
  only confirms the uploaded bytes really are a well-formed SVG document
  (its root element is `svg`) — this is a file-type check, not a content or
  security policy. Neutralising an SVG's potential inline script is instead
  the delivery path's responsibility: every file served through
  `DocumentTrait`'s shared serving seam gets `X-Content-Type-Options: nosniff`
  from `MediaResponseHeaders`, and a file whose extension is SVG or SVGZ
  additionally gets a sandboxing `Content-Security-Policy`
  (`default-src 'none'; style-src 'unsafe-inline'; sandbox`) that disables
  script execution without altering the served file's bytes. Neither header
  affects an SVG embedded via `<img>` — a browser never executes a CSP or
  acts on `X-Content-Type-Options` for an image resource load, only for a
  document opened directly — so inline `<img>` use of an uploaded SVG is
  unaffected.
- **Duplicate uploads are accepted.** There is no content-hash
  deduplication — uploading the same bytes twice creates two independent
  media documents.

```
_SOURCE: MediaAPIGroup, MediaAPIResponseInterface, MediaDocumentSerializer, the three media pieces (document lookup, metadata, file upload), and the five method classes_
# MediaAPIGroup, MediaAPIResponseInterface, MediaDocumentSerializer, the three media pieces (document lookup, metadata, file upload), and the five method classes
```
// Structure of documents
└── src/
    └── classes/
        └── Application/
            └── Media/
                └── API/
                    └── MediaAPIGroup.php
                    └── MediaAPIResponseInterface.php
                    └── MediaDocumentAPIInterface.php
                    └── MediaDocumentAPITrait.php
                    └── MediaDocumentParamsContainer.php
                    └── MediaDocumentSerializer.php
                    └── MediaFileUploadAPIInterface.php
                    └── MediaFileUploadAPITrait.php
                    └── MediaMetadataAPIInterface.php
                    └── MediaMetadataAPITrait.php
                    └── Methods/
                        ├── AddMediaDocumentAPI.php
                        ├── DeleteMediaDocumentAPI.php
                        ├── GetMediaDocumentAPI.php
                        ├── GetMediaDocumentsAPI.php
                        ├── UpdateMediaDocumentAPI.php
                    └── Params/
                        └── MediaIDHandler.php
                        └── MediaIDParam.php

```
###  Path: `/src/classes/Application/Media/API/MediaAPIGroup.php`

```php
namespace Application\Media\API;

use Application\API\Groups\GenericAPIGroup as GenericAPIGroup;

/**
 * API method group for the media document API methods.
 *
 * @package Media
 * @subpackage API
 */
class MediaAPIGroup extends GenericAPIGroup
{
	public static function create(): self
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/MediaAPIResponseInterface.php`

```php
namespace Application\Media\API;

/**
 * Response key constants for the media document API methods.
 *
 * Deliberately a constants-only interface: it is implemented by every media
 * API method so each one carries a compile-time reference to the shape of
 * the response it builds through {@see MediaDocumentSerializer}, without
 * pulling in any default method implementation (there is none to pull in).
 *
 * @package Media
 * @subpackage API
 * @see MediaDocumentSerializer
 */
interface MediaAPIResponseInterface
{
	/**
	 * The single media document shape, as built by {@see MediaDocumentSerializer::serialize()}
	 * / {@see MediaDocumentSerializer::serializePreview()}.
	 */
	public const KEY_MEDIA_DOCUMENT = 'mediaDocument';

	/** A list of media document shapes, as returned by `GetMediaDocuments`. */
	public const KEY_MEDIA_DOCUMENTS = 'mediaDocuments';
	public const KEY_MEDIA_ID = 'mediaID';
	public const KEY_NAME = 'name';
	public const KEY_MEDIA_TYPE = 'mediaType';
	public const KEY_MEDIA_TYPE_LABEL = 'mediaTypeLabel';
	public const KEY_EXTENSION = 'extension';
	public const KEY_FILE_NAME = 'fileName';
	public const KEY_FILE_SIZE = 'fileSize';

	/** ISO 8601 (`DateTime::ATOM`) timestamp of when the document was added. */
	public const KEY_DATE_ADDED = 'dateAdded';
	public const KEY_AUTHOR_ID = 'authorID';
	public const KEY_DESCRIPTION = 'description';
	public const KEY_KEYWORDS = 'keywords';

	/**
	 * Image-specific sub-shape (`width`, `height`, `isVector`, `isAnimated`).
	 * `null` for a non-image document.
	 */
	public const KEY_IMAGE = 'image';
	public const KEY_IMAGE_WIDTH = 'width';
	public const KEY_IMAGE_HEIGHT = 'height';
	public const KEY_IMAGE_IS_VECTOR = 'isVector';
	public const KEY_IMAGE_IS_ANIMATED = 'isAnimated';

	/**
	 * Base64-encoded file content, included only when explicitly requested
	 * (e.g. `GetMediaDocument`'s `includeContent` parameter).
	 */
	public const KEY_CONTENT = 'content';
	public const KEY_MODIFIED = 'modified';
	public const KEY_DELETED = 'deleted';
	public const KEY_DRY_RUN = 'dryRun';
}


```
###  Path: `/src/classes/Application/Media/API/MediaDocumentAPIInterface.php`

```php
namespace Application\Media\API;

use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * Interface for API methods that resolve a single media document from a
 * `mediaID` parameter (Get, Update, Delete).
 *
 * Use {@see MediaDocumentAPITrait} to implement this interface.
 *
 * @package Media
 * @subpackage API
 * @see MediaDocumentAPITrait
 */
interface MediaDocumentAPIInterface extends APIMethodInterface
{
	public const PARAM_MEDIA_ID = 'mediaID';

	/**
	 * The submitted (or missing) `mediaID` could not be resolved to an
	 * existing media document. Sent with HTTP 404 via
	 * {@see \Application\API\ErrorResponse::makeNotFound()} — never the
	 * generic {@see APIMethodInterface::ERROR_NO_VALUE_AVAILABLE} (183004)
	 * nor the exists-callback {@see \Application\API\Parameters\Validation\Type\ValueExistsCallbackValidation::VALIDATION_VALUE_NOT_EXISTS}
	 * (183401).
	 *
	 * Allocated in the `68120x` sub-range.
	 */
	public const VALIDATION_UNKNOWN_MEDIA_ID = 681201;

	public function manageMediaDocumentParams(): MediaDocumentParamsContainer;
}


```
###  Path: `/src/classes/Application/Media/API/MediaDocumentAPITrait.php`

```php
namespace Application\Media\API;

/**
 * Trait used to implement the interface {@see MediaDocumentAPIInterface}.
 *
 * @package Media
 * @subpackage API
 * @see MediaDocumentAPIInterface
 */
trait MediaDocumentAPITrait
{
	public function manageMediaDocumentParams(): MediaDocumentParamsContainer
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/MediaDocumentParamsContainer.php`

```php
namespace Application\Media\API;

use Application\API\Parameters\Handlers\BaseParamsHandlerContainer as BaseParamsHandlerContainer;
use Application\Media\API\Params\MediaIDHandler as MediaIDHandler;
use Application\Media\Collection\MediaRecord as MediaRecord;

/**
 * Container for the media document lookup parameter handlers.
 *
 * Resolves a single {@see MediaRecord} from the `mediaID` parameter. Its
 * `requireValue()` is overridden (the {@see \Maileditor\Mails\API\MailParamsContainer}
 * shape, from the HCP Editor) to send the domain
 * {@see MediaDocumentAPIInterface::VALIDATION_UNKNOWN_MEDIA_ID} (681201)
 * with HTTP 404 — never the generic `ERROR_NO_VALUE_AVAILABLE` (183004).
 *
 * @package Media
 * @subpackage API
 *
 * @method MediaDocumentAPIInterface getMethod()
 */
class MediaDocumentParamsContainer extends BaseParamsHandlerContainer
{
	public function resolveValue(): ?MediaRecord
	{
		/* ... */
	}


	public function requireValue(): MediaRecord
	{
		/* ... */
	}


	/**
	 * Pre-selects a media record, bypassing parameter resolution. Useful
	 * when a caller already holds the record (e.g. a chained
	 * `processReturn()` call).
	 *
	 * @param MediaRecord $record
	 * @return $this
	 */
	public function selectMediaDocument(MediaRecord $record): self
	{
		/* ... */
	}


	public function manageID(): MediaIDHandler
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/MediaDocumentSerializer.php`

```php
namespace Application\Media\API;

use Application\AppFactory as AppFactory;
use Application\Media\Collection\MediaRecord as MediaRecord;
use Application\Media\ImageDocumentInterface as ImageDocumentInterface;
use Application\Media\Writer\MediaFileContent as MediaFileContent;
use Application_Media_Document as Application_Media_Document;
use Application_Media_Document_Image as Application_Media_Document_Image;
use DateTime as DateTime;

/**
 * Serializes a {@see MediaRecord} (or, for a dry run, a not-yet-written
 * {@see MediaFileContent}) into the response shape declared by
 * {@see MediaAPIResponseInterface}.
 *
 * Stateless: every method is a pure static function of its arguments.
 *
 * @package Media
 * @subpackage API
 */
class MediaDocumentSerializer
{
	/**
	 * Serializes an existing, persisted media document.
	 *
	 * @param MediaRecord $record
	 * @param bool $includeContent Whether to include the base64-encoded file
	 *        content under {@see MediaAPIResponseInterface::KEY_CONTENT}.
	 * @return array<string, mixed>
	 */
	public static function serialize(MediaRecord $record, bool $includeContent = false): array
	{
		/* ... */
	}


	/**
	 * Builds the dry-run preview shape for a would-be media document that
	 * has not been written to disk or the database. `mediaID` is always
	 * `null`, since no record exists yet.
	 *
	 * @param MediaFileContent $content
	 * @param string $typeID The resolved media type ID (e.g. `Image`, `PDF`).
	 * @param string $name The resolved document name (already cut to the
	 *        metadata piece's length limit by the caller).
	 * @param string $description
	 * @param string $keywords
	 * @return array<string, mixed>
	 */
	public static function serializePreview(
		MediaFileContent $content,
		string $typeID,
		string $name,
		string $description = '',
		string $keywords = '',
	): array
	{
		/* ... */
	}


	/**
	 * Builds a fixed, DB-free example of a serialized media document, for
	 * use in the API documentation (`getExampleJSONResponse()`). Documentation
	 * builds must not write media files, so this never touches the database
	 * or the filesystem.
	 *
	 * @param bool $withImage Whether the example describes an image document
	 *        (`true`) or a non-image document, e.g. a PDF (`false`).
	 * @return array<string, mixed>
	 */
	public static function buildExample(bool $withImage = true): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/MediaFileUploadAPIInterface.php`

```php
namespace Application\Media\API;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\Media\Writer\MediaFileContent as MediaFileContent;

/**
 * Interface for API methods that accept a media file upload (`fileName` +
 * `fileContent`) — used by `AddMediaDocument` and `UpdateMediaDocument`.
 *
 * This piece owns no validation codes of its own: file content validation
 * failures are reported through {@see \Application\Media\Writer\MediaWriterException}'s
 * `6810xx` block (design review Decision 5), converted to a client error
 * through {@see self::sendWriterValidationError()}.
 *
 * Use {@see MediaFileUploadAPITrait} to implement this interface.
 *
 * @package Media
 * @subpackage API
 * @see MediaFileUploadAPITrait
 */
interface MediaFileUploadAPIInterface extends APIMethodInterface
{
	public const PARAM_FILE_NAME = 'fileName';
	public const PARAM_FILE_CONTENT = 'fileContent';

	/**
	 * Registers the file upload parameters.
	 *
	 * @param bool $required `true` (`AddMediaDocument`) registers both
	 *        parameters as required. `false` (`UpdateMediaDocument`)
	 *        registers them as optional, plus a pair rule that enforces
	 *        both are sent together or neither is.
	 * @return $this
	 */
	public function registerMediaFileParams(bool $required): self;


	/**
	 * Whether a file was submitted (both `fileName` and `fileContent`).
	 */
	public function hasMediaFile(): bool;


	/**
	 * Builds the submitted file content once, from `fileName` and the
	 * decoded `fileContent`. `null` when no file was submitted.
	 */
	public function getMediaFileContent(): ?MediaFileContent;


	/**
	 * Validates the submitted file content. The single validation call used
	 * identically by both dry and real runs, so neither can drift from the
	 * other (design review Decision 6).
	 *
	 * @param string|null $requiredTypeID When given, the resolved type must
	 *        match this type ID exactly.
	 * @return string The resolved media type ID (e.g. `Image`, `PDF`).
	 */
	public function validateMediaFile(?string $requiredTypeID = null): string;
}


```
###  Path: `/src/classes/Application/Media/API/MediaFileUploadAPITrait.php`

```php
namespace Application\Media\API;

use Application\API\APIMethodInterface as APIMethodInterface;
use Application\API\Parameters\Type\Base64FileParameter as Base64FileParameter;
use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\Media\Writer\MediaFileContent as MediaFileContent;
use Application\Media\Writer\MediaFileValidator as MediaFileValidator;
use Application\Media\Writer\MediaWriterException as MediaWriterException;

/**
 * Trait used to implement the interface {@see MediaFileUploadAPIInterface}.
 *
 * It owns no validation codes; file validation failures are reported
 * through {@see MediaWriterException}'s `6810xx` block (design review
 * Decision 5).
 *
 * @package Media
 * @subpackage API
 * @see MediaFileUploadAPIInterface
 */
trait MediaFileUploadAPITrait
{
	public function registerMediaFileParams(bool $required): self
	{
		/* ... */
	}


	public function hasMediaFile(): bool
	{
		/* ... */
	}


	public function getMediaFileContent(): ?MediaFileContent
	{
		/* ... */
	}


	public function validateMediaFile(?string $requiredTypeID = null): string
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/MediaMetadataAPIInterface.php`

```php
namespace Application\Media\API;

use Application\API\APIMethodInterface as APIMethodInterface;

/**
 * Interface for API methods that accept media document metadata (`name`,
 * `description`, `keywords`) — used by `AddMediaDocument` and
 * `UpdateMediaDocument`.
 *
 * Use {@see MediaMetadataAPITrait} to implement this interface.
 *
 * @package Media
 * @subpackage API
 * @see MediaMetadataAPITrait
 */
interface MediaMetadataAPIInterface extends APIMethodInterface
{
	public const PARAM_NAME = 'name';
	public const PARAM_DESCRIPTION = 'description';
	public const PARAM_KEYWORDS = 'keywords';
	public const MAX_NAME_LENGTH = 160;
	public const MAX_DESCRIPTION_LENGTH = 1200;
	public const MAX_KEYWORDS_LENGTH = 500;

	/**
	 * `description` or `keywords` contained HTML markup, which is never
	 * accepted (the admin form enforces the same rule via `addRuleNoHTML()`).
	 *
	 * Allocated in the `68121x` sub-range.
	 */
	public const VALIDATION_HTML_NOT_ALLOWED = 681211;

	/**
	 * Registers the metadata parameters.
	 *
	 * @param bool $clearable When `true` (`UpdateMediaDocument`), `description`
	 *        and `keywords` are registered as clearable: absent means
	 *        unchanged, an explicit empty value means clear. When `false`
	 *        (`AddMediaDocument`), all three parameters are plain strings.
	 * @return $this
	 */
	public function registerMediaMetadataParams(bool $clearable): self;


	/**
	 * The submitted `name`, or `null` when it was not sent.
	 */
	public function getMediaName(): ?string;


	/**
	 * The submitted `description`, or `null` when it was not sent.
	 */
	public function getMediaDescription(): ?string;


	/**
	 * The submitted `keywords`, or `null` when it was not sent.
	 */
	public function getMediaKeywords(): ?string;


	/**
	 * Resolves the document name: the submitted `name`, or `$default` cut
	 * to {@see self::MAX_NAME_LENGTH} characters (`mb_substr()`) when no
	 * name was sent. A sent name is already length-checked by its own
	 * parameter; the default is not, so this applies the same limit.
	 *
	 * @param string $default
	 * @return string
	 */
	public function resolveMediaName(string $default): string;


	/**
	 * Validates the HTML rule on `description`/`keywords`. The single home
	 * of {@see self::VALIDATION_HTML_NOT_ALLOWED}, called identically by
	 * dry and real runs so neither can drift from the other.
	 *
	 * @return void
	 */
	public function validateMediaMetadata(): void;
}


```
###  Path: `/src/classes/Application/Media/API/MediaMetadataAPITrait.php`

```php
namespace Application\Media\API;

use Application\API\Parameters\Type\ClearableStringParameter as ClearableStringParameter;
use Application\API\Parameters\Type\StringParameter as StringParameter;

/**
 * Trait used to implement the interface {@see MediaMetadataAPIInterface}.
 *
 * It needs no writer and depends on no other piece.
 *
 * @package Media
 * @subpackage API
 * @see MediaMetadataAPIInterface
 */
trait MediaMetadataAPITrait
{
	public function registerMediaMetadataParams(bool $clearable): self
	{
		/* ... */
	}


	public function getMediaName(): ?string
	{
		/* ... */
	}


	public function getMediaDescription(): ?string
	{
		/* ... */
	}


	public function getMediaKeywords(): ?string
	{
		/* ... */
	}


	public function resolveMediaName(string $default): string
	{
		/* ... */
	}


	public function validateMediaMetadata(): void
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/AddMediaDocumentAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\API\Traits\DryRunAPIInterface as DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait as DryRunAPITrait;
use Application\API\Traits\JSONRequestInterface as JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait as JSONRequestTrait;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\AppFactory as AppFactory;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\API\MediaFileUploadAPIInterface as MediaFileUploadAPIInterface;
use Application\Media\API\MediaFileUploadAPITrait as MediaFileUploadAPITrait;
use Application\Media\API\MediaMetadataAPIInterface as MediaMetadataAPIInterface;
use Application\Media\API\MediaMetadataAPITrait as MediaMetadataAPITrait;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter as MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent as MediaFileContent;
use Application\Media\Writer\MediaWriterException as MediaWriterException;
use DBHelper as DBHelper;
use Override as Override;

/**
 * API method: Creates a new media document from a base64-encoded file upload.
 *
 * Composition (per the plan's Approach §4 table): `JSONRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` pieces,
 * plus the `MediaFileUpload` and `MediaMetadata` media pieces.
 *
 * Follows the write-method shape of `UpdateMailLinkAPI` (HCP Editor): an own
 * DB transaction when none is open, domain validation shared identically by
 * dry and real runs, and a commit or rollback depending on `dryRun`. Only
 * the mutating writer call is skipped on a dry run; a dry run never touches
 * disk or the database and returns `mediaID: null`.
 *
 * @package Media
 * @subpackage API
 */
class AddMediaDocumentAPI extends BaseAPIMethod implements JSONRequestInterface, JSONResponseWithExampleInterface, DryRunAPIInterface, APIKeyMethodInterface, MediaFileUploadAPIInterface, MediaMetadataAPIInterface, MediaAPIResponseInterface
{
	use JSONRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use DryRunAPITrait;
	use APIKeyMethodTrait;
	use MediaFileUploadAPITrait;
	use MediaMetadataAPITrait;

	public const METHOD_NAME = 'AddMediaDocument';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];
	public const PARAM_MEDIA_TYPE = 'mediaType';

	/**
	 * Disambiguates the identical `KEY_DRY_RUN = 'dryRun'` constant inherited
	 * from both {@see DryRunAPIInterface} and {@see MediaAPIResponseInterface}
	 * (PHP treats two interface constants of the same name as an ambiguous
	 * conflict even when their values match, unless the implementing class
	 * redeclares the constant itself).
	 */
	public const KEY_DRY_RUN = MediaAPIResponseInterface::KEY_DRY_RUN;

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/AddMediaDocumentAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\API\Traits\DryRunAPIInterface as DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait as DryRunAPITrait;
use Application\API\Traits\JSONRequestInterface as JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait as JSONRequestTrait;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\AppFactory as AppFactory;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\API\MediaFileUploadAPIInterface as MediaFileUploadAPIInterface;
use Application\Media\API\MediaFileUploadAPITrait as MediaFileUploadAPITrait;
use Application\Media\API\MediaMetadataAPIInterface as MediaMetadataAPIInterface;
use Application\Media\API\MediaMetadataAPITrait as MediaMetadataAPITrait;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter as MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent as MediaFileContent;
use Application\Media\Writer\MediaWriterException as MediaWriterException;
use DBHelper as DBHelper;
use Override as Override;

/**
 * API method: Creates a new media document from a base64-encoded file upload.
 *
 * Composition (per the plan's Approach §4 table): `JSONRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` pieces,
 * plus the `MediaFileUpload` and `MediaMetadata` media pieces.
 *
 * Follows the write-method shape of `UpdateMailLinkAPI` (HCP Editor): an own
 * DB transaction when none is open, domain validation shared identically by
 * dry and real runs, and a commit or rollback depending on `dryRun`. Only
 * the mutating writer call is skipped on a dry run; a dry run never touches
 * disk or the database and returns `mediaID: null`.
 *
 * @package Media
 * @subpackage API
 */
class AddMediaDocumentAPI extends BaseAPIMethod implements JSONRequestInterface, JSONResponseWithExampleInterface, DryRunAPIInterface, APIKeyMethodInterface, MediaFileUploadAPIInterface, MediaMetadataAPIInterface, MediaAPIResponseInterface
{
	use JSONRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use DryRunAPITrait;
	use APIKeyMethodTrait;
	use MediaFileUploadAPITrait;
	use MediaMetadataAPITrait;

	public const METHOD_NAME = 'AddMediaDocument';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];
	public const PARAM_MEDIA_TYPE = 'mediaType';

	/**
	 * Disambiguates the identical `KEY_DRY_RUN = 'dryRun'` constant inherited
	 * from both {@see DryRunAPIInterface} and {@see MediaAPIResponseInterface}
	 * (PHP treats two interface constants of the same name as an ambiguous
	 * conflict even when their values match, unless the implementing class
	 * redeclares the constant itself).
	 */
	public const KEY_DRY_RUN = MediaAPIResponseInterface::KEY_DRY_RUN;

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/DeleteMediaDocumentAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Traits\DryRunAPIInterface as DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait as DryRunAPITrait;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface as MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait as MediaDocumentAPITrait;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter as MediaDocumentWriter;
use DBHelper as DBHelper;
use Override as Override;

/**
 * API method: Deletes an existing media document and its file.
 *
 * Composition (per the plan's Approach §4 table): `RequestRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod`
 * pieces, plus the `MediaDocument` media piece.
 *
 * A dry run never calls {@see MediaDocumentWriter::delete()}: record
 * deletion unlinks the file immediately, which is not something a dry run
 * can safely preview by undoing.
 *
 * @package Media
 * @subpackage API
 */
class DeleteMediaDocumentAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseWithExampleInterface, DryRunAPIInterface, APIKeyMethodInterface, MediaDocumentAPIInterface, MediaAPIResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use DryRunAPITrait;
	use APIKeyMethodTrait;
	use MediaDocumentAPITrait;

	public const METHOD_NAME = 'DeleteMediaDocument';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];

	/**
	 * Disambiguates the identical `KEY_DRY_RUN = 'dryRun'` constant inherited
	 * from both {@see DryRunAPIInterface} and {@see MediaAPIResponseInterface}
	 * (PHP treats two interface constants of the same name as an ambiguous
	 * conflict even when their values match, unless the implementing class
	 * redeclares the constant itself).
	 */
	public const KEY_DRY_RUN = MediaAPIResponseInterface::KEY_DRY_RUN;

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/DeleteMediaDocumentAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Traits\DryRunAPIInterface as DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait as DryRunAPITrait;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface as MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait as MediaDocumentAPITrait;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter as MediaDocumentWriter;
use DBHelper as DBHelper;
use Override as Override;

/**
 * API method: Deletes an existing media document and its file.
 *
 * Composition (per the plan's Approach §4 table): `RequestRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod`
 * pieces, plus the `MediaDocument` media piece.
 *
 * A dry run never calls {@see MediaDocumentWriter::delete()}: record
 * deletion unlinks the file immediately, which is not something a dry run
 * can safely preview by undoing.
 *
 * @package Media
 * @subpackage API
 */
class DeleteMediaDocumentAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseWithExampleInterface, DryRunAPIInterface, APIKeyMethodInterface, MediaDocumentAPIInterface, MediaAPIResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use DryRunAPITrait;
	use APIKeyMethodTrait;
	use MediaDocumentAPITrait;

	public const METHOD_NAME = 'DeleteMediaDocument';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];

	/**
	 * Disambiguates the identical `KEY_DRY_RUN = 'dryRun'` constant inherited
	 * from both {@see DryRunAPIInterface} and {@see MediaAPIResponseInterface}
	 * (PHP treats two interface constants of the same name as an ambiguous
	 * conflict even when their values match, unless the implementing class
	 * redeclares the constant itself).
	 */
	public const KEY_DRY_RUN = MediaAPIResponseInterface::KEY_DRY_RUN;

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/GetMediaDocumentAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Parameters\Type\BooleanParameter as BooleanParameter;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface as MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait as MediaDocumentAPITrait;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Override as Override;

/**
 * API method: Retrieves a single media document by its `mediaID`.
 *
 * Composition (per the plan's Approach §4 table): `RequestRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `APIKeyMethod` pieces, plus the
 * `MediaDocument` media piece.
 *
 * @package Media
 * @subpackage API
 */
class GetMediaDocumentAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseWithExampleInterface, APIKeyMethodInterface, MediaDocumentAPIInterface, MediaAPIResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use APIKeyMethodTrait;
	use MediaDocumentAPITrait;

	public const METHOD_NAME = 'GetMediaDocument';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];
	public const PARAM_INCLUDE_CONTENT = 'includeContent';

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/GetMediaDocumentAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Parameters\Type\BooleanParameter as BooleanParameter;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface as MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait as MediaDocumentAPITrait;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Override as Override;

/**
 * API method: Retrieves a single media document by its `mediaID`.
 *
 * Composition (per the plan's Approach §4 table): `RequestRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `APIKeyMethod` pieces, plus the
 * `MediaDocument` media piece.
 *
 * @package Media
 * @subpackage API
 */
class GetMediaDocumentAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseWithExampleInterface, APIKeyMethodInterface, MediaDocumentAPIInterface, MediaAPIResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use APIKeyMethodTrait;
	use MediaDocumentAPITrait;

	public const METHOD_NAME = 'GetMediaDocument';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];
	public const PARAM_INCLUDE_CONTENT = 'includeContent';

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/GetMediaDocumentsAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\OperationResult as OperationResult;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Type\StringListParameter as StringListParameter;
use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\API\Parameters\Validation\Type\EnumValidation as EnumValidation;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Traits\PaginatedAPIInterface as PaginatedAPIInterface;
use Application\API\Traits\PaginatedAPITrait as PaginatedAPITrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\AppFactory as AppFactory;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\Collection\MediaFilterCriteria as MediaFilterCriteria;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Override as Override;

/**
 * API method: Retrieves a paginated, searchable, filterable list of media
 * documents.
 *
 * Composition (per the plan's Approach §4 table): `RequestRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `APIKeyMethod` pieces, plus the
 * framework `Paginated` piece. `search`/`mediaTypes`/`extensions`/`orderBy`/
 * `orderDir` are method-local parameters, not pieces, since there is no
 * second consumer yet.
 *
 * Without parameters, this returns the 50 most recently added documents —
 * never the whole table, since live installs hold 10,000+ documents.
 *
 * @package Media
 * @subpackage API
 */
class GetMediaDocumentsAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseWithExampleInterface, APIKeyMethodInterface, PaginatedAPIInterface, MediaAPIResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use APIKeyMethodTrait;
	use PaginatedAPITrait;

	public const METHOD_NAME = 'GetMediaDocuments';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];
	public const PARAM_SEARCH = 'search';
	public const PARAM_MEDIA_TYPES = 'mediaTypes';
	public const PARAM_EXTENSIONS = 'extensions';
	public const PARAM_ORDER_BY = 'orderBy';
	public const PARAM_ORDER_DIR = 'orderDir';
	public const ORDER_BY_DATE_ADDED = 'dateAdded';
	public const ORDER_BY_NAME = 'name';
	public const ORDER_BY_FILE_SIZE = 'fileSize';
	public const ORDER_DIR_ASC = 'asc';
	public const ORDER_DIR_DESC = 'desc';

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	/**
	 * Validates every submitted media type against the known type registry.
	 *
	 * @param array<int,mixed>|int|float|bool|string $value
	 * @param OperationResult $result
	 * @param APIParameterInterface $param
	 */
	public function validateMediaTypes(
		array|int|float|bool|string $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/GetMediaDocumentsAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\OperationResult as OperationResult;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Parameters\APIParameterInterface as APIParameterInterface;
use Application\API\Parameters\Type\StringListParameter as StringListParameter;
use Application\API\Parameters\Type\StringParameter as StringParameter;
use Application\API\Parameters\Validation\Type\EnumValidation as EnumValidation;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Traits\PaginatedAPIInterface as PaginatedAPIInterface;
use Application\API\Traits\PaginatedAPITrait as PaginatedAPITrait;
use Application\API\Traits\RequestRequestInterface as RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait as RequestRequestTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\AppFactory as AppFactory;
use Application\Interfaces\FilterCriteriaInterface as FilterCriteriaInterface;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\Collection\MediaFilterCriteria as MediaFilterCriteria;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Override as Override;

/**
 * API method: Retrieves a paginated, searchable, filterable list of media
 * documents.
 *
 * Composition (per the plan's Approach §4 table): `RequestRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `APIKeyMethod` pieces, plus the
 * framework `Paginated` piece. `search`/`mediaTypes`/`extensions`/`orderBy`/
 * `orderDir` are method-local parameters, not pieces, since there is no
 * second consumer yet.
 *
 * Without parameters, this returns the 50 most recently added documents —
 * never the whole table, since live installs hold 10,000+ documents.
 *
 * @package Media
 * @subpackage API
 */
class GetMediaDocumentsAPI extends BaseAPIMethod implements RequestRequestInterface, JSONResponseWithExampleInterface, APIKeyMethodInterface, PaginatedAPIInterface, MediaAPIResponseInterface
{
	use RequestRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use APIKeyMethodTrait;
	use PaginatedAPITrait;

	public const METHOD_NAME = 'GetMediaDocuments';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];
	public const PARAM_SEARCH = 'search';
	public const PARAM_MEDIA_TYPES = 'mediaTypes';
	public const PARAM_EXTENSIONS = 'extensions';
	public const PARAM_ORDER_BY = 'orderBy';
	public const PARAM_ORDER_DIR = 'orderDir';
	public const ORDER_BY_DATE_ADDED = 'dateAdded';
	public const ORDER_BY_NAME = 'name';
	public const ORDER_BY_FILE_SIZE = 'fileSize';
	public const ORDER_DIR_ASC = 'asc';
	public const ORDER_DIR_DESC = 'desc';

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	/**
	 * Validates every submitted media type against the known type registry.
	 *
	 * @param array<int,mixed>|int|float|bool|string $value
	 * @param OperationResult $result
	 * @param APIParameterInterface $param
	 */
	public function validateMediaTypes(
		array|int|float|bool|string $value,
		OperationResult $result,
		APIParameterInterface $param,
	): void
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/UpdateMediaDocumentAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Traits\DryRunAPIInterface as DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait as DryRunAPITrait;
use Application\API\Traits\JSONRequestInterface as JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait as JSONRequestTrait;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface as MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait as MediaDocumentAPITrait;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\API\MediaFileUploadAPIInterface as MediaFileUploadAPIInterface;
use Application\Media\API\MediaFileUploadAPITrait as MediaFileUploadAPITrait;
use Application\Media\API\MediaMetadataAPIInterface as MediaMetadataAPIInterface;
use Application\Media\API\MediaMetadataAPITrait as MediaMetadataAPITrait;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\Collection\MediaRecord as MediaRecord;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter as MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent as MediaFileContent;
use Application\Media\Writer\MediaWriterException as MediaWriterException;
use DBHelper as DBHelper;
use Override as Override;

/**
 * API method: Updates the name/description/keywords and/or the file content
 * of an existing media document.
 *
 * Composition (per the plan's Approach §4 table): `JSONRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` pieces,
 * plus the `MediaDocument`, `MediaFileUpload` and `MediaMetadata` media
 * pieces.
 *
 * {@see self::VALIDATION_NO_CHANGES} is declared here rather than on a
 * piece, since only this method raises it (sub-range `68122x`).
 *
 * The real branch calls {@see MediaDocumentWriter::updateMetadata()} before
 * {@see MediaDocumentWriter::replaceFile()}, so the file overwrite is always
 * the request's last mutating writer call (audit cycle 3, Minor 2).
 *
 * @package Media
 * @subpackage API
 */
class UpdateMediaDocumentAPI extends BaseAPIMethod implements JSONRequestInterface, JSONResponseWithExampleInterface, DryRunAPIInterface, APIKeyMethodInterface, MediaDocumentAPIInterface, MediaFileUploadAPIInterface, MediaMetadataAPIInterface, MediaAPIResponseInterface
{
	use JSONRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use DryRunAPITrait;
	use APIKeyMethodTrait;
	use MediaDocumentAPITrait;
	use MediaFileUploadAPITrait;
	use MediaMetadataAPITrait;

	public const METHOD_NAME = 'UpdateMediaDocument';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];

	/**
	 * Neither a metadata field nor a file replacement was submitted; at
	 * least one change is required. Allocated in the `68122x` sub-range,
	 * since only this method raises it.
	 */
	public const VALIDATION_NO_CHANGES = 681221;

	/**
	 * Disambiguates the identical `KEY_DRY_RUN = 'dryRun'` constant inherited
	 * from both {@see DryRunAPIInterface} and {@see MediaAPIResponseInterface}
	 * (PHP treats two interface constants of the same name as an ambiguous
	 * conflict even when their values match, unless the implementing class
	 * redeclares the constant itself).
	 */
	public const KEY_DRY_RUN = MediaAPIResponseInterface::KEY_DRY_RUN;

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Methods/UpdateMediaDocumentAPI.php`

```php
namespace Application\Media\API\Methods;

use AppUtils\ArrayDataCollection as ArrayDataCollection;
use AppUtils\ClassHelper as ClassHelper;
use Application\API\BaseMethods\BaseAPIMethod as BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface as APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait as APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface as APIGroupInterface;
use Application\API\Traits\DryRunAPIInterface as DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait as DryRunAPITrait;
use Application\API\Traits\JSONRequestInterface as JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait as JSONRequestTrait;
use Application\API\Traits\JSONResponseTrait as JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface as JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait as JSONResponseWithExampleTrait;
use Application\API\Utilities\KeyDescription as KeyDescription;
use Application\Media\API\MediaAPIGroup as MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface as MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface as MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait as MediaDocumentAPITrait;
use Application\Media\API\MediaDocumentSerializer as MediaDocumentSerializer;
use Application\Media\API\MediaFileUploadAPIInterface as MediaFileUploadAPIInterface;
use Application\Media\API\MediaFileUploadAPITrait as MediaFileUploadAPITrait;
use Application\Media\API\MediaMetadataAPIInterface as MediaMetadataAPIInterface;
use Application\Media\API\MediaMetadataAPITrait as MediaMetadataAPITrait;
use Application\Media\Collection\MediaCollection as MediaCollection;
use Application\Media\Collection\MediaRecord as MediaRecord;
use Application\Media\MediaRightsInterface as MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter as MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent as MediaFileContent;
use Application\Media\Writer\MediaWriterException as MediaWriterException;
use DBHelper as DBHelper;
use Override as Override;

/**
 * API method: Updates the name/description/keywords and/or the file content
 * of an existing media document.
 *
 * Composition (per the plan's Approach §4 table): `JSONRequest`,
 * `JSONResponse`, `JSONResponseWithExample`, `DryRun`, `APIKeyMethod` pieces,
 * plus the `MediaDocument`, `MediaFileUpload` and `MediaMetadata` media
 * pieces.
 *
 * {@see self::VALIDATION_NO_CHANGES} is declared here rather than on a
 * piece, since only this method raises it (sub-range `68122x`).
 *
 * The real branch calls {@see MediaDocumentWriter::updateMetadata()} before
 * {@see MediaDocumentWriter::replaceFile()}, so the file overwrite is always
 * the request's last mutating writer call (audit cycle 3, Minor 2).
 *
 * @package Media
 * @subpackage API
 */
class UpdateMediaDocumentAPI extends BaseAPIMethod implements JSONRequestInterface, JSONResponseWithExampleInterface, DryRunAPIInterface, APIKeyMethodInterface, MediaDocumentAPIInterface, MediaFileUploadAPIInterface, MediaMetadataAPIInterface, MediaAPIResponseInterface
{
	use JSONRequestTrait;
	use JSONResponseTrait;
	use JSONResponseWithExampleTrait;
	use DryRunAPITrait;
	use APIKeyMethodTrait;
	use MediaDocumentAPITrait;
	use MediaFileUploadAPITrait;
	use MediaMetadataAPITrait;

	public const METHOD_NAME = 'UpdateMediaDocument';
	public const VERSION_1 = '1.0';
	public const CURRENT_VERSION = self::VERSION_1;
	public const VERSIONS = [self::VERSION_1];

	/**
	 * Neither a metadata field nor a file replacement was submitted; at
	 * least one change is required. Allocated in the `68122x` sub-range,
	 * since only this method raises it.
	 */
	public const VALIDATION_NO_CHANGES = 681221;

	/**
	 * Disambiguates the identical `KEY_DRY_RUN = 'dryRun'` constant inherited
	 * from both {@see DryRunAPIInterface} and {@see MediaAPIResponseInterface}
	 * (PHP treats two interface constants of the same name as an ambiguous
	 * conflict even when their values match, unless the implementing class
	 * redeclares the constant itself).
	 */
	public const KEY_DRY_RUN = MediaAPIResponseInterface::KEY_DRY_RUN;

	public function getMethodName(): string
	{
		/* ... */
	}


	public function getVersions(): array
	{
		/* ... */
	}


	public function getCurrentVersion(): string
	{
		/* ... */
	}


	public function getGroup(): APIGroupInterface
	{
		/* ... */
	}


	public function getRequiredRight(): ?string
	{
		/* ... */
	}


	public function getDescription(): string
	{
		/* ... */
	}


	public function getRelatedMethodNames(): array
	{
		/* ... */
	}


	public function getChangelog(): array
	{
		/* ... */
	}


	#[Override]
	public function getResponseKeyDescriptions(): array
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Params/MediaIDHandler.php`

```php
namespace Application\Media\API\Params;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Parameters\Handlers\BaseParamHandler as BaseParamHandler;
use Application\Media\Collection\MediaRecord as MediaRecord;

/**
 * Handler for the {@see MediaIDParam} parameter, resolving it to a
 * {@see MediaRecord}.
 *
 * @package Media
 * @subpackage API
 *
 * @method MediaIDParam|NULL getParam()
 * @method MediaIDParam register()
 */
class MediaIDHandler extends BaseParamHandler
{
	public function register(): MediaIDParam
	{
		/* ... */
	}


	public function getParam(): ?MediaIDParam
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Params/MediaIDHandler.php`

```php
namespace Application\Media\API\Params;

use AppUtils\ClassHelper as ClassHelper;
use Application\API\Parameters\Handlers\BaseParamHandler as BaseParamHandler;
use Application\Media\Collection\MediaRecord as MediaRecord;

/**
 * Handler for the {@see MediaIDParam} parameter, resolving it to a
 * {@see MediaRecord}.
 *
 * @package Media
 * @subpackage API
 *
 * @method MediaIDParam|NULL getParam()
 * @method MediaIDParam register()
 */
class MediaIDHandler extends BaseParamHandler
{
	public function register(): MediaIDParam
	{
		/* ... */
	}


	public function getParam(): ?MediaIDParam
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Params/MediaIDParam.php`

```php
namespace Application\Media\API\Params;

use Application\API\Parameters\Type\IntegerParameter as IntegerParameter;
use Application\AppFactory as AppFactory;
use Application\Media\API\MediaDocumentAPIInterface as MediaDocumentAPIInterface;
use Application\Media\Collection\MediaRecord as MediaRecord;

/**
 * The `mediaID` parameter.
 *
 * > NOTE: Unlike {@see \Application\Countries\API\Params\AppCountryIDParam},
 * > this deliberately uses neither `SelectableValueParamTrait` nor
 * > `validateByValueExistsCallback()`. Live installs hold 10,000+ media
 * > documents, which must not be enumerated for a selectable-value list, and
 * > an exists-callback failure would answer an unknown ID with the generic
 * > `183401` before {@see \Application\Media\API\MediaDocumentParamsContainer::requireValue()}
 * > can send the domain 404.
 *
 * @package Media
 * @subpackage API
 */
class MediaIDParam extends IntegerParameter
{
	public function getMediaID(): ?int
	{
		/* ... */
	}


	public function getMediaRecord(): ?MediaRecord
	{
		/* ... */
	}
}


```
###  Path: `/src/classes/Application/Media/API/Params/MediaIDParam.php`

```php
namespace Application\Media\API\Params;

use Application\API\Parameters\Type\IntegerParameter as IntegerParameter;
use Application\AppFactory as AppFactory;
use Application\Media\API\MediaDocumentAPIInterface as MediaDocumentAPIInterface;
use Application\Media\Collection\MediaRecord as MediaRecord;

/**
 * The `mediaID` parameter.
 *
 * > NOTE: Unlike {@see \Application\Countries\API\Params\AppCountryIDParam},
 * > this deliberately uses neither `SelectableValueParamTrait` nor
 * > `validateByValueExistsCallback()`. Live installs hold 10,000+ media
 * > documents, which must not be enumerated for a selectable-value list, and
 * > an exists-callback failure would answer an unknown ID with the generic
 * > `183401` before {@see \Application\Media\API\MediaDocumentParamsContainer::requireValue()}
 * > can send the domain 404.
 *
 * @package Media
 * @subpackage API
 */
class MediaIDParam extends IntegerParameter
{
	public function getMediaID(): ?int
	{
		/* ... */
	}


	public function getMediaRecord(): ?MediaRecord
	{
		/* ... */
	}
}


```