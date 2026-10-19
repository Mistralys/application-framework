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
use Application\API\Parameters\Type\StringParameter;
use Application\API\Traits\DryRunAPIInterface;
use Application\API\Traits\DryRunAPITrait;
use Application\API\Traits\JSONRequestInterface;
use Application\API\Traits\JSONRequestTrait;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait;
use Application\API\Utilities\KeyDescription;
use Application\AppFactory;
use Application\Media\API\MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentSerializer;
use Application\Media\API\MediaFileUploadAPIInterface;
use Application\Media\API\MediaFileUploadAPITrait;
use Application\Media\API\MediaMetadataAPIInterface;
use Application\Media\API\MediaMetadataAPITrait;
use Application\Media\MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use Application\Media\Writer\MediaWriterException;
use AppUtils\ArrayDataCollection;
use AppUtils\ClassHelper;
use DBHelper;
use Override;

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
class AddMediaDocumentAPI
    extends BaseAPIMethod
    implements
    JSONRequestInterface,
    JSONResponseWithExampleInterface,
    DryRunAPIInterface,
    APIKeyMethodInterface,
    MediaFileUploadAPIInterface,
    MediaMetadataAPIInterface,
    MediaAPIResponseInterface
{
    use JSONRequestTrait;
    use JSONResponseTrait;
    use JSONResponseWithExampleTrait;
    use DryRunAPITrait;
    use APIKeyMethodTrait;
    use MediaFileUploadAPITrait;
    use MediaMetadataAPITrait;

    public const string METHOD_NAME = 'AddMediaDocument';
    public const string VERSION_1 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1;
    public const array VERSIONS = array(self::VERSION_1);

    public const string PARAM_MEDIA_TYPE = 'mediaType';

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
        return MediaRightsInterface::RIGHT_CREATE_MEDIA;
    }

    // region: B - Setup

    private ?StringParameter $mediaTypeParam = null;

    protected function init() : void
    {
        $this->registerMediaFileParams(true);
        $this->registerMediaMetadataParams(false);

        $this->mediaTypeParam = $this->manageParams()
            ->addParam(self::PARAM_MEDIA_TYPE, 'Media type')
            ->string();

        $this->mediaTypeParam
            ->setDescription(
                'Optional media type constraint (e.g. Image, PDF). When given, the uploaded '.
                'file\'s resolved type must match it exactly, letting consumers fail fast at '.
                'upload rather than at content write.'
            )
            ->validateByEnum(AppFactory::createMedia()->getTypeIDs());

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
            $response->setKey(self::KEY_DRY_RUN, false);
            return;
        }

        $transaction = false;
        if(!DBHelper::isTransactionStarted()) {
            DBHelper::startTransaction();
            $transaction = true;
        }

        $this->addDocument($response);

        if($transaction) {
            if($this->isDryRun()) {
                DBHelper::rollbackTransaction();
            } else {
                DBHelper::commitTransaction();
            }
        }
    }

    private function addDocument(ArrayDataCollection $response) : void
    {
        DBHelper::requireTransaction('Add media document via API');

        $requiredTypeID = $this->mediaTypeParam?->getValue();

        $typeID = $this->validateMediaFile($requiredTypeID);
        $this->validateMediaMetadata();

        // validateMediaFile() above sends an error response (never returns)
        // when no file content resolves, so this is always non-null here.
        $content = ClassHelper::requireObjectInstanceOf(MediaFileContent::class, $this->getMediaFileContent());
        $name = $this->resolveMediaName($content->getBaseName());
        $description = (string)$this->getMediaDescription();
        $keywords = (string)$this->getMediaKeywords();

        if($this->isDryRun()) {
            $response->setKey(
                self::KEY_MEDIA_DOCUMENT,
                MediaDocumentSerializer::serializePreview($content, $typeID, $name, $description, $keywords)
            );
            $response->setKey(self::KEY_DRY_RUN, true);
            return;
        }

        $author = $this->manageParamAPIKey()->requireValue()->getPseudoUser();

        try {
            $record = (new MediaDocumentWriter())->create($content, $name, $author, $description, $keywords);
        } catch (MediaWriterException $e) {
            $this->sendWriterValidationError($e);
        }

        $response->setKey(self::KEY_MEDIA_DOCUMENT, MediaDocumentSerializer::serialize($record));
        $response->setKey(self::KEY_DRY_RUN, false);
    }

    // endregion

    // region: C - Documentation

    public function getDescription() : string
    {
        return <<<'MARKDOWN'
Creates a new media document from a base64-encoded file upload (`fileName` +
`fileContent`). SVG files are accepted, as in the admin UI. Duplicate
uploads are accepted — there is no content-hash deduplication.

When `name` is omitted, it defaults to the uploaded file's base name, cut to
160 characters. `description` and `keywords` must not contain HTML markup
(681211).

Rejections: an unsupported extension (681001), a `mediaType` mismatch
(681002), content that does not match the extension (681003), an empty file
(681004), an invalid file name (681006).

Pass `dryRun=1` to validate the request and preview the result — including
the resolved `name` — without persisting anything to the database or disk.
A dry-run response always has `mediaID: null`.
MARKDOWN;
    }

    public function getRelatedMethodNames() : array
    {
        return array(
            GetMediaDocumentAPI::METHOD_NAME,
            GetMediaDocumentsAPI::METHOD_NAME,
            UpdateMediaDocumentAPI::METHOD_NAME,
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
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT, 'The created (or, on a dry run, previewed) media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_ID, 'Numeric ID of the media document, or null on a dry run.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_NAME, 'Name of the media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_TYPE, 'Media type ID (e.g. Image, PDF).'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_TYPE_LABEL, 'Human-readable label of the media type.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_EXTENSION, 'File extension, lowercase, without the leading dot.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_FILE_NAME, 'Full file name, including extension.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_FILE_SIZE, 'File size in bytes.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_DATE_ADDED, 'ISO 8601 timestamp, or null on a dry run.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_AUTHOR_ID, 'User ID of the document\'s author (the API key\'s pseudo-user), or null on a dry run.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_DESCRIPTION, 'Free-text description of the document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_KEYWORDS, 'Comma-separated search keywords.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_IMAGE, 'Image-specific details, or null for a non-image document or a dry run.'),
            KeyDescription::create(self::KEY_DRY_RUN, 'Reflects the dryRun request parameter. When true, nothing was persisted.'),
        );
    }

    // endregion
}
