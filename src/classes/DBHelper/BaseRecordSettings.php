<?php
/**
 * @package DBHelper
 * @subpackage Collection
 */

declare(strict_types=1);

namespace DBHelper;

use Application_Formable_RecordSettings_Extended;
use Application_Formable_RecordSettings_ValueSet;
use Application_Interfaces_Formable;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * Base class for managing the form used to create or
 * edit DBHelper records.
 *
 * @package DBHelper
 * @subpackage Collection
 */
abstract class BaseRecordSettings extends Application_Formable_RecordSettings_Extended
{
    public function __construct(Application_Interfaces_Formable $formable, DBHelperCollectionInterface $collection, ?DBHelperRecordInterface $record = null)
    {
        parent::__construct($formable, $collection, $record);

        $this->setDefaultsUseStorageNames(true);
    }

    protected function processPostCreateSettings(DBHelperRecordInterface $record, Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
        // Not needed for DBHelper records
    }

    protected function getCreateData(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
        // Not needed for DBHelper records
    }

    protected function updateRecord(Application_Formable_RecordSettings_ValueSet $recordData, Application_Formable_RecordSettings_ValueSet $internalValues): void
    {
        // Not needed for DBHelper records
    }
}
