<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Submode;

use Application\AppFactory;
use Application\Users\Admin\UserAdminScreenRights;
use Application_Users;
use Application_Users_User;
use AppUtils\ConvertHelper;
use DBHelper\Admin\Screens\Submode\BaseRecordSubmode;
use UI\AdminURLs\AdminURLInterface;
use UI_PropertiesGrid;
use UI_Themes_Theme_ContentRenderer;

/**
 * @method Application_Users_User getRecord()
 */
abstract class BaseUserStatusSubmode extends BaseRecordSubmode
{
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

    protected function createCollection() : Application_Users
    {
        return AppFactory::createUsers();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getDefaultAction(): string
    {
        return '';
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->getNavigationTitle())
            ->makeLinked($this->getRecord()->adminURL()->status());
    }

    protected function _renderContent() : UI_Themes_Theme_ContentRenderer
    {
        return $this->renderer
            ->appendContent($this->createPropertiesGrid())
            ->makeWithSidebar();
    }

    private function createPropertiesGrid() : UI_PropertiesGrid
    {
        $grid = $this->getUI()->createPropertiesGrid();

        $user = $this->getRecord();

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

        $grid->add(t('UI Locale'), $this->getRecord()->getUILocale()->getLabel());

        return $grid;
    }
}
