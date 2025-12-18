<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Manage\Mode\View;

use Application\Users\Admin\Traits\ViewSubmodeInterface;
use Application\Users\Admin\Traits\ViewSubmodeTrait;
use Application\Users\Admin\UserAdminScreenRights;
use Application\Users\UsersSettingsManager;
use Application_Users_User;
use AppUtils\ClassHelper;
use DBHelper\Admin\Screens\Submode\BaseRecordSettingsSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;

/**
 * @property Application_Users_User $record
 */
class SettingsSubmode extends BaseRecordSettingsSubmode implements ViewSubmodeInterface
{
    use ViewSubmodeTrait;

    public const string URL_NAME = 'settings';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('User settings');
    }

    public function getRequiredRight(): string
    {
        return UserAdminScreenRights::SCREEN_VIEW_SETTINGS;
    }

    public function getSettingsManager() : UsersSettingsManager
    {
        return new UsersSettingsManager($this, $this->record);
    }

    public function isUserAllowedEditing(): bool
    {
        return $this->getUser()->canEditUsers();
    }

    public function isEditable(): bool
    {
        return true;
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        return t(
            'The settings for user %1$s have been saved successfully at %2$s.',
            sb()->reference($this->resolveUser($record)->getLabel()),
            sb()->time()
        );
    }

    private function resolveUser(DBHelperRecordInterface $record) : Application_Users_User
    {
        return ClassHelper::requireObjectInstanceOf(
            Application_Users_User::class,
            $record
        );
    }
}
