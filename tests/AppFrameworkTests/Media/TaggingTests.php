<?php

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Media;

use Application\AppFactory;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

final class TaggingTests extends MediaTestCase
{
    public function test_addTag() : void
    {
        $image1 = $this->createTestImage()->getRecord();
        $image2 = $this->createTestImage()->getRecord();

        $media = AppFactory::createMedia();

        $this->assertSame(2, $media->getFilterCriteria()->countItems());

        $tag = $media->getMediaRootTag()->addSubTag('Foo');

        $image1->getTagManager()->addTag($tag);

        $documents = $media->getFilterCriteria()
            ->selectTag($tag)
            ->getItemsObjects();

        $this->assertCount(1, $documents);
        $this->assertContains($image1, $documents);
        $this->assertNotContains($image2, $documents);
    }
}
