<?php

declare(strict_types=1);

namespace Application\Tags\Admin\Screens\Mode;

use Application\Tags\Admin\Screens\Mode\View\TagTreeSubmode;
use Application\Tags\Admin\TagScreenRights;
use Application\Tags\Admin\Traits\TagModeInterface;
use Application\Tags\Admin\Traits\TagModeTrait;
use Application\Tags\TagRecord;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;

/**
 * @property TagRecord $record
 */
class ViewMode extends BaseRecordMode implements TagModeInterface
{
    use TagModeTrait;

    public const string URL_NAME = 'view-tag';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return TagScreenRights::SCREEN_VIEW;
    }

    public function getDefaultSubmode(): string
    {
        return TagTreeSubmode::URL_NAME;
    }

    public function getDefaultSubscreenClass(): ?string
    {
        return TagTreeSubmode::class;
    }

    public function getNavigationTitle(): string
    {
        return t('View');
    }

    public function getTitle(): string
    {
        return t('View a tag');
    }

    protected function _handleHelp(): void
    {
        $this->renderer
            ->getTitle()
            ->setText($this->record->getLabel())
            ->setIcon(UI::icon()->tags());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->getAdminURL());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->addURL(
            t('Tag tree'),
            $this->record->getAdminTagTreeURL()
        )
            ->setIcon(UI::icon()->tree());

        $this->subnav->addURL(
            t('Settings'),
            $this->record->getAdminSettingsURL()
        )
            ->setIcon(UI::icon()->settings());
    }

    public function getRecordMissingURL(): string
    {
        return $this->createCollection()->getAdminURL();
    }
}
