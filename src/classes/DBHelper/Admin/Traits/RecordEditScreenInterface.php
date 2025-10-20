<?php

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use Application_Interfaces_Admin_CollectionSettings;

/**
 * @see RecordEditScreenTrait
 */
interface RecordEditScreenInterface extends Application_Interfaces_Admin_CollectionSettings, CollectionRecordScreenInterface
{
    public function isUserAllowedEditing() : bool;

    /**
     * Whether the record can be edited at all.
     *
     * @return bool
     */
    public function isEditable() : bool;
}
