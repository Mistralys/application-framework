<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\API;

use Application\API\APIManager;
use Application\API\APIMethodInterface;
use Application\API\ErrorResponsePayload;
use Application\API\Parameters\Validation\ParamValidationInterface;
use Application\Media\API\MediaFileUploadAPIInterface;
use Application\Media\Writer\MediaFileContent;
use Application\Media\Writer\MediaWriterException;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;
use ReflectionProperty;
use TestDriver\API\TestMediaFileUploadMethod;

/**
 * Covers the media file upload piece ({@see MediaFileUploadAPIInterface}/
 * {@see \Application\Media\API\MediaFileUploadAPITrait}), through a
 * {@see TestMediaFileUploadMethod} instance — the DB-free isolation harness
 * for this piece.
 */
final class MediaFileUploadPieceTest extends MediaTestCase
{
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

    private function createMethod() : TestMediaFileUploadMethod
    {
        $_REQUEST[APIMethodInterface::REQUEST_PARAM_METHOD] = TestMediaFileUploadMethod::METHOD_NAME;

        return new TestMediaFileUploadMethod(APIManager::getInstance());
    }

    /**
     * Directly injects an already-resolved {@see MediaFileContent}, bypassing
     * the usual `fileName`/`fileContent` parameter resolution. Used by
     * {@see self::test_emptyFileIsRejected()} — see that test's docblock.
     */
    private function injectResolvedFileContent(TestMediaFileUploadMethod $method, MediaFileContent $content) : void
    {
        (new ReflectionProperty($method, 'mediaFileContentResolved'))->setValue($method, true);
        (new ReflectionProperty($method, 'mediaFileContent'))->setValue($method, $content);
    }

    public function test_validPngResolvesImageType() : void
    {
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);

        $data = $this->createMethod()->processReturn()->getData();

        $this->assertSame('Image', $data[TestMediaFileUploadMethod::KEY_TYPE_ID]);
        $this->assertSame('hero.png', $data[MediaFileUploadAPIInterface::PARAM_FILE_NAME]);
        $this->assertSame(strlen(self::$pngBytes), $data[TestMediaFileUploadMethod::KEY_FILE_SIZE]);
    }

    public function test_unsupportedExtensionIsRejected() : void
    {
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.unsupported';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);

        $response = $this->createMethod()->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);
        $this->assertSame(MediaWriterException::VALIDATION_UNSUPPORTED_EXTENSION, $response->getErrorCode());
    }

    public function test_contentMismatchIsRejected() : void
    {
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode('this is not a valid PNG file');

        $response = $this->createMethod()->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);
        $this->assertSame(MediaWriterException::VALIDATION_CONTENT_MISMATCH, $response->getErrorCode());
    }

    /**
     * {@see \Application\API\Parameters\Type\Base64FileParameter::resolveValue()}
     * treats a truly empty decoded value as absent (`null`) before
     * {@see \Application\Media\Writer\MediaFileValidator} ever sees it — the
     * same limitation {@see AddMediaDocumentAPITest}'s docblock documents for
     * the full `AddMediaDocument` method, since both consume the identical
     * {@see \Application\Media\API\MediaFileUploadAPITrait}. A real
     * zero-byte submission is therefore unreachable through `fileContent`
     * and instead surfaces as the generic "no value available" error.
     *
     * This test proves the other half of the contract: once an
     * already-resolved, zero-byte {@see MediaFileContent} does reach
     * `validateMediaFile()` — e.g. from a future non-base64 content source —
     * it is reported end-to-end through the API response as
     * {@see MediaWriterException::VALIDATION_EMPTY_FILE} (681004), by
     * injecting it directly via reflection, bypassing parameter resolution.
     * {@see \AppFrameworkTests\Media\Writer\MediaFileValidatorTest::test_emptyContentThrows()}
     * covers the same code at the validator's own unit level.
     */
    public function test_emptyFileIsRejected() : void
    {
        // A well-formed submission is required to pass the parameter layer
        // (fileName/fileContent are both required); the injected override
        // below replaces the resolved content before validateMediaFile()
        // reads it.
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'empty.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = base64_encode(self::$pngBytes);

        $method = $this->createMethod();

        $this->injectResolvedFileContent($method, MediaFileContent::fromBinary('empty.png', ''));

        $response = $method->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);
        $this->assertSame(MediaWriterException::VALIDATION_EMPTY_FILE, $response->getErrorCode());
    }

    public function test_invalidBase64IsNestedValidationError() : void
    {
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_NAME] = 'hero.png';
        $_REQUEST[MediaFileUploadAPIInterface::PARAM_FILE_CONTENT] = 'not-valid-base64-content!!!';

        $response = $this->createMethod()->processReturn();

        $this->assertInstanceOf(ErrorResponsePayload::class, $response);

        // Invalid base64 is rejected at the parameter-validation stage
        // (before the method body ever runs), which the boundary reports to
        // the client as the generic ERROR_INVALID_REQUEST_PARAMS (183003);
        // the field-specific VALIDATION_INVALID_BASE64 (183508) is nested in
        // the response's validationErrors[] — the same pattern
        // AddMediaDocumentAPITest::test_rejectsOverLengthName uses for its
        // own field-specific-code-wrapped-by-generic-code case.
        $this->assertSame(APIMethodInterface::ERROR_INVALID_REQUEST_PARAMS, $response->getErrorCode());

        $errorData = $response->getErrorData();
        $this->assertArrayHasKey('validationErrors', $errorData);

        $codes = array_column($errorData['validationErrors'], 'code');
        $this->assertContains(ParamValidationInterface::VALIDATION_INVALID_BASE64, $codes);
    }
}
