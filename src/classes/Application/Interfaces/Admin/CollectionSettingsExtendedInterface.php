<?php

declare(strict_types=1);

namespace Application\Interfaces\Admin;

use Application_Formable_RecordSettings_Extended;
use Application_Interfaces_Admin_CollectionSettings;

interface CollectionSettingsExtendedInterface extends Application_Interfaces_Admin_CollectionSettings
{
    public function isUserAllowedEditing() : bool;

    /**
     * @return Application_Formable_RecordSettings_Extended
     */
    public function getSettingsManager();

    /**
     * Whether the record can be edited at all,
     * independently of the user's rights. The
     * form will be frozen if it is not.
     *
     * @return bool
     */
    public function isEditable() : bool;
}
