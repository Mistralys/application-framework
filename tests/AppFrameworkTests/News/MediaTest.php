<?php

declare(strict_types=1);

namespace AppFrameworkTests\News;

use AppFrameworkTestClasses\NewsTestCase;
use AppFrameworkTestClasses\Traits\ImageMediaTestInterface;
use AppFrameworkTestClasses\Traits\ImageMediaTestTrait;
use Application\AppFactory;
use Application\NewsCentral\NewsEntryCriticalities;
use AppLocalize\Locale\en_UK;

final class MediaTest extends NewsTestCase implements ImageMediaTestInterface
{
    use ImageMediaTestTrait;

    // region: _Tests

    public function test_articleWithImage() : void
    {
        $image = $this->createTestImage();
        $collection = AppFactory::createNews();

        $article = $collection->createNewArticle(
            'Test alert',
            'en_UK',
            'Message',
            sprintf('{media: %s width="313"}', $image->getID())
        );

        $html = $article->renderArticle();

        $this->assertStringContainsString($image->getThumbnailURL(313), $html);
    }

    // endregion
}
