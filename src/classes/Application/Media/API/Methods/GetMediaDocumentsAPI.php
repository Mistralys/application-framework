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
use Application\API\Parameters\APIParameterInterface;
use Application\API\Parameters\Type\StringListParameter;
use Application\API\Parameters\Type\StringParameter;
use Application\API\Parameters\Validation\Type\EnumValidation;
use Application\API\Traits\JSONResponseTrait;
use Application\API\Traits\JSONResponseWithExampleInterface;
use Application\API\Traits\JSONResponseWithExampleTrait;
use Application\API\Traits\PaginatedAPIInterface;
use Application\API\Traits\PaginatedAPITrait;
use Application\API\Traits\RequestRequestInterface;
use Application\API\Traits\RequestRequestTrait;
use Application\API\Utilities\KeyDescription;
use Application\AppFactory;
use Application\Interfaces\FilterCriteriaInterface;
use Application\Media\API\MediaAPIGroup;
use Application\Media\API\MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentSerializer;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaFilterCriteria;
use Application\Media\MediaRightsInterface;
use AppUtils\ArrayDataCollection;
use AppUtils\OperationResult;
use Override;

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
class GetMediaDocumentsAPI
    extends BaseAPIMethod
    implements
    RequestRequestInterface,
    JSONResponseWithExampleInterface,
    APIKeyMethodInterface,
    PaginatedAPIInterface,
    MediaAPIResponseInterface
{
    use RequestRequestTrait;
    use JSONResponseTrait;
    use JSONResponseWithExampleTrait;
    use APIKeyMethodTrait;
    use PaginatedAPITrait;

    public const string METHOD_NAME = 'GetMediaDocuments';
    public const string VERSION_1 = '1.0';
    public const string CURRENT_VERSION = self::VERSION_1;
    public const array VERSIONS = array(self::VERSION_1);

    public const string PARAM_SEARCH = 'search';
    public const string PARAM_MEDIA_TYPES = 'mediaTypes';
    public const string PARAM_EXTENSIONS = 'extensions';
    public const string PARAM_ORDER_BY = 'orderBy';
    public const string PARAM_ORDER_DIR = 'orderDir';

    public const string ORDER_BY_DATE_ADDED = 'dateAdded';
    public const string ORDER_BY_NAME = 'name';
    public const string ORDER_BY_FILE_SIZE = 'fileSize';

    public const string ORDER_DIR_ASC = 'asc';
    public const string ORDER_DIR_DESC = 'desc';

    private const int DEFAULT_LIMIT = 50;
    private const int MAX_LIMIT = 200;

    /**
     * Maps the public `orderBy` values to their underlying database column.
     *
     * @var array<string,string>
     */
    private const array ORDER_BY_COLUMNS = array(
        self::ORDER_BY_DATE_ADDED => MediaCollection::COL_DATE_ADDED,
        self::ORDER_BY_NAME => MediaCollection::COL_NAME,
        self::ORDER_BY_FILE_SIZE => MediaCollection::COL_SIZE,
    );

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

    private ?StringParameter $searchParam = null;
    private ?StringListParameter $mediaTypesParam = null;
    private ?StringListParameter $extensionsParam = null;
    private ?StringParameter $orderByParam = null;
    private ?StringParameter $orderDirParam = null;

    protected function init() : void
    {
        $this->registerPaginationParams(self::DEFAULT_LIMIT, self::MAX_LIMIT);

        $this->searchParam = $this->manageParams()
            ->addParam(self::PARAM_SEARCH, 'Search')
            ->string();

        $this->searchParam->setDescription(
            'Free-text search terms, matched against the document name, keywords and description. '.
            'Terms are AND-combined by default; the AND/OR/NOT operators are supported.'
        );

        $this->mediaTypesParam = $this->manageParams()
            ->addParam(self::PARAM_MEDIA_TYPES, 'Media types')
            ->stringList();

        $this->mediaTypesParam
            ->setDescription('Comma-separated list of media type IDs to filter by (e.g. Image, PDF).')
            ->validateByCallback(array($this, 'validateMediaTypes'));

        $this->extensionsParam = $this->manageParams()
            ->addParam(self::PARAM_EXTENSIONS, 'Extensions')
            ->stringList();

        $this->extensionsParam->setDescription('Comma-separated list of file extensions to filter by (e.g. png, pdf).');

        $this->orderByParam = $this->manageParams()
            ->addParam(self::PARAM_ORDER_BY, 'Order by')
            ->string();

        $this->orderByParam
            ->setDescription('Field to sort the results by. Defaults to dateAdded.')
            ->validateByEnum(array_keys(self::ORDER_BY_COLUMNS));

        $this->orderDirParam = $this->manageParams()
            ->addParam(self::PARAM_ORDER_DIR, 'Order direction')
            ->string();

        $this->orderDirParam
            ->setDescription('Sort direction, asc or desc. Defaults to desc.')
            ->validateByEnum(array(self::ORDER_DIR_ASC, self::ORDER_DIR_DESC));
    }

    /**
     * Validates every submitted media type against the known type registry.
     *
     * @param array<int,mixed>|int|float|bool|string $value
     * @param OperationResult $result
     * @param APIParameterInterface $param
     */
    public function validateMediaTypes(array|int|float|bool|string $value, OperationResult $result, APIParameterInterface $param) : void
    {
        if(!is_array($value)) {
            return;
        }

        $allowed = AppFactory::createMedia()->getTypeIDs();

        foreach($value as $type) {
            if(!in_array($type, $allowed, true)) {
                $result->makeError(
                    sprintf(
                        'Invalid media type [%s] for parameter [%s]. Allowed values are: %s.',
                        $type,
                        $param->getName(),
                        implode(', ', $allowed)
                    ),
                    EnumValidation::VALIDATION_INVALID_VALUE
                );
                return;
            }
        }
    }

    protected function collectRequestData(string $version) : void
    {
    }

    // endregion

    // region: A - Response Payload

    protected function collectResponseData(ArrayDataCollection $response, string $version) : void
    {
        if($this->isExampleResponse()) {
            $response->setKey(self::KEY_MEDIA_DOCUMENTS, array(
                MediaDocumentSerializer::buildExample(true),
                MediaDocumentSerializer::buildExample(false),
            ));

            $this->addPaginationResponseKeys($response, 2);
            return;
        }

        $criteria = AppFactory::createMediaCollection()->getFilterCriteria();

        $this->applyFilters($criteria);
        $this->applyOrdering($criteria);

        $totalCount = $criteria->countItems();

        $this->applyPagination($criteria);

        $documents = array();

        foreach($criteria->getItemsObjects() as $record) {
            $documents[] = MediaDocumentSerializer::serialize($record);
        }

        $response->setKey(self::KEY_MEDIA_DOCUMENTS, $documents);

        $this->addPaginationResponseKeys($response, $totalCount);
    }

    private function applyFilters(MediaFilterCriteria $criteria) : void
    {
        $search = $this->searchParam?->getValue();

        if($search !== null && $search !== '') {
            $criteria->setSearch($search);
        }

        $mediaTypes = $this->mediaTypesParam?->getValue();

        if(!empty($mediaTypes)) {
            $criteria->selectMediaTypes($mediaTypes);
        }

        $extensions = $this->extensionsParam?->getValue();

        if(!empty($extensions)) {
            $criteria->selectExtensions($extensions);
        }
    }

    private function applyOrdering(MediaFilterCriteria $criteria) : void
    {
        $orderBy = $this->orderByParam?->getValue() ?? self::ORDER_BY_DATE_ADDED;
        $orderDir = $this->orderDirParam?->getValue() ?? self::ORDER_DIR_DESC;

        $column = self::ORDER_BY_COLUMNS[$orderBy] ?? MediaCollection::COL_DATE_ADDED;

        $direction = $orderDir === self::ORDER_DIR_ASC
            ? FilterCriteriaInterface::ORDER_DIR_ASCENDING
            : FilterCriteriaInterface::ORDER_DIR_DESCENDING;

        $criteria->setOrderBy($column, $direction);
    }

    // endregion

    // region: C - Documentation

    public function getDescription() : string
    {
        return <<<'MARKDOWN'
Retrieves a paginated list of media documents, newest first by default.
Without parameters, returns at most 50 documents — never the whole table.
Supports free-text search (name, keywords, description), filtering by
media type and extension, and sorting. Content is never included in the
list; use `GetMediaDocument` with `includeContent=true` for a single
document's content.
MARKDOWN;
    }

    public function getRelatedMethodNames() : array
    {
        return array(
            GetMediaDocumentAPI::METHOD_NAME,
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
        $descriptions = array(
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS, 'List of media documents matching the given filters.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_MEDIA_ID, 'Numeric ID of the media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_NAME, 'Name of the media document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_MEDIA_TYPE, 'Media type ID (e.g. Image, PDF).'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_MEDIA_TYPE_LABEL, 'Human-readable label of the media type.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_EXTENSION, 'File extension, lowercase, without the leading dot.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_FILE_NAME, 'Full file name, including extension.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_FILE_SIZE, 'File size in bytes.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_DATE_ADDED, 'ISO 8601 timestamp of when the document was added.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_AUTHOR_ID, 'User ID of the document\'s author.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_DESCRIPTION, 'Free-text description of the document.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_KEYWORDS, 'Comma-separated search keywords.'),
            KeyDescription::create(self::KEY_MEDIA_DOCUMENTS.'.'.self::KEY_IMAGE, 'Image-specific details, or null for a non-image document.'),
        );

        return array_merge($descriptions, $this->getPaginationKeyDescriptions());
    }

    // endregion
}
