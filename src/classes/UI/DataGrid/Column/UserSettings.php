<?php

declare(strict_types=1);

use AppUtils\ConvertHelper;

class UI_DataGrid_Column_UserSettings
{
    public const USER_SETTING_HIDDEN = 'hidden';

    /**
     * @var UI_DataGrid_Column
     */
    private $column;

    public function __construct(UI_DataGrid_Column $column)
    {
        $this->column = $column;
    }

    public function setHiddenForUser(bool $hidden, ?Application_User $user=null) : UI_DataGrid_Column_UserSettings
    {
        $this->setSetting(
            self::USER_SETTING_HIDDEN,
            ConvertHelper::boolStrict2string($hidden),
            $user
        );

        return $this;
    }

    public function isHiddenForUser(?Application_User $user=null) : bool
    {
        return $this->getSetting(self::USER_SETTING_HIDDEN, $user) === 'true';
    }

    public function setSetting(string $name, string $value, ?Application_User $user) : UI_DataGrid_Column_UserSettings
    {
        if($user === null)
        {
            $user = Application::getUser();
        }

        $user->setSetting($this->resolveSettingName($name), $value);

        return $this;
    }

    private function resolveSettingName(string $name) : string
    {
        return sprintf(
            'grid-%s-%s-%s',
            $this->column->getDataGrid()->getID(),
            $this->column->getDataKey(),
            $name
        );
    }

    public function getSetting(string $name, ?Application_User $user) : string
    {
        if($user === null)
        {
            $user = Application::getUser();
        }

        return $user->getSetting($this->resolveSettingName($name));
    }
}
