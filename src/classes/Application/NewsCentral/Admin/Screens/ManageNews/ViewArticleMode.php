<?php

declare(strict_types=1);

namespace Application\NewsCentral\Admin\Screens\Mode;

use Application\AppFactory;
use Application\NewsCentral\Admin\NewsScreenRights;
use Application\NewsCentral\Admin\Screens\Mode\ViewArticle\BaseArticleStatusScreen;
use Application\NewsCentral\Admin\Traits\ManageNewsModeInterface;
use Application\NewsCentral\Admin\Traits\ManageNewsModeTrait;
use Application\NewsCentral\NewsCollection;
use DBHelper\Admin\Screens\Mode\BaseRecordMode;
use NewsCentral\Entries\NewsEntry;
use UI;
use UI\AdminURLs\AdminURLInterface;

/**
 * @property NewsEntry $record
 */
class ViewArticleMode extends BaseRecordMode implements ManageNewsModeInterface
{
    use ManageNewsModeTrait;

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

    public function getDefaultSubscreenClass(): string
    {
        return BaseArticleStatusScreen::class;
    }

    protected function createCollection() : NewsCollection
    {
        return AppFactory::createNews();
    }

    public function getRecordMissingURL(): AdminURLInterface
    {
        return $this->createCollection()->adminURL()->manage()->list();
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
                $this->record->adminURL()->base()
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
            ->makeLinked($this->record->adminURL()->base());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();

        $this->subnav->addURL(
            t('Status'),
            $this->record->adminURL()->status(),
        )
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(
            t('Settings'),
            $this->record->adminURL()->settings(),
        )
            ->setIcon(UI::icon()->settings());
    }

    public function getNewsEntry() : NewsEntry
    {
        return $this->record;
    }
}
