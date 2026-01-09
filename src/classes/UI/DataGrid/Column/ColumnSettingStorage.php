<?php
/**
 * @package User Interface
 * @subpackage Data Grids
 */

declare(strict_types=1);

use Application\Application;
use AppUtils\ConvertHelper;

/**
 * Specialized data grid column storage class used
 * to store column settings for a specific user.
 *
 * It uses the user settings to store the column settings,
 * using data key prefixes based on the column and grid IDs.
 *
 * @package User Interface
 * @subpackage Data Grids
 */
class ColumnSettingStorage
{
    public const SETTING_HIDDEN = 'hidden';
    public const SETTING_ORDER = 'order';

    private UI_DataGrid_Column $column;

    public function __construct(UI_DataGrid_Column $column)
    {
        $this->column = $column;
    }

    public function setHiddenForUser(bool $hidden, ?Application_User $user=null) : ColumnSettingStorage
    {
        $this->setSetting(
            self::SETTING_HIDDEN,
            ConvertHelper::boolStrict2string($hidden),
            $user
        );

        return $this;
    }

    public function isHiddenForUser(?Application_User $user=null) : bool
    {
        return $this->getSetting(self::SETTING_HIDDEN, $user) === 'true';
    }

    public function setSetting(string $name, string $value, ?Application_User $user) : ColumnSettingStorage
    {
        if($user === null) {
            $user = Application::getUser();
        }

        $user->setSetting($this->resolveSettingName($name), $value);
        $user->saveSettings();

        return $this;
    }

    private function resolveSettingName(string $name) : string
    {
        $parts = array(
            trim($this->column->getDataGrid()->resolveSettingName(''), UI_DataGrid::SETTING_SEPARATOR),
            $this->column->getDataKey(),
            $name
        );

        return implode(UI_DataGrid::SETTING_SEPARATOR, $parts);
    }

    public function getSetting(string $name, ?Application_User $user) : string
    {
        if($user === null)
        {
            $user = Application::getUser();
        }

        return $user->getSetting($this->resolveSettingName($name));
    }

    public function setOrder(int $order, ?Application_User $user=null) : self
    {
        $this->setSetting(self::SETTING_ORDER, (string)$order, $user);
        return $this;
    }

    public function getOrder(?Application_User $user=null) : int
    {
        $order = $this->getSetting(self::SETTING_ORDER, $user);
        if($order !== '') {
            return (int)$order;
        }

        return $this->column->getNumber();
    }
}
