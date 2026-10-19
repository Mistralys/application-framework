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
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\API\Utilities\KeyDescription;
use Application\Media\API\MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface;
use Application\Media\API\MediaDocumentAPITrait;
use Application\Media\API\MediaDocumentSerializer;
use Application\Media\MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter;
use AppUtils\ArrayDataCollection;
use DBHelper;
use Override;

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
class DeleteMediaDocumentAPI
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseWithExampleInterface,
    DryRunAPIInterface,
    APIKeyMethodInterface,
    MediaDocumentAPIInterface,
    MediaAPIResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use JSONResponseWithExampleTrait;
    use DryRunAPITrait;
    use APIKeyMethodTrait;
    use MediaDocumentAPITrait;

    public const string METHOD_NAME = 'DeleteMediaDocument';
    public const string VERSION_1 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1;
    public const array VERSIONS = array(self::VERSION_1);

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
        return MediaRightsInterface::RIGHT_DELETE_MEDIA;
    }

    // region: B - Setup

    protected function init() : void
    {
        $this->manageMediaDocumentParams()
            ->manageID()
            ->register()
            ->makeRequired();

        $this->registerDryRunParam();
    }

    protected function collectRequestData(string $version) : void
    {
    }

    // endregion

    // region: A - Response Payload

    protected function collectResponseData(ArrayDataCollection $response, string $version) : void
    {
        if($this->isExampleResponse()) {
            $response->setKey(self::KEY_MEDIA_DOCUMENT, MediaDocumentSerializer::buildExample(false));
            $response->setKey(self::KEY_DELETED, true);
            $response->setKey(self::KEY_DRY_RUN, false);
            return;
        }

        $transaction = false;
        if(!DBHelper::isTransactionStarted()) {
            DBHelper::startTransaction();
            $transaction = true;
        }

        $this->deleteDocument($response);

        if($transaction) {
            if($this->isDryRun()) {
                DBHelper::rollbackTransaction();
            } else {
                DBHelper::commitTransaction();
            }
        }
    }

    private function deleteDocument(ArrayDataCollection $response) : void
    {
        DBHelper::requireTransaction('Delete media document via API');

        $record = $this->manageMediaDocumentParams()->requireValue();

        // Captured before deletion: the record (and its file) are gone
        // immediately after a real delete, so the pre-deletion shape is the
        // only one that can ever be returned.
        $data = MediaDocumentSerializer::serialize($record);

        if($this->isDryRun()) {
            $response->setKey(self::KEY_MEDIA_DOCUMENT, $data);
            $response->setKey(self::KEY_DELETED, false);
            $response->setKey(self::KEY_DRY_RUN, true);
            return;
        }

        (new MediaDocumentWriter())->delete($record);

        $response->setKey(self::KEY_MEDIA_DOCUMENT, $data);
        $response->setKey(self::KEY_DELETED, true);
        $response->setKey(self::KEY_DRY_RUN, false);
    }

    // endregion

    // region: C - Documentation

    public function getDescription() : string
    {
        return <<<'MARKDOWN'
Deletes a media document's database record and its underlying file.
Returns the document's pre-deletion shape with `deleted: true`. Rejects an
unknown `mediaID` with HTTP 404 (681201).

There is no in-use guard: the framework has no concept of which other
records reference a media document, so deleting one still referenced
elsewhere is the caller's responsibility (see the Media API README).

Pass `dryRun=1` to preview the document that would be deleted without
actually deleting it. A dry run never calls the delete operation, since
deletion unlinks the file immediately and cannot be safely undone within a
preview.
MARKDOWN;
    }

    public function getRelatedMethodNames() : array
    {
        return array(
            GetMediaDocumentAPI::METHOD_NAME,
            GetMediaDocumentsAPI::METHOD_NAME,
            AddMediaDocumentAPI::METHOD_NAME,
            UpdateMediaDocumentAPI::METHOD_NAME,
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
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT, 'The document\'s shape as it was immediately before deletion.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_ID, 'Numeric ID of the deleted media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_NAME, 'Name of the media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_TYPE, 'Media type ID (e.g. Image, PDF).'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_TYPE_LABEL, 'Human-readable label of the media type.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_EXTENSION, 'File extension, lowercase, without the leading dot.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_FILE_NAME, 'Full file name, including extension.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_FILE_SIZE, 'File size in bytes.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_DATE_ADDED, 'ISO 8601 timestamp of when the document was added.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_AUTHOR_ID, 'User ID of the document\'s author.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_DESCRIPTION, 'Free-text description of the document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_KEYWORDS, 'Comma-separated search keywords.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_IMAGE, 'Image-specific details, or null for a non-image document.'),
            KeyDescription::create(self::KEY_DELETED, 'Whether the document was actually deleted. Always false on a dry run.'),
            KeyDescription::create(self::KEY_DRY_RUN, 'Reflects the dryRun request parameter. When true, nothing was deleted.'),
        );
    }

    // endregion
}
