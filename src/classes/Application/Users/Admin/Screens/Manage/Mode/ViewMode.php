<?php

declare(strict_types=1);

namespace Application\Users\Admin\Screens\Manage\Mode;

use Application\Users\Admin\Screens\Manage\Mode\View\StatusSubmode;
use Application\Users\Admin\Traits\ManageModeInterface;
use Application\Users\Admin\Traits\ManageModeTrait;
use Application\Users\Admin\UserAdminScreenRights;
use Application_Users_User;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;

/**
 * @method Application_Users_User getRecord()
 * @property Application_Users_User $record
 */
class ViewMode extends BaseRecordMode implements ManageModeInterface
{
    use ManageModeTrait;

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

    public function getDefaultSubmode(): string
    {
        return StatusSubmode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): string
    {
        return StatusSubmode::class;
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->addURL(t('Status'), $this->record->adminURL()->status())
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(t('Settings'), $this->record->adminURL()->settings())
            ->setIcon(UI::icon()->settings());
    }

    protected function _handleHelp(): void
    {
        $this->renderer->setTitle($this->record->getLabel());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->adminURL()->base());
    }
}
