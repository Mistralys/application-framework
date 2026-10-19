<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\ErrorResponsePayload;
use Application\Application;
use Application\Media\API\MediaAPIResponseInterface;
use Application\Media\API\MediaDocumentAPIInterface;
use Application\Media\API\Methods\GetMediaDocumentAPI;
use Application\Media\Collection\MediaRecord;
use Application\Media\MediaRightsInterface;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use AppFrameworkTestClasses\API\APIClientTestTrait;
use AppFrameworkTestClasses\API\APIMethodTestTrait;
use AppFrameworkTestClasses\Traits\OperationResultTestTrait;
use Connectors_ResponseCode;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

/**
 * Covers {@see GetMediaDocumentAPI}: the document shape, `includeContent`,
 * the unknown-ID 404, and the API key grant check.
 */
final class GetMediaDocumentAPITest extends MediaTestCase
{
    use APIClientTestTrait;
    use APIMethodTestTrait;
    use OperationResultTestTrait;

    private static string $pngBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$pngBytes = (string)file_get_contents(__DIR__.'/../../../files/Media/example-image.png');
    }

    protected function setUp() : void
    {
        parent::setUp();

        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    private function createRecord() : MediaRecord
    {
        $writer = new MediaDocumentWriter();

        $record = $writer->create(
            MediaFileContent::fromBinary('document.png', self::$pngBytes),
            'Test Document',
            Application::getUser(),
            'A description',
            'keyword1, keyword2'
        );

        $this->testMedia[] = $record->getMediaDocument();

        return $record;
    }

    private function createMethod(APIKeyRecord $key) : GetMediaDocumentAPI
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = GetMediaDocumentAPI::METHOD_NAME;

        $method = new GetMediaDocumentAPI(APIManager::getInstance());
        $method->manageParamAPIKey()->selectKey($key);

        return $method;
    }

    public function test_returnsDocumentShape() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(GetMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertSame($record->getID(), $document[MediaAPIResponseInterface::KEY_MEDIA_ID]);
        $this->assertSame('Test Document', $document[MediaAPIResponseInterface::KEY_NAME]);
        $this->assertSame('Image', $document[MediaAPIResponseInterface::KEY_MEDIA_TYPE]);
        $this->assertSame('png', $document[MediaAPIResponseInterface::KEY_EXTENSION]);
        $this->assertSame('A description', $document[MediaAPIResponseInterface::KEY_DESCRIPTION]);
        $this->assertSame('keyword1, keyword2', $document[MediaAPIResponseInterface::KEY_KEYWORDS]);
        $this->assertIsArray($document[MediaAPIResponseInterface::KEY_IMAGE]);
        $this->assertArrayNotHasKey(MediaAPIResponseInterface::KEY_CONTENT, $document);
    }

    public function test_includeContentReturnsBase64Content() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(GetMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[GetMediaDocumentAPI::PARAM_INCLUDE_CONTENT] = 'true';

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertArrayHasKey(MediaAPIResponseInterface::KEY_CONTENT, $document);
        $this->assertSame(self::$pngBytes, base64_decode((string)$document[MediaAPIResponseInterface::KEY_CONTENT], true));
    }

    public function test_unknownIDIsRejectedWith404() : void
    {
        $key = $this->createTestAPIKeyForMethod(GetMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = 999999999;

        $response = $this->createMethod($key)->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);
        $this->assertSame(MediaDocumentAPIInterface::VALIDATION_UNKNOWN_MEDIA_ID, $response->getErrorCode());
        $this->assertSame(Connectors_ResponseCode::HTTP_NOT_FOUND, $response->getHttpStatusCode());
    }

    public function test_deniedWithoutGrant() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKey();

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();

        $response = $this->assertErrorResponse($this->createMethod($key));

        $this->assertSame(APIMethodInterface::ERROR_METHOD_NOT_GRANTED, $response->getErrorCode());
    }

    public function test_succeedsWithGrant() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(GetMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();

        $this->assertSuccessfulResponse($this->createMethod($key));
    }
}
