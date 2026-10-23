<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\Traits\PaginatedAPIInterface;
use Application\Application;
use Application\Media\API\MediaAPIResponseInterface;
use Application\Media\API\Methods\GetMediaDocumentsAPI;
use Application\Media\Collection\MediaCollection;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use AppFrameworkTestClasses\API\APIClientTestTrait;
use AppFrameworkTestClasses\API\APIMethodTestTrait;
use AppFrameworkTestClasses\Traits\OperationResultTestTrait;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

/**
 * Covers {@see GetMediaDocumentsAPI}: list defaults, search, type/extension
 * filters, ordering, pagination (including WP-004's tie-breaker for stable
 * paging), and the API key grant check.
 */
final class GetMediaDocumentsAPITest extends MediaTestCase
{
    use APIClientTestTrait;
    use APIMethodTestTrait;
    use OperationResultTestTrait;

    private static string $pngBytes;
    private static string $jpgBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $folder = __DIR__.'/../../../files/Media';
        self::$pngBytes = (string)file_get_contents($folder.'/example-image.png');
        self::$jpgBytes = (string)file_get_contents($folder.'/example-image.jpg');
    }

    protected function setUp() : void
    {
        parent::setUp();

        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    private function createMethod(APIKeyRecord $key) : GetMediaDocumentsAPI
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = GetMediaDocumentsAPI::METHOD_NAME;

        $method = new GetMediaDocumentsAPI(APIManager::getInstance());
        $method->manageParamAPIKey()->selectKey($key);

        return $method;
    }

    private function createKey() : APIKeyRecord
    {
        return $this->createTestAPIKeyForMethod(GetMediaDocumentsAPI::METHOD_NAME);
    }

    private function createPNGRecord(string $name, string $description='', string $keywords='') : void
    {
        $writer = new MediaDocumentWriter();

        $record = $writer->create(
            MediaFileContent::fromBinary($name.'.png', self::$pngBytes),
            $name,
            Application::getUser(),
            $description,
            $keywords
        );

        $this->testMedia[] = $record->getMediaDocument();
    }

    private function createJPGRecord(string $name) : void
    {
        $writer = new MediaDocumentWriter();

        $record = $writer->create(
            MediaFileContent::fromBinary($name.'.jpg', self::$jpgBytes),
            $name,
            Application::getUser()
        );

        $this->testMedia[] = $record->getMediaDocument();
    }

    /**
     * Minimal synthetic content satisfying {@see \Application_Media_Document_PDF::isContentValid()}
     * (a `%PDF-` signature), used only to give the `mediaTypes` filter a
     * non-Image type to exclude — no real PDF structure is needed.
     */
    private function createPDFRecord(string $name) : void
    {
        $writer = new MediaDocumentWriter();

        $record = $writer->create(
            MediaFileContent::fromBinary($name.'.pdf', '%PDF-1.4 synthetic test content'),
            $name,
            Application::getUser()
        );

        $this->testMedia[] = $record->getMediaDocument();
    }

    // region: Defaults and shape

    public function test_parameterlessCallReturnsAtMost50NewestFirst() : void
    {
        $this->createPNGRecord('Doc A');
        $this->createPNGRecord('Doc B');

        $key = $this->createKey();
        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();

        $this->assertLessThanOrEqual(50, count($data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS]));
        $this->assertSame(50, $data[PaginatedAPIInterface::KEY_LIMIT]);
        $this->assertSame(0, $data[PaginatedAPIInterface::KEY_OFFSET]);
        $this->assertGreaterThanOrEqual(2, $data[PaginatedAPIInterface::KEY_TOTAL_COUNT]);

        $firstDoc = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS][0];
        $this->assertSame('Doc B', $firstDoc[MediaAPIResponseInterface::KEY_NAME]);
        $this->assertArrayNotHasKey(MediaAPIResponseInterface::KEY_CONTENT, $firstDoc);
    }

    // endregion

    // region: Example response

    public function test_exampleResponseUsesDefaultLimit() : void
    {
        $method = $this->createMethod($this->createKey());
        $data = $method->getExampleJSONResponse();

        $this->assertSame(50, $data[PaginatedAPIInterface::KEY_LIMIT]);
        $this->assertSame(0, $data[PaginatedAPIInterface::KEY_OFFSET]);
        $this->assertLessThanOrEqual(
            $data[PaginatedAPIInterface::KEY_TOTAL_COUNT],
            count($data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS])
        );
    }

    // endregion

    // region: Search

    public function test_searchMatchesName() : void
    {
        $this->createPNGRecord('Unique-Needle-Name-Alpha');
        $this->createPNGRecord('Something-Else');

        $_REQUEST[GetMediaDocumentsAPI::PARAM_SEARCH] = 'Unique-Needle-Name-Alpha';

        $data = $this->assertSuccessfulResponse($this->createMethod($this->createKey()))->getData();
        $docs = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS];

        $this->assertCount(1, $docs);
        $this->assertSame('Unique-Needle-Name-Alpha', $docs[0][MediaAPIResponseInterface::KEY_NAME]);
    }

    public function test_searchMatchesKeywordsOnly() : void
    {
        $this->createPNGRecord('Plain Name One', '', 'uniquekeywordxyz');
        $this->createPNGRecord('Plain Name Two');

        $_REQUEST[GetMediaDocumentsAPI::PARAM_SEARCH] = 'uniquekeywordxyz';

        $data = $this->assertSuccessfulResponse($this->createMethod($this->createKey()))->getData();
        $docs = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS];

        $this->assertCount(1, $docs);
        $this->assertSame('Plain Name One', $docs[0][MediaAPIResponseInterface::KEY_NAME]);
    }

    public function test_searchMatchesDescriptionOnly() : void
    {
        $this->createPNGRecord('Desc Name One', 'uniquedescriptionxyz');
        $this->createPNGRecord('Desc Name Two');

        $_REQUEST[GetMediaDocumentsAPI::PARAM_SEARCH] = 'uniquedescriptionxyz';

        $data = $this->assertSuccessfulResponse($this->createMethod($this->createKey()))->getData();
        $docs = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS];

        $this->assertCount(1, $docs);
        $this->assertSame('Desc Name One', $docs[0][MediaAPIResponseInterface::KEY_NAME]);
    }

    // endregion

    // region: Type and extension filters

    public function test_mediaTypesFilter() : void
    {
        $this->createPNGRecord('Filter-Type-Image');
        $this->createPDFRecord('Filter-Type-PDF');

        $_REQUEST[GetMediaDocumentsAPI::PARAM_SEARCH] = 'Filter-Type';
        $_REQUEST[GetMediaDocumentsAPI::PARAM_MEDIA_TYPES] = 'Image';

        $data = $this->assertSuccessfulResponse($this->createMethod($this->createKey()))->getData();
        $docs = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS];

        $this->assertCount(1, $docs);
        $this->assertSame('Image', $docs[0][MediaAPIResponseInterface::KEY_MEDIA_TYPE]);
    }

    public function test_extensionsFilter() : void
    {
        $this->createPNGRecord('Filter-Ext-PNG');
        $this->createJPGRecord('Filter-Ext-JPG');

        $_REQUEST[GetMediaDocumentsAPI::PARAM_SEARCH] = 'Filter-Ext';
        $_REQUEST[GetMediaDocumentsAPI::PARAM_EXTENSIONS] = 'png';

        $data = $this->assertSuccessfulResponse($this->createMethod($this->createKey()))->getData();
        $docs = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS];

        $this->assertCount(1, $docs);
        $this->assertSame('png', $docs[0][MediaAPIResponseInterface::KEY_EXTENSION]);
    }

    public function test_invalidMediaTypeIsRejected() : void
    {
        $_REQUEST[GetMediaDocumentsAPI::PARAM_MEDIA_TYPES] = 'NotARealType';

        $response = $this->assertErrorResponse($this->createMethod($this->createKey()));

        $this->assertSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $response->getErrorCode());
    }

    // endregion

    // region: Ordering

    public function test_orderByNameAscending() : void
    {
        $this->createPNGRecord('Order-Zebra');
        $this->createPNGRecord('Order-Alpha');

        $_REQUEST[GetMediaDocumentsAPI::PARAM_SEARCH] = 'Order-';
        $_REQUEST[GetMediaDocumentsAPI::PARAM_ORDER_BY] = GetMediaDocumentsAPI::ORDER_BY_NAME;
        $_REQUEST[GetMediaDocumentsAPI::PARAM_ORDER_DIR] = GetMediaDocumentsAPI::ORDER_DIR_ASC;

        $data = $this->assertSuccessfulResponse($this->createMethod($this->createKey()))->getData();
        $docs = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS];

        $this->assertSame('Order-Alpha', $docs[0][MediaAPIResponseInterface::KEY_NAME]);
        $this->assertSame('Order-Zebra', $docs[1][MediaAPIResponseInterface::KEY_NAME]);
    }

    // endregion

    // region: Pagination validation

    public function test_limitZeroIsRejected() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_LIMIT] = '0';

        $response = $this->assertErrorResponse($this->createMethod($this->createKey()));

        $this->assertSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $response->getErrorCode());
    }

    public function test_limitAbove200IsRejected() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_LIMIT] = '201';

        $response = $this->assertErrorResponse($this->createMethod($this->createKey()));

        $this->assertSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $response->getErrorCode());
    }

    public function test_negativeOffsetIsRejected() : void
    {
        $_REQUEST[PaginatedAPIInterface::PARAM_OFFSET] = '-1';

        $response = $this->assertErrorResponse($this->createMethod($this->createKey()));

        $this->assertSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $response->getErrorCode());
    }

    // endregion

    // region: Stable paging (WP-004's buildOrderby() tie-breaker)

    /**
     * @return int[] The created record IDs, in creation order.
     */
    private function createRecordsWithIdenticalDateAdded(int $count) : array
    {
        $writer = new MediaDocumentWriter();
        $ids = array();

        for($i = 0; $i < $count; $i++) {
            $record = $writer->create(
                MediaFileContent::fromBinary('tie-api-'.$i.'.png', self::$pngBytes),
                'Tie-API-Doc-'.$i,
                Application::getUser()
            );

            $this->testMedia[] = $record->getMediaDocument();
            $ids[] = $record->getID();
        }

        DBHelper::update(
            "UPDATE `".MediaCollection::TABLE_NAME."` ".
            "SET `".MediaCollection::COL_DATE_ADDED."`=:date ".
            "WHERE `".MediaCollection::PRIMARY_NAME."` IN (".implode(',', $ids).")",
            array(':date' => '2024-01-01 00:00:00')
        );

        return $ids;
    }

    public function test_pagingIsStableAcrossIdenticalDateAdded() : void
    {
        $ids = $this->createRecordsWithIdenticalDateAdded(5);

        $key = $this->createKey();
        $collected = array();

        for($offset = 0; $offset < 5; $offset++) {
            $_REQUEST = array();
            $_REQUEST[GetMediaDocumentsAPI::PARAM_SEARCH] = 'Tie-API-Doc-';
            $_REQUEST[PaginatedAPIInterface::PARAM_LIMIT] = '1';
            $_REQUEST[PaginatedAPIInterface::PARAM_OFFSET] = (string)$offset;
            $_REQUEST[GetMediaDocumentsAPI::PARAM_ORDER_BY] = GetMediaDocumentsAPI::ORDER_BY_DATE_ADDED;
            $_REQUEST[GetMediaDocumentsAPI::PARAM_ORDER_DIR] = GetMediaDocumentsAPI::ORDER_DIR_ASC;

            $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
            $docs = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENTS];

            $this->assertCount(1, $docs, 'Each single-item page must return exactly one document.');

            $collected[] = $docs[0][MediaAPIResponseInterface::KEY_MEDIA_ID];
        }

        $this->assertSame($ids, $collected, 'Paging one at a time must yield every ID exactly once, in order.');
    }

    // endregion

    // region: Authorization

    public function test_deniedWithoutGrant() : void
    {
        $key = $this->createTestAPIKey();

        $response = $this->assertErrorResponse($this->createMethod($key));

        $this->assertSame(APIMethodInterface::ERROR_METHOD_NOT_GRANTED, $response->getErrorCode());
    }

    public function test_succeedsWithGrant() : void
    {
        $this->assertSuccessfulResponse($this->createMethod($this->createKey()));
    }

    // endregion
}
