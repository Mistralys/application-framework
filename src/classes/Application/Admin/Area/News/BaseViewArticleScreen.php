<?php

declare(strict_types=1);

namespace Application\Admin\Area\News;

use Application\Admin\Area\News\ViewArticle\BaseArticleStatusScreen;
use Application\AppFactory;
use Application\NewsCentral\NewsCollection;
use Application\NewsCentral\NewsEntry;
use Application\NewsCentral\NewsScreenRights;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use UI;

/**
 * @property NewsEntry $record
 */
abstract class BaseViewArticleScreen extends BaseRecordMode
{
    public const string URL_NAME = 'view';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    public function getRequiredRight(): string
    {
        return NewsScreenRights::SCREEN_VIEW_ARTICLE;
    }

    public function getDefaultSubmode(): string
    {
        return BaseArticleStatusScreen::URL_NAME;
    }

    protected function createCollection() : NewsCollection
    {
        return AppFactory::createNews();
    }

    public function getRecordMissingURL(): string
    {
        return $this->createCollection()->getAdminListURL();
    }

    public function getNavigationTitle(): string
    {
        return t('View');
    }

    public function getTitle(): string
    {
        return t('View news entry');
    }

    protected function _handleHelp(): void
    {
        $this->user->getRecent()->getCategoryByAlias(NewsCollection::RECENT_ITEMS_CATEGORY)
            ->addEntry(
                NewsCollection::RECENT_ITEMS_CATEGORY.$this->record->getID(),
                (string)sb()
                    ->add($this->record->getLabel())
                    ->add('-')
                    ->add($this->record->getLocale()->getLabel()),
                $this->record->getAdminURL()
            );

        $type = $this->record->getType();

        $this->renderer
            ->getTitle()
            ->setText($this->record->getLabel())
            ->setIcon($type->getIcon())
            ->setSubline($type->getLabel())
            ->addBadge($this->record->getStatus()->getBadge())
            ->addBadge($this->record->getSchedulingBadge());
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->getAdminURL());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();

        $this->subnav->addURL(
            t('Status'),
            $this->record->getAdminStatusURL(),
        )
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(
            t('Settings'),
            $this->record->getAdminSettingsURL(),
        )
            ->setIcon(UI::icon()->settings());
    }

    public function getNewsEntry() : NewsEntry
    {
        return $this->record;
    }
}
