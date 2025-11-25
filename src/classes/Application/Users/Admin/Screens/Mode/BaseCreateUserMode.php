<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Mode;

use Application\AppFactory;
use Application\Traits\AllowableMigrationTrait;
use Application\Users\Admin\UserAdminScreenRights;
use Application\Users\UsersSettingsManager;
use Application_Admin_Area_Mode_CollectionCreate;
use Application_Users;
use Application_Users_User;
use AppUtils\ClassHelper;
use DBHelper\Interfaces\DBHelperRecordInterface;

abstract class BaseCreateUserMode extends Application_Admin_Area_Mode_CollectionCreate
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'create';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getTitle(): string
    {
        return t('Create a new user');
    }

    public function getRequiredRight(): string
    {
        return UserAdminScreenRights::SCREEN_CREATE;
    }

    public function createCollection() : Application_Users
    {
        return AppFactory::createUsers();
    }

    public function getSettingsManager() : UsersSettingsManager
    {
        return new UsersSettingsManager($this);
    }

    public function getSuccessMessage(DBHelperRecordInterface $record): string
    {
        $user = ClassHelper::requireObjectInstanceOf(
            Application_Users_User::class,
            $record
        );

        return t(
            'The user %1$s has been created successfully at %2$s.',
            $user->getName(),
            sb()->time()
        );
    }

    public function getBackOrCancelURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }
}
