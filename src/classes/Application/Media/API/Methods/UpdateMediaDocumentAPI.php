<?php
/**
 * @package Media
 * @subpackage API
 */

declare(strict_types=1);

namespace Application\Media\API\Methods;

use Application\API\BaseMethods\BaseAPIMethod;
use Application\API\Clients\API\APIKeyMethodInterface;
use Application\API\Clients\API\APIKeyMethodTrait;
use Application\API\Groups\APIGroupInterface;
use Application\API\Traits\DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait;
use Application\API\Traits\JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait;
use Application\API\Utilities\KeyDescription;
use Application\Media\API\MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait;
use Application\Media\API\MediaDocumentSerializer;
use Application\Media\API\MediaFileUploadAPIInterface;
use Application\Media\API\MediaFileUploadAPITrait;
use Application\Media\API\MediaMetadataAPIInterface;
use Application\Media\API\MediaMetadataAPITrait;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application\Media\MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use Application\Media\Writer\MediaWriterException;
use AppUtils\ArrayDataCollection;
use AppUtils\ClassHelper;
use DBHelper;
use Override;

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
class UpdateMediaDocumentAPI
    extends BaseAPIMethod
    implements
    JSONRequestInterface,
    JSONResponseWithExampleInterface,
    DryRunAPIInterface,
    APIKeyMethodInterface,
    MediaDocumentAPIInterface,
    MediaFileUploadAPIInterface,
    MediaMetadataAPIInterface,
    MediaAPIResponseInterface
{
    use JSONRequestTrait;
    use JSONResponseTrait;
    use JSONResponseWithExampleTrait;
    use DryRunAPITrait;
    use APIKeyMethodTrait;
    use MediaDocumentAPITrait;
    use MediaFileUploadAPITrait;
    use MediaMetadataAPITrait;

    public const string METHOD_NAME = 'UpdateMediaDocument';
    public const string VERSION_1 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1;
    public const array VERSIONS = array(self::VERSION_1);

    /**
     * Neither a metadata field nor a file replacement was submitted; at
     * least one change is required. Allocated in the `68122x` sub-range,
     * since only this method raises it.
     */
    public const int VALIDATION_NO_CHANGES = 681221;

    /**
     * Disambiguates the identical `KEY_DRY_RUN = 'dryRun'` constant inherited
     * from both {@see DryRunAPIInterface} and {@see MediaAPIResponseInterface}
     * (PHP treats two interface constants of the same name as an ambiguous
     * conflict even when their values match, unless the implementing class
     * redeclares the constant itself).
     */
    public const string KEY_DRY_RUN = MediaAPIResponseInterface::KEY_DRY_RUN;

    public function getMethodName() : string
    {
        return self::METHOD_NAME;
    }

    public function getVersions() : array
    {
        return self::VERSIONS;
    }

    public function getCurrentVersion() : string
    {
        return self::CURRENT_VERSION;
    }

    public function getGroup() : APIGroupInterface
    {
        return MediaAPIGroup::create();
    }

    public function getRequiredRight() : ?string
    {
        return MediaRightsInterface::RIGHT_EDIT_MEDIA;
    }

    // region: B - Setup

    protected function init() : void
    {
        $this->manageMediaDocumentParams()
            ->manageID()
            ->register()
            ->makeRequired();

        $this->registerMediaMetadataParams(true);
        $this->registerMediaFileParams(false);
        $this->registerDryRunParam();
    }

    protected function collectRequestData(string $version) : void
    {
        // JSON body handling is entirely covered by JSONRequestTrait.
    }

    // endregion

    // region: A - Response Payload

    protected function collectResponseData(ArrayDataCollection $response, string $version) : void
    {
        if($this->isExampleResponse()) {
            $response->setKey(self::KEY_MEDIA_DOCUMENT, MediaDocumentSerializer::buildExample(true));
            $response->setKey(self::KEY_MODIFIED, true);
            $response->setKey(self::KEY_DRY_RUN, false);
            return;
        }

        $transaction = false;
        if(!DBHelper::isTransactionStarted()) {
            DBHelper::startTransaction();
            $transaction = true;
        }

        $this->updateDocument($response);

        if($transaction) {
            if($this->isDryRun()) {
                DBHelper::rollbackTransaction();
            } else {
                DBHelper::commitTransaction();
            }
        }
    }

    private function updateDocument(ArrayDataCollection $response) : void
    {
        DBHelper::requireTransaction('Update media document via API');

        $record = $this->manageMediaDocumentParams()->requireValue();

        $this->validateMediaMetadata();

        $name = $this->getMediaName();
        $description = $this->getMediaDescription();
        $keywords = $this->getMediaKeywords();
        $hasFile = $this->hasMediaFile();

        if($name === null && $description === null && $keywords === null && !$hasFile) {
            $this->errorResponse(self::VALIDATION_NO_CHANGES)
                ->setErrorMessage('At least one of name, description, keywords or a replacement file must be submitted.')
                ->send();
        }

        $content = null;

        if($hasFile) {
            $content = $this->resolveAndCheckFileContent($record);
        }

        if($this->isDryRun()) {
            $response->setKey(
                self::KEY_MEDIA_DOCUMENT,
                $this->buildDryRunPreview($record, $name, $description, $keywords, $content)
            );
            $response->setKey(self::KEY_MODIFIED, $hasFile || $this->isMetadataChanged($record, $name, $description, $keywords));
            $response->setKey(self::KEY_DRY_RUN, true);
            return;
        }

        $writer = new MediaDocumentWriter();

        $metadataChanged = $writer->updateMetadata($record, $name, $description, $keywords);

        if($hasFile) {
            // $content is always set here: it is resolved above whenever
            // $hasFile is true (resolveAndCheckFileContent() never returns
            // null), so no separate null check is needed.
            try {
                $writer->replaceFile($record, $content);
            } catch (MediaWriterException $e) {
                $this->sendWriterValidationError($e);
            }
        }

        $response->setKey(self::KEY_MEDIA_DOCUMENT, MediaDocumentSerializer::serialize($record));
        $response->setKey(self::KEY_MODIFIED, $hasFile || $metadataChanged);
        $response->setKey(self::KEY_DRY_RUN, false);
    }

    /**
     * Validates the submitted replacement file (shared by dry and real
     * runs, design review Decision 6), additionally enforcing that its
     * extension matches the record's current one (681005) — the API-layer
     * counterpart of {@see MediaDocumentWriter::replaceFile()}'s own check,
     * needed here because a dry run never calls the writer.
     *
     * @param MediaRecord $record
     * @return MediaFileContent
     */
    private function resolveAndCheckFileContent(MediaRecord $record) : MediaFileContent
    {
        $requiredTypeID = $record->getRecordStringKey(MediaCollection::COL_TYPE);

        $this->validateMediaFile($requiredTypeID);

        // validateMediaFile() above sends an error response (never returns)
        // when no file content resolves, so this is always non-null here.
        $content = ClassHelper::requireObjectInstanceOf(MediaFileContent::class, $this->getMediaFileContent());

        $currentExtension = $record->getRecordStringKey(MediaCollection::COL_EXTENSION);

        if($content->getExtension() !== $currentExtension) {
            $this->errorResponse(MediaWriterException::VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED)
                ->setErrorMessage(sprintf(
                    'Cannot change the file extension of an existing media document. Current extension: [%s], submitted: [%s].',
                    $currentExtension,
                    $content->getExtension()
                ))
                ->send();
        }

        return $content;
    }

    private function isMetadataChanged(MediaRecord $record, ?string $name, ?string $description, ?string $keywords) : bool
    {
        if($name !== null && $name !== $record->getLabel()) {
            return true;
        }

        if($description !== null && $description !== $record->getDescription()) {
            return true;
        }

        if($keywords !== null && $keywords !== $record->getKeywords()) {
            return true;
        }

        return false;
    }

    /**
     * Builds the dry-run preview shape: the existing document, overridden
     * with the submitted-but-not-yet-persisted fields.
     *
     * @param MediaRecord $record
     * @param string|null $name
     * @param string|null $description
     * @param string|null $keywords
     * @param MediaFileContent|null $content
     * @return array<string, mixed>
     */
    private function buildDryRunPreview(
        MediaRecord $record,
        ?string $name,
        ?string $description,
        ?string $keywords,
        ?MediaFileContent $content
    ) : array
    {
        $data = MediaDocumentSerializer::serialize($record);

        if($name !== null) {
            $data[self::KEY_NAME] = $name;
        }

        if($description !== null) {
            $data[self::KEY_DESCRIPTION] = $description;
        }

        if($keywords !== null) {
            $data[self::KEY_KEYWORDS] = $keywords;
        }

        if($content !== null) {
            $data[self::KEY_FILE_NAME] = $content->getFileName();
            $data[self::KEY_FILE_SIZE] = $content->getSize();
        }

        return $data;
    }

    // endregion

    // region: C - Documentation

    public function getDescription() : string
    {
        return <<<'MARKDOWN'
Updates the name, description and/or keywords of an existing media document,
and optionally replaces its file content (`fileName` + `fileContent`, sent
together or not at all). An empty `description` or `keywords` clears the
field; omitting a parameter leaves it unchanged.

At least one change must be submitted (681221). A replacement file must keep
the document's current extension (681005). The HTML rule, grant check and
unknown-ID handling are identical to the other media methods.

Pass `dryRun=1` to validate the request and preview the result without
persisting anything to the database or disk.
MARKDOWN;
    }

    public function getRelatedMethodNames() : array
    {
        return array(
            GetMediaDocumentAPI::METHOD_NAME,
            GetMediaDocumentsAPI::METHOD_NAME,
            AddMediaDocumentAPI::METHOD_NAME,
            DeleteMediaDocumentAPI::METHOD_NAME,
        );
    }

    public function getChangelog() : array
    {
        return array();
    }

    #[Override]
    public function getResponseKeyDescriptions() : array
    {
        return array(
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT, 'The updated (or, on a dry run, previewed) media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_ID, 'Numeric ID of the media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_NAME, 'Name of the media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_TYPE, 'Media type ID (e.g. Image, PDF).'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_TYPE_LABEL, 'Human-readable label of the media type.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_EXTENSION, 'File extension, lowercase, without the leading dot.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_FILE_NAME, 'Full file name, including extension.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_FILE_SIZE, 'File size in bytes.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_DATE_ADDED, 'ISO 8601 timestamp of when the document was originally added.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_AUTHOR_ID, 'User ID of the document\'s original author.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_DESCRIPTION, 'Free-text description of the document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_KEYWORDS, 'Comma-separated search keywords.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_IMAGE, 'Image-specific details, or null for a non-image document.'),
            KeyDescription::create(self::KEY_MODIFIED, 'Whether the request actually changed anything.'),
            KeyDescription::create(self::KEY_DRY_RUN, 'Reflects the dryRun request parameter. When true, nothing was persisted.'),
        );
    }

    // endregion
}
