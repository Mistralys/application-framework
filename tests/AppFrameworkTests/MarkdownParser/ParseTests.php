<?php

declare(strict_types=1);

namespace AppFrameworkTests\MarkdownParser;

use Application\MarkdownRenderer;
use Application\MarkdownRenderer\CustomTags\MediaTag;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

final class ParseTests extends MediaTestCase
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

    public function test_parseMediaDocumentExists() : void
    {
        $image = $this->createTestImageMedia();

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
}
