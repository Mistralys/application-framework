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
use Application\API\Parameters\Type\BooleanParameter;
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
use AppUtils\ArrayDataCollection;
use Override;

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
class GetMediaDocumentAPI
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseWithExampleInterface,
    APIKeyMethodInterface,
    MediaDocumentAPIInterface,
    MediaAPIResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use JSONResponseWithExampleTrait;
    use APIKeyMethodTrait;
    use MediaDocumentAPITrait;

    public const string METHOD_NAME = 'GetMediaDocument';
    public const string VERSION_1 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1;
    public const array VERSIONS = array(self::VERSION_1);

    public const string PARAM_INCLUDE_CONTENT = 'includeContent';

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
        return MediaRightsInterface::RIGHT_VIEW_MEDIA;
    }

    // region: B - Setup

    private ?BooleanParameter $includeContentParam = null;

    protected function init() : void
    {
        $this->manageMediaDocumentParams()
            ->manageID()
            ->register()
            ->makeRequired();

        $this->includeContentParam = $this->manageParams()
            ->addParam(self::PARAM_INCLUDE_CONTENT, 'Include content')
            ->boolean();

        $this->includeContentParam->setDescription(
            'Whether to include the base64-encoded file content in the response. Defaults to false.'
        );
    }

    protected function collectRequestData(string $version) : void
    {
    }

    // endregion

    // region: A - Response Payload

    protected function collectResponseData(ArrayDataCollection $response, string $version) : void
    {
        if($this->isExampleResponse()) {
            $response->setKey(self::KEY_MEDIA_DOCUMENT, MediaDocumentSerializer::buildExample(true));
            return;
        }

        $record = $this->manageMediaDocumentParams()->requireValue();
        $includeContent = $this->includeContentParam?->getValue() === true;

        $response->setKey(self::KEY_MEDIA_DOCUMENT, MediaDocumentSerializer::serialize($record, $includeContent));
    }

    // endregion

    // region: C - Documentation

    public function getDescription() : string
    {
        return <<<'MARKDOWN'
Retrieves a single media document by its `mediaID`. Returns an HTTP 404
with error code 681201 when the ID does not resolve to an existing
document. The base64-encoded file content is included only when
`includeContent=true` is submitted.
MARKDOWN;
    }

    public function getRelatedMethodNames() : array
    {
        return array(
            GetMediaDocumentsAPI::METHOD_NAME,
            AddMediaDocumentAPI::METHOD_NAME,
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
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT, 'The media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_MEDIA_ID, 'Numeric ID of the media document.'),
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
            KeyDescription::create(self::KEY_MEDIA_DOCUMENT.'.'.self::KEY_CONTENT, 'Base64-encoded file content, present only when includeContent=true.'),
        );
    }

    // endregion
}
