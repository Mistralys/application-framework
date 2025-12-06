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
use Application_Formable_RecordSettings;
use DBHelper\BaseCollection\DBHelperCollectionInterface;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;

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

    public function createCollection() : DBHelperCollectionInterface;

    public function getBackOrCancelURL(): string|AdminURLInterface;

    /**
     * Retrieves the name of the HTML form tag.
     *
     * @return string
     */
    public function getFormName(): string;

    /**
     * The URL to redirect to once the record has been created.
     *
     * @param DBHelperRecordInterface $record
     * @return string|AdminURLInterface
     */
    public function getSuccessURL(DBHelperRecordInterface $record): string|AdminURLInterface;

    /**
     * @param DBHelperRecordInterface $record
     * @return string
     */
    public function getSuccessMessage(DBHelperRecordInterface $record): string;

    /**
     * Retrieves the form values to use once the form has been
     * submitted and validated.
     *
     * @return array<string,mixed>
     */
    public function getSettingsFormValues() : array;

    /**
     * @return string[]
     */
    public function getSettingsKeyNames(): array;

    /**
     * @return Application_Formable_RecordSettings|NULL
     */
    public function getSettingsManager() : ?Application_Formable_RecordSettings;

    public function isEditMode(): bool;

    public function getDeleteScreen(): ?AdminScreenInterface;

    /**
     * @return array<string,mixed>
     */
    public function getDefaultFormValues(): array;

    public function getDeleteConfirmMessage(): string;

    public function isUserAllowedEditing(): bool;
}
