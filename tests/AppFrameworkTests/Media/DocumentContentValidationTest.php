<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media;

use Application_Media;
use Application_Media_Document;
use Application_Media_Document_Image;
use Application_Media_Document_PDF;
use AppFrameworkTestClasses\ApplicationTestCase;
use AppUtils\ClassHelper;
use ReflectionClass;

/**
 * Covers the media type registry (`Application_Media::getTypeIDs()`/
 * `getTypeLabel()`) and the per-type `isContentValid()` content checks
 * (JPEG/PNG/GIF via image type detection, SVG via its root element, PDF via
 * its signature) added by this work package.
 *
 * Fixture bytes are read into strings once and reused across test methods
 * (`setUpBeforeClass()`); the mismatched-extension and synthetic cases need
 * no files at all, proving the checks are purely in-memory.
 */
final class DocumentContentValidationTest extends ApplicationTestCase
{
    private static string $pngBytes;
    private static string $jpgBytes;
    private static string $gifBytes;
    private static string $svgBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        $folder = __DIR__.'/../../files/Media';

        self::$pngBytes = (string)file_get_contents($folder.'/example-image.png');
        self::$jpgBytes = (string)file_get_contents($folder.'/example-image.jpg');
        self::$gifBytes = (string)file_get_contents($folder.'/example-gif-non-animated.gif');
        self::$svgBytes = (string)file_get_contents($folder.'/example-image.svg');
    }

    // region: Valid fixtures

    public function test_validPNG() : void
    {
        $this->assertTrue(Application_Media_Document_Image::isContentValid(self::$pngBytes, 'png'));
    }

    public function test_validJPG() : void
    {
        $this->assertTrue(Application_Media_Document_Image::isContentValid(self::$jpgBytes, 'jpg'));
    }

    public function test_validJPEGExtensionAlias() : void
    {
        $this->assertTrue(Application_Media_Document_Image::isContentValid(self::$jpgBytes, 'jpeg'));
    }

    public function test_validGIF() : void
    {
        $this->assertTrue(Application_Media_Document_Image::isContentValid(self::$gifBytes, 'gif'));
    }

    public function test_validSVG() : void
    {
        $this->assertTrue(Application_Media_Document_Image::isContentValid(self::$svgBytes, 'svg'));
    }

    public function test_validPDF() : void
    {
        // Synthetic in-memory content: no fixture file needed, since the
        // check is a pure signature match.
        $this->assertTrue(Application_Media_Document_PDF::isContentValid('%PDF-1.4'."\n%synthetic content", 'pdf'));
    }

    // endregion

    // region: Mismatched content/extension pairs

    public function test_pngBytesWithJpgExtensionAreInvalid() : void
    {
        $this->assertFalse(Application_Media_Document_Image::isContentValid(self::$pngBytes, 'jpg'));
    }

    public function test_plainTextWithPngExtensionIsInvalid() : void
    {
        $this->assertFalse(Application_Media_Document_Image::isContentValid('just some plain text, not an image', 'png'));
    }

    public function test_nonSVGXMLWithSVGExtensionIsInvalid() : void
    {
        $this->assertFalse(Application_Media_Document_Image::isContentValid(
            '<?xml version="1.0"?><root><child/></root>',
            'svg'
        ));
    }

    public function test_malformedXMLWithSVGExtensionIsInvalid() : void
    {
        $this->assertFalse(Application_Media_Document_Image::isContentValid('<svg><unclosed>', 'svg'));
    }

    public function test_nonPDFContentWithPdfExtensionIsInvalid() : void
    {
        $this->assertFalse(Application_Media_Document_PDF::isContentValid('just some plain text, not a PDF', 'pdf'));
    }

    public function test_emptyContentIsInvalidForEveryType() : void
    {
        $this->assertFalse(Application_Media_Document_Image::isContentValid('', 'png'));
        $this->assertFalse(Application_Media_Document_Image::isContentValid('', 'svg'));
        $this->assertFalse(Application_Media_Document_PDF::isContentValid('', 'pdf'));
    }

    // endregion

    // region: Type registry

    public function test_getTypeIDsReturnsSortedRegisteredTypes() : void
    {
        $media = Application_Media::getInstance();

        $typeIDs = $media->getTypeIDs();

        $this->assertContains('Image', $typeIDs);
        $this->assertContains('PDF', $typeIDs);

        $sorted = $typeIDs;
        sort($sorted);
        $this->assertSame($sorted, $typeIDs, 'getTypeIDs() must return the IDs sorted.');
    }

    public function test_getTypeLabelReturnsEachTypesLabel() : void
    {
        $media = Application_Media::getInstance();

        $this->assertSame(Application_Media_Document_Image::getLabel(), $media->getTypeLabel('Image'));
        $this->assertSame(Application_Media_Document_PDF::getLabel(), $media->getTypeLabel('PDF'));
    }

    // endregion

    // region: Registry-wide reflection check

    /**
     * Every type class the registry discovers (`Application_Media::getTypeIDs()`)
     * must implement `isContentValid()` as a concrete (non-abstract) static
     * method — a new type class omitting it fails at class-definition time
     * already, since the method is declared `abstract` on
     * {@see Application_Media_Document}, but this also guards against a
     * type class that is abstract itself (and therefore could not have been
     * discovered as a concrete type to begin with).
     */
    public function test_everyRegisteredTypeImplementsIsContentValid() : void
    {
        $media = Application_Media::getInstance();
        $typeIDs = $media->getTypeIDs();

        $this->assertNotEmpty($typeIDs);

        foreach($typeIDs as $typeID)
        {
            $class = ClassHelper::requireResolvedClass(Application_Media_Document::class.'_'.$typeID);

            $reflection = new ReflectionClass($class);
            $method = $reflection->getMethod('isContentValid');

            $this->assertTrue($method->isStatic(), sprintf('isContentValid() must be static on [%s].', $class));
            $this->assertFalse($method->isAbstract(), sprintf('isContentValid() must be concretely implemented on [%s].', $class));
            $this->assertFalse($reflection->isAbstract(), sprintf('A registered type class [%s] must not be abstract.', $class));
        }
    }

    // endregion
}
