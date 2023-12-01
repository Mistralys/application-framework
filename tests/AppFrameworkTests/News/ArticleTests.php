<?php

declare(strict_types=1);

namespace AppFrameworkTests\News;

use AppFrameworkTestClasses\NewsTestCase;
use Application\AppFactory;
use AppLocalize\Locale\en_UK;

final class ArticleTests extends NewsTestCase
{
    // region: _Tests

    public function test_createArticle(): void
    {
        $collection = AppFactory::createNews();

        $article = $collection->createNewArticle(
            'Test label',
            'en_UK',
            'Synopsis',
            'Article'
        );

        $this->assertSame('Test label', $article->getLabel());
        $this->assertSame('en_UK', $article->getLocaleID());
        $this->assertInstanceOf(en_UK::class, $article->getLocale());
        $this->assertSame('Synopsis', $article->getSynopsis());
        $this->assertSame('Article', $article->getArticle());
        $this->assertDatesHaveBeenSet($article);
    }

    // endregion
}
