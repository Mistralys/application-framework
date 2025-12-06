<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Mode;

use Application\AppFactory;
use Application\Users\Admin\Screens\Submode\BaseUserStatusSubmode;
use Application\Users\Admin\UserAdminScreenRights;
use Application_Users;
use Application_Users_User;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;
use UI\AdminURLs\AdminURLInterface;

/**
 * @method Application_Users_User getRecord()
 */
abstract class BaseViewUserMode extends BaseRecordMode
{
    public const string URL_NAME = 'view';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('View');
    }

    public function getTitle(): string
    {
        return t('View user details');
    }

    public function getRequiredRight(): string
    {
        return UserAdminScreenRights::SCREEN_VIEW;
    }

    protected function createCollection() : Application_Users
    {
        return AppFactory::createUsers();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->list();
    }

    public function getDefaultSubmode(): string
    {
        return BaseUserStatusSubmode::URL_NAME;
    }

    protected function _handleSubnavigation(): void
    {
        $user = $this->getRecord();

        $this->subnav->addURL(t('Status'), $user->adminURL()->status())
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(t('Settings'), $user->adminURL()->settings())
            ->setIcon(UI::icon()->settings());
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->getRecord()->getLabel());
    }

    protected function _handleBreadcrumb(): void
    {
        $user = $this->getRecord();

        $this->breadcrumb->appendItem($user->getLabel())
            ->makeLinked($this->getRecord()->adminURL()->base());
    }
}
