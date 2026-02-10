<?php

declare(strict_types=1);

namespace AppFrameworkTests\MarkdownParser;

use Application\MarkdownRenderer\CustomTags\APIMethodDocTag;
use Application\MarkdownRenderer\CustomTags\MediaTag;
use Application\MarkdownRenderer\MarkdownRenderer;
use Application_Media_Document_Image;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

final class ParseTest extends MediaTestCase
{
    public function test_parseMediaDefaultNoDocument() : void
    {
        $text = '{media: 42 width="78"}';

        $tags = MediaTag::findTags($text);

        $this->assertCount(1, $tags);
        $tag = $tags[0];
        $this->assertSame(42, $tag->getMediaID());
        $this->assertSame('78', $tag->getAttribute('width'));
        $this->assertSame(78, $tag->getWidth());
        $this->assertNull($tag->getDocument());
        $this->assertStringContainsString('not found', $tag->render());
    }

    public function test_parseMediaWithClass() : void
    {
        $image = $this->createTestImage();

        $text = '{media: '.$image->getID().' class="my-class"}';

        $tags = MediaTag::findTags($text);

        $this->assertCount(1, $tags);
        $tag = $tags[0];
        $this->assertSame(array('my-class', 'visual'), $tag->getClasses());
        $this->assertStringContainsString('class="my-class visual"', $tag->render());
    }

    /**
     * When turning off the thumbnail, the image source should be the full image.
     */
    public function test_parseMediaNoThumbnail() : void
    {
        $image = $this->createTestImage();

        $text = '{media: '.$image->getID().' width="78" thumbnail="no"}';

        $tags = MediaTag::findTags($text);

        $this->assertCount(1, $tags);
        $tag = $tags[0];
        $image = $tag->getDocument();
        $this->assertInstanceOf(Application_Media_Document_Image::class, $image);
        $this->assertFalse($tag->isThumbnail());
        $this->assertStringContainsString('width="78"', $tag->render());
        $this->assertStringContainsString(sprintf('src="%s"', $image->getThumbnailURL()), $tag->render());
    }

    public function test_parseMediaDocumentExists() : void
    {
        $image = $this->createTestImage();

        $text = '{media: '.$image->getID().'}';

        $tags = MediaTag::findTags($text);
        $this->assertCount(1, $tags);
        $tag = $tags[0];

        $this->assertSame($image->getID(), $tag->getMediaID());
        $this->assertNotNull($tag->getDocument());
        $this->assertStringContainsString('<img', $tag->render());
    }

    public function test_parseParams() : void
    {
        $this->assertSame(array(), MarkdownRenderer::parseParams('')->getAttributes());
        $this->assertSame(array(), MarkdownRenderer::parseParams('   ')->getAttributes());
        $this->assertSame(array('width' => '78'), MarkdownRenderer::parseParams('width="78"')->getAttributes());
        $this->assertSame(array('width' => '78', 'height' => '90'), MarkdownRenderer::parseParams('width="78" height="90"')->getAttributes());
        $this->assertSame(array('width' => '"argh"'), MarkdownRenderer::parseParams('width="\"argh\""')->getAttributes());
    }

    public function test_parseAPIMethodTags() : void
    {
        $text = '{api: MethodName}';

        $tags = APIMethodDocTag::findTags($text);
        $this->assertCount(1, $tags);
        $tag = $tags[0];

        $this->assertSame('MethodName', $tag->getMethodName());
    }

    public function test_renderAPIMethodMarkdown() : void
    {
        $text = '{api: MethodName}';

        $html = MarkdownRenderer::create()->render($text);

        $this->assertStringContainsString('MethodName</a>', $html);
    }
}
