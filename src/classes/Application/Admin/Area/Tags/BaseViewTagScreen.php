<?php

declare(strict_types=1);

namespace Application\Area\Tags;

use Application\AppFactory;
use Application\Area\Tags\ViewTag\BaseTagTreeScreen;
use Application\Tags\TagRecord;
use Application_Admin_Area_Mode_CollectionRecord;
use Application\Area\Tags\ViewTag\BaseTagSettingsScreen;
use Application\Tags\TagCollection;
use UI;

/**
 * @property TagRecord $record
 */
abstract class BaseViewTagScreen extends Application_Admin_Area_Mode_CollectionRecord
{
    public const URL_NAME = 'view-tag';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getDefaultSubmode(): string
    {
        return BaseTagTreeScreen::URL_NAME;
    }

    public function isUserAllowed(): bool
    {
        return true;
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

    protected function createCollection() : TagCollection
    {
        return AppFactory::createTags();
    }

    public function getRecordMissingURL(): string
    {
        return $this->createCollection()->getAdminURL();
    }
}
