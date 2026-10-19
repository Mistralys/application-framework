# Module: Media

## Purpose

Media document management for the Application Framework: storing, serving and
validating uploaded files (images, PDFs and other registered document
types), with database metadata (name, type, extension, size, description,
keywords) and physical file storage kept in sync. Covers both the original
legacy domain classes and the newer namespaced collection/record/writer
layers that were added on top of them.

---

## Key Concepts

- **Legacy domain classes** — `Application_Media` (`Media.php`) is the
  singleton entry point: creating documents from a local file or an upload
  (`createFromFile()`, `createImageFromFile()`, `createFromUpload()`),
  looking them up by ID, and resolving the type registry
  (`getTypeByExtension()`, `getTypeIDs()`, `getTypeLabel()`).
  `Application_Media_Document` (`Document.php`) is the abstract per-document
  class handling file paths, `setSourceFile()`/`getPath()`/`getExtension()`,
  and the legacy `createNew()`/`createNewFromFile()`/`createNewFromUpload()`
  creation paths. These stay in place for their existing UI and upload
  callers.
- **Namespaced Collection/Record** — `Application\Media\Collection\MediaCollection`
  (`DBHelper_BaseCollection`) and `MediaRecord` (`DBHelper_BaseRecord`)
  provide the DBHelper-pattern view of the same `media` table, including the
  `file_size`/`keywords`/`description` metadata columns (see Requirements
  below) and tag support (`TagCollectionTrait`/`TaggableTrait`).
  `MediaFilterCriteria` adds `selectExtension(s)()` and `selectMediaType(s)()`
  filters, and overrides `buildOrderby()` to append the primary key as a
  tie-breaker in the order direction whenever the chosen order field is not
  already the primary key — so paging stays stable, duplicate-free and
  gap-free when several records share the same value for that field (e.g.
  several documents added within the same second).
- **Document Types** — Each concrete type under `Document/` (`Image.php`,
  `PDF.php`) declares its extensions (`getExtensions()`), label/icon, and a
  pure, in-memory `isContentValid(string $content, string $extension): bool`
  check: for `Image`, `getimagesizefromstring()` for raster formats
  (jpg/jpeg/png/gif, matched against the extension's family) or an `XMLReader`
  check for the `svg` root element; for `PDF`, a `%PDF-` signature check.
  These checks do no filesystem access, so they can run during a dry run and
  on content that has not been (and may never be) written to disk. The type
  registry (`Application_Media::loadTypes()`) discovers type classes by
  scanning `Document/` and resolves a type ID (e.g. `Image`, `PDF`) to its
  class via `ClassHelper::requireResolvedClass(Application_Media_Document::class.'_'.$typeID)`.
- **`Writer/` services** — The single namespaced write path, built on the
  `MediaCollection` record path (not the legacy `createNew()`), because it
  is the only one that records size, description, keywords and an explicit
  author:
  - `MediaFileContent` — immutable value object wrapping a file name and its
    binary content (`fromBinary()`/`fromFile()`), exposing
    `getFileName()`/`getBaseName()`/`getExtension()`/`getContent()`/`getSize()`.
    The constructor rejects an empty name, a name containing a path
    separator, or a name with no extension
    (`MediaWriterException::VALIDATION_INVALID_FILE_NAME`). `writeTempFile()`
    is used only by `MediaDocumentWriter`, for the `setSourceFile()` copy in
    a real write — validation never calls it.
  - `MediaFileValidator` — stateless, in-memory, the single validation
    channel: `validate()` checks, in order, that the extension resolves to a
    known type, that the type matches an optional required type, that the
    content is non-empty, and that the content is valid per the type's
    `isContentValid()`. It returns the resolved type ID or throws
    `MediaWriterException` with a client-safe `6810xx` code. Because the
    dry-run branches of the media API's Add/Update methods call this same
    method directly, a dry run cannot drift from what a real write would
    accept.
  - `MediaDocumentWriter` — `create()`, `replaceFile()`, `updateMetadata()`
    and `delete()`. File writes and record deletion are not transactional,
    so the writer provides its own failure cleanup: `create()` deletes the
    target file if anything fails after it was written (the temp file used
    to stage the content is always removed, success or failure); `replaceFile()`
    stores the new size on the record *before* touching the file (so a DB
    failure there never touches disk), backs up the current file, overwrites
    it last, and restores the backup if the overwrite or its verification
    fails. The protected `verifyWrittenFile()` step checks that the stored
    file exists and matches the expected size — a real safeguard against a
    silently truncated copy (e.g. a full disk), not just a test seam (though
    `MediaDocumentWriterTest` does subclass it to force a post-write
    failure and drive the cleanup/restore branches). A known residual
    window: a failure *after* `replaceFile()` returns (e.g. a failing
    transaction commit) can leave the new bytes on disk with the old size
    still in the DB, since file operations are outside the writer's
    transactional reach — see `API/README.md`.
  - `MediaWriterException` — the single source of truth for the writer's
    client-facing `6810xx` validation codes (`isValidationError()`
    distinguishes them from the internal `6811xx` codes).
- **`API/` pieces** — The Media API methods are composed from self-contained
  interface+trait "pieces" (the API Lego Rule's framework reference; see
  `src/classes/Application/API/README.md`'s "Composing methods from pieces"
  entry and `docs/agents/project-manifest/constraints.md`), rather than one
  module-wide trait:
  - `MediaAPIGroup` — the `media`/"Media" method group singleton
    (`GenericAPIGroup`).
  - `MediaAPIResponseInterface` — constants-only interface declaring the
    serialized document response shape.
  - `MediaDocumentSerializer` — stateless: `serialize()`/`serializePreview()`
    turn a `MediaRecord` (or, for a dry run, a not-yet-written
    `MediaFileContent`) into that shape; `buildExample()` provides the
    OpenAPI example payload.
  - `MediaDocumentAPIInterface`/`MediaDocumentAPITrait` +
    `MediaDocumentParamsContainer` + `Params/MediaIDHandler`/`MediaIDParam` —
    the record-resolution piece: resolves a `MediaRecord` from a `mediaID`
    parameter, or accepts one pre-selected via `selectMediaDocument()`.
    `requireValue()` answers an unknown or missing ID with the domain code
    `681201` via `ErrorResponse::makeNotFound()` (HTTP 404), not the generic
    `183004`/`183401`.
  - `MediaMetadataAPIInterface`/`MediaMetadataAPITrait` — the metadata piece:
    `registerMediaMetadataParams(bool $clearable)` registers name/description/
    keywords parameters, `resolveMediaName()` resolves the effective name, and
    `validateMediaMetadata()` rejects HTML in description or keywords with
    `681211` (the HTML rule), identically for dry and real runs.
  - `MediaFileUploadAPIInterface`/`MediaFileUploadAPITrait` — the file-upload
    piece: `registerMediaFileParams(bool $required)` registers the base64
    file parameter, `getMediaFileContent()` builds a `MediaFileContent`,
    `validateMediaFile()` is the one validation call used by both dry and
    real branches, and the protected `sendWriterValidationError()` converts a
    `MediaWriterException` into a client error when `isValidationError()` is
    true, or rethrows it otherwise.
  - Each piece interface extends `APIMethodInterface` directly (no
    inheritance between pieces) and each trait declares no traits of its own;
    each piece's validation codes stay inside its own `6812xx` sub-range.

---

## Folder Structure

| Directory | Contents |
|---|---|
| `./` | Legacy domain classes (`Media.php`, `Document.php`, `MediaTagConnector`, `Processor`, `Delivery`, `Configuration`, `ThumbnailRenderer`), interfaces/traits (`DocumentTrait`, `ImageDocumentTrait`, `MediaRightsInterface`/`Trait`), and the module exception. |
| `API/` | The Media API method pieces (document lookup, metadata, file upload), the method group, the response interface and the serializer. See "`API/` pieces" above. |
| `Admin/` | Admin UI layer: screens, admin URL builders, mode/view traits. |
| `Collection/` | Namespaced DBHelper collection/record/filter criteria (`MediaCollection`, `MediaRecord`, `MediaFilterCriteria`, `MediaFilterSettings`, `MediaSettingsManager`). |
| `Configuration/` | Per-type processing configuration (e.g. `Image.php` for resize presets). |
| `Document/` | Concrete document type classes (`Image.php`, `PDF.php`), each declaring extensions, label/icon and `isContentValid()`. |
| `Events/` | Domain event listeners (tag registration). |
| `Writer/` | The namespaced write path: `MediaFileContent`, `MediaFileValidator`, `MediaDocumentWriter`, `MediaWriterException`. |

---

## Rights

Declared on `MediaRightsInterface`: `CreateMedia`, `EditMedia`, `DeleteMedia`,
`ViewMedia`, `AdminMedia`. The write path itself does not check rights — the
UI screens and the Media API methods are responsible for requiring the
appropriate right before calling into `MediaDocumentWriter`.

---

## Configuration

- `BaseConfigRegistry::API_MAX_FILE_CONTENT_SIZE` (`APP_API_MAX_FILE_CONTENT_SIZE`,
  default 10 MiB) bounds the size of base64 file content accepted by the
  media API's upload parameter (`Application\API\Parameters\Type\Base64FileParameter`);
  see `src/classes/Application/API/Parameters/README.md`.

### Requirements

The `media` table must have the `file_size`, `keywords` and `description`
columns (added by `docs/sql/2023-10-16-news.sql`). `MediaCollection::hasSizeColumn()`
remains for its existing UI/search callers, but the writer always sets these
columns — there is no columns-absent mode in `MediaDocumentWriter`.

---

## Integration Points

- **DBHelper** — `MediaCollection`/`MediaRecord` extend `DBHelper_BaseCollection`/`DBHelper_BaseRecord`;
  all namespaced persistence flows through the DBHelper infrastructure.
  `MediaDocumentWriter`'s mutators require an active DB transaction.
- **Tagging** — `MediaCollection`/`MediaRecord` implement `TagCollectionInterface`/`TaggableInterface`
  via `TagCollectionTrait`/`TaggableTrait`.
- **Media API** — `src/classes/Application/Media/API/` (its pieces are
  described under "`API/` pieces" above; see
  `src/classes/Application/API/README.md` for the framework-wide API
  subsystem, including the Lego Rule these pieces follow) is the primary
  consumer of `Writer/`: its Add/Update methods drive `MediaDocumentWriter`,
  and their dry-run branches call `MediaFileValidator::validate()` directly.
- **Application Framework config** — `API_MAX_FILE_CONTENT_SIZE` is
  registered via `Bootstrap::registerOptionalSetting()` and read through
  `AppConfig::getAPIMaxFileContentSize()`.
