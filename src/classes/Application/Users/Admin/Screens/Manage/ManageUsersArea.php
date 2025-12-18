<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Manage;

use Application\Admin\BaseArea;
use Application\Users\Admin\Screens\Manage\Mode\ListMode;
use Application\Users\Admin\UserAdminScreenRights;
use UI;
use UI_Icon;

class ManageUsersArea extends BaseArea
{
    public const string URL_NAME = 'users';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getNavigationTitle(): string
    {
        return t('Users');
    }

    public function getNavigationIcon(): ?UI_Icon
    {
        return UI::icon()->users();
    }

    public function getTitle(): string
    {
        return t('Users');
    }

    public function getDefaultMode(): string
    {
        return ListMode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return ListMode::class;
    }

    public function getNavigationGroup(): string
    {
        return t('Manage');
    }

    public function getDependencies(): array
    {
        return array();
    }

    public function isCore(): bool
    {
        return true;
    }

    public function getRequiredRight(): string
    {
        return UserAdminScreenRights::SCREEN_AREA;
    }

    protected function _handleHelp(): void
    {
        $this->renderer->getTitle()->setIcon(UI::icon()->users());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendArea($this);
    }
}
