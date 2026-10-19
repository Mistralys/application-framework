<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media;

use Application\Media\DocumentTrait;
use Application_Media_Document_Image;
use Application_Media_Document_PDF;
use Application_Media_DocumentInterface;
use AppUtils\FileHelper\FileInfo;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;
use ReflectionMethod;
use ReflectionClass;

/**
 * Covers {@see DocumentTrait::getMaxThumbnailSize()}, which was changed
 * from a defective, unreachable concrete default (a stray `$this-` token)
 * to an explicit abstract contract that every concrete document type must
 * implement.
 *
 * Also covers {@see DocumentTrait::getThumbnailDefaultSize()}, which relies
 * on `getMaxThumbnailSize()` to cap the preferred thumbnail size for raster
 * documents, while bypassing it entirely for vector documents.
 */
final class MaxThumbnailSizeTest extends MediaTestCase
{
    public function test_traitDeclaresMethodAbstract() : void
    {
        $method = new ReflectionMethod(DocumentTrait::class, 'getMaxThumbnailSize');

        $this->assertTrue($method->isAbstract(), 'DocumentTrait::getMaxThumbnailSize() must be abstract.');

        $interface = new ReflectionClass(Application_Media_DocumentInterface::class);

        $this->assertTrue(
            $interface->hasMethod('getMaxThumbnailSize'),
            'Application_Media_DocumentInterface must declare getMaxThumbnailSize().'
        );
    }

    public function test_rasterImageReturnsPixelWidth() : void
    {
        $png = $this->createTestPNGImage();

        $this->assertSame($png->getWidth(), $png->getMaxThumbnailSize());

        $jpg = $this->createTestJPGImage();

        $this->assertSame($jpg->getWidth(), $jpg->getMaxThumbnailSize());
    }

    public function test_pdfReturnsTypeIconSize() : void
    {
        $document = $this->media->createFromFile(
            'test-pdf-'.$this->getTestCounter(),
            FileInfo::factory($this->createTestPDFFile())
        );

        $this->assertInstanceOf(Application_Media_Document_PDF::class, $document);
        $this->testMedia[] = $document;

        $this->assertSame(
            Application_Media_DocumentInterface::DEFAULT_TYPE_ICON_THUMBNAIL_SIZE,
            $document->getMaxThumbnailSize()
        );
    }

    public function test_imageUploadReturnsPixelWidth() : void
    {
        $file = FileInfo::factory($this->getExamplePNGPath());

        $upload = $this->uploads->addFromLocalFile($file);

        $this->assertSame(
            $upload->getThumbnailSourceImage()->getWidth(),
            $upload->getMaxThumbnailSize()
        );
    }

    public function test_defaultSizeIsCappedByMaxSize() : void
    {
        $png = $this->createTestPNGImage();
        $maxSize = $png->getMaxThumbnailSize();

        // A preferred size larger than the document's max size must be
        // capped to the max size.
        $this->assertSame($maxSize, $png->getThumbnailDefaultSize($maxSize + 1000));
    }

    public function test_vectorDefaultSizeUsesPreferred() : void
    {
        $svg = $this->createTestSVGImage();

        $this->assertTrue($svg->isVector());

        $preferred = 42;

        $this->assertSame($preferred, $svg->getThumbnailDefaultSize($preferred));
    }

    /**
     * Creates a synthetic, minimal PDF file on disk for use in media
     * document creation tests. No fixture file is checked into the
     * repository since {@see Application_Media_Document_PDF::isContentValid()}
     * only requires the file signature.
     *
     * @return string Absolute path to the created file.
     */
    private function createTestPDFFile() : string
    {
        $path = sys_get_temp_dir().'/test-'.$this->getTestCounter().'.pdf';

        file_put_contents($path, '%PDF-1.4'."\n%synthetic content");

        $this->testFiles[] = $path;

        return $path;
    }

    /**
     * @var string[]
     */
    private array $testFiles = array();

    protected function tearDown() : void
    {
        foreach($this->testFiles as $file) {
            if(file_exists($file)) {
                unlink($file);
            }
        }

        parent::tearDown();
    }
}
