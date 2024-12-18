<?php
/**
 * @package DBHelper
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace DBHelper\Admin;

use Application\Interfaces\Admin\AdminScreenInterface;
use DBHelper_BaseCollection;
use DBHelper_BaseRecord;

/**
 * @package DBHelper
 * @subpackage Admin Screens
 */
interface RecordSelectionTieInInterface
{
    public const COMPACT_LIST_THRESHOLD = 10;

    public function getScreen() : AdminScreenInterface;
    public function getCollection() : DBHelper_BaseCollection;

    /**
     * Whether the record selection can require specific user rights.
     * This is used when displaying the empty selection screen,
     * to hint at the fact that rights may be missing.
     *
     * @return bool
     */
    public function isSelectionRightsBased() : bool;
    public function isRecordSelected(): bool;

    /**
     * Gets all records that may be selected
     * @return DBHelper_BaseRecord[]
     */
    public function getSelectableRecords() : array;
}
