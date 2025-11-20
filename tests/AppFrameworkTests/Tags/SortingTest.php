<?php

declare(strict_types=1);

namespace AppFrameworkTests\Tags;

use Application\Tags\TagCollection;
use Application\Tags\TagSortTypes;
use Mistralys\AppFrameworkTests\TestClasses\TaggingTestCase;

final class SortingTest extends TaggingTestCase
{
    public function test_createRootWithInheritSorting() : void
    {
        $this->expectExceptionCode(TagCollection::ERROR_CANNOT_CREATE_ROOT_WITH_INHERIT_SORTING);

        $this->tagsCollection->createNewRecord(array(
            TagCollection::COL_LABEL => 'Root tag',
            TagCollection::COL_SORT_TYPE => TagSortTypes::SORT_INHERIT
        ));
    }

    public function test_rootIsNotSetToInherit() : void
    {
        $rootTag = $this->createTestRootTag();

        $this->assertSame(TagSortTypes::SORT_ALPHA_ASC, $rootTag->getSortTypeID());
    }

    public function test_defaultAlphabeticalSorting() : void
    {
        $rootTag = $this->createTestRootTag();

        // Add them in the wrong alphabetical order on purpose
        $subA = $rootTag->addSubTag('Sub tag A');
        $subC = $rootTag->addSubTag('Sub tag C');
        $subB = $rootTag->addSubTag('Sub tag B');

        $subs = $rootTag->getSubTags();

        $this->assertCount(3, $subs);

        // Default is sorting alphabetically by label.
        $this->assertSame($subA, $subs[0]);
        $this->assertSame($subB, $subs[1]);
        $this->assertSame($subC, $subs[2]);
    }

    public function test_weightSorting() : void
    {
        $rootTag = $this->createTestRootTag();
        $rootTag->setSortType(TagSortTypes::getInstance()->getWeightASC());

        // Add them in the wrong alphabetical order on purpose
        $subA = $rootTag->addSubTag('Sub tag A')->setSortWeight(1)->saveChained();
        $subB = $rootTag->addSubTag('Sub tag B')->setSortWeight(3)->saveChained();
        $subC = $rootTag->addSubTag('Sub tag C')->setSortWeight(2)->saveChained();

        $subs = $rootTag->getSubTags();

        $this->assertCount(3, $subs);

        // Sorted by weight
        $this->assertSame($subA->getID(), $subs[0]->getID(), 'Sub A, weight '.$subA->getSortWeight());
        $this->assertSame($subB->getID(), $subs[2]->getID(), 'Sub A, weight '.$subA->getSortWeight());
        $this->assertSame($subC->getID(), $subs[1]->getID(), 'Sub A, weight '.$subA->getSortWeight());
    }
}
