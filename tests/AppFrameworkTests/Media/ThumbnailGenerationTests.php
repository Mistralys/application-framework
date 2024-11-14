<?php

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Media;

use AppFrameworkTestClasses\Traits\ImageMediaTestTrait;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

final class ThumbnailGenerationTests extends MediaTestCase
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
}
