<?php

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Media;

use AppFrameworkTestClasses\Traits\ImageMediaTestTrait;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

final class ThumbnailGenerationTest extends MediaTestCase
{
    use ImageMediaTestTrait;

    public function test_canCreateThumbnail() : void
    {
        $this->assertTrue($this->createTestJPGImage()->supportsThumbnails());
        $this->assertTrue($this->createTestPNGImage()->supportsThumbnails());
        $this->assertTrue($this->createTestGIFImage()->supportsThumbnails());
        $this->assertFalse($this->createTestGIFImage(true)->supportsThumbnails());
        $this->assertFalse($this->createTestSVGImage()->supportsThumbnails());
    }

    /**
     * As a thumbnail cannot be generated for an SVG file,
     * calling the thumbnail method must return the original
     * file path.
     */
    public function test_generateSVGThumbnailReturnsOriginalPath() : void
    {
        $svg = $this->createTestSVGImage();

        $this->assertSame($svg->getPath(), $svg->getThumbnailPath(42));
        $this->assertSame($svg->getPath(), $svg->createThumbnail(42));
    }

    /**
     * As a thumbnail must not be generated for an animated GIF
     * file (it would lose the animation), calling the thumbnail
     * method must return the original file path.
     */
    public function test_generateAnimatedGIFThumbnailReturnsOriginalPath() : void
    {
        $svg = $this->createTestGIFImage(true);

        $this->assertSame($svg->getPath(), $svg->getThumbnailPath(42));
        $this->assertSame($svg->getPath(), $svg->createThumbnail(42));
    }
    public function test_generateThumbnailCreatesNewFile() : void
    {
        $jpg = $this->createTestJPGImage();

        $this->assertNotSame($jpg->getPath(), $jpg->getThumbnailPath(42));
        $this->assertNotSame($jpg->getPath(), $jpg->createThumbnail(42));
    }
}
