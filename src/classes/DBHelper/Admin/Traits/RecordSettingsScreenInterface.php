<?php
/**
 * File containing the interface {@see RecordSettingsScreenInterface}.
 *
 * @package Application
 * @subpackage Admin
 * @see RecordSettingsScreenInterface
 */

declare(strict_types=1);

namespace DBHelper\Admin\Traits;

use Application\Interfaces\Admin\AdminScreenInterface;
use Application_Formable_RecordSettings;use DBHelper\Admin\Traits\RecordSettingsScreenTrait;use DBHelper_BaseCollection;use DBHelper_BaseRecord;

/**
 * Interface for admin screens that display a settings form for
 * a DB item collection record.
 *
 * Supports both creating new records and editing existing ones.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see RecordSettingsScreenTrait
 */
interface RecordSettingsScreenInterface extends AdminScreenInterface
{
    public const int ERROR_UNKNOWN_SETTING_KEY = 17901;
    public const int ERROR_MISSING_REQUIRED_METHOD = 17902;

    /**
     * @return DBHelper_BaseCollection
     */
    public function createCollection();

    /**
     * @return string
     */
    public function getBackOrCancelURL(): string;

    /**
     * Retrieves the name of the HTML form tag.
     *
     * @return string
     */
    public function getFormName(): string;

    /**
     * The URL to redirect to once the record has been created.
     *
     * @param DBHelper_BaseRecord $record
     * @return string
     */
    public function getSuccessURL(DBHelper_BaseRecord $record): string;

    /**
     * @param DBHelper_BaseRecord $record
     * @return string
     */
    public function getSuccessMessage(DBHelper_BaseRecord $record): string;

    /**
     * Retrieves the form values to use once the form has been
     * submitted and validated.
     *
     * @return array<string,mixed>
     */
    public function getSettingsFormValues();

    /**
     * @return string[]
     */
    public function getSettingsKeyNames(): array;

    /**
     * @return Application_Formable_RecordSettings|NULL
     */
    public function getSettingsManager();

    public function isEditMode(): bool;

    public function getDeleteScreen(): ?AdminScreenInterface;

    /**
     * @return array<string,mixed>
     */
    public function getDefaultFormValues(): array;

    public function getDeleteConfirmMessage(): string;

    public function isUserAllowedEditing(): bool;
}
