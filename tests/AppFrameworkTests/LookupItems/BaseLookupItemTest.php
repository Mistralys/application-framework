<?php
/**
 * @package Application
 * @subpackage UnitTests
 */

declare(strict_types=1);

namespace AppFrameworkTests\LookupItems;

use AppFrameworkTestClasses\LookupItems\TestLookupItem;
use Mistralys\AppFrameworkTests\TestClasses\DBHelperTestCase;

/**
 * Tests for the new public API surface on {@see \Application\LookupItems\BaseLookupItem}:
 * - {@see \Application\LookupItems\BaseLookupItem::findMatchingIDs()}
 * - {@see \Application\LookupItems\BaseLookupItem::setLimit()}
 * - {@see \Application\LookupItems\BaseLookupItem::addWhere()} (visibility change)
 * - Backward compatibility of {@see \Application\LookupItems\BaseLookupItem::findMatches()}
 *
 * @package Application
 * @subpackage UnitTests
 */
final class BaseLookupItemTest extends DBHelperTestCase
{
    // region: findMatchingIDs — numeric term

    public function test_findMatchingIDs_numericTermReturnsMatchingID() : void
    {
        $record = $this->createTestDBRecord('Alpha Item', 'alpha-item');

        $lookup = new TestLookupItem();
        $ids = $lookup->findMatchingIDs(array((string)$record->getID()));

        $this->assertContains($record->getID(), $ids);
    }

    public function test_findMatchingIDs_numericTermForNonExistentIDReturnsEmpty() : void
    {
        $lookup = new TestLookupItem();
        $ids = $lookup->findMatchingIDs(array('999991'));

        $this->assertEmpty($ids);
    }

    // endregion

    // region: findMatchingIDs — string term

    public function test_findMatchingIDs_stringTermReturnsMatchingIDs() : void
    {
        $record1 = $this->createTestDBRecord('Fruit Apple', 'fruit-apple');
        $record2 = $this->createTestDBRecord('Fruit Mango', 'fruit-mango');
        $record3 = $this->createTestDBRecord('Unrelated Record', 'unrelated-record');

        $lookup = new TestLookupItem();
        $ids = $lookup->findMatchingIDs(array('Fruit'));

        $this->assertContains($record1->getID(), $ids);
        $this->assertContains($record2->getID(), $ids);
        $this->assertNotContains($record3->getID(), $ids);
    }

    public function test_findMatchingIDs_returnsIntegers() : void
    {
        $record = $this->createTestDBRecord('Integer Check Item', 'integer-check-item');

        $lookup = new TestLookupItem();
        $ids = $lookup->findMatchingIDs(array('Integer Check'));

        $this->assertNotEmpty($ids);
        $this->assertContains($record->getID(), $ids);
    }

    // endregion

    // region: setLimit

    public function test_setLimit_capsResultCount() : void
    {
        for($i = 1; $i <= 5; $i++)
        {
            $this->createTestDBRecord('LimitItem '.$i, 'limit-item-'.$i);
        }

        $lookup = new TestLookupItem();
        $lookup->setLimit(3);

        $ids = $lookup->findMatchingIDs(array('LimitItem'));

        $this->assertCount(3, $ids);
    }

    public function test_setLimit_returnsFluentInterface() : void
    {
        $lookup = new TestLookupItem();

        $this->assertSame($lookup, $lookup->setLimit(10));
    }

    public function test_setLimit_zeroMeansNoLimit() : void
    {
        for($i = 1; $i <= 4; $i++)
        {
            $this->createTestDBRecord('NoLimit Item '.$i, 'no-limit-item-'.$i);
        }

        $lookup = new TestLookupItem();
        $lookup->setLimit(0);

        $ids = $lookup->findMatchingIDs(array('NoLimit Item'));

        $this->assertCount(4, $ids);
    }

    // endregion

    // region: addWhere

    public function test_addWhere_constrainsSearchResults() : void
    {
        $record1 = $this->createTestDBRecord('Widget Alpha', 'where-widget-a');
        $record2 = $this->createTestDBRecord('Widget Beta', 'where-widget-b');

        $lookup = new TestLookupItem();
        $lookup->addWhere("main_tbl.`alias` = 'where-widget-a'");

        $ids = $lookup->findMatchingIDs(array('Widget'));

        $this->assertContains($record1->getID(), $ids);
        $this->assertNotContains($record2->getID(), $ids);
    }

    public function test_addWhere_returnsFluentInterface() : void
    {
        $lookup = new TestLookupItem();

        $this->assertSame($lookup, $lookup->addWhere("1=1"));
    }

    // endregion

    // region: findMatches — backward compatibility

    public function test_findMatches_backwardCompatibility() : void
    {
        $record1 = $this->createTestDBRecord('Compat Widget', 'compat-widget-1');
        $record2 = $this->createTestDBRecord('Compat Widget', 'compat-widget-2');
        $this->createTestDBRecord('Unrelated Record', 'compat-unrelated');

        $lookup = new TestLookupItem();
        $lookup->findMatches(array('Compat Widget'));

        $results = $lookup->getResults();

        $resultIDs = array();
        foreach($results as $result)
        {
            // Identify results by label (both records share the same label)
            $this->assertSame('Compat Widget', $result->getLabel());
            $this->assertSame('#test', $result->getURL());
        }

        $this->assertGreaterThanOrEqual(2, count($results));

        // Verify IDs independently using findMatchingIDs to cross-check
        $lookup2 = new TestLookupItem();
        $ids = $lookup2->findMatchingIDs(array('Compat Widget'));

        $this->assertContains($record1->getID(), $ids);
        $this->assertContains($record2->getID(), $ids);
        $this->assertCount(count($results), $ids, 'findMatches() and findMatchingIDs() must return the same number of results.');
    }

    // endregion
}
