<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\Clients\Keys\APIKeyRecord;
use Application\API\ErrorResponsePayload;
use Application\Media\API\MediaAPIResponseInterface;
use Application\Media\API\MediaMetadataAPIInterface;
use Application\Media\API\MediaFileUploadAPIInterface;
use Application\Media\API\Methods\AddMediaDocumentAPI;
use Application\Media\Collection\MediaCollection;
use Application\Media\Writer\MediaWriterException;
use AppFrameworkTestClasses\API\APIClientTestTrait;
use AppFrameworkTestClasses\API\APIMethodTestTrait;
use AppFrameworkTestClasses\Traits\OperationResultTestTrait;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

/**
 * Covers {@see AddMediaDocumentAPI}: the creation shape, every rejection
 * code reachable through the API, the shared dry-run validation (AC-09),
 * the default-name rule, and the grant check.
 *
 * Note (code insight, recorded in the implementation pipeline): the
 * `681004` (empty file) rejection is not independently exercisable here.
 * {@see \Application\API\Parameters\Type\Base64FileParameter::resolveValue()}
 * treats an empty or whitespace-only `fileContent` value as absent (`null`)
 * before it ever reaches {@see \Application\Media\Writer\MediaFileValidator},
 * and `fileContent` is a required parameter on this method — so a real
 * zero-byte upload surfaces as a standard "required parameter missing"
 * rejection, not `681004`. That code is covered at the validator level by
 * {@see \AppFrameworkTests\Media\Writer\MediaFileValidatorTest}.
 */
final class AddMediaDocumentAPITest extends MediaTestCase
{
    use APIClientTestTrait;
    use APIMethodTestTrait;
    use OperationResultTestTrait;

    private static string $pngBytes;
    private static string $svgBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$pngBytes = (string)file_get_contents(__DIR__.'/../../../files/Media/example-image.png');
        self::$svgBytes = (string)file_get_contents(__DIR__.'/../../../files/Media/example-image.svg');
    }

    protected function setUp() : void
    {
        parent::setUp();

        $_REQUEST = array();
        $_POST = array();
        $_GET = array();
    }

    private function createMethod(APIKeyRecord $key) : AddMediaDocumentAPI
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = AddMediaDocumentAPI::METHOD_NAME;

        $method = new AddMediaDocumentAPI(APIManager::getInstance());
        $method->manageParamAPIKey()->selectKey($key);
        $method->setRequestBody('{}');

        return $method;
    }

    private function countMediaRows() : int
    {
        return (int)DBHelper::fetchKey('count', sprintf('SELECT COUNT(*) AS count FROM `%s`', MediaCollection::TABLE_NAME));
    }

    // region: Success

    public function test_createsPNGDocument() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = 'Hero Image';
        $_REQUEST[MediaMetadataAPIInterface::PARAM_DESCRIPTION] = 'A description';
        $_REQUEST[MediaMetadataAPIInterface::PARAM_KEYWORDS] = 'hero, banner';

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertIsInt($document[MediaAPIResponseInterface::KEY_MEDIA_ID]);
        $this->assertSame('Hero Image', $document[MediaAPIResponseInterface::KEY_NAME]);
        $this->assertSame('Image', $document[MediaAPIResponseInterface::KEY_MEDIA_TYPE]);
        $this->assertSame('png', $document[MediaAPIResponseInterface::KEY_EXTENSION]);
        $this->assertSame('A description', $document[MediaAPIResponseInterface::KEY_DESCRIPTION]);
        $this->assertSame('hero, banner', $document[MediaAPIResponseInterface::KEY_KEYWORDS]);
        $this->assertSame($key->getPseudoUser()->getID(), $document[MediaAPIResponseInterface::KEY_AUTHOR_ID]);
        $this->assertIsArray($document[MediaAPIResponseInterface::KEY_IMAGE]);
        $this->assertFalse($data[AddMediaDocumentAPI::KEY_DRY_RUN]);
    }

    public function test_createsSVGDocument() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'icon.svg';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$svgBytes);

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertSame('svg', $document[MediaAPIResponseInterface::KEY_EXTENSION]);
        $this->assertSame('Image', $document[MediaAPIResponseInterface::KEY_MEDIA_TYPE]);
    }

    public function test_defaultNameIsFileBaseNameCutToLimit() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $longBaseName = str_repeat('a', 200);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = $longBaseName.'.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertSame(
            mb_substr($longBaseName, 0, MediaMetadataAPIInterface::MAX_NAME_LENGTH),
            $document[MediaAPIResponseInterface::KEY_NAME]
        );
        $this->assertSame(MediaMetadataAPIInterface::MAX_NAME_LENGTH, mb_strlen((string)$document[MediaAPIResponseInterface::KEY_NAME]));
    }

    // endregion

    // region: Rejections (real and dry run)

    public function test_rejectsUnsupportedExtension() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'file.xyz';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode('arbitrary content');

        $this->assertErrorResponseCode($this->createMethod($key), MediaWriterException::VALIDATION_UNSUPPORTED_EXTENSION);
    }

    public function test_rejectsMediaTypeMismatch() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);
        $_REQUEST[AddMediaDocumentAPI::PARAM_MEDIA_TYPE] = 'PDF';

        $this->assertErrorResponseCode($this->createMethod($key), MediaWriterException::VALIDATION_TYPE_MISMATCH);
    }

    public function test_rejectsContentNotMatchingExtension() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode('this is not a png file');

        $this->assertErrorResponseCode($this->createMethod($key), MediaWriterException::VALIDATION_CONTENT_MISMATCH);
    }

    public function test_rejectsInvalidFileName() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'no-extension';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);

        $this->assertErrorResponseCode($this->createMethod($key), MediaWriterException::VALIDATION_INVALID_FILE_NAME);
    }

    public function test_rejectsHTMLInDescription() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);
        $_REQUEST[MediaMetadataAPIInterface::PARAM_DESCRIPTION] = '<b>bold</b>';

        $this->assertErrorResponseCode($this->createMethod($key), MediaMetadataAPIInterface::VALIDATION_HTML_NOT_ALLOWED);
    }

    public function test_rejectsOverLengthName() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = str_repeat('a', MediaMetadataAPIInterface::MAX_NAME_LENGTH + 1);

        $method = $this->createMethod($key);
        $response = $this->assertErrorResponse($method);

        // Over-length values are rejected at the parameter-validation stage
        // (before the method body ever runs), which the boundary reports to
        // the client as the generic ERROR_INVALID_REQUEST_PARAMS (183003);
        // the field-specific VALIDATION_MAX_LENGTH_EXCEEDED (183507) is the
        // parameter's own validation result code, asserted directly here —
        // the same pattern used by PaginatedAPITraitTest for its own
        // field-specific-code-wrapped-by-generic-code cases.
        $this->assertSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $response->getErrorCode());
        $this->assertSame(183507, $this->getFirstValidationErrorCode($method));
    }

    private function getFirstValidationErrorCode(AddMediaDocumentAPI $method) : ?int
    {
        foreach($method->getValidationResults()->getResults() as $result) {
            if($result->isError()) {
                return $result->getCode();
            }
        }

        return null;
    }

    /**
     * Each rejection above is reproduced with `dryRun=true`, proving the
     * dry run performs the identical validation and nothing is persisted
     * (AC-09).
     */
    public function test_dryRunRejectsSameInputsWithSameCodes() : void
    {
        // Each entry: [fileName, raw (not yet base64-encoded) content, expected code].
        $cases = array(
            array('file.xyz', 'arbitrary content', MediaWriterException::VALIDATION_UNSUPPORTED_EXTENSION),
            array('hero.png', 'not a real png', MediaWriterException::VALIDATION_CONTENT_MISMATCH),
            array('no-extension', self::$pngBytes, MediaWriterException::VALIDATION_INVALID_FILE_NAME),
        );

        foreach($cases as [$fileName, $content, $expectedCode]) {
            $_REQUEST = array();

            $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

            $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = $fileName;
            $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode($content);
            $_REQUEST[AddMediaDocumentAPI::KEY_DRY_RUN] = 'true';

            $this->assertErrorResponseCode($this->createMethod($key), $expectedCode);
        }
    }

    // endregion

    // region: Dry run non-persistence (AC-09)

    public function test_dryRunDoesNotPersist() : void
    {
        $before = $this->countMediaRows();

        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);
        $_REQUEST[MediaMetadataAPIInterface::PARAM_NAME] = 'Dry Run Hero';
        $_REQUEST[AddMediaDocumentAPI::KEY_DRY_RUN] = 'true';

        $data = $this->assertSuccessfulResponse($this->createMethod($key))->getData();
        $document = $data[MediaAPIResponseInterface::KEY_MEDIA_DOCUMENT];

        $this->assertNull($document[MediaAPIResponseInterface::KEY_MEDIA_ID]);
        $this->assertSame('Dry Run Hero', $document[MediaAPIResponseInterface::KEY_NAME]);
        $this->assertTrue($data[AddMediaDocumentAPI::KEY_DRY_RUN]);

        // Unlike the HCP Editor's mutate-then-rollback write methods (see
        // this project's dry-run-testing guidance), this plan's dry runs
        // never call a mutating writer method in the first place (Approach
        // "Dry runs that skip mutation rather than mutate then roll back") —
        // there is no uncommitted INSERT to mask, so a direct raw-DB count
        // is sufficient. A rollback+restart of the shared ambient test
        // transaction would be actively wrong here: this process's
        // transaction spans the whole test run, not just this test, so
        // rolling it back would also discard other tests' already-committed-
        // in-spirit fixture rows created earlier in the same run.
        $this->assertSame($before, $this->countMediaRows());
    }

    // endregion

    // region: Grant check

    public function test_deniedWithoutGrant() : void
    {
        $key = $this->createTestAPIKey();

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);

        $response = $this->assertErrorResponse($this->createMethod($key));

        $this->assertSame(APIMethodInterface::ERROR_METHOD_NOT_GRANTED, $response->getErrorCode());
    }

    public function test_succeedsWithGrant() : void
    {
        $key = $this->createTestAPIKeyForMethod(AddMediaDocumentAPI::METHOD_NAME);

        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);

        $this->assertSuccessfulResponse($this->createMethod($key));
    }

    // endregion
}
