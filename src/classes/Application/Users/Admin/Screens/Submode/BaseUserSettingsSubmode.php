<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Submode;

use Application\AppFactory;
use Application\Traits\AllowableMigrationTrait;
use Application\Users\Admin\UserAdminScreenRights;
use Application\Users\UsersSettingsManager;
use Application_Admin_Area_Mode_Submode_CollectionEdit;
use Application_Users;
use Application_Users_User;
use AppUtils\ClassHelper;
use DBHelper\Interfaces\DBHelperRecordInterface;
use DBHelper_BaseRecord;

/**
 * @property Application_Users_User $record
 */
abstract class BaseUserSettingsSubmode extends Application_Admin_Area_Mode_Submode_CollectionEdit
{
    use AllowableMigrationTrait;

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

    public function createCollection() : Application_Users
    {
        return AppFactory::createUsers();
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

    public function getBackOrCancelURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }
}
