<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\Application;
use Application\Media\API\MediaDocumentAPIInterface;
use Application\Media\Collection\MediaRecord;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use Connectors_ResponseCode;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;
use TestDriver\API\TestMediaDocumentMethod;

/**
 * Covers the media document lookup piece ({@see MediaDocumentAPIInterface}/
 * {@see \Application\Media\API\MediaDocumentAPITrait}), through
 * {@see MediaDocumentParamsContainer} resolved via a
 * {@see TestMediaDocumentMethod} instance.
 */
final class MediaDocumentParamsContainerTest extends MediaTestCase
{
    protected function setUp() : void
    {
        parent::setUp();

        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    private function createMethod() : TestMediaDocumentMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestMediaDocumentMethod::METHOD_NAME;

        return new TestMediaDocumentMethod(APIManager::getInstance());
    }

    private function createRecordWithPNG() : MediaRecord
    {
        $writer = new MediaDocumentWriter();

        $folder = __DIR__.'/../../../files/Media';
        $bytes = (string)file_get_contents($folder.'/example-image.png');

        $record = $writer->create(
            MediaFileContent::fromBinary('example.png', $bytes),
            'Container Test Image',
            Application::getUser()
        );

        $this->testMedia[] = $record->getMediaDocument();

        return $record;
    }

    public function test_resolvesExistingRecordByMediaID() : void
    {
        $record = $this->createRecordWithPNG();

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();

        $method = $this->createMethod();
        $data = $method->processReturn()->getData();

        $this->assertSame($record->getID(), $data[MediaDocumentAPIInterface::PARAM_MEDIA_ID]);
    }

    public function test_selectMediaDocumentOverridesTheRequest() : void
    {
        $requestRecord = $this->createRecordWithPNG();
        $selectedRecord = $this->createRecordWithPNG();

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $requestRecord->getID();

        $method = $this->createMethod();
        $method->manageMediaDocumentParams()->selectMediaDocument($selectedRecord);

        $data = $method->processReturn()->getData();

        $this->assertSame($selectedRecord->getID(), $data[MediaDocumentAPIInterface::PARAM_MEDIA_ID]);
        $this->assertNotSame($requestRecord->getID(), $data[MediaDocumentAPIInterface::PARAM_MEDIA_ID]);
    }

    public function test_unknownMediaIDIsRejectedWith404() : void
    {
        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = 999999999;

        $method = $this->createMethod();
        $response = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);
        $this->assertSame(MediaDocumentAPIInterface::VALIDATION_UNKNOWN_MEDIA_ID, $response->getErrorCode());
        $this->assertSame(Connectors_ResponseCode::HTTP_NOT_FOUND, $response->getHttpStatusCode());
    }

    public function test_missingMediaIDIsRejectedWith404() : void
    {
        // No mediaID submitted at all.
        $method = $this->createMethod();
        $response = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);
        $this->assertSame(MediaDocumentAPIInterface::VALIDATION_UNKNOWN_MEDIA_ID, $response->getErrorCode());
        $this->assertSame(Connectors_ResponseCode::HTTP_NOT_FOUND, $response->getHttpStatusCode());
    }
}
