<?php

declare(strict_types=1);

namespace Application\Admin\Area\Media;

use Application\Admin\Area\Media\View\BaseMediaStatusScreen;
use Application\AppFactory;
use Application\Media\Collection\MediaCollection;
use Application\Media\Collection\MediaRecord;
use Application\Tags\TagCollection;
use Application_Admin_Area_Mode_CollectionRecord;
use UI;

/**
 * @property MediaRecord $record
 */
abstract class BaseViewMediaScreen extends Application_Admin_Area_Mode_CollectionRecord
{
    public const URL_NAME = 'view';

    public function getURLName(): string
    {
        return self::URL_NAME;
    }

    protected function createCollection() : MediaCollection
    {
        return AppFactory::createMediaCollection();
    }

    public function getRecordMissingURL(): string
    {
        return (string)$this->createCollection()->adminURL()->list();
    }

    public function getDefaultSubmode(): string
    {
        return BaseMediaStatusScreen::URL_NAME;
    }

    public function isUserAllowed(): bool
    {
        return $this->user->canViewMedia();
    }

    public function getNavigationTitle(): string
    {
        return t('Media file');
    }

    public function getTitle(): string
    {
        return t('Media file');
    }

    protected function _handleBreadcrumb(): void
    {
        $this->breadcrumb->appendItem($this->record->getLabel())
            ->makeLinked($this->record->adminURL()->view());
    }

    protected function _handleSubnavigation(): void
    {
        $this->subnav->clearItems();

        $this->subnav->addURL(
            t('Status'),
            $this->record->adminURL()->status()
        )
            ->setIcon(UI::icon()->status());

        $this->subnav->addURL(
            t('Tagging'),
            $this->record->adminURLTagging()
        )
            ->requireTrue(AppFactory::createMediaCollection()->isTaggingEnabled())
            ->setIcon(UI::icon()->tags());

        $this->subnav->addURL(
            t('Settings'),
            $this->record->adminURL()->settings()
        )
            ->setIcon(UI::icon()->settings());
    }

    protected function _handleHelp(): void
    {
        $this->user->getRecent()->getCategoryByAlias(MediaCollection::RECENT_ITEMS_CATEGORY)
            ->addEntry(
                MediaCollection::RECENT_ITEMS_CATEGORY.$this->record->getID(),
                (string)sb()
                    ->add($this->record->getMediaDocument()->getTypeIcon())
                    ->add($this->record->getLabel()),
                (string)$this->record->adminURL()->view()
            );

        $this->renderer
            ->getTitle()
            ->setText($this->record->getLabel())
            ->setIcon($this->record->getMediaDocument()->getTypeIcon());
    }
}
