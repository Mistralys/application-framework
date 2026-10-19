<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\Writer;

use Application\Application;
use Application\Media\Writer\MediaFileContent;
use Application\Media\Writer\MediaFileValidator;
use Application\Media\Writer\MediaWriterException;
use AppFrameworkTestClasses\ApplicationTestCase;

/**
 * Covers {@see MediaFileValidator}: the ordered checks (extension
 * resolution, required type match, non-zero size, content validity), and
 * that validation alone never touches the filesystem.
 */
final class MediaFileValidatorTest extends ApplicationTestCase
{
    private static string $pngBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$pngBytes = (string)file_get_contents(__DIR__.'/../../../files/Media/example-image.png');
    }

    public function test_validContentReturnsTypeID() : void
    {
        $content = MediaFileContent::fromBinary('example.png', self::$pngBytes);

        $typeID = (new MediaFileValidator())->validate($content);

        $this->assertSame('Image', $typeID);
    }

    public function test_validPDFContentReturnsTypeID() : void
    {
        $content = MediaFileContent::fromBinary('example.pdf', '%PDF-1.4'."\n%synthetic content");

        $typeID = (new MediaFileValidator())->validate($content);

        $this->assertSame('PDF', $typeID);
    }

    public function test_unsupportedExtensionThrows() : void
    {
        $content = MediaFileContent::fromBinary('example.exe', 'some content');

        $this->expectException(MediaWriterException::class);
        $this->expectExceptionCode(MediaWriterException::VALIDATION_UNSUPPORTED_EXTENSION);

        (new MediaFileValidator())->validate($content);
    }

    public function test_requiredTypeMismatchThrows() : void
    {
        $content = MediaFileContent::fromBinary('example.png', self::$pngBytes);

        $this->expectException(MediaWriterException::class);
        $this->expectExceptionCode(MediaWriterException::VALIDATION_TYPE_MISMATCH);

        (new MediaFileValidator())->validate($content, 'PDF');
    }

    public function test_requiredTypeMatchPasses() : void
    {
        $content = MediaFileContent::fromBinary('example.png', self::$pngBytes);

        $typeID = (new MediaFileValidator())->validate($content, 'Image');

        $this->assertSame('Image', $typeID);
    }

    public function test_emptyContentThrows() : void
    {
        $content = MediaFileContent::fromBinary('example.png', '');

        $this->expectException(MediaWriterException::class);
        $this->expectExceptionCode(MediaWriterException::VALIDATION_EMPTY_FILE);

        (new MediaFileValidator())->validate($content);
    }

    public function test_contentMismatchThrows() : void
    {
        $content = MediaFileContent::fromBinary('example.png', 'not actually a png');

        $this->expectException(MediaWriterException::class);
        $this->expectExceptionCode(MediaWriterException::VALIDATION_CONTENT_MISMATCH);

        (new MediaFileValidator())->validate($content);
    }

    public function test_exceptionsAreValidationErrors() : void
    {
        $content = MediaFileContent::fromBinary('example.exe', 'some content');

        try
        {
            (new MediaFileValidator())->validate($content);
            $this->fail('Expected a MediaWriterException to be thrown.');
        }
        catch(MediaWriterException $e)
        {
            $this->assertTrue($e->isValidationError());
        }
    }

    /**
     * Validation is purely in memory: it must never create a temp file,
     * even for valid content that a subsequent write would persist.
     */
    public function test_noTempFileCreatedDuringValidation() : void
    {
        $tempFolder = Application::getTempFolder();

        $before = glob($tempFolder.'/*') ?: array();

        $content = MediaFileContent::fromBinary('example.png', self::$pngBytes);
        (new MediaFileValidator())->validate($content);

        $after = glob($tempFolder.'/*') ?: array();

        $this->assertSame($before, $after, 'No temp file must be created during validate() alone.');
    }
}
