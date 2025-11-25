<?php
/**
 * @package DBHelper
 * @subpackage Admin Screens
 */

declare(strict_types=1);

namespace DBHelper\Admin;

use Application\Collection\Admin\RecordSelectionTieInInterface;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Interface for admin-screen tie-in classes that
 * select a DB record from a short list.
 * See the class {@see BaseDBRecordSelectionTieIn} for
 * the implementation.
 *
 * @package DBHelper
 * @subpackage Admin Screens
 *
 * @method DBHelperRecordInterface[] getSelectableRecords()
 */
interface DBRecordSelectionTieInInterface extends RecordSelectionTieInInterface
{
    public function getCollection() : DBHelperCollectionInterface;

    public function getRecordID() : ?int;
}
