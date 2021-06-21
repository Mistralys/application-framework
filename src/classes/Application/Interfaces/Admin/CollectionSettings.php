<?php
/**
 * File containing the interface {@see Application_Interfaces_Admin_CollectionSettings}.
 *
 * @package Application
 * @subpackage Admin
 * @see Application_Interfaces_Admin_CollectionSettings
 */

declare(strict_types=1);

/**
 * Interface for admin screens that display a settings form for
 * a DB item collection record.
 *
 * @package Application
 * @subpackage Admin
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 *
 * @see Application_Traits_Admin_CollectionSettings
 */
interface Application_Interfaces_Admin_CollectionSettings extends Application_Admin_ScreenInterface
{
    const ERROR_UNKNOWN_SETTING_KEY = 17901;
    const ERROR_MISSING_REQUIRED_METHOD = 17902;

    /**
     * @return DBHelper_BaseCollection
     */
    public function createCollection();

    /**
     * @return string
     */
    public function getBackOrCancelURL() : string;

    /**
     * Retrieves the name of the HTML form tag.
     *
     * @return string
     */
    public function getFormName() : string;

    /**
     * The URL to redirect to once the record has been created.
     *
     * @param DBHelper_BaseRecord $record
     * @return string
     */
    public function getSuccessURL(DBHelper_BaseRecord $record) : string;

    /**
     * @param DBHelper_BaseRecord $record
     * @return string
     */
    public function getSuccessMessage(DBHelper_BaseRecord $record) : string;

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
    public function getSettingsKeyNames() : array;

    /**
     * @return Application_Formable_RecordSettings|NULL
     */
    public function getSettingsManager();

    public function isEditMode() : bool;

    public function getDeleteScreen() : ?Application_Admin_ScreenInterface;

    /**
     * @return array<string,mixed>
     */
    public function getDefaultFormValues() : array;

    public function getDeleteConfirmMessage() : string;
}
