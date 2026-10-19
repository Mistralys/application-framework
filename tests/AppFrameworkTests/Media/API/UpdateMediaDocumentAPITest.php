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
use Application\Media\API\MediaFileUploadAPIInterface;
use Application\Media\API\MediaMetadataAPIInterface;
use Application\Media\API\Methods\UpdateMediaDocumentAPI;
use Application\Media\Collection\MediaRecord;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use Application\Media\Writer\MediaWriterException;
use AppFrameworkTestClasses\API\APIClientTestTrait;
use AppFrameworkTestClasses\API\APIMethodTestTrait;
use AppFrameworkTestClasses\Traits\OperationResultTestTrait;
use Connectors_ResponseCode;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

/**
 * Covers {@see UpdateMediaDocumentAPI}: metadata updates, clearing,
 * file replacement, the no-changes/extension-change/pair rejections, the
 * unknown-ID 404, dry-run non-persistence, and the grant check.
 */
final class UpdateMediaDocumentAPITest extends MediaTestCase
{
    use APIClientTestTrait;
    use APIMethodTestTrait;
    use OperationResultTestTrait;

    private static string $pngBytes;
    private static string $jpgBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$pngBytes = (string)file_get_contents(__DIR__.'/../../../files/Media/example-image.png');
        self::$jpgBytes = (string)file_get_contents(__DIR__.'/../../../files/Media/example-image.jpg');
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
            'Original Name',
            Application::getUser(),
            'Original description',
            'original, keywords'
        );

        $this->testMedia[] = $record->getMediaDocument();

        return $record;
    }

    private function createMethod(APIKeyRecord $key) : UpdateMediaDocumentAPI
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = UpdateMediaDocumentAPI::METHOD_NAME;

        $method = new UpdateMediaDocumentAPI(APIManager::getInstance());
        $method->manageParamAPIKey()->selectKey($key);
        $method->setRequestBody('{}');

        return $method;
    }

    // region: Metadata updates

    public function test_updatesName() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = 'Updated Name';

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertSame('Updated Name', $document[MediaAPIResponseInterface::KEY_NAME]);
        $this->assertTrue($data[MediaAPIResponseInterface::KEY_MODIFIED]);
        $this->assertFalse($data[UpdateMediaDocumentAPI::KEY_DRY_RUN]);
    }

    public function test_clearingDescriptionAndKeywords() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaMetadataAPIInterface::PARAM_DESCRIPTION] = '';
        $_REQUEST[MediaMetadataAPIInterface::PARAM_KEYWORDS] = '';

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertSame('', $document[MediaAPIResponseInterface::KEY_DESCRIPTION]);
        $this->assertSame('', $document[MediaAPIResponseInterface::KEY_KEYWORDS]);
        $this->assertTrue($data[MediaAPIResponseInterface::KEY_MODIFIED]);
    }

    public function test_fileReplacement() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        // Same extension (png), different bytes: re-use the JPG fixture's
        // bytes would change the detected type, so a second, distinct PNG
        // payload is needed. The GIF/JPG fixtures have different extensions,
        // so the replacement content must stay a valid PNG — reusing the
        // same bytes is sufficient to prove the write path runs; content
        // equality is not the point of this test.
        $newContent = self::$pngBytes;

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'replacement.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode($newContent);

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();

        $this->assertTrue($data[MediaAPIResponseInterface::KEY_MODIFIED]);

        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];
        $this->assertSame(strlen($newContent), $document[MediaAPIResponseInterface::KEY_FILE_SIZE]);
    }

    // endregion

    // region: Rejections

    public function test_rejectsNoChanges() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();

        $this->assertErrorResponseCode($this->createMethod($key), UpdateMediaDocumentAPI::VALIDATION_NO_CHANGES);
    }

    public function test_rejectsExtensionChange() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'replacement.jpg';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$jpgBytes);

        $this->assertErrorResponseCode($this->createMethod($key), MediaWriterException::VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED);
    }

    public function test_rejectsFileNameWithoutFileContent() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'replacement.png';

        $this->assertErrorResponse($this->createMethod($key));
    }

    public function test_rejectsFileContentWithoutFileName() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);

        $this->assertErrorResponse($this->createMethod($key));
    }

    public function test_unknownIDIsRejectedWith404() : void
    {
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = 999999999;
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = 'New name';

        $response = $this->createMethod($key)->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);
        $this->assertSame(MediaDocumentAPIInterface::VALIDATION_UNKNOWN_MEDIA_ID, $response->getErrorCode());
        $this->assertSame(Connectors_ResponseCode::HTTP_NOT_FOUND, $response->getHttpStatusCode());
    }

    // endregion

    // region: Dry run

    public function test_dryRunDoesNotPersist() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = 'Dry Run Name';
        $_REQUEST[UpdateMediaDocumentAPI::KEY_DRY_RUN] = 'true';

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertSame($record->getID(), $document[MediaAPIResponseInterface::KEY_MEDIA_ID]);
        $this->assertSame('Dry Run Name', $document[MediaAPIResponseInterface::KEY_NAME]);
        $this->assertTrue($data[MediaAPIResponseInterface::KEY_MODIFIED]);
        $this->assertTrue($data[UpdateMediaDocumentAPI::KEY_DRY_RUN]);

        // Unlike the HCP Editor's mutate-then-rollback write methods (see
        // this project's dry-run-testing guidance), this plan's dry runs
        // never call a mutating writer method in the first place (Approach
        // "Dry runs that skip mutation rather than mutate then roll back") —
        // there is no uncommitted UPDATE to mask, so a direct raw-DB query
        // is sufficient. A rollback+restart of the shared ambient test
        // transaction would be actively wrong here: this process's
        // transaction spans the whole test run, not just this test, so
        // rolling it back would also discard createRecord()'s insert above.
        $dbName = (string)DBHelper::fetchKey(
            'media_name',
            'SELECT media_name FROM media WHERE media_id = :id',
            array(':id' => $record->getID())
        );

        $this->assertSame('Original Name', $dbName);
    }

    public function test_dryRunRejectsSameExtensionChangeCode() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'replacement.jpg';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$jpgBytes);
        $_REQUEST[UpdateMediaDocumentAPI::KEY_DRY_RUN] = 'true';

        $this->assertErrorResponseCode($this->createMethod($key), MediaWriterException::VALIDATION_EXTENSION_CHANGE_NOT_ALLOWED);
    }

    // endregion

    // region: Grant check

    public function test_deniedWithoutGrant() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKey();

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = 'New name';

        $response = $this->assertErrorResponse($this->createMethod($key));

        $this->assertSame(APIMethodInterface::ERROR_METHOD_NOT_GRANTED, $response->getErrorCode());
    }

    public function test_succeedsWithGrant() : void
    {
        $record = $this->createRecord();
        $key = $this->createTestAPIKeyForMethod(UpdateMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaDocumentAPIInterface::PARAM_MEDIA_ID] = $record->getID();
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = 'New name';

        $this->assertSuccessfulResponse($this->createMethod($key));
    }

    // endregion
}
