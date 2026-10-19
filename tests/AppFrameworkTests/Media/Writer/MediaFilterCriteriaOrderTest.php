<?php

declare(strict_types=1);

namespace AppFrameworkTests\Media\Writer;

use Application\Application;
use Application\AppFactory;
use Application\Interfaces\FilterCriteriaInterface;
use Application\Media\Collection\MediaCollection;
use Application\Media\Writer\MediaDocumentWriter;
use Application\Media\Writer\MediaFileContent;
use DBHelper;
use Mistralys\AppFrameworkTests\TestClasses\MediaTestCase;

/**
 * Covers {@see \Application\Media\Collection\MediaFilterCriteria::buildOrderby()}:
 * several records sharing the same value for the order field must be
 * tie-broken by the primary key, in the order direction, so paging stays
 * stable, duplicate-free and gap-free.
 */
final class MediaFilterCriteriaOrderTest extends MediaTestCase
{
    private static string $pngBytes;

    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        self::$pngBytes = (string)file_get_contents(__DIR__.'/../../../files/Media/example-image.png');
    }

    /**
     * @return int[] The IDs, in creation order.
     */
    private function createRecordsWithIdenticalDateAdded(int $count) : array
    {
        $writer = new MediaDocumentWriter();
        $ids = array();

        for($i = 0; $i < $count; $i++) {
            $record = $writer->create(
                MediaFileContent::fromBinary('tie-'.$i.'.png', self::$pngBytes),
                'Tie Doc '.$i,
                Application::getUser()
            );

            $this->testMedia[] = $record->getMediaDocument();
            $ids[] = $record->getID();
        }

        // Force every record to share the same order-field value, since real
        // creation timestamps are usually distinct even a few milliseconds
        // apart, and the tie-breaker is specifically for the case where they
        // are not.
        DBHelper::update(
            "UPDATE `".MediaCollection::TABLE_NAME."` ".
            "SET `".MediaCollection::COL_DATE_ADDED."`=:date ".
            "WHERE `".MediaCollection::PRIMARY_NAME."` IN (".implode(',', $ids).")",
            array(':date' => '2024-01-01 00:00:00')
        );

        return $ids;
    }

    /**
     * @param int[] $ids
     * @param int[] $resultIDs
     * @return int[]
     */
    private function filterToOwnIDs(array $ids, array $resultIDs) : array
    {
        return array_values(array_filter($resultIDs, static fn(int $id) => in_array($id, $ids, true)));
    }

    public function test_tiebreakerOrdersAscendingByPrimaryKey() : void
    {
        $ids = $this->createRecordsWithIdenticalDateAdded(3);

        $resultIDs = $this->filterToOwnIDs(
            $ids,
            AppFactory::createMediaCollection()
                ->getFilterCriteria()
                ->setOrderBy(MediaCollection::COL_DATE_ADDED, FilterCriteriaInterface::ORDER_DIR_ASCENDING)
                ->getIDs()
        );

        $this->assertSame($ids, $resultIDs);
    }

    public function test_tiebreakerOrdersDescendingByPrimaryKey() : void
    {
        $ids = $this->createRecordsWithIdenticalDateAdded(3);

        $resultIDs = $this->filterToOwnIDs(
            $ids,
            AppFactory::createMediaCollection()
                ->getFilterCriteria()
                ->setOrderBy(MediaCollection::COL_DATE_ADDED, FilterCriteriaInterface::ORDER_DIR_DESCENDING)
                ->getIDs()
        );

        $this->assertSame(array_reverse($ids), $resultIDs);
    }

    public function test_noDuplicatesOrGapsAcrossIdenticalValues() : void
    {
        $ids = $this->createRecordsWithIdenticalDateAdded(5);

        $resultIDs = $this->filterToOwnIDs(
            $ids,
            AppFactory::createMediaCollection()
                ->getFilterCriteria()
                ->setOrderBy(MediaCollection::COL_DATE_ADDED)
                ->getIDs()
        );

        $this->assertCount(count($ids), $resultIDs, 'No record must be duplicated or missing from the ordered result.');
        $this->assertSame($ids, $resultIDs);
    }

    public function test_orderingDirectlyByPrimaryKeyDoesNotDuplicateTiebreaker() : void
    {
        $ids = $this->createRecordsWithIdenticalDateAdded(3);

        // Ordering directly by the primary key must not append a second,
        // redundant primary-key clause.
        $resultIDs = $this->filterToOwnIDs(
            $ids,
            AppFactory::createMediaCollection()
                ->getFilterCriteria()
                ->setOrderBy(MediaCollection::PRIMARY_NAME, FilterCriteriaInterface::ORDER_DIR_ASCENDING)
                ->getIDs()
        );

        $this->assertSame($ids, $resultIDs);
    }
}
