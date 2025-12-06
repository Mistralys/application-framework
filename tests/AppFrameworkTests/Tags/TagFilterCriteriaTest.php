<?php

declare(strict_types=1);

namespace AppFrameworkTests\TestSuites\Tags;

use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;

final class TagFilterCriteriaTest extends TaggingTestCase
{
    public function test_selectTag() : void
    {
        $tagContainer = $this->recordCollection->getTagConnector();
        $tag = $this->tagsCollection->createNewTag('Foo');

        $this->recordCollection->createTestRecord('Without tag', 'without');
        $this->recordCollection->createTestRecord('With tag 1', 'with1')->getTagManager()->addTag($tag);
        $this->recordCollection->createTestRecord('With tag 2', 'with2')->getTagManager()->addTag($tag);

        $this->assertCount(2, $tagContainer->getByTag($tag));

        // Selecting a tag in the filter criteria should only return the
        // matching records, using the JOIN of the tag connection table.
        $matches = $this->recordCollection->getFilterCriteria()
            ->selectTag($tag)
            ->getItemsObjects();

        $this->assertCount(3, $this->recordCollection->getFilterCriteria()->getItemsObjects());
        $this->assertCount(2, $matches);
    }
}
