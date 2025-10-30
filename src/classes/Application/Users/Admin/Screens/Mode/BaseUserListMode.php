<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Mode;

use Application\AppFactory;
use Application\Traits\AllowableMigrationTrait;
use Application\Users\Admin\UserAdminScreenRights;
use Application_Admin_Area_Mode_CollectionList;
use Application_Users;
use Application_Users_User;
use AppUtils\ClassHelper;
use DBHelper_BaseCollection;
use DBHelper_BaseFilterCriteria_Record;
use DBHelper_BaseRecord;
use UI;
use UI_DataGrid_Entry;

abstract class BaseUserListMode extends Application_Admin_Area_Mode_CollectionList
{
    use AllowableMigrationTrait;

    public const string URL_NAME = 'list';
    public const string COL_NAME = 'name';
    public const string COL_EMAIL = 'email';
    public const string COL_ID = 'id';
    public const string COL_FIRSTNAME = 'firstname';
    public const string COL_LASTNAME = 'lastname';
    public const string COL_NICKNAME = 'nickname';
    public const string COL_UI_LOCALE = 'ui_locale';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Overview');
    }

    public function getTitle(): string
    {
        return t('Available users');
    }

    public function getRequiredRight(): string
    {
        return UserAdminScreenRights::SCREEN_LIST;
    }

    /**
     * @return Application_Users
     */
    protected function createCollection(): DBHelper_BaseCollection
    {
        return AppFactory::createUsers();
    }

    protected function getEntryData(DBHelper_BaseRecord $record, DBHelper_BaseFilterCriteria_Record $entry) : UI_DataGrid_Entry
    {
        return $this->getUserData(ClassHelper::requireObjectInstanceOf(
            Application_Users_User::class,
            $record
        ));
    }

    private function getUserData(Application_Users_User $user) : UI_DataGrid_Entry
    {
        return $this->grid->createEntry(array(
            self::COL_ID => $user->getID(),
            self::COL_EMAIL => $user->getEmail(),
            self::COL_NAME => $user->getLabelLinked(),
            self::COL_FIRSTNAME => $user->getFirstName(),
            self::COL_LASTNAME => $user->getLastName(),
            self::COL_NICKNAME => $user->getNickname(),
            self::COL_UI_LOCALE => $user->getUILocale()->getLabel(),
        ));
    }

    protected function configureColumns(): void
    {
        $this->grid->addColumn(self::COL_ID, t('ID'))
            ->setCompact()
            ->setSortable(false, Application_Users::PRIMARY_NAME);

        $this->grid->addColumn(self::COL_NAME, t('Display name'));

        $this->grid->addColumn(self::COL_FIRSTNAME, t('First name'))
            ->setSortable(false, Application_Users::COL_FIRSTNAME);

        $this->grid->addColumn(self::COL_LASTNAME, t('Last name'))
            ->setSortable(false, Application_Users::COL_LASTNAME);

        $this->grid->addColumn(self::COL_NICKNAME, t('Nickname'))
            ->setSortable(false, Application_Users::COL_NICKNAME);

        $this->grid->addColumn(self::COL_EMAIL, t('Email'))
            ->setSortable(true, Application_Users::COL_EMAIL);

        $this->grid->addColumn(self::COL_UI_LOCALE, t('UI locale'));
    }

    protected function configureActions(): void
    {
    }

    public function getBackOrCancelURL(): string
    {
        return APP_URL;
    }

    protected function _handleSidebar(): void
    {
        $this->sidebar->addButton('create-user', t('Create new user'))
            ->setIcon(UI::icon()->add())
            ->link($this->createCollection()->adminURL()->create())
            ->requireRight(UserAdminScreenRights::SCREEN_LIST_CREATE);

        parent::_handleSidebar();
    }
}
