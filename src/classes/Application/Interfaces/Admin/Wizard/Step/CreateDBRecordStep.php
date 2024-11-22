<?php
/**
 * File containing the interface {@see Application_Interfaces_Admin_Wizard_CreateDBRecordStep}.
 *
 * @package Application
 * @subpackage Wizard
 * @see Application_Interfaces_Admin_Wizard_CreateDBRecordStep
 */

declare(strict_types=1);

/**
 * Wizard step trait interface: Create a DB record.
 *
 * @package Application
 * @subpackage Wizard
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_Wizard_CreateDBRecordStep
 */
interface Application_Interfaces_Admin_Wizard_CreateDBRecordStep extends Application_Interfaces_Admin_Wizard_Step
{
    public const ERROR_NO_RECORD_CREATED_YET = 93801;

    public const KEY_RECORD_ID = 'record_id';

    public function createCollection() : DBHelper_BaseCollection;

    public function createSettingsManager() : Application_Formable_RecordSettings_Extended;
}
