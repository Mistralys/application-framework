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
use Application\Media\API\Methods\DeleteMediaDocumentAPI;
use Application\Media\Collection\MediaRecord;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use AppFrameworkTestClasses\API\APIClientTestTrait;
use AppFrameworkTestClasses\API\APIMethodTestTrait;
use AppFrameworkTestClasses\Traits\OperationResultTestTrait;
use Connectors_ResponseCode;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

/**
 * Covers {@see DeleteMediaDocumentAPI}: deletion of the record and its file,
 * the pre-deletion response shape, the dry run that never deletes, the
 * unknown-ID 404, and the grant check.
 */
final class DeleteMediaDocumentAPITest extends MediaTestCase
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
            'Deletable Document',
            Application::getUser(),
            'A description',
            'keyword1, keyword2'
        );

        $this->testMedia[] = $record->getMediaDocument();

        return $record;
    }

    private function createMethod(APIKeyRecord $key) : DeleteMediaDocumentAPI
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = DeleteMediaDocumentAPI::METHOD_NAME;

        $method = new DeleteMediaDocumentAPI(APIManager::getInstance());
        $method->manageParamAPIKey()->selectKey($key);

        return $method;
    }

    public function test_deletesRecordAndFile() : void
    {
        $record = $this->createRecord();
        $filePath = $record->getMediaDocument()->getPath();
        $id = $record->getID();

        $this->assertFileExists($filePath);

        $key = $this->createTestAPIKeyForMethod(DeleteMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $id;

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertSame($id, $document[MediaAPIResponseInterface::KEY_MEDIA_ID]);
        $this->assertSame('Deletable Document', $document[MediaAPIResponseInterface::KEY_NAME]);
        $this->assertTrue($data[MediaAPIResponseInterface::KEY_DELETED]);
        $this->assertFalse($data[DeleteMediaDocumentAPI::KEY_DRY_RUN]);

        $this->assertFalse(
            (bool)DBHelper::fetchKey('media_id', 'SELECT media_id FROM media WHERE media_id = :id', array(':id' => $id))
        );
        $this->assertFileDoesNotExist($filePath);
    }

    public function test_dryRunDoesNotDelete() : void
    {
        $record = $this->createRecord();
        $filePath = $record->getMediaDocument()->getPath();
        $id = $record->getID();

        $key = $this->createTestAPIKeyForMethod(DeleteMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $id;
        $_REQUEST[DeleteMediaDocumentAPI::KEY_DRY_RUN] = 'true';

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertSame($id, $document[MediaAPIResponseInterface::KEY_MEDIA_ID]);
        $this->assertFalse($data[MediaAPIResponseInterface::KEY_DELETED]);
        $this->assertTrue($data[DeleteMediaDocumentAPI::KEY_DRY_RUN]);

        // This plan's dry runs never call a mutating writer method (see this
        // project's application-framework dry-run-testing memory note) — a
        // direct raw-DB query is correct here, with no rollback/restart.
        $this->assertSame(
            $id,
            (int)DBHelper::fetchKey('media_id', 'SELECT media_id FROM media WHERE media_id = :id', array(':id' => $id))
        );
        $this->assertFileExists($filePath);
    }

    public function test_unknownIDIsRejectedWith404() : void
    {
        $key = $this->createTestAPIKeyForMethod(DeleteMediaDocumentAPI::METHOD_NAME);

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
        $key = $this->createTestAPIKeyForMethod(DeleteMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();

        $this->assertSuccessfulResponse($this->createMethod($key));
    }
}
