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

All client-facing codes a caller of the media API may see, from every piece
and the shared writer:

| Code | Constant | Source |
|---|---|---|
| `681001` | `VALIDATION_UNSUPPORTED_EXTENSION` | `MediaWriterException` |
| `681002` | `VALIDATION_TYPE_MISMATCH` | `MediaWriterException` |
| `681003` | `VALIDATION_CONTENT_MISMATCH` | `MediaWriterException` |
| `681004` | `VALIDATION_EMPTY_FILE` | `MediaWriterException` |
| `681005` | `VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED` | `MediaWriterException` |
| `681006` | `VALIDATION_INVALID_FILE_NAME` | `MediaWriterException` |
| `681201` | `VALIDATION_UNKNOWN_MEDIA_ID` | `MediaDocumentAPIInterface` |
| `681211` | `VALIDATION_HTML_NOT_ALLOWED` | `MediaMetadataAPIInterface` |
| `681221` | `VALIDATION_NO_CHANGES` | `UpdateMediaDocumentAPI` (the only method that raises it) |
| `183508` | `VALIDATION_INVALID_BASE64` | `ParamValidationInterface` (`Base64FileParameter`) |
| `183509` | `VALIDATION_FILE_SIZE_EXCEEDED` | `ParamValidationInterface` (`Base64FileParameter`) |
| `183701` | `VALIDATION_INVALID_LIMIT` | `PaginatedAPIInterface` |
| `183702` | `VALIDATION_INVALID_OFFSET` | `PaginatedAPIInterface` |
| `182801` | `ERROR_FAILED_TO_READ_INPUT` | `JSONRequestInterface` (an unparseable or non-object JSON body) |

`681001`–`681006` are reported only as `MediaWriterException`, the module's
one validation failure channel (`isValidationError()` distinguishes them
from the writer's internal `6811xx` codes, which are never surfaced to a
client and instead reach the shared execution boundary as unexpected
throwables).

---

## Size limits

The base64 `fileContent` parameter enforces a configurable decoded-size
limit, `AppConfig::getAPIMaxFileContentSize()` (`APP_API_MAX_FILE_CONTENT_SIZE`,
default 10 MiB), checked from the *encoded* length before any decoding
occurs. Oversized input is rejected with `183509` without ever being
decoded.

**`post_max_size` guidance.** Base64 adds roughly 33% transfer overhead, so
a 10 MiB decoded limit needs a raw JSON body of around 13–14 MiB. Configure
PHP's `post_max_size` on target installs to at least **about 1.4× the
configured `APP_API_MAX_FILE_CONTENT_SIZE`** value, to leave headroom for
the surrounding JSON structure and other fields.

---

## SVG and duplicate uploads

- **SVG** is accepted, exactly as in the admin UI. The content check for SVG
  only confirms the uploaded bytes really are a well-formed SVG document
  (its root element is `svg`) — this is a file-type check, not a content or
  security policy. SVGs served from the application origin can carry
  scripts; access to the media API requires an explicitly granted key, and
  any further restriction (e.g. serving SVGs with a non-rendering
  `Content-Type` or `Content-Disposition: attachment`) is the responsibility
  of the delivery path, not this API.
- **Duplicate uploads are accepted.** There is no content-hash
  deduplication — uploading the same bytes twice creates two independent
  media documents.
