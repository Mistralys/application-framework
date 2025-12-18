<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Manage\Mode\View;

use Application\Users\Admin\Traits\ViewSubmodeInterface;
use Application\Users\Admin\Traits\ViewSubmodeTrait;
use Application\Users\Admin\UserAdminScreenRights;
use Application_Users_User;
use AppUtils\ClassHelper;
use AppUtils\ConvertHelper;
use DBHelper\Admin\Screens\Submode\BaseRecordStatusSubmode;
use DBHelper\Interfaces\DBHelperRecordInterface;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;

/**
 * @property Application_Users_User $record
 */
class StatusSubmode extends BaseRecordStatusSubmode implements ViewSubmodeInterface
{
    use ViewSubmodeTrait;

    public const string URL_NAME = 'status';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Status');
    }

    public function getTitle(): string
    {
        return t('User Status');
    }

    public function getRequiredRight(): string
    {
        return UserAdminScreenRights::SCREEN_VIEW_STATUS;
    }

    public function getRecordStatusURL(): AdminURLInterface
    {
        return $this->record->adminURL()->status();
    }

    protected function _populateGrid(UI_PropertiesGrid $grid, DBHelperRecordInterface $record): void
    {
        $user = ClassHelper::requireObjectInstanceOf(
            Application_Users_User::class,
            $record
        );

        $grid->add(t('ID'), sb()->codeCopy($user->getID()));
        $grid->add(t('Display Name'), $user->getLabel());
        $grid->add(t('Email'), $user->getEmail());
        $grid->add(t('First Name'), $user->getFirstName());
        $grid->add(t('Last Name'), $user->getLastName());
        $grid->add(t('Nickname'), $user->getNickname());
        $grid->add(t('Date registered'), ConvertHelper::date2listLabel($user->getDateRegistered(), true));

        $grid->addHeader(t('Foreign identity'));

        $grid->add(t('Foreign ID'), $user->getForeignID());
        $grid->add(t('Foreign Nickname'), $user->getForeignNickname());

        $grid->addHeader(t('Settings'));

        $grid->add(t('UI Locale'), $user->getUILocale()->getLabel());
    }
}
