<?php
/**
 * @package DBHelper
 * @subpackage Admin
 */

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

/**
 * Interface for screens that allow editing of a record.
 *
 * This is an extension of the interface {@see RecordSettingsScreenInterface}
 * with the difference that a record is required for editing.
 *
 * @package DBHelper
 * @subpackage Admin
 * @see RecordEditScreenTrait
 */
interface RecordEditScreenInterface extends RecordSettingsScreenInterface, RecordScreenInterface
{
    public function isUserAllowedEditing() : bool;

    /**
     * Whether the record can be edited at all.
     *
     * @return bool
     */
    public function isEditable() : bool;
}
